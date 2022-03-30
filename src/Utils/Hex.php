<?php

namespace Awuxtron\LaravelEthereum\Utils;

use Awuxtron\LaravelEthereum\Exceptions\HexException;
use Brick\Math\BigInteger;
use Brick\Math\BigNumber;
use Illuminate\Support\Collection;
use Illuminate\Support\Stringable;
use Illuminate\Support\Traits\Conditionable;
use Illuminate\Support\Traits\Macroable;
use Illuminate\Support\Traits\Tappable;
use InvalidArgumentException;
use JsonSerializable;

class Hex implements JsonSerializable
{
    use Conditionable;
    use Macroable;
    use Tappable;

    /**
     * Create a new instance of the class.
     *
     * @param string $value The underlying hex string value without sign and prefix.
     * @param bool   $isNegative
     * @param bool   $isTwosComplement
     */
    final protected function __construct(
        protected string $value,
        public bool $isNegative,
        public bool $isTwosComplement = false
    )
    {
    }

    /**
     * Get a new hex object from the given hex string.
     *
     * @param self|string $hex
     * @param bool|null   $isNegative
     * @param bool        $isTwosComplement
     *
     * @return self
     */
    public static function of(self|string $hex, ?bool $isNegative = null, bool $isTwosComplement = false): self
    {
        if ($hex instanceof self) {
            return $hex;
        }

        if (!static::isValid($hex)) {
            throw new InvalidArgumentException("The given value '$hex' is not a valid hex string.");
        }

        if ($isNegative === null) {
            if ($isTwosComplement) {
                throw new InvalidArgumentException('You must to specified $isNegative when $isTwosComplement is true');
            }

            $isNegative = static::isNegative($hex);
        }

        return new static(static::stripPrefix($hex, true), $isNegative, $isTwosComplement);
    }

    /**
     * Convert a boolean into the hex object.
     *
     * @param bool $value
     *
     * @return static
     */
    public static function fromBoolean(bool $value): static
    {
        return new static($value ? '1' : '0', false);
    }

    /**
     * Convert an integer or big number object into hexadecimal object.
     *
     * @param BigNumber|string|int|float $number
     * @param bool                       $twosComplement
     *
     * @return static
     */
    public static function fromInteger(BigNumber|string|int|float $number, bool $twosComplement = false): static
    {
        if (str_contains((string) $number, '.')) {
            throw new InvalidArgumentException("Float number '$number' is not supported. If you try to convert a large integer, pass it as string.");
        }

        $number = BigInteger::of($number);

        return new static(
            $twosComplement ? bin2hex($number->toBytes()) : $number->abs()->toBase(16),
            $number->isNegative(),
            $twosComplement
        );
    }

    /**
     * Convert a string into the hex object.
     *
     * @param string $value
     *
     * @return static
     * @throws HexException
     */
    public static function fromString(string $value): static
    {
        $hex = unpack('H*', trim($value));

        if (!$hex) {
            throw new HexException("Unable to convert the given string '$value' into hex object.");
        }

        return new static(implode('', $hex), false);
    }

    /**
     * Concat multiple hex into one.
     *
     * @param self|string ...$values
     *
     * @return static
     */
    public static function concat(self|string ...$values): static
    {
        $isNegative = static::isNegative($values[0]);

        $hexes = collect($values)->map(function ($hex) use ($isNegative) {
            $instance = static::of($hex);

            if ($isNegative != $instance->isNegative) {
                throw new InvalidArgumentException('All hex strings must same sign.');
            }

            return $instance->value;
        });

        return new static($hexes->implode(''), $isNegative);
    }

    /**
     * Checks if the hex string is negative.
     *
     * @param self|string $hex
     *
     * @return bool
     */
    public static function isNegative(self|string $hex): bool
    {
        return ($hex instanceof self && $hex->isNegative) || str_starts_with($hex, '-');
    }

    /**
     * Checks if given value is a valid hex string.
     *
     * @param self|string $hex
     * @param bool        $strict
     *
     * @return bool
     */
    public static function isValid(self|string $hex, bool $strict = false): bool
    {
        if ($hex instanceof self) {
            return true;
        }

        return $hex !== '' && preg_match('/^' . ($strict ? '(-)?0x' : '(-0x|0x|-)?') . '[[:xdigit:]]*$/i', $hex);
    }

    /**
     * Strip 0x prefix from start of hex string.
     *
     * @param self|string $hex
     * @param bool        $stripSign
     *
     * @return string
     */
    public static function stripPrefix(self|string $hex, bool $stripSign = false): string
    {
        if ($hex instanceof self) {
            return $hex->value;
        }

        $pattern = '/' . ($stripSign ? '(-)?' : '') . '(0x)?/i';

        return str($hex)->pipe(function (Stringable $str) use ($pattern) {
            $sign = $str->substr(0, 3)->replaceMatches($pattern, '');

            return $sign . $str->substr(3);
        });
    }

    /**
     * Append the given hexes to the current hex string.
     *
     * @param self|string ...$values
     *
     * @return static
     */
    public function append(self|string ...$values): static
    {
        return $this->newInstance(static::concat($this->value(), ...$values)->value);
    }

    /**
     * Checks if the hex is zero.
     *
     * @return bool
     */
    public function isZero(): bool
    {
        return (bool) preg_match('/^0+$/', $this->value);
    }

    /**
     * Returns the length (in bytes) of the hex string.
     *
     * @return int
     */
    public function length(): int
    {
        return count(str_split($this->value, 2));
    }

