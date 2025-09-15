<?php


namespace App\Security;

class InputSanitizer
{
    public function sanitizeSearchInput(string $search): string
    {
        // Remove SQL injection patterns
        $search = preg_replace('/(\%27)|(\')|(\-\-)|(\%23)|(#)/i', '', $search);
        $search = preg_replace('/((\%3D)|(=))[^\n]*((\%27)|(\')|(\-\-)|(\%3B)|(;))/i', '', $search);
        
        // Remove XSS patterns
        $search = htmlspecialchars($search, ENT_QUOTES, 'UTF-8');
        
        return trim($search);
    }

    public function sanitizePaymentInput(array $input): array
    {
        $sanitized = [];
        foreach ($input as $key => $value) {
            if (is_string($value)) {
                switch ($key) {
                    case 'card_number':
                    case 'cvv':
                    case 'account_number':
                        $sanitized[$key] = preg_replace('/[^0-9]/', '', $value);
                        break;
                    case 'cardholder_name':
                    case 'account_holder_name':
                        $sanitized[$key] = preg_replace('/[^a-zA-Z\s]/', '', trim($value));
                        break;
                    case 'paypal_email':
                        $sanitized[$key] = filter_var(trim($value), FILTER_SANITIZE_EMAIL);
                        break;
                    default:
                        $sanitized[$key] = htmlspecialchars(trim($value), ENT_QUOTES, 'UTF-8');
                }
            } else {
                $sanitized[$key] = $value;
            }
        }
        return $sanitized;
    }

    public function sanitizeInteger($value): ?int
    {
        return is_numeric($value) && $value > 0 ? (int) $value : null;
    }
}