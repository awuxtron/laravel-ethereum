<?php

namespace Awuxtron\LaravelEthereum\Rules;

use Awuxtron\LaravelEthereum\Utils\Address as AddressUtil;
use Illuminate\Contracts\Validation\Rule;

class Address implements Rule
{
    /**
     * Determine if the validation rule passes.
     *
     * @param string $attribute
     * @param mixed  $value
     *
     * @return bool
     */
    public function passes($attribute, $value): bool
    {
        return AddressUtil::isValid($value);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message(): string
    {
        return 'The :attribute must be a valid Ethereum address.';
    }
}
