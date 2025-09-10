<?php

namespace App\Payments;

use Illuminate\Support\Facades\Log;
use App\Payments\CreditCardPaymentStrategy;
use App\Payments\PayPalPaymentStrategy;
use App\Payments\BankTransferPaymentStrategy;
use App\Payments\CashPaymentStrategy;

class PaymentContext
{
    private PaymentStrategyInterface $strategy;
    
    /**
     * Available payment strategies
     */
    private array $availableStrategies = [
        'cash' => CashPaymentStrategy::class,
        'credit_card' => CreditCardPaymentStrategy::class,
        'paypal' => PayPalPaymentStrategy::class,
        'bank_transfer' => BankTransferPaymentStrategy::class,
    ];

    public function __construct(PaymentStrategyInterface $strategy = null)
    {
        if ($strategy) {
            $this->strategy = $strategy;
        }
    }

    /**
     * Set payment strategy by method name
     *
     * @param string $paymentMethod
     * @return self
     * @throws \InvalidArgumentException
     */
    public function setStrategyByMethod(string $paymentMethod): self
    {
        if (!isset($this->availableStrategies[$paymentMethod])) {
            throw new \InvalidArgumentException("Unsupported payment method: {$paymentMethod}");
        }

        $strategyClass = $this->availableStrategies[$paymentMethod];
        $this->strategy = new $strategyClass();

        return $this;
    }

    /**
     * Set payment strategy directly
     *
     * @param PaymentStrategyInterface $strategy
     * @return self
     */
    public function setStrategy(PaymentStrategyInterface $strategy): self
    {
        $this->strategy = $strategy;
        return $this;
    }

    /**
     * Get current strategy
     *
     * @return PaymentStrategyInterface
     * @throws \RuntimeException
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
     *
     * @param float $amount
     * @param array $paymentData
     * @return array
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
     *
     * @param array $paymentData
     * @return array
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
     *
     * @return string
     */
    public function getPaymentMethodName(): string
    {
        if (!isset($this->strategy)) {
            return 'Unknown';
        }

        return $this->strategy->getPaymentMethodName();
    }

    /**
     * Get all available payment methods
     *
     * @return array
     */
    public function getAvailablePaymentMethods(): array
    {
        $methods = [];
        
        foreach ($this->availableStrategies as $key => $strategyClass) {
            $strategy = new $strategyClass();
            $methods[$key] = [
                'key' => $key,
                'name' => $strategy->getPaymentMethodName(),
                'class' => $strategyClass
            ];
        }

        return $methods;
    }

    /**
     * Check if a payment method is available
     *
     * @param string $paymentMethod
     * @return bool
     */
    public function isPaymentMethodAvailable(string $paymentMethod): bool
    {
        return isset($this->availableStrategies[$paymentMethod]);
    }

    /**
     * Add a new payment strategy
     *
     * @param string $methodKey
     * @param string $strategyClass
     * @return self
     * @throws \InvalidArgumentException
     */
    public function addPaymentStrategy(string $methodKey, string $strategyClass): self
    {
        if (!is_subclass_of($strategyClass, PaymentStrategyInterface::class)) {
            throw new \InvalidArgumentException("Strategy class must implement PaymentStrategyInterface");
        }

        $this->availableStrategies[$methodKey] = $strategyClass;
        return $this;
    }
}