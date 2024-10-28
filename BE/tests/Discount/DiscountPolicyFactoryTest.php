<?php

namespace App\Tests\Discount;

use App\Discount\DiscountPolicyFactory;
use App\Discount\DiscountPolicyInterface;
use PHPUnit\Framework\TestCase;

class DiscountPolicyFactoryTest extends TestCase
{
    public function testFactoryCreatesPolicies(): void
    {
        $factory = new DiscountPolicyFactory();
        $policies = $factory->createPolicies();

        $this->assertNotEmpty($policies, 'The factory should create at least one policy.');
        foreach ($policies as $policy) {
            $this->assertInstanceOf(DiscountPolicyInterface::class, $policy, 'Each policy should implement DiscountPolicyInterface.');
        }

        // Check for specific policy names
        $policyNames = array_map(fn($policy) => $policy->getName(), $policies);
        $this->assertContains('moreThanHundred', $policyNames, 'Factory should include the moreThanHundred discount policy.');
        $this->assertContains('oneOfFive', $policyNames, 'Factory should include the oneOfFive discount policy.');
    }
}