    /**
     * Wrap of str_pad function with length in bytes.
     *
     * @param int    $length
     * @param string $pad
     * @param int    $type
     *
     * @return static
     */
    public function pad(int $length, string $pad = '0', int $type = STR_PAD_LEFT): static
    {
        return $this->newInstance(str_pad($this->value, $length * 2, $pad, $type));
    }

    /**
     * Pad the left side of the hex string with another.
     *
     * @param int         $length The length of final hex string (in bytes).
     * @param string|null $pad
     *
     * @return static
     */
    public function padLeft(int $length, string $pad = null): static
    {
        if ($pad === null) {
            $pad = $this->isTwosComplement && $this->isNegative ? 'f' : '0';
        }

        return $this->pad($length, $pad);
    }

    /**
     * Pad the right side of the hex string with another.
     *
     * @param int    $length The length of final hex string (in bytes).
     * @param string $pad
     *
     * @return static
     */
    public function padRight(int $length, string $pad = '0'): static
    {
        return $this->pad($length, $pad, STR_PAD_RIGHT);
    }

    /**
     * Call the given callback and return a new hex object.
     *
     * @param callable $callback
     *
     * @return static
     */
    public function pipe(callable $callback): static
    {
        return $this->newInstance($callback($this));
    }

    /**
     * Get the underlying hex string value includes sign and prefix.
     *
     * @return string
     */
    public function prefixed(): string
    {
        return $this->getSign() . '0x' . $this->value;
    }

    /**
     * Prepend the given hexes to the current hex string.
     *
     * @param self|string ...$values
     *
     * @return static
     */
    public function prepend(self|string ...$values): static
    {
        $values[] = $this->value();

        return $this->newInstance(static::concat(...$values)->value);
    }

    /**
     * Returns part of the hex string specified by the start (in bytes) and length (in bytes) parameters.
     *
     * @param BigNumber|int      $start
     * @param BigNumber|int|null $length
     *
     * @return static
     */
    public function slice(BigNumber|int $start, BigNumber|int|null $length = null): static
    {
        $start = BigInteger::of($start)->toInt();

        if ($length != null) {
            $length = BigInteger::of($length)->multipliedBy(2)->toInt();
        }

        return $this->newInstance(substr($this->value, $start * 2, $length));
    }

    /**
     * Split the hex object by length (in bytes).
     *
     * @param int $length
     *
     * @return Collection<int,static>
     */
    public function split(int $length): Collection
    {
        return str($this->value)->split($length * 2)->map(function ($part) {
            return $this->newInstance($part);
        });
    }

    /**
     * Remove 00 paddings from either side of the hex string.
     *
     * @return static
     */
    public function stripZeros(): static
    {
        $stripped = str($this->value)->replaceMatches('/^(?:00)*/', '')
            ->reverse()
            ->replaceMatches('/^(?:00)*/', '')
            ->reverse()
            ->toString();

        return new static($stripped ?: '0', $this->isNegative, $this->isTwosComplement);
    }

    /**
     * Convert the current hex object to boolean.
     *
     * @return bool
     */
    public function toBoolean(): bool
    {
        return !$this->isZero();
    }

    /**
     * Convert the current hex object to a big number object.
     *
     * @return BigInteger
     */
    public function toInteger(): BigInteger
    {
        if ($this->isTwosComplement) {
            return BigInteger::fromBytes((string) hex2bin($this->value));
        }

        return BigInteger::fromBase($this->value(), 16);
    }

    /**
     * Convert the current hex object to a decoded string.
     *
     * @return string
     * @throws HexException
     */
    public function toString(): string
    {
        if ($this->isNegative) {
            throw new HexException('Unable to decode negative hex to string.');
        }

        return trim(pack('H*', $this->value));
    }

    /**
     * Convert the current hex object to hex signed two's complement.
     *
     * @return static
     */
    public function toTwosComplement(): static
    {
        if ($this->isTwosComplement) {
            return $this;
        }

        $number = BigInteger::fromBytes((string) hex2bin($this->value));

        return static::fromInteger($number, true);
    }

    /**
     * Get the underlying hex string value with sign and without 0x prefix.
     *
     * @return string
     */
    public function value(): string
    {
        return $this->getSign() . $this->value;
    }

    /**
     * Execute the given callback if the hex is negative.
     *
     * @param callable      $callback
     * @param callable|null $default
     *
     * @return static
     */
    public function whenIsNegative(callable $callback, callable $default = null): static
    {
        return $this->when($this->isNegative, $callback, $default);
    }

    /**
     * Execute the given callback if the hex is positive.
     *
     * @param callable      $callback
     * @param callable|null $default
     *
     * @return static
     */
    public function whenIsPositive(callable $callback, callable $default = null): static
    {
        return $this->when(!$this->isNegative, $callback, $default);
    }

    /**
     * Get the raw string value.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->value();
    }

    /**
     * Convert the object to a string when JSON encoded.
     *
     * @return string
     */
    public function jsonSerialize(): string
    {
        return $this->__toString();
    }

    /**
     * Returns the new instance of the hex object.
     *
     * @param string $value
     *
     * @return static
     */
    protected function newInstance(string $value): static
    {
        return new static($value, $this->isNegative, $this->isTwosComplement);
    }

    /**
     * Get the sign of current hex string.
     *
     * @return string
     */
    protected function getSign(): string
    {
        return $this->isNegative && !$this->isTwosComplement ? '-' : '';
    }
}
