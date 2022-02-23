<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class TurkishPhone implements Rule
{
    /**
     * Determine if the validation rule passes.
     *
     * @param string $attribute
     * @param mixed $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $value = trim($value);
        if (substr($value, 0, 1) == '+')
            $value = substr($value, 1);

        $value = str_replace(' ', '', $value);

        if (preg_match('/[^0-9]/', $value))
            return FALSE;

        if (strlen($value) > 12 || strlen($value) < 10)
            return FALSE;

        if (strlen($value) == 12 && substr($value, 0, 1) != '9')
            return FALSE;

        if (strlen($value) == 11 && substr($value, 0, 1) != '0')
            return FALSE;

        return TRUE;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'Invalid phone number.';
    }
}
