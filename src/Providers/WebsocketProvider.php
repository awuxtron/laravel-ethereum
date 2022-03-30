<?php

namespace Awuxtron\LaravelEthereum\Providers;

class WebsocketProvider extends Provider
{
    /**
     * Create a new socket provider instance.
     *
     * @param string $provider The provider socket url.
     */
    public function __construct(protected string $provider)
    {
    }
}
