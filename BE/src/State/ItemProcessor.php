<?php

namespace App\State;

use ApiPlatform\Metadata\DeleteOperationInterface;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Repository\CartRepository;
use App\Repository\CartItemRepository;
use DateTime;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @implements ProcessorInterface<mixed, void>
 */
readonly class ItemProcessor implements ProcessorInterface
{
    public function __construct(
        private CartRepository $cartRepository,
        private CartItemRepository $cartItemRepository
    ) {
    }

    /**
     * {@inheritDoc}
     */
    #[\Override] public function process(
        mixed $data,
        Operation $operation,
        array $uriVariables = [],
        array $context = []
    ): void {
        if (!$operation instanceof DeleteOperationInterface) {
            throw new HttpException(
                Response::HTTP_BAD_REQUEST,
                'Unsupported operation.'
            );
        }

        if (!isset($uriVariables['id'])) {
            throw new HttpException(
                Response::HTTP_BAD_REQUEST,
                'Missing required parameter: id.'
            );
            //            throw new BadRequestException('Missing required parameter: id.');
        }

        $item = $this->cartItemRepository->find($uriVariables['id']);

        if (!$item) {
            throw new NotFoundHttpException('Item not found.');
        }

        $cart = $item->getCart();
        if (!$cart) {
            throw new NotFoundHttpException('Cart not found.');
        }

        $cart->removeItem($item);
        $cart->setUpdatedAt(new DateTime());
        $this->cartRepository->save($cart, true);
    }
}
