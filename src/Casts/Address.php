<?php

namespace Awuxtron\LaravelEthereum\Casts;

use Awuxtron\LaravelEthereum\Types\Address as AddressType;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;

class Address implements CastsAttributes
{
    /**
     * Transform the attribute from the underlying model values.
     *
     * @param Model                $model
     * @param string               $key
     * @param mixed                $value
     * @param array<string, mixed> $attributes
     *
     * @return AddressType
     */
    public function get($model, string $key, $value, array $attributes): AddressType
    {
        return AddressType::from($value);
    }

    /**
     * Transform the attribute to its underlying model values.
     *
     * @param Model                $model
     * @param string               $key
     * @param mixed                $value
     * @param array<string, mixed> $attributes
     *
     * @return string
     */
    public function set($model, string $key, $value, array $attributes): string
    {
        return AddressType::from($value)->encoded()->prefixed();
    }
}
