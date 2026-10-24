<?php

namespace App\ApiResource;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Post;
use ApiPlatform\OpenApi\Model;
use App\Controller\ApiLoginController;

// Documentation-only resource: the login is handled by ApiLoginController, not persisted.
#[ApiResource(
    operations: [
        new Post(
            uriTemplate: '/login',
            controller: ApiLoginController::class,
            input: false,
            output: false,
            status: 200,
            openapi: new Model\Operation(
                summary: 'Obtenir un token JWT à partir de la clé API',
                parameters: [
                    new Model\Parameter(name: 'X-API-KEY', in: 'header', required: true, description: 'Clé API de l\'utilisateur'),
                ],
                responses: [
                    '200' => new Model\Response(description: 'Token JWT généré'),
                    '401' => new Model\Response(description: 'Clé API invalide'),
                ],
            ),
        ),
    ],
)]
class ApiLogin
{
}
