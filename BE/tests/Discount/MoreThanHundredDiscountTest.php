<?php

namespace App\Tests\Discount;

use App\Discount\MoreThanHundredDiscount;
use App\Entity\Cart;
use PHPUnit\Framework\TestCase;

class MoreThanHundredDiscountTest extends TestCase
{
    public function testApplyDiscountAboveThreshold(): void
    {
        $cart = new Cart();
        $policy = new MoreThanHundredDiscount();
        $totalValue = 150.0;

        $result = $policy->apply($cart, $totalValue);
        $this->assertSame(135.0, $result);
    }

    public function testNoDiscountBelowThreshold(): void
    {
        $cart = new Cart();
        $policy = new MoreThanHundredDiscount();
        $totalValue = 90.0;

        $result = $policy->apply($cart, $totalValue);
        $this->assertSame(90.0, $result);
    }
}
