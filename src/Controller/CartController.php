<?php

namespace App\Controller;

use App\Entity\Cart;
use App\Entity\CartItem;
use App\Entity\Order;
use App\Entity\OrderItem;
use App\Entity\OrderStatusHistory;
use App\Entity\Product;
use App\Enum\OrderStatusEnum;
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

    #[Route('/cart', name: 'app_cart', methods: ['GET'])]
    public function cartView(Request $request): Response
    {
        //Get the active cart of the current user
        $cart = $this->cartRepository->findActiveCartByUserId($this->getUser()->getId());

        if (!$cart) {
            $cartExist = false;
            $cart = null;
        }

        return $this->render('public/cart.html.twig', [
            'cart' => $cart,
            'cartExist' => $cartExist ?? true,
        ]);
    }

    #[Route('/cart/add/{id}', name: 'app_cart_add', methods: ['POST'])]
    public function add(Product $product, Request $request): RedirectResponse
    {
        // Get the quantity from the request, defaulting to 1 and ensuring it's at least 1
        $quantity = max(1, (int) $request->request->get('quantity', 1));

        //Get the active cart of the current user
        $cart = $this->cartRepository->findActiveCartByUserId($this->getUser()->getId());

        if (!$cart) {
            $cart = new Cart();
            $cart->setUser($this->getUser());
            $cart->setCreatedAt(new \DateTimeImmutable());
            $this->entityManager->persist($cart);
        }

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
    
        // Ensure the current user owns the cart item before updating it
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
        $cart = $this->cartRepository->findActiveCartByUserId($this->getUser()->getId());

        if ($cart) {
            foreach ($cart->getCartItems() as $item) {
                $this->entityManager->remove($item);
            }
            $this->entityManager->flush();
        }

        return $this->redirectToRoute('app_cart');
    }

    #[Route('/cart/validate', name: 'app_cart_validate', methods: ['POST'])]
    public function validate(Request $request): RedirectResponse
    {
        $cart = $this->cartRepository->findActiveCartByUserId($this->getUser()->getId());

        if (!$cart || $cart->getCartItems()->isEmpty()) {
            return $this->redirectToRoute('app_cart');
        }

        $now = new \DateTimeImmutable();

        $order = new Order();
        $order->setUser($this->getUser());
        $order->setCart($cart);
        $order->setStatus(OrderStatusEnum::PENDING);
        $order->setOrderDate($now);

        $total = 0.0;
        foreach ($cart->getCartItems() as $item) {
            $product = $item->getProduct();

            // Snapshot the unit price so later price changes don't alter the order
            $orderItem = new OrderItem();
            $orderItem->setProduct($product);
            $orderItem->setQuantity($item->getQuantity());
            $orderItem->setUnitPriceAtOrder($product->getPrice());
            $order->addOrderItem($orderItem);
            $this->entityManager->persist($orderItem);

            $total += $product->getPrice() * $item->getQuantity();

            $product->setStockQuantity($product->getStockQuantity() - $item->getQuantity());
        }
        $order->setTotalAmount($total);

        $history = new OrderStatusHistory();
        $history->setStatus(OrderStatusEnum::PENDING);
        $history->setChangedAt($now);
        $order->addOrderStatusHistory($history);

        $this->entityManager->persist($order);
        $this->entityManager->persist($history);

        // Mark the cart as validated (set is_deleted_at) and convert it to an order
        $cart->setIsDeletedAt($now);
        $cart->setConvertedAt($now);

        $request->getSession()->remove('cart_id');

        $this->entityManager->flush();

        return $this->redirectToRoute('app_account');
    }

    /**
     * Fetches the cart stored in session if anonymous user, optionally creating a new one.
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
     * Prevents acting on a cart item that doesn't belong to the user cart.
     */
    private function assertOwnsCartItem(CartItem $cartItem, Request $request): void
    {
        if ($cartItem->getCart()?->getId() !== $this->cartRepository->findActiveCartByUserId($this->getUser()->getId())?->getId()) {
            throw $this->createAccessDeniedException();
        }
    }
}
