<?php

namespace Awuxtron\LaravelEthereum\Rules;

use Illuminate\Contracts\Validation\Rule;

class Hex implements Rule
{
    /**
     * Create a new validation object.
     *
     * @param bool $strict
     */
    public function __construct(protected bool $strict = false)
    {
    }

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
        return \Awuxtron\LaravelEthereum\Utils\Hex::isValid($value, $this->strict);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message(): string
    {
        return 'The :attribute is not a valid hex string.';
    }
}
