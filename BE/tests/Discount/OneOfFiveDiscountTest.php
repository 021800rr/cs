<?php

namespace App\Tests\Discount;

use App\Discount\OneOfFiveDiscount;
use App\Entity\Cart;
use App\Entity\CartItem;
use App\Entity\Product;
use PHPUnit\Framework\TestCase;

class OneOfFiveDiscountTest extends TestCase
{
    public function testApplyDiscountWithExactMultipleOfFive(): void
    {
        $cart = new Cart();
        $policy = new OneOfFiveDiscount();
        $product = (new Product())->setPrice(20.0);
        $this->setProductId($product, 1);

        $item = (new CartItem())->setProduct($product)->setQuantity(5);
        $cart->addItem($item);
        $totalValue = 100.0;

        $result = $policy->apply($cart, $totalValue);
        $this->assertSame(80.0, $result);
    }

    public function testApplyDiscountWithMoreThanFiveButNotMultiple(): void
    {
        $cart = new Cart();
        $policy = new OneOfFiveDiscount();
        $product = (new Product())->setPrice(20.0);
        $this->setProductId($product, 1);

        $item = (new CartItem())->setProduct($product)->setQuantity(7);
        $cart->addItem($item);
        $totalValue = 140.0;

        $result = $policy->apply($cart, $totalValue);
        $this->assertSame(120.0, $result);
    }

    public function testApplyDiscountWithMoreThanFiveMultipleProduct(): void
    {
        $cart = new Cart();
        $policy = new OneOfFiveDiscount();
        $product = (new Product())->setPrice(20.0);
        $this->setProductId($product, 1);

        $item = (new CartItem())->setProduct($product)->setQuantity(3);
        $cart->addItem($item);
        $item = (new CartItem())->setProduct($product)->setQuantity(4);
        $cart->addItem($item);
        $totalValue = 140.0;

        $result = $policy->apply($cart, $totalValue);
        $this->assertSame(120.0, $result);
    }

    public function testNoDiscountWithLessThanFive(): void
    {
        $cart = new Cart();
        $policy = new OneOfFiveDiscount();
        $product = (new Product())->setPrice(20.0);
        $this->setProductId($product, 1);

        $item = (new CartItem())->setProduct($product)->setQuantity(3);
        $cart->addItem($item);
        $totalValue = 60.0;

        $result = $policy->apply($cart, $totalValue);
        $this->assertSame(60.0, $result);
    }

    private function setProductId(Product $product, int $id): void
    {
        $reflection = new \ReflectionObject($product);
        $property = $reflection->getProperty('id');
        $property->setValue($product, $id);
    }
}
