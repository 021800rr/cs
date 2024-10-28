<?php

namespace App\Service;

use App\Entity\Cart;

interface CartCalculatorInterface
{
    /**
     * @param Cart $cart
     * @return array{0: null|string, 1: float}
     */
    public function calculateTotal(Cart $cart): array;
}
