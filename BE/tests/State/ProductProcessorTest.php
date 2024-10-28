<?php

namespace App\Tests\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Dto\ProductDto;
use App\Entity\Product;
use App\Repository\ProductRepository;
use App\State\ProductProcessor;
use Doctrine\ORM\EntityRepository;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ProductProcessorTest extends TestCase
{
    /** @var ProductRepository&MockObject */
    private ProductRepository $productRepository;
    private ProductProcessor $productProcessor;

    protected function setUp(): void
    {
        $this->productRepository = $this->createMock(ProductRepository::class);
        $this->productProcessor = new ProductProcessor($this->productRepository);
    }

    public function testPostProcessValidProductDto(): void
    {
        $productDto = new ProductDto();
        $productDto->name = 'Test Product';
        $productDto->description = 'Test Description';
        $productDto->price = 10.1;

        $this->productRepository->expects($this->once())->method('save');

        $this->productProcessor->process($productDto, new Post());
    }

    public function testPutProcessValidProductDto(): void
    {
        $productDto = new ProductDto();
        $productDto->name = 'Updated Product';
        $productDto->description = 'Updated Description';
        $productDto->price = 110.1;

        $uriVariables = ['id' => 1];

        $existingProduct = new Product();
        $existingProduct->setName('Old Product');
        $existingProduct->setDescription('Old Description');

        $this->productRepository->expects($this->once())
            ->method('find')
            ->with($uriVariables['id'])
            ->willReturn($existingProduct);

        $this->productRepository->expects($this->once())->method('save');

        $this->productProcessor->process($productDto, new Put(), $uriVariables);
    }

    public function testPostProcessWithMissingPrice(): void
    {
        $productDto = new ProductDto();
        $productDto->name = 'Test Product';
        $productDto->description = 'Test Description';
        $productDto->price = null;

        $this->productRepository->expects($this->once())->method('save');

        $this->productProcessor->process($productDto, new Post());
    }

    public function testProcessInvalidProductDto(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Expected instance of ProductDto');

        $operation = $this->createMock(Operation::class);
        $invalidData = new \stdClass();

        // @phpstan-ignore-next-line
        $this->productProcessor->process($invalidData, $operation);
    }

    public function testPutProcessNonExistentProduct(): void
    {
        $productDto = new ProductDto();
        $productDto->name = 'Non Existent Product';
        $productDto->description = 'Non Existent Description';
        $productDto->price = 110.1;

        $uriVariables = ['id' => 999];

        $this->productRepository->expects($this->once())
            ->method('find')
            ->with($uriVariables['id'])
            ->willReturn(null);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Product not found');

        $this->productProcessor->process($productDto, new Put(), $uriVariables);
    }
}
