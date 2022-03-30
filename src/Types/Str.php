<?php

namespace Awuxtron\LaravelEthereum\Types;

use Awuxtron\LaravelEthereum\Exceptions\HexException;
use Awuxtron\LaravelEthereum\Utils\Hex;

class Str extends EthType
{
    /**
     * The underlying value.
     *
     * @var string
     */
    protected string $value;

    /**
     * The pad type using when pad hex object.
     *
     * @var int
     */
    protected int $padType = STR_PAD_RIGHT;

    /**
     * Protected constructor. Use a factory method to obtain an instance.
     *
     * @param mixed $value
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
        return new static((string) $value);
    }

    /**
     * Create an ethereum type object from hex.
     *
     * @param Hex|string          $value
     * @param array<string,mixed> $options
     *
     * @return static
     * @throws HexException
     */
    public static function fromHex(Hex|string $value, array $options = []): static
    {
        return static::from(Hex::of($value)->toString(), $options);
    }

    /**
     * Checks if the type is dynamic-type.
     *
     * @return bool
     */
    public function isDynamic(): bool
    {
        return true;
    }

    /**
     * Get the length of value.
     *
     * @return int|null
     */
    public function length(): ?int
    {
        return strlen($this->value);
    }

    public function value(): string
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
        return is_string($value);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message(): string
    {
        return 'The :attribute must be a valid string.';
    }

    /**
     * Encode the given value to hex object.
     *
     * @return Hex
     * @throws HexException
     */
    protected function encode(): Hex
    {
        return Hex::fromString($this->value);
    }
}
