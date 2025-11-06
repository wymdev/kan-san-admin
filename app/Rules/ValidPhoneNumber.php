<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use libphonenumber\PhoneNumberUtil;
use libphonenumber\NumberParseException;

class ValidPhoneNumber implements Rule
{
    /**
     * Determine if the validation rule passes
     * @param string $attribute Field name
     * @param mixed $value Phone number to validate
     * @return bool
     */
    public function passes($attribute, $value): bool
    {
        try {
            $phoneUtil = PhoneNumberUtil::getInstance();
            // 'ZZ' = auto-detect country from phone number format (E.164)
            $phoneNumber = $phoneUtil->parse($value, 'ZZ');
            return $phoneUtil->isValidNumber($phoneNumber);
        } catch (NumberParseException $e) {
            return false;
        }
    }

    /**
     * Get the validation error message
     * @return string
     */
    public function message(): string
    {
        return 'The :attribute must be a valid international phone number (e.g., +66812345678).';
    }
}
