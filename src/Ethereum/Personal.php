<?php

namespace Awuxtron\LaravelEthereum\Ethereum;

use Awuxtron\LaravelEthereum\Types\Address;
use Awuxtron\LaravelEthereum\Utils\Hex;
use Awuxtron\LaravelEthereum\Utils\Sha3;
use Elliptic\EC;
use Exception;
use InvalidArgumentException;

class Personal
{
    /**
     * Recovers the account that signed the data.
     *
     * @param Hex|string $data      Data that was signed. If data is string, it will be converted to hex.
     * @param Hex|string $signature The signature.
     *
     * @return Address
     * @throws Exception
     */
    public function ecRecover(Hex|string $data, Hex|string $signature): Address
    {
        $signature = Hex::of($signature);

        if (!Hex::isValid($data, true)) {
            $data = Sha3::hash(sprintf("\x19Ethereum Signed Message:\n%s%s", strlen($data), $data));
        }

        $sign = [
            'r' => (string) $signature->slice(0, 32),
            's' => (string) $signature->slice(32, 32),
        ];

        $recId = ord((string) hex2bin($signature->slice(64, 1))) - 27;

        if ($recId !== ($recId & 1)) {
            throw new InvalidArgumentException('Invalid signature.');
        }

        $publicKey = @(new EC('secp256k1'))->recoverPubKey((string) $data, $sign, $recId);

        return Address::from(Sha3::hash(substr((string) hex2bin($publicKey->encode('hex')), 1))?->slice(12));
    }
}
