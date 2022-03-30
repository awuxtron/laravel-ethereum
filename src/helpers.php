<?php

use Brick\Math\BigInteger;
use Brick\Math\BigNumber;

if (!function_exists('nearest_divisible')) {
    /**
     * Find the nearest number larger than $a divisible by $b.
     *
     * @param BigNumber|int $a
     * @param BigNumber|int $b
     *
     * @return BigNumber
     */
    function nearest_divisible(BigNumber|int $a, BigNumber|int $b): BigNumber
    {
        $a = BigInteger::of($a)->minus(1);
        $b = BigInteger::of($b);

        return $a->plus($b)->minus($a->remainder($b));
    }
}
