<?php

namespace App\Tests\Entity;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use App\Dto\ProductDto;
use App\Entity\Product;
use App\Entity\User;
use App\Tests\SetUpTrait;
use PHPUnit\Framework\Attributes\DataProvider;

class ProductTest extends ApiTestCase
{
    use SetUpTrait;

    private const array NEW_PRODUCT = [
        'name' => 'New Product',
        'description' => 'Product Description',
        'price' => 100.1
    ];
    private const array UPDATED_PRODUCT = [
        'name' => 'Updated Product',
        'description' => 'Updated Description',
        'price' => 110.1
    ];

    private string $editorToken;
    private string $userToken;

    protected function setUp(): void
    {
        $this->editorToken = $this->login(self::EDITOR_MAIL, self::PLAIN_PASSWORD);
        $this->userToken = $this->login(self::USER_MAIL, self::PLAIN_PASSWORD);
        $this->setUpRepositories();
    }

    public function testUserCanAccessProducts(): void
    {
        self::createClient()->request('GET', self::PRODUCTS_URL, [
            'auth_bearer' => $this->userToken,
        ]);

        $this->assertResponseIsSuccessful();
    }

    public function testUserCannotCreateProduct(): void
    {
        self::createClient()->request('POST', self::PRODUCTS_URL, [
            'auth_bearer' => $this->userToken,
            'headers' => self::HEADERS,
            'json' => self::NEW_PRODUCT,
        ]);

        $this->assertResponseStatusCodeSame(403);
    }

    public function testEditorCanCreateProduct(): void
    {
        self::createClient()->request('POST', self::PRODUCTS_URL, [
            'auth_bearer' => $this->editorToken,
            'headers' => self::HEADERS,
            'json' => self::NEW_PRODUCT,
        ]);

        $this->assertResponseStatusCodeSame(201);
    }

    public function testUserCannotUpdateProduct(): void
    {
        self::createClient()->request('PUT', self::PRODUCTS_URL . '/' . self::PRODUCT_ID_1, [
            'auth_bearer' => $this->userToken,
            'headers' => self::HEADERS,
            'json' => self::UPDATED_PRODUCT,
        ]);

        $this->assertResponseStatusCodeSame(403);
    }

    public function testEditorCanUpdateProduct(): void
    {
        $client = self::createClient();

        $response = $client->request('GET', self::PRODUCTS_URL . '/' . self::PRODUCT_ID_1, [
            'auth_bearer' => $this->editorToken,
            'headers' => self::HEADERS,
        ]);

        $this->assertResponseStatusCodeSame(200);

        $apiProduct = $response->toArray();
        /** @var Product $dbProduct */
        $dbProduct = $this->productRepository->find(self::PRODUCT_ID_1);

        $this->assertEquals($dbProduct->getName(), $apiProduct['name']);
        $this->assertEquals($dbProduct->getDescription(), $apiProduct['description']);
        $this->assertEquals($dbProduct->getPrice(), $apiProduct['price']);

        $client->request('PUT', self::PRODUCTS_URL . '/' . self::PRODUCT_ID_1, [
            'auth_bearer' => $this->editorToken,
            'headers' => self::HEADERS,
            'json' => self::UPDATED_PRODUCT,
        ]);

        $this->assertResponseStatusCodeSame(200);

        // Fetch the updated product to verify the changes
        $response = $client->request('GET', self::PRODUCTS_URL . '/' . self::PRODUCT_ID_1, [
            'auth_bearer' => $this->editorToken,
            'headers' => self::HEADERS,
        ]);

        $this->assertResponseStatusCodeSame(200);

        $updatedProduct = $response->toArray();

        $this->assertEquals(self::UPDATED_PRODUCT['name'], $updatedProduct['name']);
        $this->assertEquals(self::UPDATED_PRODUCT['description'], $updatedProduct['description']);
        $this->assertEquals(self::UPDATED_PRODUCT['price'], $updatedProduct['price']);
    }

    public function testUserCannotDeleteProduct(): void
    {
        self::createClient()->request('DELETE', self::PRODUCTS_URL . '/4', [
            'auth_bearer' => $this->userToken,
        ]);

        $this->assertResponseStatusCodeSame(403);
    }

    public function testEditorCanDeleteProduct(): void
    {
        self::createClient()->request('DELETE', self::PRODUCTS_URL . '/4', [
            'auth_bearer' => $this->editorToken,
        ]);

        $this->assertResponseStatusCodeSame(204);
    }

    public function testEditorCannotCreateProductWithMissingFields(): void
    {
        self::createClient()->request('POST', self::PRODUCTS_URL, [
            'auth_bearer' => $this->editorToken,
            'headers' => self::HEADERS,
            'json' => [
                'name' => '',
                'price' => null,
            ],
        ]);

        $this->assertResponseStatusCodeSame(422);
    }

    public function testEditorCannotCreateProductWithInvalidPrice(): void
    {
        self::createClient()->request('POST', self::PRODUCTS_URL, [
            'auth_bearer' => $this->editorToken,
            'headers' => self::HEADERS,
            'json' => [
                'name' => 'Invalid Price Product',
                'price' => -100,
            ],
        ]);

        $this->assertResponseStatusCodeSame(422);
        $this->assertJsonContains(['hydra:description' => 'price: Ta wartość powinna być dodatnia.']);
    }
}
