<?php

use Awuxtron\LaravelEthereum\Utils\Ether;
use Brick\Math\BigNumber;

it('should throw error when passing invalid unit', function () {
    expect(fn () => Ether::getUnitValue('nan'))->toThrow(InvalidArgumentException::class);
});

it('should return instance of big number when getting value of unit', function () {
    $this->assertInstanceOf(BigNumber::class, Ether::getUnitValue());
});

it('should return value of unit', function () {
    foreach (Ether::UNITS as $unit => $value) {
        $this->assertEquals($value, (string) Ether::getUnitValue($unit));
    }
});

it('should return the correct value when convert the number from wei', function () {
    $tests = [
        [
            'from' => '0',
            'unit' => 'wei',
            'expected' => '0',
        ],
        [
            'from' => 1,
            'unit' => 'wei',
            'expected' => '1',
        ],
        [
            'from' => '1',
            'unit' => 'wei',
            'expected' => '1',
        ],
        [
            'from' => 1.23,
            'unit' => 'kwei',
            'expected' => '0.00123',
        ],
        [
            'from' => '1.23',
            'unit' => 'kwei',
            'expected' => '0.00123',
        ],
        [
            'from' => BigNumber::of(1),
            'unit' => 'wei',
            'expected' => '1',
        ],
        [
            'from' => BigNumber::of(1.23),
            'unit' => 'kwei',
            'expected' => '0.00123',
        ],
        [
            'from' => '1000000000000000000',
            'unit' => 'wei',
            'expected' => '1000000000000000000',
        ],
        [
            'from' => '1000000000000000000',
            'unit' => 'kwei',
            'expected' => '1000000000000000',
        ],
        [
            'from' => '1000000000000000000',
            'unit' => 'mwei',
            'expected' => '1000000000000',
        ],
        [
            'from' => '1000000000000000000',
            'unit' => 'gwei',
            'expected' => '1000000000',
        ],
        [
            'from' => '1000000000000000000',
            'unit' => 'szabo',
            'expected' => '1000000',
        ],
        [
            'from' => '1000000000000000000',
            'unit' => 'finney',
            'expected' => '1000',
        ],
        [
            'from' => '1000000000000000000',
            'unit' => 'ether',
            'expected' => '1',
        ],
        [
            'from' => '1000000000000000000',
            'unit' => 'kether',
            'expected' => '0.001',
        ],
        [
            'from' => '1000000000000000000',
            'unit' => 'grand',
            'expected' => '0.001',
        ],
        [
            'from' => '1000000000000000000',
            'unit' => 'mether',
            'expected' => '0.000001',
        ],
        [
            'from' => '1000000000000000000',
            'unit' => 'gether',
            'expected' => '0.000000001',
        ],
        [
            'from' => '1000000000000000000',
            'unit' => 'tether',
            'expected' => '0.000000000001',
        ],
        [
            'from' => '-1000000000000000000',
            'unit' => 'ether',
            'expected' => '-1',
        ],
        [
            'from' => '-1000000000000000000',
            'unit' => 'finney',
            'expected' => '-1000',
        ],
    ];

    foreach ($tests as $test) {
        $result = Ether::fromWei($test['from'], $test['unit']);

        $this->assertInstanceOf(BigNumber::class, $result);
        $this->assertEquals($test['expected'], (string) $result);
    }
});

it('should return the correct value when convert the number to wei', function () {
    $tests = [
        [
            'from' => -1,
            'unit' => 'kwei',
            'expected' => '-1000',
        ],
        [
            'from' => '0',
            'unit' => 'wei',
            'expected' => '0',
        ],
        [
            'from' => 0.0000123,
            'unit' => 'kwei',
            'expected' => '0.0123',
        ],
        [
            'from' => BigNumber::of(1),
            'unit' => 'wei',
            'expected' => '1',
        ],
        [
            'from' => BigNumber::of(1.23),
            'unit' => 'kwei',
            'expected' => '1230',
        ],
        [
            'from' => BigNumber::of(0.0000123),
            'unit' => 'kwei',
            'expected' => '0.0123',
        ],
        [
            'from' => '1',
            'unit' => 'wei',
            'expected' => '1',
        ],
        [
            'from' => '1',
            'unit' => 'kwei',
            'expected' => '1000',
        ],
        [
            'from' => '1',
            'unit' => 'mwei',
            'expected' => '1000000',
        ],
        [
            'from' => '1',
            'unit' => 'gwei',
            'expected' => '1000000000',
        ],
        [
            'from' => '1',
            'unit' => 'szabo',
            'expected' => '1000000000000',
        ],
        [
            'from' => '1',
            'unit' => 'finney',
            'expected' => '1000000000000000',
        ],
        [
            'from' => '1',
            'unit' => 'ether',
            'expected' => '1000000000000000000',
        ],
        [
            'from' => '1',
            'unit' => 'kether',
            'expected' => '1000000000000000000000',
        ],
        [
            'from' => '1',
            'unit' => 'grand',
            'expected' => '1000000000000000000000',
        ],
        [
            'from' => '1',
            'unit' => 'mether',
            'expected' => '1000000000000000000000000',
        ],
        [
            'from' => '1',
            'unit' => 'gether',
            'expected' => '1000000000000000000000000000',
        ],
        [
            'from' => '1',
            'unit' => 'tether',
            'expected' => '1000000000000000000000000000000',
        ],
    ];

    foreach ($tests as $test) {
        $result = Ether::toWei($test['from'], $test['unit']);

        $this->assertInstanceOf(BigNumber::class, $result);
        $this->assertEquals($test['expected'], (string) $result);
    }
});

it('can get the unit by unit value', function () {
    $tests = [
        3 => 'kwei',
        6 => 'mwei',
        9 => 'gwei',
        12 => 'szabo',
        15 => 'finney',
        18 => 'ether',
        21 => 'kether',
        24 => 'mether',
        27 => 'gether',
        30 => 'tether',
    ];

    foreach ($tests as $value => $expected) {
        $this->assertEquals($expected, Ether::getUnitByValue(10 ** $value));
    }

    $this->assertEquals('noether', Ether::getUnitByValue(0));
    $this->assertEquals('wei', Ether::getUnitByValue(1));
});
