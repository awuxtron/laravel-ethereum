<?php

namespace Awuxtron\LaravelEthereum\Types;

use Awuxtron\LaravelEthereum\Exceptions\InvalidByteSizeException;
use Awuxtron\LaravelEthereum\Utils\Hex;
use InvalidArgumentException;

class Bytes extends EthType
{
    /**
     * The underlying value.
     *
     * @var Hex
     */
    protected Hex $value;

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
     * @throws InvalidByteSizeException
     */
    public static function from(mixed $value, array $options = []): static
    {
        $instance = new static($value = Hex::of($value));

        // Set the bytes size for this instance.
        if (isset($options['bytes'])) {
            static::isValidSize($options['bytes'], false);

            if (($length = $value->length()) > $options['bytes']) {
                throw new InvalidArgumentException("The length of given value '$value' ($length) is larger than {$options['bytes']} bytes.");
            }

            $instance->bytes = $options['bytes'];
        }

        return $instance;
    }

    /**
     * Create an ethereum type object from hex.
     *
     * @param Hex|string          $value
     * @param array<string,mixed> $options
     *
     * @return static
     * @throws InvalidByteSizeException
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
        return empty($this->bytes);
    }

    /**
     * Get the length of value.
     *
     * @return int|null
     */
    public function length(): ?int
    {
        return $this->value->length();
    }

    public function value(): Hex
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
        return Hex::isValid($value);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message(): string
    {
        return 'The :attribute must be a valid hex string.';
    }

    /**
     * Encode the given value to hex object.
     *
     * @return Hex
     */
    protected function encode(): Hex
    {
        return $this->value;
    }
}
