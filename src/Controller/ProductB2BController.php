<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ProductB2BController extends AbstractController
{
    #[Route('/product/b2/b', name: 'app_product_b2_b')]
    public function index(): Response
    {
        return $this->render('product_b2_b/index.html.twig', [
            'controller_name' => 'ProductB2BController',
        ]);
    }
}
