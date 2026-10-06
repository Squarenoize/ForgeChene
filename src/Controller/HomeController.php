<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\Product;
use App\Repository\ProductRepository;

final class HomeController extends AbstractController
{
    public function __construct(
        private ProductRepository $productRepository
        )
    {
    }

    #[Route('/home', name: 'app_home')]
    public function index(): Response
    {
        // Get all products from the repository
        $products = $this->productRepository->findAll();

        return $this->render('public/home.html.twig', [
            'products' => $products,
        ]);
    }

    #[Route('/product/{id}', name: 'app_product')]
    public function product(Product $product): Response
    {
        return $this->render('public/product.html.twig', [
            'product' => $product,
        ]);
    }

    #[Route('/cart', name: 'app_cart')]
    public function cart(): Response
    {
        return $this->render('public/cart.html.twig', [
            'controller_name' => 'HomeController',
        ]);
    }
}
