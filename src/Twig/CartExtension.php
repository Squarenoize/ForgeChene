<?php

namespace App\Twig;

use App\Repository\CartRepository;
use Symfony\Component\HttpFoundation\RequestStack;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class CartExtension extends AbstractExtension
{
    public function __construct(
        private readonly CartRepository $cartRepository,
        private readonly RequestStack $requestStack,
    ) {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('cart_item_count', [$this, 'getCartItemCount']),
        ];
    }

    public function getCartItemCount(): int
    {
        $cartId = $this->requestStack->getSession()->get('cart_id');
        $cart = $cartId ? $this->cartRepository->find($cartId) : null;

        if (!$cart) {
            return 0;
        }

        $count = 0;
        foreach ($cart->getCartItems() as $item) {
            $count += $item->getQuantity();
        }

        return $count;
    }
}
