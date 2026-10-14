<?php

namespace App\Twig;

use App\Repository\CartRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\RequestStack;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class CartExtension extends AbstractExtension
{
    public function __construct(
        private readonly CartRepository $cartRepository,
        private readonly RequestStack $requestStack,
        private readonly Security $security,
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
        //Get the active cart of the current user
        $user = $this->security->getUser();
        
        if (!$user) {
            return 0;
        }

        $cart = $this->cartRepository->findActiveCartByUserId($user->getId());

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
