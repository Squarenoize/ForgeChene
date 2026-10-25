# ForgeChene

Application web e-commerce (projet 13 OpenClassrooms) construite avec **Symfony 7.4** et **API Platform 5**. Elle propose un catalogue de produits, un panier, un espace client et une API sécurisée par JWT.

## Fonctionnalités

- Catalogue de produits (accueil, fiche produit)
- Panier (`CartController`, extension Twig `CartExtension`)
- Inscription, connexion / déconnexion, espace client (`/account`)
- Commandes avec historique de statuts (`OrderStatusEnum`, `OrderStatusHistory`)
- API REST (API Platform) : `/api/login` pour obtenir un jeton JWT, `/api/products` protégé par JWT
- Fixtures de données (Foundry / DoctrineFixtures)

## Stack technique

- PHP >= 8.2, Symfony 7.4, Doctrine ORM 3 + Migrations
- API Platform 5, LexikJWTAuthenticationBundle, NelmioCors
- Twig, AssetMapper, Stimulus, Turbo
- Base de données : MySQL 8 (config `.env`) ; PostgreSQL disponible via `compose.yaml`
- Tests : PHPUnit 11, Foundry

## Prérequis

- PHP >= 8.2 (extensions `ctype`, `iconv`, plus le driver PDO de votre base)
- [Composer](https://getcomposer.org/)
- MySQL 8 (ou PostgreSQL via Docker)
- OpenSSL (génération des clés JWT)
- [Symfony CLI](https://symfony.com/download) (optionnel)

## Installation

```bash
git clone https://github.com/Squarenoize/ForgeChene.git
cd ForgeChene
composer install
```

### Configuration

Créez un fichier `.env.local` (non versionné) et surchargez les valeurs sensibles :

```dotenv
DATABASE_URL="mysql://utilisateur:motdepasse@127.0.0.1:3306/forgechene?serverVersion=8.0.32&charset=utf8mb4"
JWT_PASSPHRASE=votre_passphrase
```

> Ne commitez jamais de secrets : changez la passphrase JWT et `APP_SECRET` pour tout environnement non local.

### Clés JWT

```bash
php bin/console lexik:jwt:generate-keypair
```

Les clés sont créées dans `config/jwt/` (`private.pem`, `public.pem`).

### Base de données

```bash
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
php bin/console doctrine:fixtures:load   # données de démonstration
```

Pour utiliser PostgreSQL avec Docker : `docker compose up -d database`, puis adaptez `DATABASE_URL`.

## Lancement

```bash
symfony serve
# ou
php -S localhost:8000 -t public
```

Application : <http://localhost:8000> — documentation de l'API : <http://localhost:8000/api>

## Utilisation de l'API

```bash
# Obtenir un jeton
curl -X POST http://localhost:8000/api/login -H "X-API-KEY: <votre_cle_api>"

# Appeler une ressource protégée
curl http://localhost:8000/api/products -H "Authorization: Bearer <TOKEN>"
```

## Tests

```bash
php bin/phpunit
```

## Structure du projet

| Dossier | Contenu |
|---|---|
| `src/Controller` | Contrôleurs (accueil, panier, compte, sécurité, inscription, login API) |
| `src/Entity` | Entités : User, Product, Cart, CartItem, Order, OrderItem, OrderStatusHistory |
| `src/ApiResource`, `src/State` | Ressources et providers API Platform |
| `src/DataFixtures`, `src/Story` | Données de démonstration |
| `templates/` | Vues Twig |
| `migrations/` | Migrations Doctrine |
| `config/` | Configuration Symfony (sécurité, packages, routes) |

Diagrammes de conception : [diagrammeDeClasse.mmd](diagrammeDeClasse.mmd) et [diagrammeBaseDeDonnees.mmd](diagrammeBaseDeDonnees.mmd).

## Licence

Propriétaire.
