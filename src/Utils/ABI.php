<?php

namespace Awuxtron\LaravelEthereum\Utils;

use Awuxtron\LaravelEthereum\Exceptions\InvalidByteSizeException;
use Awuxtron\LaravelEthereum\Exceptions\InvalidValueException;
use Awuxtron\LaravelEthereum\Types\EthType;
use Awuxtron\LaravelEthereum\Types\Integer;
use Illuminate\Support\Str;
use InvalidArgumentException;

class ABI
{
    /**
     * Decodes an ABI encoded parameter to its Eth type.
     *
     * @param string     $type
     * @param Hex|string $hex
     *
     * @return mixed
     */
    public static function decodeParameter(string $type, Hex|string $hex): mixed
    {
        return static::decodeParameters([$type], $hex)[0] ?? null;
    }

    /**
     * Decodes ABI encoded parameters to its Eth types.
     *
     * @param array<string> $types
     * @param Hex|string    $hex
     *
     * @return array<mixed>
     */
    public static function decodeParameters(array $types, Hex|string $hex): array
    {
        $hex = $raw = Hex::of($hex);
        $result = [];
        $dynamicArrays = [];

        foreach ($types as $index => $type) {
            if (static::isArray($type) || static::isTuple($type)) {
                $position = $hex->slice(0, 32)->toInteger();
                $isDynamic = static::isDynamicArray($type);

                // Calculator the length of array.
                $length = static::getArrayLength($type);

                if ($isDynamic) {
                    $length = $raw->slice($position, 32)->toInteger()->toInt();
                }

                $arrTypes = static::getArrayTypes($type, $length);

                // Static array handle.
                if (static::isStaticArrayOrTuple($type, $arrTypes)) {
                    $size = static::getStaticBytesSize($arrTypes);

                    $result[$index] = static::decodeParameters(
                        $arrTypes,
                        $hex->slice(0, $size)
                    );

                    $hex = $hex->slice($size);

                    continue;
                }

                // Dynamic array handle.
                $dynamicArrays[] = [
                    'index' => $index,
                    'has_size' => $isDynamic,
                    'position' => $position,
                    'types' => $arrTypes,
                ];

                $hex = $hex->slice(32);

                continue;
            }

            $parsed = EthType::parseType($type);

            // Dynamic type handle.
            if (static::isDynamicType($type)) {
                $position = $hex->slice(0, 32)->toInteger();
                $size = nearest_divisible($raw->slice($position, 32)->toInteger(), 32);

                $result[$index] = $parsed['class']::fromHex($raw->slice($position->plus(32), $size), $parsed);
                $hex = $hex->slice(32);

                continue;
            }

            // Static type handle.
            $result[$index] = $parsed['class']::fromHex($hex->slice(0, 32), $parsed);
            $hex = $hex->slice(32);
        }

        // Dynamic array handle.
        foreach ($dynamicArrays as $i => $arr) {
            $size = null;

            if (isset($dynamicArrays[$i + 1])) {
                $size = $dynamicArrays[$i + 1]['position']->minus($arr['position']);
            }

            $result[$arr['index']] = static::decodeParameters(
                $arr['types'],
                $raw->slice($arr['position'], $size)->slice($arr['has_size'] ? 32 : 0)
            );
        }

        ksort($result);

        return $result;
    }

    /**
     * Encodes single parameter to its ABI representation.
     *
     * @param string $type
     * @param mixed  $param
     *
     * @return Hex
     * @throws InvalidByteSizeException
     * @throws InvalidValueException
     */
    public static function encodeParameter(string $type, mixed $param): Hex
    {
        return static::encodeParameters([$type], [$param]);
    }

    /**
     * Encodes parameters to its ABI representation.
     *
     * @param string[]     $types
     * @param array<mixed> $params
     *
     * @return Hex
     * @throws InvalidByteSizeException
     * @throws InvalidValueException
     * @see https://docs.soliditylang.org/en/latest/abi-spec.html
     */
    public static function encodeParameters(array $types, array $params): Hex
    {
        static::validateTypes($types, $params);

        [$static, $dynamic, $emptyStr] = [[], [], str_repeat('ffff', 16)];

        foreach ($types as $index => $type) {
            if (static::isTuple($type) || static::isArray($type)) {
                if (!is_array($params[$index])) {
                    throw new InvalidArgumentException(sprintf('The param #%d: %s must be type of array.', $index, serialize($params[$index])));
                }

                $arrTypes = static::getArrayTypes($type, count($params[$index]));

                // Static array handle.
                if (static::isStaticArrayOrTuple($type, $arrTypes)) {
                    $static[$index] = static::encodeParameters($arrTypes, $params[$index]);

                    continue;
                }

                // Dynamic array handle.
                $size = '';

                if (static::isDynamicArray($type)) {
                    $size = static::getValueSize($params[$index]);
                }

                $static[$index] = $emptyStr;
                $dynamic[$index] = $size . static::encodeParameters($arrTypes, $params[$index]);

                continue;
            }

            $value = EthType::resolve($type, $params[$index]);

            // Dynamic type handle.
            if ($value->isDynamic()) {
                $dynamic[$index] = static::getValueSize($value) . $value->encoded();
                $static[$index] = $emptyStr;

                continue;
            }

            // Static type handle.
            $static[$index] = $value->encoded();
        }

        // Calculator position for dynamic types.
        $position = strlen(implode('', $static)) / 2;

        foreach ($dynamic as $i => $v) {
            $static[$i] = Integer::from($position)->encoded();
            $position += strlen($v) / 2;
        }

        return Hex::of(implode('', $static) . implode('', $dynamic));
    }

