<?php

namespace Awuxtron\LaravelEthereum\Types;

use Awuxtron\LaravelEthereum\Utils\Hex;

class Boolean extends EthType
{
    /**
     * The underlying value.
     *
     * @var bool
     */
    protected bool $value;

    /**
     * Protected constructor. Use a factory method to obtain an instance.
     *
     * @param bool $value
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
        return new static((bool) $value);
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
        return static::from(Hex::of($value)->toBoolean(), $options);
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

    public function value(): bool
    {
        return $this->value;
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
        return is_bool($value);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message(): string
    {
        return 'The :attribute must be of type boolean.';
    }

    /**
     * Encode the given value to hex object.
     *
     * @return Hex
     */
    protected function encode(): Hex
    {
        return Hex::fromBoolean($this->value);
    }
}
