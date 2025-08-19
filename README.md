# `andydefer/jwt`

Un package d'authentification JWT pour Laravel, conçu pour gérer l'authentification sans session et le déploiement sur plusieurs appareils. Il s'intègre parfaitement avec des applications **React/Inertia.js** et le package front-end [`andydefer-jwt`](https://www.google.com/search?q=%5Bhttps://www.npmjs.com/package/andydefer-jwt%5D\(https://www.npmjs.com/package/andydefer-jwt\)).

## Table des matières

  - [Installation](https://www.google.com/search?q=%23installation)
  - [Configuration](https://www.google.com/search?q=%23configuration)
  - [Utilisation](https://www.google.com/search?q=%23utilisation)
  - [Intégration Front-end](https://www.google.com/search?q=%23int%C3%A9gration-front-end)

-----

## Installation

La méthode préférée pour installer ce package est d'utiliser [Composer](https://getcomposer.org/).

1.  Ajoutez le package à votre projet Laravel :

    ```bash
    composer require andydefer/jwt
    ```

2.  Ajoutez le middleware du package à votre fichier `app/Http/Kernel.php` pour le groupe de routes `api`.

    ```php
    protected $middlewareGroups = [
        'api' => [
            // ... autres middlewares
            \AndyDefer\Jwt\Middleware\JwtAuthMiddleware::class,
        ],
    ];
    ```

    > **Note :** Le package enregistre automatiquement le middleware avec l'alias `jwt.auth`, mais cette étape garantit qu'il est appliqué à toutes vos routes d'API.

3.  Lancez les migrations pour créer la table `jwt_auth` :

    ```bash
    php artisan migrate
    ```

4.  Publiez les routes du package. Elles seront ajoutées à votre fichier `routes/api.php`.

    ```bash
    php artisan vendor:publish --provider="AndyDefer\Jwt\JwtAuthServiceProvider" --tag="routes"
    ```

-----

## Configuration

Le package fonctionne de manière préconfigurée avec les routes d'API suivantes :

  * `POST /jwt/register`
  * `POST /jwt/login`
  * `GET /jwt/token`
  * `POST /jwt/logout`
  * `POST /jwt/refresh`
  * `GET /jwt/user`
  * `POST /jwt/verify-signature`

Vous pouvez personnaliser le préfixe de ces routes en modifiant le Service Provider.

-----

## Utilisation

Le package gère l'ensemble du flux d'authentification JWT pour votre backend, y compris la génération de paires de clés RSA pour les communications sécurisées entre les appareils.

### Authentification

Les utilisateurs peuvent s'authentifier via les endpoints suivants :

  * **`POST /jwt/register`** : Enregistre un nouvel utilisateur et émet un token JWT.
      * **Paramètres :** `name`, `email`, `password`, `password_confirmation`, `device_id` (optionnel)
  * **`POST /jwt/login`** : Authentifie un utilisateur existant et émet un token JWT.
      * **Paramètres :** `email`, `password`, `device_id` (optionnel)
  * **`GET /jwt/token`** : Génère un token JWT pour un utilisateur déjà authentifié via la session Laravel (utile pour les premières requêtes depuis une application Inertia.js).

### Gestion des tokens

  * **`POST /jwt/logout`** : Invalide le token JWT actuel.
  * **`POST /jwt/refresh`** : Invalide le token actuel et en émet un nouveau.
  * **`GET /jwt/user`** : Récupère les informations de l'utilisateur authentifié.
  * **`POST /jwt/verify-signature`** : Vérifie la signature d'une requête client pour des communications ultra-sécurisées.

-----

## Intégration Front-end avec React

Ce package a été conçu pour être utilisé en tandem avec son homologue front-end, le package NPM [`andydefer-jwt`](https://www.google.com/search?q=%5Bhttps://www.npmjs.com/package/andydefer-jwt%5D\(https://www.npmjs.com/package/andydefer-jwt\)).

Le package front-end utilise `axios` pour communiquer avec les endpoints d'API fournis par ce package Laravel, simplifiant la gestion de l'état d'authentification dans vos applications React/Inertia.js.

### Exemple de Configuration `axios`

Assurez-vous que votre application front-end est configurée pour pointer vers les endpoints de ce package :

```javascript
import axios from 'axios';

// Remplacez par l'URL de votre application Laravel
axios.defaults.baseURL = 'https://mon-domaine-laravel.com/jwt';
axios.defaults.withCredentials = true;
```

-----

*Continuez à développer avec des outils d'authentification solides et fiables \!*