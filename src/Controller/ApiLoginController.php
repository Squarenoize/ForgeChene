<?php

namespace App\Controller;

use App\Repository\UserRepository;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ApiLoginController extends AbstractController
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly JWTTokenManagerInterface $jwtManager,
    ) {
    }

    #[Route('/api/login', name: 'api_login', methods: ['POST'])]
    public function login(Request $request): JsonResponse
    {
        $apiKey = (string) $request->headers->get('X-API-KEY');

        // The database stores the SHA-256 hash of the key, never the key itself
        $user = '' !== $apiKey ? $this->userRepository->findOneBy(['apiKey' => hash('sha256', $apiKey)]) : null;
        if (null === $user) {
            return new JsonResponse(['message' => 'Invalid credentials.'], Response::HTTP_UNAUTHORIZED);
        }

        return new JsonResponse(['token' => $this->jwtManager->create($user)]);
    }
}
