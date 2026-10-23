<?php

namespace App\Controller;

use App\Repository\OrderRepository;
use App\Repository\CartRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class AccountController extends AbstractController
{
    #[Route('/account', name: 'app_account')]
    public function showAccount(OrderRepository $orderRepository): Response
    {
        $user = $this->getUser();
        return $this->render('account/account.html.twig', [
            'user' => $user,
            'orders' => $orderRepository->findBy(['user' => $user], ['order_date' => 'DESC', 'id' => 'DESC']),
        ]);
    }

    #[Route('/account/delete', name: 'app_account_delete', methods: ['POST'])]
    public function deleteAccount(Request $request, EntityManagerInterface $entityManager, CartRepository $cartRepository): Response
    {
        if (!$this->isCsrfTokenValid('delete-account', (string) $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Jeton CSRF invalide.');
        }

        $user = $this->getUser();

        if ($user) {
            // Delete current cart if it exists
            $cart = $cartRepository->findActiveCartByUserId($this->getUser()->getId());
            if ($cart) {
                $cart->setIsDeletedAt(new \DateTimeImmutable());
            }
            // Anonymous the user - 
            $user->setEmail(null);
            $user->setPassword(null);
            $user->setFirstname(null);
            $user->setLastname(null);
            $user->setApiKey(null);
            $user->setIsDeletedAt(new \DateTimeImmutable());

            $entityManager->persist($user);
            $entityManager->flush();
        }

        // logout the user
        return $this->redirectToRoute('app_logout');
    }

    #[Route('/account/api/generate-api-key', name: 'app_account_api_access')]
    public function generateApiAccess(EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        if ($user) {
            $user->setApiKey(bin2hex(random_bytes(32)));
            $entityManager->persist($user);
            $entityManager->flush();
        }
        return $this->redirectToRoute('app_account');
    }
}
