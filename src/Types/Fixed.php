<?php

namespace Awuxtron\LaravelEthereum\Types;

use Awuxtron\LaravelEthereum\Exceptions\InvalidByteSizeException;
use Awuxtron\LaravelEthereum\Exceptions\InvalidValueException;
use Awuxtron\LaravelEthereum\Utils\Hex;
use Brick\Math\BigDecimal;
use InvalidArgumentException;

class Fixed extends Integer
{
    /**
     * The default bytes of this type.
     *
     * @var int
     */
    protected int $bytes = 128;

    /**
     * The decimals of the value.
     *
     * @var int
     */
    protected int $decimals = 18;

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
        $instance = new static($num = BigDecimal::of($value));

        // Set the decimals for this instance.
        if (isset($options['decimals'])) {
            if ($options['decimals'] < 1 || $options['decimals'] > 80) {
                throw new InvalidArgumentException('Decimal points must be in range: 1-80.');
            }

            $instance->decimals = $options['decimals'];
        }

        // Validate decimals.
        if ($num->getScale() != $instance->decimals) {
            throw new InvalidArgumentException("The value '$value' must have exactly $instance->decimals numbers in decimal part.");
        }

        return parent::from($instance, $options);
    }

    /**
     * Encode the given value to hex object.
     *
     * @return Hex
     */
    protected function encode(): Hex
    {
        return Hex::fromInteger($this->value->toBigDecimal()->getUnscaledValue(), true);
    }
}
