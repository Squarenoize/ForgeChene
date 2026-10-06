<?php

namespace App\Controller;

use App\Entity\Cart;
use App\Entity\CartItem;
use App\Entity\Product;
use App\Repository\CartRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class CartController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly CartRepository $cartRepository,
    ) {
    }

    #[Route('/cart', name: 'app_cart')]
    public function index(Request $request): Response
    {
        return $this->render('public/cart.html.twig', [
            'cart' => $this->getCart($request, false),
        ]);
    }

    #[Route('/cart/add/{id}', name: 'app_cart_add', methods: ['POST'])]
    public function add(Product $product, Request $request): RedirectResponse
    {
        $quantity = max(1, (int) $request->request->get('quantity', 1));
        $cart = $this->getCart($request, true);

        $cartItem = null;
        foreach ($cart->getCartItems() as $item) {
            if ($item->getProduct() === $product) {
                $cartItem = $item;
                break;
            }
        }

        if ($cartItem) {
            $cartItem->setQuantity($cartItem->getQuantity() + $quantity);
        } else {
            $cartItem = new CartItem();
            $cartItem->setProduct($product);
            $cartItem->setQuantity($quantity);
            $cart->addCartItem($cartItem);
            $this->entityManager->persist($cartItem);
        }

        $this->entityManager->flush();

        return $this->redirectToRoute('app_cart');
    }

    #[Route('/cart/update/{id}', name: 'app_cart_update', methods: ['POST'])]
    public function update(CartItem $cartItem, Request $request): RedirectResponse
    {
        $this->assertOwnsCartItem($cartItem, $request);

        $quantity = (int) $request->request->get('quantity', 1);

        if ($quantity < 1) {
            $this->entityManager->remove($cartItem);
        } else {
            $cartItem->setQuantity($quantity);
        }

        $this->entityManager->flush();

        return $this->redirectToRoute('app_cart');
    }

    #[Route('/cart/remove/{id}', name: 'app_cart_remove', methods: ['POST'])]
    public function remove(CartItem $cartItem, Request $request): RedirectResponse
    {
        $this->assertOwnsCartItem($cartItem, $request);

        $this->entityManager->remove($cartItem);
        $this->entityManager->flush();

        return $this->redirectToRoute('app_cart');
    }

    #[Route('/cart/clear', name: 'app_cart_clear', methods: ['POST'])]
    public function clear(Request $request): RedirectResponse
    {
        $cart = $this->getCart($request, false);

        if ($cart) {
            foreach ($cart->getCartItems() as $item) {
                $this->entityManager->remove($item);
            }
            $this->entityManager->flush();
        }

        return $this->redirectToRoute('app_cart');
    }

    /**
     * Fetches the cart stored in session, optionally creating a new one.
     */
    private function getCart(Request $request, bool $createIfMissing): ?Cart
    {
        $session = $request->getSession();
        $cartId = $session->get('cart_id');

        $cart = $cartId ? $this->cartRepository->find($cartId) : null;

        if (!$cart && $createIfMissing) {
            $cart = new Cart();
            $cart->setCreatedAt(new \DateTimeImmutable());
            $this->entityManager->persist($cart);
            $this->entityManager->flush();
            $session->set('cart_id', $cart->getId());
        }

        return $cart;
    }

    /**
     * Prevents acting on a cart item that doesn't belong to the current session's cart.
     */
    private function assertOwnsCartItem(CartItem $cartItem, Request $request): void
    {
        if ($cartItem->getCart()?->getId() !== $request->getSession()->get('cart_id')) {
            throw $this->createAccessDeniedException();
        }
    }
}
