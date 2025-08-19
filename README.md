# 🔐 Laravel Simple JWT Auth - Documentation Française

## 📖 Table des Matières
1. [Introduction](#-introduction)
2. [Fonctionnalités](#-fonctionnalités)
3. [Installation](#-installation)
4. [Configuration](#-configuration)
5. [Utilisation](#-utilisation)
6. [Endpoints API](#-endpoints-api)
7. [Sécurité](#-sécurité)
8. [Dépannage](#-dépannage)

## 🌟 Introduction

**Laravel Simple JWT Auth** est un package complet et facile à utiliser pour l'authentification JWT dans Laravel. Il fournit un système d'authentification prêt à l'emploi avec gestion des tokens, support multi-appareils et une intégration transparente.

## ✨ Fonctionnalités

- **✅ Intégration Simple**: Système d'authentification JWT complet en une commande
- **🚀 Endpoints Prêts à l'emploi**: Routes API pré-construites pour l'inscription, connexion, déconnexion, etc.
- **🔄 Rafraîchissement de Tokens**: Gestion automatique du renouvellement des tokens
- **🔒 Sécurité Renforcée**: Stockage sécurisé des tokens en base de données avec suivi des appareils
- **🛡️ Middleware Dédié**: Protection des routes avec middleware JWT personnalisé
- **🧩 Support des Modèles Personnalisés**: Compatible avec votre modèle User existant

## 📦 Installation

### Étape 1: Installation via Composer
```bash
composer require andydefer/jwt-auth
```

### Étape 2: Publier la configuration
```bash
php artisan vendor:publish --tag=jwt-config
```

### Étape 3: Exécuter les migrations
```bash
php artisan migrate
```

### Étape 4: Configurer le modèle User (Optionnel)

Si vous utilisez un modèle User personnalisé, assurez-vous qu'il implémente `JWTSubject`:

```php
<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    // ...

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }
}
```

## ⚙️ Configuration

### Fichier de configuration (`config/jwt.php`)

```php
<?php

return [
    'secret' => env('JWT_SECRET'), // Clé secrète JWT
    'ttl' => env('JWT_TTL', 60), // Durée de vie du token (minutes)
    'refresh_ttl' => env('JWT_REFRESH_TTL', 20160), // Durée de rafraîchissement (minutes)
];
```

### Variables d'environnement (.env)

```env
JWT_SECRET=votre_clé_secrète_très_longue_ici
JWT_TTL=1440
JWT_REFRESH_TTL=20160
```

## 🚀 Utilisation

### Protection des Routes

Utilisez le middleware `jwt.auth` pour protéger vos routes:

```php
<?php

use Illuminate\Support\Facades\Route;

Route::middleware('jwt.auth')->group(function () {
    Route::get('/profile', 'ProfileController@show');
    Route::get('/dashboard', 'DashboardController@index');
    // Vos autres routes protégées...
});
```

### Récupérer l'utilisateur authentifié

Dans vos contrôleurs:

```php
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function show(Request $request)
    {
        $user = $request->jwt_user; // Utilisateur authentifié
        $jwtAuth = $request->jwt_auth; // Informations du token

        return response()->json([
            'user' => $user,
            'device' => $jwtAuth->device_id
        ]);
    }
}
```

## 📡 Endpoints API

### 🔹 Inscription (Register)

**Endpoint:** `POST /jwt/register`

**Body:**
```json
{
    "name": "Jean Dupont",
    "email": "jean@exemple.com",
    "password": "motdepasse123",
    "password_confirmation": "motdepasse123"
}
```

**Réponse:**
```json
{
    "user": {
        "id": 1,
        "name": "Jean Dupont",
        "email": "jean@exemple.com",
        "created_at": "2023-01-01T00:00:00.000000Z",
        "updated_at": "2023-01-01T00:00:00.000000Z"
    },
    "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9..."
}
```

### 🔹 Connexion (Login)

**Endpoint:** `POST /jwt/login`

**Body:**
```json
{
    "email": "jean@exemple.com",
    "password": "motdepasse123"
}
```

**Réponse:**
```json
{
    "user": {
        "id": 1,
        "name": "Jean Dupont",
        "email": "jean@exemple.com"
    },
    "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9..."
}
```

### 🔹 Récupérer l'utilisateur (User)

**Endpoint:** `GET /jwt/user`

**Headers:**
```
Authorization: Bearer votre_token_jwt_ici
```

**Réponse:**
```json
{
    "user": {
        "id": 1,
        "name": "Jean Dupont",
        "email": "jean@exemple.com"
    }
}
```

### 🔹 Déconnexion (Logout)

**Endpoint:** `POST /jwt/logout`

**Headers:**
```
Authorization: Bearer votre_token_jwt_ici
```

**Réponse:**
```json
{
    "message": "Successfully logged out"
}
```

### 🔹 Rafraîchir le Token (Refresh)

**Endpoint:** `POST /jwt/refresh`

**Headers:**
```
Authorization: Bearer votre_token_jwt_ici
```

**Réponse:**
```json
{
    "token": "nouveau_token_jwt_ici"
}
```

### 🔹 Récupérer le Token (Token)

**Endpoint:** `GET /jwt/token`

**Headers:**
```
Authorization: Bearer votre_token_jwt_ici
```

**Réponse:**
```json
{
    "token": "votre_token_jwt_ici"
}
```

## 🔒 Sécurité

### Gestion des Tokens

Le package enregistre chaque token JWT dans la table `jwt_auth` avec:
- ✅ User ID associé
- ✅ Token JWT (hashé)
- ✅ Identifiant d'appareil
- ✅ Adresse IP
- ✅ User Agent
- ✅ Date d'émission
- ✅ Dernière utilisation

### Invalidation des Tokens

Les tokens peuvent être invalidés individuellement via:
```php
use Andydefer\JwtAuth\Facades\JwtAuth;

// Invalider un token spécifique
JwtAuth::invalidateToken($token);
```

## 🛠️ Dépannage

### Problème: "Token not provided"
**Solution:** Vérifiez que le header Authorization est correctement formaté:
```
Authorization: Bearer votre_token_ici
```

### Problème: "Token has expired"
**Solution:** Utilisez l'endpoint `/jwt/refresh` pour obtenir un nouveau token.

### Problème: "User not found"
**Solution:** Vérifiez que votre modèle User implémente correctement `JWTSubject`.

### Problème: Clé JWT non générée
**Solution:** Exécutez la commande:
```bash
php artisan config:clear
```

## 📋 Exemple Complet

### Controller Protégé
```php
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ApiController extends Controller
{
    public function __construct()
    {
        $this->middleware('jwt.auth');
    }

    public function getData(Request $request)
    {
        $user = $request->jwt_user;

        return response()->json([
            'data' => 'Données sensibles',
            'user' => $user->only('id', 'name', 'email')
        ]);
    }
}
```

### Routes API
```php
<?php

use Illuminate\Support\Facades\Route;

Route::prefix('api')->group(function () {
    // Routes publiques
    Route::post('register', 'AuthController@register');
    Route::post('login', 'AuthController@login');

    // Routes protégées
    Route::middleware('jwt.auth')->group(function () {
        Route::get('profile', 'ProfileController@show');
        Route::get('data', 'ApiController@getData');
        Route::post('logout', 'AuthController@logout');
        Route::post('refresh', 'AuthController@refresh');
    });
});
```

## 📞 Support

Pour toute question ou problème, consultez la documentation officielle de [tymon/jwt-auth](https://github.com/tymondesigns/jwt-auth) ou créez une issue sur le repository du package.

## 📄 Licence

Ce package est open-source et disponible sous la licence MIT.

---

**Note:** Cette documentation est basée sur la version actuelle du package. Consultez le repository officiel pour les mises à jour et changements récents.