# Fonctionnalités de ForgeChene

Ce document décrit les fonctionnalités de l'application, telles qu'implémentées dans le code (`src/Controller`, `src/Entity`, `config/packages/security.yaml`).

## Vue d'ensemble

| Domaine | Accès | Routes principales |
|---|---|---|
| Catalogue | Public | `/home`, `/product/{id}` |
| Inscription / connexion | Public | `/register`, `/login`, `/logout` |
| Panier | Utilisateur connecté | `/cart`, `/cart/add/{id}`, `/cart/update/{id}`, `/cart/remove/{id}`, `/cart/clear`, `/cart/validate` |
| Espace client | Utilisateur connecté | `/account`, `/account/api/generate-api-key`, `/account/api/delete-api-key`, `/account/delete` |
| API REST | Clé API puis JWT | `POST /api/login`, `GET /api/products` |

## 1. Catalogue de produits

- **Accueil** (`/home`) : liste de tous les produits.
- **Fiche produit** (`/product/{id}`) : détail d'un produit.
- Un produit comporte : référence, nom, description courte et complète, photo, prix et quantité en stock.

## 2. Comptes utilisateurs

### Inscription (`/register`)
- Champs : prénom, nom, email, mot de passe (saisi deux fois), acceptation des conditions.
- Validations : champs non vides, longueurs min/max pour prénom et nom, email valide et **unique**, mot de passe avec majuscule, minuscule, chiffre et caractère spécial.
- Mot de passe haché, rôle `ROLE_USER` attribué, redirection vers la connexion avec message de succès.

### Connexion / déconnexion
- Formulaire de connexion avec protection CSRF ; redirection vers l'accueil après connexion.
- Déconnexion via `/logout`.

### Messages utilisateur
- `MessageService` affiche des messages flash (succès, erreur, avertissement) dans les pages.

## 3. Panier

Un panier « actif » est associé à chaque utilisateur connecté.

- **Consulter** le panier ; le nombre d'articles est exposé dans les vues par la fonction Twig `cart_item_count`.
- **Ajouter** un produit (quantité minimale 1) : le panier est créé au besoin, et la quantité est cumulée si le produit y figure déjà.
- **Modifier** la quantité d'une ligne (une quantité inférieure à 1 supprime la ligne).
- **Supprimer** une ligne ou **vider** le panier.
- Contrôle d'accès : on ne peut modifier ou supprimer que les lignes de son propre panier.

## 4. Commandes

**Validation du panier** (`POST /cart/validate`) :
1. Une commande est créée au statut `pending` (en attente), avec sa date.
2. Chaque ligne du panier devient une ligne de commande avec **le prix unitaire figé** au moment de l'achat.
3. Le montant total est calculé et le **stock** de chaque produit est décrémenté.
4. Une entrée est ajoutée à l'**historique des statuts**.
5. Le panier est clôturé (marqué supprimé et converti), puis l'utilisateur est redirigé vers son compte.

Statuts possibles (`OrderStatusEnum`) : `pending`, `processing`, `shipped`, `delivered`, `cancelled`.

## 5. Espace client (`/account`)

- Affichage des informations du compte et de la liste des commandes (de la plus récente à la plus ancienne).
- **Gestion de la clé API** : génération d'une clé aléatoire (64 caractères hexadécimaux) affichée **une seule fois** ; seul son hash SHA-256 est stocké. Elle peut être régénérée ou supprimée.
- **Suppression du compte** : le panier actif est clôturé, les données personnelles (email, mot de passe, prénom, nom, clé API) sont effacées (anonymisation) et la suppression est datée, puis l'utilisateur est déconnecté.
- Les actions sensibles sont protégées par jeton CSRF.

## 6. API REST

Documentation interactive (OpenAPI) disponible sur `/api`.

1. **Obtenir un jeton** : `POST /api/login` avec l'en-tête `X-API-KEY: <clé API>`. Réponse `200` `{"token": "<JWT>"}` ou `401` si la clé est invalide.
2. **Lister les produits** : `GET /api/products` avec l'en-tête `Authorization: Bearer <JWT>` (accès réservé aux utilisateurs authentifiés).
3. Les produits renvoyés incluent une URL absolue de la photo (`photoUrl`), calculée par `ProductProvider`.

```bash
TOKEN=$(curl -s -X POST http://localhost:8000/api/login -H "X-API-KEY: <clé>" | jq -r .token)
curl http://localhost:8000/api/products -H "Authorization: Bearer $TOKEN"
```

## 7. Sécurité

- Pare-feu `main` (sessions, formulaire de login) et pare-feu `api` sans état (JWT).
- Accès : `/home`, `/product/*` publics ; `/account` et `/api/products` réservés aux utilisateurs authentifiés.
- Mots de passe hachés, clés API hachées, protection CSRF sur les formulaires et actions sensibles.

## 8. Données de démonstration

`DataFixtures/AppFixtures.php` et `Story/AppStory.php` (Foundry) alimentent la base : `php bin/console doctrine:fixtures:load`.
