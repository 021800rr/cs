<?php

namespace App\Discount;

use App\Entity\Cart;

class OneOfFiveDiscount implements DiscountPolicyInterface
{
    public function apply(Cart $cart, float $totalValue): float
    {
        $productCount = [];

        foreach ($cart->getItems() as $item) {
            $product = $item->getProduct();
            if ($product === null || !$product->getId()) {
                continue;
            }

            $productId = $product->getId();
            $count = $item->getQuantity();
            $price = $product->getPrice();

            if (!isset($productCount[$productId])) {
                $productCount[$productId] = ['count' => 0, 'price' => $price];
            }

            $productCount[$productId]['count'] += $count;
        }

        foreach ($productCount as $data) {
            $count = $data['count'];
            $price = $data['price'];

            if ($count >= 5) {
                $maxMultipleOfFive = floor($count / 5);
                $discountValue = round($maxMultipleOfFive * $price, 2);
                $totalValue -= $discountValue;
            }
        }

        return round($totalValue, 2);
    }

    public function getName(): string
    {
        return 'oneOfFive';
    }
}
