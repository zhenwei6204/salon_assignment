<?php

namespace App\Helpers;

class PaymentFormHelper
{
    /**
     * Render form field HTML based on field configuration
     */
    public static function renderField(array $field, array $bookingDetails = []): string
    {
        switch ($field['type']) {
            case 'info':
                return self::renderInfoField($field);
            
            case 'apple_pay_button':
                return self::renderApplePayButton($field);
            
            case 'text':
            case 'email':
            case 'tel':
                return self::renderInputField($field, $bookingDetails);
            
            default:
                return self::renderInputField($field, $bookingDetails);
        }
    }

    /**
     * Render info/alert field
     */
    private static function renderInfoField(array $field): string
    {
        $class = $field['class'] ?? 'alert alert-info';
        return "<div class=\"{$class}\">{$field['content']}</div>";
    }

    /**
     * Render Apple Pay button
     */
    private static function renderApplePayButton(array $field): string
    {
        $class = $field['class'] ?? 'apple-pay-container';
        return "
            <div class=\"{$class}\">
                <button type=\"button\" class=\"apple-pay-button\" id=\"apple-pay-btn\">
                    🍎 Pay with Apple Pay
                </button>
                <p class=\"text-muted\">{$field['content']}</p>
            </div>";
    }

    /**
     * Render input field
     */
    private static function renderInputField(array $field, array $bookingDetails = []): string
    {
        $wrapperClass = $field['wrapper_class'] ?? '';
        $inputClass = $field['class'] ?? 'form-control';
        $required = ($field['required'] ?? false) ? 'required' : '';
        $requiredAsterisk = ($field['required'] ?? false) ? '<span class="text-danger">*</span>' : '';
        
        // Get default value
        $defaultValue = old($field['name'], $bookingDetails[$field['name']] ?? '');
        
        // Handle data attributes
        $dataAttributes = '';
        if (isset($field['data_attributes'])) {
            foreach ($field['data_attributes'] as $attr => $value) {
                $dataAttributes .= " {$attr}=\"{$value}\"";
            }
        }

        return "
            <div class=\"form-group {$wrapperClass}\">
                <label for=\"{$field['name']}\">
                    {$field['label']} {$requiredAsterisk}
                </label>
                <input type=\"{$field['type']}\" 
                       id=\"{$field['name']}\" 
                       name=\"{$field['name']}\"
                       class=\"{$inputClass}\"
                       placeholder=\"{$field['placeholder']}\"
                       value=\"{$defaultValue}\"
                       {$required}
                       {$dataAttributes}>
            </div>";
    }

    /**
     * Generate Laravel validation rules from payment strategies
     */
    public static function generateValidationRules(array $availablePaymentMethods): array
    {
        $rules = ['payment_method' => 'required|string'];
        
        foreach ($availablePaymentMethods as $method) {
            if (empty($method['form_fields'])) continue;
            
            foreach ($method['form_fields'] as $field) {
                if (!isset($field['name']) || !isset($field['validation'])) continue;
                
                // Add conditional validation based on payment method
                $fieldName = $field['name'];
                $validation = $field['validation'];
                
                // Make validation conditional on payment method selection
                $rules[$fieldName] = "required_if:payment_method,{$method['key']}|{$validation}";
            }
        }
        
        return $rules;
    }

    /**
     * Get custom validation messages
     */
    public static function getValidationMessages(array $availablePaymentMethods): array
    {
        $messages = [];
        
        foreach ($availablePaymentMethods as $method) {
            if (empty($method['form_fields'])) continue;
            
            foreach ($method['form_fields'] as $field) {
                if (!isset($field['name'])) continue;
                
                $fieldName = $field['name'];
                $label = $field['label'] ?? ucfirst(str_replace('_', ' ', $fieldName));
                
                $messages["{$fieldName}.required_if"] = "The {$label} field is required when paying with {$method['name']}.";
                $messages["{$fieldName}.email"] = "Please enter a valid email address.";
                $messages["{$fieldName}.min"] = "The {$label} must be at least :min characters.";
                $messages["{$fieldName}.max"] = "The {$label} may not be greater than :max characters.";
            }
        }
        
        return $messages;
    }
}