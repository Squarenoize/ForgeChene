<?php

namespace App\Controller;

use App\Repository\OrderRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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
}
