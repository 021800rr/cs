<?php

namespace App\Tests\Service;

use App\Discount\DiscountPolicyFactory;
use App\Entity\Cart;
use App\Entity\CartItem;
use App\Entity\Product;
use App\Service\CartCalculator;
use PHPUnit\Framework\TestCase;

class CartCalculatorTest extends TestCase
{
    private CartCalculator $cartCalculator;

    protected function setUp(): void
    {
        $discountPolicyFactory = new DiscountPolicyFactory();
        $this->cartCalculator = new CartCalculator($discountPolicyFactory);
    }

    public function testCalculateTotalWithEmptyCart(): void
    {
        $cart = new Cart();
        $result = $this->cartCalculator->calculateTotal($cart);

        $this->assertSame([null, 0.0], $result);
    }

    public function testCalculateTotalWithInvalidProduct(): void
    {
        // Test calculating the total value when a cart item does not have a valid product
        $cart = new Cart();
        $item = (new CartItem())->setQuantity(3);
        $cart->addItem($item);

        // Calculate the total with discounts applied
        $result = $this->cartCalculator->calculateTotal($cart);

        // Assert that items without valid products are ignored in the total calculation
        $this->assertSame(
            [null, 0.0],
            $result,
            'Items without valid products should be ignored in the total calculation.'
        );
    }

    public function testCalculateTotalWithMultipleDiscountsScenarioOne(): void
    {
        // Test calculating the total value when multiple discounts could apply
        $cart = new Cart();
        $product1 = (new Product())->setPrice(44.4);
        $product2 = (new Product())->setPrice(10.0);

        // Set unique IDs for products
        $this->setProductId($product1, 1);
        $this->setProductId($product2, 2);

        // Add products to the cart
        $item1 = (new CartItem())->setProduct($product1)->setQuantity(5); // 5 x 44.4 -> 222
        $item2 = (new CartItem())->setProduct($product2)->setQuantity(6); // 6 x 10 -> 60

        $cart->addItem($item1);
        $cart->addItem($item2);

        // Calculate the total with discounts applied
        $result = $this->cartCalculator->calculateTotal($cart);

        // Assert the expected total after applying the most beneficial discount
        // (5 - 1) x 44.4 + (6 - 1) x 10 = 227.6
        // (5 x 44.4 + 6 x 10) - 10% = 253.8
        $this->assertSame(['oneOfFive', 227.6], $result);
    }

    public function testCalculateTotalWithMultipleDiscountsScenarioTwo(): void
    {
        // Test calculating the total value with products triggering different discounts
        $cart = new Cart();
        $product1 = (new Product())->setPrice(30.0);
        $product2 = (new Product())->setPrice(10.0);

        // Set unique IDs for products
        $this->setProductId($product1, 1);
        $this->setProductId($product2, 2);

        // Add products to the cart
        $item1 = (new CartItem())->setProduct($product1)->setQuantity(4); // 4 x 30 -> 120
        $item2 = (new CartItem())->setProduct($product2)->setQuantity(5); // 5 x 10 -> 50

        $cart->addItem($item1);
        $cart->addItem($item2);

        // Calculate the total with discounts applied
        $result = $this->cartCalculator->calculateTotal($cart);

        // Assert the expected total after applying the most beneficial discount
        // 4 x 30 + (5 - 1) x 10 = 160
        // (4 x 30 + 5 x 10) - 10% = 153
        $this->assertSame(['moreThanHundred', 153.0], $result);
    }


    private function setProductId(Product $product, int $id): void
    {
        $reflection = new \ReflectionObject($product);
        $property = $reflection->getProperty('id');
        $property->setValue($product, $id);
    }
}