    /**
     * Validate inputs of encodeParameters function.
     *
     * @param string[]     $types
     * @param array<mixed> $params
     *
     * @return void
     */
    protected static function validateTypes(array $types, array $params): void
    {
        if (empty($types) || empty($params)) {
            throw new InvalidArgumentException('Types and params can not be empty.');
        }

        if (count($types) !== count($params)) {
            throw new InvalidArgumentException('Number of param elements must be same as types.');
        }

        if (!collect($types)->every(fn ($type) => is_string($type))) {
            throw new InvalidArgumentException('The types must be string array.');
        }
    }

    /**
     * Checks if the given type is tuple.
     *
     * @param string $type
     *
     * @return bool
     */
    protected static function isTuple(string $type): bool
    {
        return (bool) preg_match('/^tuple\([a-z0-9\[\],() ]+\)$/', $type);
    }

    /**
     * Checks if the given type is an array.
     *
     * @param string $type
     *
     * @return bool
     */
    protected static function isArray(string $type): bool
    {
        return (bool) preg_match('/^[a-z0-9\[\],() ]+(\[([\d]+)?])$/', $type);
    }

    /**
     * Get the list of types from the array or tuple type.
     *
     * @param string $type
     * @param int    $length
     *
     * @return string[]
     */
    protected static function getArrayTypes(string $type, int $length): array
    {
        preg_match('/(?<type>[a-z0-9\[\](), ]+)(\[(?<length>\d+)?])$/', $type, $matches);

        if (!empty($matches['type'])) {
            return array_fill(0, $matches['length'] ?? $length, $matches['type']);
        }

        $types = str($type)->substr(6, -1)->replaceMatches('/(,)|\((((?>[^()]+)|(?R))*)\)/', function ($match) {
            return $match[0] === ',' ? '&' : $match[0];
        });

        return $types->explode('&')->map(fn ($t) => trim($t))->all();
    }

    /**
     * Checks if the type is a static array or tuple.
     *
     * @param string   $type
     * @param string[] $arrayTypes
     *
     * @return bool
     */
    protected static function isStaticArrayOrTuple(string $type, array $arrayTypes): bool
    {
        if (static::isDynamicArray($type)) {
            return false;
        }

        if (Str::startsWith($type, 'tuple')) {
            return collect($arrayTypes)->every(fn ($t) => static::isStaticArrayOrTuple($t, static::getArrayTypes($type, 1)));
        }

        return !static::isDynamicType($type);
    }

    /**
     * Checks if the type is a dynamic array.
     *
     * @param string $type
     *
     * @return bool
     */
    protected static function isDynamicArray(string $type): bool
    {
        return Str::endsWith($type, '[]');
    }

    /**
     * Checks if the type is dynamic or static.
     *
     * @param string $type
     *
     * @return bool
     */
    protected static function isDynamicType(string $type): bool
    {
        return (bool) preg_match('/^(bytes|string)$|^[a-z0-9\[\]]+\[]$|^(bytes|string|[a-z0-9\[\]]+\[])\[[1-9]+]$/', $type);
    }

    /**
     * Get byte size of value.
     *
     * @param EthType|array<mixed> $value
     *
     * @return string
     * @throws InvalidByteSizeException
     * @throws InvalidValueException
     */
    protected static function getValueSize(EthType|array $value): string
    {
        return Integer::from(is_array($value) ? count($value) : $value->length())->encoded();
    }

    /**
     * Get the length of array from type name.
     *
     * @param string $type
     *
     * @return int
     */
    protected static function getArrayLength(string $type): int
    {
        preg_match('/\[(?<length>\d)]$/', $type, $match);

        return $match['length'] ?? 0;
    }

    /**
     * Get the bytes size of static-type.
     *
     * @param array<string> $types
     *
     * @return int
     */
    protected static function getStaticBytesSize(array $types): int
    {
        return collect($types)->sum(function ($type) {
            if (static::isArray($type) || static::isTuple($type)) {
                return static::getStaticBytesSize(static::getArrayTypes($type, 0));
            }

            return 32;
        });
    }
}
