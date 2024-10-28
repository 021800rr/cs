<?php

namespace App\Tests\Entity;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use App\Entity\Product;
use App\Tests\SetUpTrait;
use DateTime;
use App\Entity\Cart;

class CartTest extends ApiTestCase
{
    use SetUpTrait;

    protected function setUp(): void
    {
        $this->setUpRepositories();
    }

    public function testGet(): void
    {
        $token = $this->login(self::USER_MAIL, self::PLAIN_PASSWORD);

        $response = static::createClient()->request('GET', self::CARTS_URL . '/' . self::CART_ID, [
            'auth_bearer' => $token,
            'headers' => self::HEADERS,
        ]);

        $this->assertResponseStatusCodeSame(200);

        /** @var object{id: int, user: string, totalValue: float} $cart */
        $cart = json_decode($response->getContent());
        $this->assertSame(self::CART_ID, $cart->id);
        $this->assertSame(self::USERS_URL . '/' . self::USER_ID, $cart->user);
        $this->assertEquals(54.4, $cart->totalValue);
    }

    public function testGetNotFound(): void
    {
        $token = $this->login(self::ADMIN_MAIL, self::PLAIN_PASSWORD);

        static::createClient()->request('GET', self::CARTS_URL . '/' . self::CART_ID, [
            'auth_bearer' => $token,
            'headers' => self::HEADERS,
        ]);

        $this->assertResponseStatusCodeSame(404);
    }

    public function testGetCollection(): void
    {
        $token = $this->login(self::USER_MAIL, self::PLAIN_PASSWORD);

        $response = static::createClient()->request('GET', self::CARTS_URL, [
            'auth_bearer' => $token,
            'headers' => self::HEADERS,
        ]);

        $this->assertResponseStatusCodeSame(200);

        /** @var array<"hydra:member", array<int, mixed>> $carts */
        $carts = json_decode($response->getContent(), true);
        $this->assertCount(2, $carts[self::HYDRA_MEMBER]);
    }

    public function testPost(): void
    {
        $now = new DateTime();
        sleep(2);
        $token = $this->login(self::USER_MAIL, self::PLAIN_PASSWORD);

        $response = static::createClient()->request('POST', self::CARTS_URL, [
            'auth_bearer' => $token,
            'headers' => self::HEADERS,
            'json' => []
        ]);

        $this->assertResponseStatusCodeSame(201);

        /** @var object{id: int, user: string, createdAt: string} $cart */
        $cart = json_decode($response->getContent());

        $this->assertSame(self::USERS_URL . '/' . self::USER_ID, $cart->user);
        $this->assertGreaterThan($now->format(DateTime::ATOM), $cart->createdAt);
    }

    public function testPut(): void
    {
        $token = $this->login(self::USER_MAIL, self::PLAIN_PASSWORD);

        $data = [
            'items' => [
                [
                    'product' => self::PRODUCTS_URL . '/' . self::PRODUCT_ID_1,
                    'quantity' => 1
                ],
                [
                    'product' => self::PRODUCTS_URL . '/' . self::PRODUCT_ID_1,
                    'quantity' => 2
                ],
            ]
        ];

        static::createClient()->request('PUT', self::CARTS_URL . '/' . self::CART_ID, [
            'auth_bearer' => $token,
            'headers' => self::HEADERS,
            'json' => $data,
        ]);

        $this->assertResponseStatusCodeSame(200);

        /** @var Cart $cart */
        $cart = $this->cartRepository->find(self::CART_ID);
        $items = $cart->getItems();

        $this->assertCount(2, $items);

        /** @var Product $product1 */
        $product1 = $this->productRepository->find(self::PRODUCT_ID_1);

        $expectedItems = [
            ['name' => $product1->getName(), 'price' => $product1->getPrice(), 'quantity' => 1],
            ['name' => $product1->getName(), 'price' => $product1->getPrice(), 'quantity' => 2],
        ];

        foreach ($items as $index => $item) {
            /** @var Product $product */
            $product = $item->getProduct();
            $this->assertSame($expectedItems[$index]['name'], $product->getName());
            $this->assertEquals($expectedItems[$index]['price'], $item->getPrice());
            $this->assertSame($expectedItems[$index]['quantity'], $item->getQuantity());
        }
    }

    public function testPutNotFound(): void
    {
        $token = $this->login(self::ADMIN_MAIL, self::PLAIN_PASSWORD);

        static::createClient()->request('PUT', self::CARTS_URL . '/' . self::CART_ID, [
            'auth_bearer' => $token,
            'headers' => self::HEADERS,
            'json' => [
                "product" => self::PRODUCTS_URL . '/' . self::PRODUCT_ID_1,
                "quantity" => 1,
            ]
        ]);

        $this->assertResponseStatusCodeSame(404);
    }

    public function testGetAndSetTotalValue(): void
    {
        $cart = new Cart();
        $cart->setTotalValue(100.5);
        $this->assertSame(100.5, $cart->getTotalValue());
    }

    public function testPutWithZeroQuantity(): void
    {
        $token = $this->login(self::USER_MAIL, self::PLAIN_PASSWORD);

        $data = [
            'items' => [
                [
                    'product' => self::PRODUCTS_URL . '/' . self::PRODUCT_ID_1,
                    'quantity' => 0
                ],
            ]
        ];

        static::createClient()->request('PUT', self::CARTS_URL . '/' . self::CART_ID, [
            'auth_bearer' => $token,
            'headers' => self::HEADERS,
            'json' => $data,
        ]);

        $this->assertResponseStatusCodeSame(400);
    }
}
