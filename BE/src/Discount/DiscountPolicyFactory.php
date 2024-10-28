<?php

namespace App\Discount;

class DiscountPolicyFactory
{
    /**
     * @return DiscountPolicyInterface[]
     */
    public function createPolicies(): array
    {
        return [
            new MoreThanHundredDiscount(),
            new OneOfFiveDiscount(),
            // Add new policies here without modifying existing code
        ];
    }
}
