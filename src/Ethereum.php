<?php

namespace Awuxtron\LaravelEthereum;

use Awuxtron\LaravelEthereum\Providers\Provider;

class Ethereum
{
    /**
     * The provider instance.
     *
     * @var Provider
     */
    protected static Provider $provider;

    /**
     * Create a new Ethereum instance with given provider.
     *
     * @param Provider $provider
     */
    public function __construct(Provider $provider)
    {
        self::$provider = $provider;
    }

    /**
     * Get the provider instance.
     *
     * @return Provider
     */
    public function getProvider(): Provider
    {
        return self::$provider;
    }

    /**
     * Set the provider instance.
     *
     * @param Provider $provider
     */
    public function setProvider(Provider $provider): void
    {
        self::$provider = $provider;
    }
}
