<?php

namespace Awuxtron\LaravelEthereum\Providers;

class HttpProvider extends Provider
{
    /**
     * Create a new HTTP provider instance.
     *
     * @param string $provider The provider HTTP url.
     */
    public function __construct(protected string $provider)
    {
    }
}
