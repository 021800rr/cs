<?php

namespace App\Service;

use App\Entity\Cart;
use App\Discount\DiscountPolicyFactory;

readonly class CartCalculator implements CartCalculatorInterface
{
    public function __construct(private DiscountPolicyFactory $discountPolicyFactory)
    {
    }

    /** {@inheritDoc} */
    public function calculateTotal(Cart $cart): array
    {
        $totalValue = 0.0;

        foreach ($cart->getItems() as $item) {
            if ($product = $item->getProduct()) {
                $totalValue += $product->getPrice() * $item->getQuantity();
            }
        }

        $discountedTotals = $this->applyDiscountPolicies($cart, $totalValue);
        $minimumValue = (float) min($discountedTotals);

        if ($minimumValue < $totalValue) {
            return [
                array_search($minimumValue, $discountedTotals, true) ?: null,
                $minimumValue
            ];
        }

        return [null, $totalValue];
    }

    /**
     * Applies all discount policies to the cart and returns the resulting values.
     *
     * @param Cart $cart
     * @param float $totalValue
     * @return array<string, float> Discounted total values based on different policies
     */
    private function applyDiscountPolicies(Cart $cart, float $totalValue): array
    {
        $discountedTotals = [];
        foreach ($this->discountPolicyFactory->createPolicies() as $policy) {
            $discountedTotals[$policy->getName()] = $policy->apply($cart, $totalValue);
        }

        return $discountedTotals;
    }
}
