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
        //Validate the quantity from the request
        $rawQuantity = $request->request->get('quantity', 1);

        if (!is_numeric($rawQuantity) || (int) $rawQuantity < 1) {
            throw $this->createNotFoundException('Invalid quantity.');
        }

        $quantity = (int) $rawQuantity;

        //Get the active cart of the current user
        $cart = $this->cartRepository->findActiveCartByUserId($this->getUser()->getId());

        if (!$cart) {
            $cart = new Cart();
            $cart->setUser($this->getUser());
            $cart->setCreatedAt(new \DateTimeImmutable());
            $this->entityManager->persist($cart);
        }

        // Check if the product is already in the cart
        $cartItem = null;
        foreach ($cart->getCartItems() as $item) {
            if ($item->getProduct() === $product) {
                $cartItem = $item;
                break;
            }
        }

        // Calculate the new quantity
        $currentQuantity = $cartItem ? $cartItem->getQuantity() : 0;
        $newQuantity = $currentQuantity + $quantity;

        //Quantity validation for the new quantity
        if ($newQuantity > $product->getStockQuantity()) {
            throw $this->createNotFoundException('Quantity exceeds available stock.');
        }
        
        // Update the cart item with the new quantity or create a new one if it doesn't exist
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
        if ($cartItem->getCart()?->getId() !== $this->cartRepository->findActiveCartByUserId($this->getUser()->getId())?->getId()) {
            throw $this->createAccessDeniedException();
        }

        $quantity = (int) $request->request->get('quantity', 1);

        if ($quantity < 1) {
            $this->entityManager->remove($cartItem);
        } else {
            // Validate that the new quantity doesn't exceed available stock
            if ($quantity > $cartItem->getProduct()->getStockQuantity()) {
                throw $this->createNotFoundException('Quantity exceeds available stock.');
            }
            $cartItem->setQuantity($quantity);
        }

        $this->entityManager->flush();

        return $this->redirectToRoute('app_cart');
    }

    #[Route('/cart/remove/{id}', name: 'app_cart_remove', methods: ['POST'])]
    public function remove(CartItem $cartItem, Request $request): RedirectResponse
    {
        // Ensure the current user owns the cart item before removing it
        if ($cartItem->getCart()?->getId() !== $this->cartRepository->findActiveCartByUserId($this->getUser()->getId())?->getId()) {
            throw $this->createAccessDeniedException();
        }

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

        // Validate that stock is still available for all cart items
        foreach ($cart->getCartItems() as $item) {
            if ($item->getQuantity() > $item->getProduct()->getStockQuantity()) {
                throw $this->createNotFoundException(
                    'Quantity exceeds stock for: ' . $item->getProduct()->getName()
                );
            }
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

}
