<?php

namespace Awuxtron\LaravelEthereum\Types;

use Awuxtron\LaravelEthereum\Exceptions\InvalidByteSizeException;
use Awuxtron\LaravelEthereum\Utils\Hex;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;
use JsonSerializable;

abstract class EthType implements JsonSerializable, CastsAttributes, Rule
{
    /**
     * The regex pattern for matching a solidity type.
     *
     * @var string
     */
    protected static string $typePattern = '/^(?<type1>address|bool|function|string)$|^((?<type2>bytes|((?<unsigned>u)?(int|fixed)))(?<bytes>[\d]|[1-9][\d]|1[\d]{2}|2[0-4][\d]|25[0-6])?(x(?<decimals>[\d]|[1-7][\d]|80))?)$/i';

    /**
     * Map the name of types in solidity to it classname.
     *
     * @var array<string,string>
     */
    protected static array $types = [
        'address' => Address::class,
        'bool' => Boolean::class,
        'bytes' => Bytes::class,
        'fixed' => Fixed::class,
        'int' => Integer::class,
        'string' => Str::class,
    ];

    /**
     * The min bytes of this type.
     *
     * @var int
     */
    protected static int $minBytes = 8;

    /**
     * The max bytes of this type.
     *
     * @var int
     */
    protected static int $maxBytes = 256;

    /**
     * The default bytes of this type.
     *
     * @var int
     */
    protected int $bytes;

    /**
     * The pad type using when pad hex object.
     *
     * @var int
     */
    protected int $padType = STR_PAD_LEFT;

    /**
     * Protected constructor. Use a factory method to obtain an instance.
     *
     * @param mixed $value
     */
    abstract protected function __construct(mixed $value);

    /**
     * Resolve an EthType instance from the type name.
     *
     * @param string              $type
     * @param mixed               $value
     * @param array<string,mixed> $options
     *
     * @return EthType
     */
    public static function resolve(string $type, mixed $value, array $options = []): EthType
    {
        $parsed = static::parseType($type);

        return $parsed['class']::from($value, [...$parsed, ...$options]);
    }

    /**
     * Parse the type name to get type class, and it options.
     *
     * @param string $type
     *
     * @return array<string,mixed>
     */
    public static function parseType(string $type): array
    {
        preg_match(self::$typePattern, $type, $matches);

        $matches['type'] = '';

        if (!empty($matches['type1'])) {
            $matches['type'] = $matches['type1'];
        }

        if (!empty($matches['type2'])) {
            $matches['type'] = str($matches['type2'])->replaceFirst('u', '')->toString();
        }

        if (!array_key_exists($matches['type'], self::$types)) {
            throw new InvalidArgumentException("$type is not supported.");
        }

        return [
            'class' => static::$types[$matches['type']],
            'bytes' => $matches['bytes'] ?? null,
            'decimals' => $matches['decimals'] ?? null,
            'unsigned' => ($matches['unsigned'] ?? false) && $matches['unsigned'] === 'u',
        ];
    }

    /**
     * Check if byte size is valid.
     *
     * @param int  $size
     * @param bool $strict
     *
     * @return void
     * @throws InvalidByteSizeException
     */
    public static function isValidSize(int $size, bool $strict = true): void
    {
        if ($size < static::$minBytes || $size > static::$maxBytes) {
            throw new InvalidByteSizeException(
                sprintf('Byte size must be in range: %d - %d', static::$minBytes, static::$maxBytes)
            );
        }

        if ($strict && $size % 8 !== 0) {
            throw new InvalidByteSizeException('Byte size must be divisible for 8.');
        }
    }

    /**
     * Create an ethereum type object from any given value.
     *
     * @param mixed               $value
     * @param array<string,mixed> $options
     *
     * @return static
     */
    abstract public static function from(mixed $value, array $options = []): static;

    /**
     * Create an ethereum type object from hex.
     *
     * @param Hex|string          $value
     * @param array<string,mixed> $options
     *
     * @return static
     */
    abstract public static function fromHex(Hex|string $value, array $options = []): static;

    /**
     * Checks if the type is dynamic-type.
     *
     * @return bool
     */
    abstract public function isDynamic(): bool;

    /**
     * Returns the hex object from the given value.
     *
     * @return Hex
     */
    public function encoded(): Hex
    {
        $hex = $this->encode();

        $parts = $hex->split(32)->map(function (Hex $hex) {
            return (string) $hex->pad(32, $hex->isTwosComplement && $hex->isNegative ? 'f' : '0', $this->padType);
        });

        return $hex->pipe(fn () => $parts->implode(''));
    }

    /**
     * Get the length of value.
     *
     * @return int|null
     */
    public function length(): ?int
    {
        return null;
    }

    public function jsonSerialize(): mixed
    {
        return $this->value();
    }

    public function __toString(): string
    {
        return (string) $this->value();
    }

    abstract public function value(): mixed;

    /**
     * Transform the attribute from the underlying model values.
     *
     * @param Model                $model
     * @param string               $key
     * @param mixed                $value
     * @param array<string, mixed> $attributes
     *
     * @return static
     */
    public function get($model, string $key, $value, array $attributes): static
    {
        return new static($value);
    }

    /**
     * Transform the attribute to its underlying model values.
     *
     * @param Model                $model
     * @param string               $key
     * @param mixed                $value
     * @param array<string, mixed> $attributes
     *
     * @return static
     */
    public function set($model, string $key, $value, array $attributes): static
    {
        return static::from($value);
    }

    /**
     * Encode the given value to hex object.
     *
     * @return Hex
     */
    abstract protected function encode(): Hex;
}
