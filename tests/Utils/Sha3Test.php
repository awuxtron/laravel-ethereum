<?php

use Awuxtron\LaravelEthereum\Utils\Sha3;

it('should return hex object with sha3 value', function () {
    $tests = [
        '' => '0x' . Sha3::SHA3_NULL_HASH,
        'test123' => '0xf81b517a242b218999ec8eec0ea6e2ddbef2a367a14e93f4a32a39e260f686ad',
        'test(int)' => '0xf4d03772bec1e62fbe8c5691e1a9101e520e8f8b5ca612123694632bf3cb51b1',
        '0x80' => '0x56e81f171bcc55a6ff8345e692c0f86e5b48e01b996cadc001622fb5e363b421',
        '0x3c9229289a6125f7fdf1885a77bb12c37a8d3b4962d936f7e3084dece32a3ca1' => '0x82ff40c0a986c6a5cfad4ddf4c3aa6996f1a7837f9c398e17e5de5cbd5a12b28',
        '0x265385c7f4132228a0d54eb1a9e7460b91c0cc68:9382:image' => '0x3ec7b047254e2b906ed1d3af460b970ce1c9001b3882e42cdcc090e6ca049fa5',
    ];

    foreach ($tests as $value => $expected) {
        expect(Sha3::hash($value, true)->prefixed())->toEqual($expected);
    }
});

it('should not return the same sha3 hash', function () {
    $this->assertNotEquals(
        (string) Sha3::hash('0x265385c7f4132228a0d54eb1a9e7460b91c0cc68:9382:image'),
        (string) Sha3::hash('0x265385c7f4132228a0d54eb1a9e7460b91c0cc68:2382:image')
    );
});
