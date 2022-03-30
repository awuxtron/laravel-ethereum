<?php

namespace Awuxtron\LaravelEthereum\Types;

use Awuxtron\LaravelEthereum\Exceptions\InvalidByteSizeException;
use Awuxtron\LaravelEthereum\Exceptions\InvalidValueException;
use Awuxtron\LaravelEthereum\Utils\Hex;
use Brick\Math\BigInteger;
use Brick\Math\BigNumber;
use Exception;
use OutOfRangeException;

class Integer extends EthType
{
    /**
     * The default bytes of this type.
     *
     * @var int
     */
    protected int $bytes = 256;

    /**
     * The underlying value.
     *
     * @var BigNumber
     */
    protected BigNumber $value;

    /**
     * Protected constructor. Use a factory method to obtain an instance.
     *
     * @param BigNumber $value
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
     * @throws InvalidByteSizeException|InvalidValueException
     */
    public static function from(mixed $value, array $options = []): static
    {
        $instance = $value instanceof self ? $value : new static(BigInteger::of($value));
        $num = $instance->value;
        $isUnsigned = $options['unsigned'] ?? false;

        // Set the bytes size for this instance.
        if (isset($options['bytes'])) {
            static::isValidSize($options['bytes']);

            $instance->bytes = $options['bytes'];
        }

        // Validate bytes.
        $min = 0;
        $max = BigInteger::of(2)->power($instance->bytes)->minus(1);

        if (!$isUnsigned) {
            $max = $max->plus(1)->dividedBy(2)->minus(1);
            $min = $max->plus(1)->negated();
        }

        if ($num->isLessThan($min) || $num->isGreaterThan($max)) {
            throw new OutOfRangeException("The value '$num' is out of range. For " . ($isUnsigned ? 'unsigned ' : '') . "$instance->bytes bytes number, value must be in range: [$min - $max].");
        }

        // Validate unsigned.
        if ($isUnsigned && $num->isNegative()) {
            throw new InvalidValueException("The value '$num' is a negative number but type is unsigned.");
        }

        //@phpstan-ignore-next-line
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
     * @throws InvalidValueException
     */
    public static function fromHex(Hex|string $value, array $options = []): static
    {
        return static::from(Hex::of($value)->toTwosComplement()->toInteger(), $options);
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

    public function value(): BigNumber
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
        try {
            BigInteger::of($value);

            return true;
        } catch (Exception) {
            return false;
        }
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message(): string
    {
        return 'The :attribute must be a valid number.';
    }

    /**
     * Encode the given value to hex object.
     *
     * @return Hex
     */
    protected function encode(): Hex
    {
        return Hex::fromInteger($this->value, true);
    }
}
