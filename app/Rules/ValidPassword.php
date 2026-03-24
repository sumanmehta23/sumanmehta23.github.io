<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class ValidPassword implements Rule
{
    /**
     * Store individual validation errors
     */
    private $errors = [];

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $this->errors = [];

        // Check for spaces first
        if (strpos($value, ' ') !== false) {
            $this->errors[] = 'Invalid password: Password cannot contain spaces';
            return false;
        }

        // Check all password requirements (matching frontend validation exactly)
        $hasMinLength = strlen($value) >= 8;
        $hasUppercase = preg_match('/[A-Z]/', $value);
        $hasLowercase = preg_match('/[a-z]/', $value);
        $hasDigit = preg_match('/\d/', $value);
        // Special characters matching frontend: ! @ # $ % ^ & * ( ) , . - ? " : { } | < >
        $hasSpecialChar = preg_match('/[!@#$%^&*(),\.\-?\":{}<|>]/', $value);


        if (!$hasMinLength) {
            $this->errors[] = 'Invalid password: At least 8 characters';
        }
        if (!$hasLowercase) {
            $this->errors[] = 'Invalid password: At least 1 lowercase letter (a-z)';
        }
        if (!$hasUppercase) {
            $this->errors[] = 'Invalid password: At least 1 uppercase letter (A-Z)';
        }
        if (!$hasDigit) {
            $this->errors[] = 'Invalid password: At least 1 number (0-9)';
        }
        if (!$hasSpecialChar) {
            $this->errors[] = 'Invalid password: At least 1 special character: ! @ # $ % ^ & * ( ) , . - ? " : { } | < >';
        }

        return $hasMinLength && $hasUppercase && $hasLowercase && $hasDigit && $hasSpecialChar;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        if (empty($this->errors)) {
            return 'Invalid password: Password does not meet the required criteria.';
        }

        return  implode(', ', $this->errors);
    }
}
