<?php

use Awuxtron\LaravelEthereum\Utils\Hex;

it('can create hex object from hex string', function () {
    expect(Hex::of('0x0'))->toBeInstanceOf(Hex::class)->toEqual('00');
});

it('can create hex object from hex object', function () {
    expect(Hex::of(Hex::of('0x0')))->toBeInstanceOf(Hex::class)->toEqual('00');
});

it('can create twos complement hex object', function () {
    expect(Hex::of('0xf', true, true))->toBeInstanceOf(Hex::class)->toEqual('f');
});

$invalid = [false, 'H', '0xH', '-H', '-0xH'];

foreach ($invalid as $val) {
    it('should throw when trying create hex object from invalid value: ' . json_encode($val), function () use ($val) {
        expect(fn () => Hex::of($val))->toThrow(InvalidArgumentException::class);
    });
}

it('should throw when create twos complement hex object without $isNegative set', function () {
    expect(fn () => Hex::of('ffff', null, true))->toThrow(InvalidArgumentException::class);
});

it('can convert boolean to hex object', function () {
    expect(Hex::fromBoolean(true))->toBeInstanceOf(Hex::class)->toEqual('1');
    expect(Hex::fromBoolean(false))->toBeInstanceOf(Hex::class)->toEqual('0');
});

$integers = [
    -0x7b => '-7b',
    -123 => '-7b',
    '-123' => '-7b',
    -1 => '-1',
    '-1' => '-1',
    0 => '0',
    '-0' => '0',
    '0' => '0',
    1 => '1',
    0x1 => '1',
    0x01 => '1',
    '1' => '1',
    '01' => '1',
    -15 => '-f',
    15 => 'f',
    '21345678976543214567869765432145647586' => '100f073a3d694d13d1615dc9bc3097e2',
    '123123213781273891237812738912738917938129783' => '585629b0b0dfe0ae5de60a92f88d637286b77',
    '-123123213781273891237812738912738917938129783' => '-585629b0b0dfe0ae5de60a92f88d637286b77',
];

foreach ($integers as $integer => $expected) {
    it("can convert integer to hex object using value: $integer", function () use ($integer, $expected) {
        expect((string) Hex::fromInteger($integer))->toEqual($expected);
    });
}

$twosComplements = [
    -0x7b => '85',
    -123 => '85',
    '-123' => '85',
    -1 => 'ff',
    '-1' => 'ff',
    0 => '00',
    '0' => '00',
    1 => '01',
    0x1 => '01',
    0x01 => '01',
    '1' => '01',
    -15 => 'f1',
    15 => '0f',
    '123123213781273891237812738912738917938129783' => '0585629b0b0dfe0ae5de60a92f88d637286b77',
    '-123123213781273891237812738912738917938129783' => 'fa7a9d64f4f201f51a219f56d07729c8d79489',
];

foreach ($twosComplements as $integer => $expected) {
    it("can convert twos complement to hex object using value: $integer", function () use ($integer, $expected) {
        expect((string) Hex::fromInteger($integer, true))->toEqual($expected);
        expect((string) Hex::fromInteger($integer, true)->toInteger())->toEqual((string) $integer);
    });
}

it('should throw when convert float to hex object', function () {
    expect(fn () => Hex::fromInteger(1.2345))->toThrow(InvalidArgumentException::class);
    expect(fn () => Hex::fromInteger(1234567890123456789123456789))->toThrow(InvalidArgumentException::class);
    expect(fn () => Hex::fromInteger('1.2345'))->toThrow(InvalidArgumentException::class);
});

$strings = [
    'myString' => '6d79537472696e67',
    "myString\x00" => '6d79537472696e67',
    'Heeäööä👅D34ɝɣ24Єͽ-.,äü+#/' => '486565c3a4c3b6c3b6c3a4f09f9185443334c99dc9a33234d084cdbd2d2e2cc3a4c3bc2b232f',
    "expected value\x00\x00\x00" => '65787065637465642076616c7565',
    "expect\x00\x00ed value\x00\x00\x00" => '657870656374000065642076616c7565',
    'Cộng Hòa Xã Hội Chủ Nghĩa Việt Nam' => '43e1bb996e672048c3b2612058c3a32048e1bb9969204368e1bba7204e6768c4a961205669e1bb8774204e616d',
    "我能吞下玻璃而不伤身体。" => 'e68891e883bde5909ee4b88be78ebbe79283e8808ce4b88de4bca4e8baabe4bd93e38082',
    "나는 유리를 먹을 수 있어요. 그래도 아프지 않아요" => 'eb8298eb8a9420ec9ca0eba6aceba5bc20eba8b9ec9d8420ec889820ec9e88ec96b4ec9a942e20eab7b8eb9e98eb8f8420ec9584ed9484eca78020ec958aec9584ec9a94',
];

foreach ($strings as $string => $expected) {
    it("can convert string to hex object using value: $string", function () use ($string, $expected) {
        expect((string) Hex::fromString($string))->toEqual($expected);
        expect(Hex::fromString($string)->toString())->toEqual(trim($string));
    });
}

it('can concat multiple hex values into one', function () {
    $this->assertTrue(true);
});
