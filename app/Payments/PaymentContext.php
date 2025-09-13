<?php

namespace App\Payments;

use Illuminate\Support\Facades\Log;

class PaymentContext
{
    private PaymentStrategyInterface $strategy;

    public function __construct(PaymentStrategyInterface $strategy = null)
    {
        if ($strategy) {
            $this->strategy = $strategy;
        }
    }

    /**
     * Get available payment strategies from config
     */
    private function getAvailableStrategies(): array
    {
        $configMethods = config('payment.methods', []);
        $strategies = [];

        foreach ($configMethods as $key => $config) {
            if ($config['enabled'] ?? true) {
                $strategies[$key] = $config['strategy'];
            }
        }

        // Sort by order if specified
        $orderedStrategies = [];
        foreach ($configMethods as $key => $config) {
            if (isset($strategies[$key])) {
                $orderedStrategies[$config['order'] ?? 999] = [$key => $strategies[$key]];
            }
        }
        ksort($orderedStrategies);

        $result = [];
        foreach ($orderedStrategies as $group) {
            $result = array_merge($result, $group);
        }

        return $result ?: $strategies;
    }

    /**
     * Set payment strategy by method name
     */
    public function setStrategyByMethod(string $paymentMethod): self
    {
        $availableStrategies = $this->getAvailableStrategies();
        
        if (!isset($availableStrategies[$paymentMethod])) {
            throw new \InvalidArgumentException("Unsupported payment method: {$paymentMethod}");
        }

        $strategyClass = $availableStrategies[$paymentMethod];
        $this->strategy = new $strategyClass();

        return $this;
    }

    /**
     * Set payment strategy directly
     */
    public function setStrategy(PaymentStrategyInterface $strategy): self
    {
        $this->strategy = $strategy;
        return $this;
    }

    /**
     * Get current strategy
     */
    public function getStrategy(): PaymentStrategyInterface
    {
        if (!isset($this->strategy)) {
            throw new \RuntimeException('Payment strategy not set');
        }

        return $this->strategy;
    }

    /**
     * Process payment using current strategy
     */
    public function processPayment(float $amount, array $paymentData = []): array
    {
        if (!isset($this->strategy)) {
            return [
                'success' => false,
                'message' => 'Payment strategy not set',
                'payment_status' => 'failed'
            ];
        }

        Log::info('Processing payment with strategy', [
            'strategy' => get_class($this->strategy),
            'amount' => $amount,
            'payment_method' => $this->strategy->getPaymentMethodName()
        ]);

        try {
            return $this->strategy->processPayment($amount, $paymentData);
        } catch (\Exception $e) {
            Log::error('Payment processing failed', [
                'strategy' => get_class($this->strategy),
                'amount' => $amount,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'message' => 'Payment processing failed: ' . $e->getMessage(),
                'payment_status' => 'failed',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Validate payment data using current strategy
     */
    public function validatePaymentData(array $paymentData): array
    {
        if (!isset($this->strategy)) {
            return [
                'valid' => false,
                'errors' => ['Payment strategy not set']
            ];
        }

        return $this->strategy->validatePaymentData($paymentData);
    }

    /**
     * Get payment method name from current strategy
     */
    public function getPaymentMethodName(): string
    {
        if (!isset($this->strategy)) {
            return 'Unknown';
        }

        return $this->strategy->getPaymentMethodName();
    }

    /**
     * Get all available payment methods with enhanced details from config
     */
    public function getAvailablePaymentMethods(): array
    {
        $availableStrategies = $this->getAvailableStrategies();
        $methods = [];
        
        foreach ($availableStrategies as $key => $strategyClass) {
            try {
                $strategy = new $strategyClass();
                $methods[$key] = [
                    'key' => $key,
                    'name' => $strategy->getPaymentMethodName(),
                    'icon' => $strategy->getIcon(),
                    'description' => $strategy->getDescription(),
                    'form_fields' => $strategy->getFormFields(),
                    'validation_rules' => $strategy->getClientValidationRules(),
                    'class' => $strategyClass
                ];
            } catch (\Exception $e) {
                Log::error("Error loading payment strategy: {$strategyClass}", [
                    'error' => $e->getMessage()
                ]);
                // Skip this payment method if it can't be loaded
                continue;
            }
        }

        return $methods;
    }

    /**
     * Check if a payment method is available
     */
    public function isPaymentMethodAvailable(string $paymentMethod): bool
    {
        $availableStrategies = $this->getAvailableStrategies();
        return isset($availableStrategies[$paymentMethod]);
    }

    /**
     * Add a new payment strategy dynamically
     */
    public function addPaymentStrategy(string $methodKey, string $strategyClass): self
    {
        if (!is_subclass_of($strategyClass, PaymentStrategyInterface::class)) {
            throw new \InvalidArgumentException("Strategy class must implement PaymentStrategyInterface");
        }

        // This would require updating the config or using a runtime registry
        // For now, we'll log this action
        Log::info("Dynamic payment strategy registration attempted", [
            'method' => $methodKey,
            'strategy' => $strategyClass
        ]);

        return $this;
    }

    /**
     * Get payment method configuration from config file
     */
    public function getPaymentMethodConfig(string $paymentMethod): array
    {
        return config("payment.methods.{$paymentMethod}", []);
    }

    /**
     * Get general payment settings
     */
    public function getPaymentSettings(): array
    {
        return config('payment.settings', []);
    }

    /**
     * Check if payment method has specific constraints
     */
    public function validatePaymentConstraints(string $paymentMethod, float $amount): array
    {
        $config = $this->getPaymentMethodConfig($paymentMethod);
        $errors = [];

        // Check maximum amount for cash payments
        if ($paymentMethod === 'cash' && isset($config['max_amount'])) {
            if ($amount > $config['max_amount']) {
                $errors[] = "Cash payments are limited to {$config['max_amount']}";
            }
        }

        // Add more constraint checks here as needed

        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }
}