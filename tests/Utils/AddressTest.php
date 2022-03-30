<?php

use Awuxtron\LaravelEthereum\Utils\Address;

it('can check checksum address', function () {
    $tests = [
        '0x52908400098527886E0F7030069857D2E4169EE7' => true,
        '0x8617E340B3D01FA5F11F306F4090FD50E238070D' => true,
        '0xde709f2102306220921060314715629080e2fb77' => true,
        '0x27b1fdb04752bbc536007a920d24acb045561c26' => true,
        '0x5aAeb6053F3E94C9b9A09f33669435E7Ef1BeAed' => true,
        '0xfB6916095ca1df60bB79Ce92cE3Ea74c37c5d359' => true,
        '0xdbF03B407c01E7cD3CBea99509d93f8DDDC8C6FB' => true,
        '0xD1220A0cf47c7B9Be7A2E6BA89F429762e7b9aDb' => true,
        '0XD1220A0CF47C7B9BE7A2E6BA89F429762E7B9ADB' => false,
        '0xd1220a0cf47c7b9be7a2e6ba89f429762e7b9adb' => false,
    ];

    foreach ($tests as $value => $expected) {
        $this->assertEquals(Address::isValidChecksum($value), $expected);
    }
});

it('can check valid address', function () {
    $tests = [
        1 => false,
        '1' => false,
        0x1 => false,
        0x8617E340B3D01FA5F11F306F4090FD50E238070D => false,
        '0xc6d9d2cd449a754c494264e1809c50e34d64562b' => true,
        'c6d9d2cd449a754c494264e1809c50e34d64562b' => true,
        '0xE247A45c287191d435A8a5D72A7C8dc030451E9F' => true,
        '0xE247a45c287191d435A8a5D72A7C8dc030451E9F' => false,
        '0xe247a45c287191d435a8a5d72a7c8dc030451e9f' => true,
        '0xE247A45C287191D435A8A5D72A7C8DC030451E9F' => true,
        '0XE247A45C287191D435A8A5D72A7C8DC030451E9F' => true,
    ];

    foreach ($tests as $value => $expected) {
        $this->assertEquals(Address::isValid($value), $expected);
    }
});

it('can convert to checksum address', function () {
    $tests = [
        '0x52908400098527886e0f7030069857d2e4169ee7',
        '0x8617e340b3d01fa5f11f306f4090fd50e238070d',
        '0xDE709F2102306220921060314715629080E2FB77',
        '0x27B1FDB04752BBC536007A920D24ACB045561C26',
        '0XD1220A0CF47C7B9BE7A2E6BA89F429762E7B9ADB',
        '0xd1220a0cf47c7b9be7a2e6ba89f429762e7b9adb',
    ];

    foreach ($tests as $value) {
        expect(Address::isValidChecksum(Address::toChecksum($value)->prefixed()))->toBeTrue();
    }
});
