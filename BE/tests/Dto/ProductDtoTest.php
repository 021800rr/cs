<?php

namespace App\Tests\Dto;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use App\Dto\ProductDto;
use App\Tests\SetUpTrait;
use PHPUnit\Framework\Attributes\DataProvider;

class ProductDtoTest extends ApiTestCase
{
    use SetUpTrait;

    protected function setUp(): void
    {
        $this->setUpValidator();
    }

    #[DataProvider('priceProvider')]
    public function testProductDto(null|float|int $price, int $expectedViolationCount): void
    {
        $dto = new ProductDto();
        $dto->name = 'Test Product';
        $dto->description = 'Test Description';
        $dto->price = $price;

        $violations = $this->validator->validate($dto);

        $this->assertCount($expectedViolationCount, $violations);
    }

    /**
     * @return array<int, array{0: float|int|null, 1: int}>
     */
    public static function priceProvider(): array
    {
        return [
            [10, 0], // Valid case
            [11.1, 0], // Valid case
            [null, 1], // Missing price
        ];
    }

    public function testProductDtoWithMissingName(): void
    {
        $dto = new ProductDto();
        $dto->description = 'Test Description';
        $dto->price = 10;

        $violations = $this->validator->validate($dto);

        $this->assertCount(1, $violations);
        $this->assertSame('Ta wartość nie powinna być pusta.', $violations[0]?->getMessage());
    }
}
