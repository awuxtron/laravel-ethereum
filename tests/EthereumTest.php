<?php

use Awuxtron\LaravelEthereum\Ethereum;
use Awuxtron\LaravelEthereum\Providers\HttpProvider;
use Awuxtron\LaravelEthereum\Providers\WebsocketProvider;

it('can initialize with the http provider', function () {
    $ethereum = new Ethereum(new HttpProvider('https://data-seed-prebsc-1-s1.binance.org:8545/'));

    $this->assertInstanceOf(HttpProvider::class, $ethereum->getProvider());
});

it('can change the provider', function () {
    $ethereum = new Ethereum(new HttpProvider('https://data-seed-prebsc-1-s1.binance.org:8545/'));

    $ethereum->setProvider(new WebsocketProvider('wss://bsc-ws-node.nariox.org:443'));

    $this->assertInstanceOf(WebsocketProvider::class, $ethereum->getProvider());
});

it('should throw when passed invalid provider', function () {
    $provider = new class {
    };

    expect(fn () => new Ethereum($provider))->toThrow(TypeError::class);
});
