<?php

namespace Awuxtron\LaravelEthereum\Types;

use Awuxtron\LaravelEthereum\Utils\Address as AddressUtil;
use Awuxtron\LaravelEthereum\Utils\Hex;
use InvalidArgumentException;

class Address extends EthType
{
    /**
     * The underlying value.
     *
     * @var string
     */
    protected string $value;

    /**
     * Protected constructor. Use a factory method to obtain an instance.
     *
     * @param string $value
     */
    protected function __construct(mixed $value)
    {
        $this->value = $value;
    }

    /**
     * Create an ethereum type object from any given value.
     *
     * @param mixed               $value
     * @param array<string,mixed> $options
     *
     * @return static
     */
    public static function from(mixed $value, array $options = []): static
    {
        if (!AddressUtil::isValid($value)) {
            throw new InvalidArgumentException("The given value '$value' is not a valid ethereum address.");
        }

        return new static(strtolower($value));
    }

    /**
     * Create an ethereum type object from hex.
     *
     * @param Hex|string          $value
     * @param array<string,mixed> $options
     *
     * @return static
     */
    public static function fromHex(Hex|string $value, array $options = []): static
    {
        return static::from(Hex::of($value)->stripZeros(), $options);
    }

    /**
     * Checks if the type is dynamic-type.
     *
     * @return bool
     */
    public function isDynamic(): bool
    {
        return false;
    }

    public function value(): string
    {
        return $this->encode()->prefixed();
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

    /**
     * Encode the given value to hex object.
     *
     * @return Hex
     */
    protected function encode(): Hex
    {
        return Hex::of($this->value);
    }
}
