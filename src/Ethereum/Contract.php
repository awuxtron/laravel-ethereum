<?php

namespace Awuxtron\LaravelEthereum\Ethereum;

use Awuxtron\LaravelEthereum\Ethereum;

class Contract
{
    /**
     * Create a new contract instance.
     *
     * @param Ethereum $ethereum The Ethereum Instance.
     */
    public function __construct(protected Ethereum $ethereum)
    {
    }
}
