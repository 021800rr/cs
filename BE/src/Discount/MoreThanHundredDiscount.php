<?php

namespace App\Discount;

use App\Entity\Cart;

class MoreThanHundredDiscount implements DiscountPolicyInterface
{
    public function apply(Cart $cart, float $totalValue): float
    {
        if ($totalValue > 100) {
            return $totalValue - round($totalValue / 10, 2);
        }

        return $totalValue;
    }

    public function getName(): string
    {
        return 'moreThanHundred';
    }
}
