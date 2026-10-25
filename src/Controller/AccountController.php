<?php

namespace App\Controller;

use App\Repository\OrderRepository;
use App\Repository\CartRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Service\MessageService;

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

    #[Route('/account/api/generate-api-key', name: 'app_account_api_access', methods: ['POST'])]
    public function generateApiAccess(Request $request, EntityManagerInterface $entityManager, MessageService $messageService): Response
    {
        if (!$this->isCsrfTokenValid('generate-api-key', (string) $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Jeton CSRF invalide.');
        }

        $user = $this->getUser();
        if ($user) {
            $apiKey = bin2hex(random_bytes(32));
            // Only the hash is stored: the plain key is shown once and cannot be recovered.
            $user->setApiKey(hash('sha256', $apiKey));
            $entityManager->persist($user);
            $entityManager->flush();
            $messageService->addWarning("Ci-dessous se trouve votre clé API. Copiez-la maintenant, elle ne sera plus jamais affichée.");
            $messageService->addSuccess("$apiKey");
            
        }
        return $this->redirectToRoute('app_account');
    }
    #[Route('/account/api/delete-api-key', name: 'app_account_api_delete', methods: ['POST'])]
    public function deleteApiKey(Request $request, EntityManagerInterface $entityManager, MessageService $messageService): Response
    {
        if (!$this->isCsrfTokenValid('generate-api-key', (string) $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Jeton CSRF invalide.');
        }

        $user = $this->getUser();
        if ($user) {
            $user->setApiKey(null);
            $entityManager->persist($user);
            $entityManager->flush();
            $messageService->addSuccess("Votre clé API a été supprimée avec succès.");
        }
        return $this->redirectToRoute('app_account');
    }
}
