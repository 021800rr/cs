<?php

namespace App\Discount;

use App\Entity\Cart;

interface DiscountPolicyInterface
{
    public function apply(Cart $cart, float $totalValue): float;

    public function getName(): string;
}
