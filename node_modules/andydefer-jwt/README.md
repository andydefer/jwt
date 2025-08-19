# andydefer-jwt

Une solution de **gestion d’authentification JWT** pour React avec **Zustand** et **Inertia.js**, incluant :

* Login / Logout / Register
* Gestion du token JWT et clé publique
* Initialisation automatique à partir de la session
* Vérification de signature
* Stockage persistant côté client (localStorage)

> ⚠️ Ce package est conçu pour fonctionner **en tandem avec le package Laravel `andydefer/jwt`**, qui fournit l’API JWT côté serveur.

---

## 🚀 Installation

```bash
npm install andydefer-jwt
```

ou

```bash
yarn add andydefer-jwt
```

---

## ⚙️ Usage

### 1️⃣ Hook principal `useAuth`

```ts
import { useAuth } from 'andydefer-jwt';

const MyComponent = () => {
  const { user, isAuthenticated, login, logout, register, isLoading, error } = useAuth();

  return (
    <div>
      {isAuthenticated ? (
        <div>
          <p>Bienvenue, {user?.name}</p>
          <button onClick={logout}>Se déconnecter</button>
        </div>
      ) : (
        <div>
          <button onClick={() => login('email@example.com', 'password')}>Se connecter</button>
        </div>
      )}
      {isLoading && <p>Chargement...</p>}
      {error && <p style={{ color: 'red' }}>{error}</p>}
    </div>
  );
};
```

---

### 2️⃣ Store `useAuthStore`

Pour accéder directement aux fonctionnalités avancées ou créer des hooks personnalisés :

```ts
import { useAuthStore } from 'andydefer-jwt';

const token = useAuthStore(state => state.token);
const login = useAuthStore(state => state.login);

login('email@example.com', 'password');
```

---

## 📝 API

### `useAuth()`

| Nom                                          | Type                  | Description                           |
| -------------------------------------------- | --------------------- | ------------------------------------- |
| `isAuthenticated`                            | `boolean`             | Indique si l’utilisateur est connecté |
| `user`                                       | `User \| null`        | Objet utilisateur courant             |
| `isLoading`                                  | `boolean`             | Indique que l’action est en cours     |
| `error`                                      | `string \| null`      | Message d’erreur si échec             |
| `login(email, password, deviceId?)`          | `() => Promise<void>` | Connecte un utilisateur               |
| `logout()`                                   | `() => Promise<void>` | Déconnecte l’utilisateur              |
| `register(name, email, password, deviceId?)` | `() => Promise<void>` | Enregistre un nouvel utilisateur      |

### `useAuthStore()`

| Nom                                          | Type                     | Description                                 |
| -------------------------------------------- | ------------------------ | ------------------------------------------- |
| `token`                                      | `string \| null`         | Token JWT actuel                            |
| `publicKey`                                  | `string \| null`         | Clé publique associée                       |
| `user`                                       | `User \| null`           | Utilisateur courant                         |
| `isLoading`                                  | `boolean`                | Indicateur de chargement                    |
| `error`                                      | `string \| null`         | Message d’erreur                            |
| `isInitialized`                              | `boolean`                | Indique si le store a été initialisé        |
| `initialize()`                               | `() => Promise<void>`    | Initialise le store depuis la session       |
| `login(email, password, deviceId?)`          | `() => Promise<void>`    | Connexion                                   |
| `register(name, email, password, deviceId?)` | `() => Promise<void>`    | Inscription                                 |
| `logout()`                                   | `() => void`             | Déconnexion                                 |
| `fetchUser()`                                | `() => Promise<void>`    | Récupère l’utilisateur depuis l’API         |
| `verifySignature(data, signature)`           | `() => Promise<boolean>` | Vérifie la signature d’un message           |
| `getTokenFromSession()`                      | `() => Promise<void>`    | Récupère le token depuis la session serveur |

---

## 🔒 Stockage persistant

Le token, la clé publique et l’utilisateur sont stockés via **Zustand Persist** dans `localStorage` sous le nom :

```
jwt-auth-storage
```

---

## 🧩 Intégration backend Laravel

Pour que le package fonctionne correctement, installez le package Laravel `andydefer/jwt` :

```bash
composer require andydefer/jwt
php artisan migrate
php artisan vendor:publish --provider="AndyDefer\Jwt\JwtAuthServiceProvider" --tag="routes"
```

Configurez Axios dans votre front-end pour pointer vers les endpoints JWT Laravel :

```ts
import axios from 'axios';
axios.defaults.baseURL = 'https://mon-domaine-laravel.com/api/jwt';
axios.defaults.withCredentials = true;
```

---

## 🛠 Workflow complet avec Makefile

Ce projet inclut un **Makefile** pour automatiser tests, build, versionning et publication :

```bash
make install           # Installer les dépendances
make test              # Lancer les tests
make build             # Compiler le package
make version-minor     # Incrémenter la version (patch/minor/major)
make publish           # Publier sur npm
make release-interactive  # Workflow complet : commit + tag + push + publication
```

---

## 📌 Notes importantes

* Ce package nécessite **React 18+** et **Zustand**.
* Compatible avec **Inertia.js** et une API JWT Laravel.
* Ne jamais publier sur npm sans incrémenter la version.
* Toujours initialiser le store via `initialize()` dans votre hook principal.

---

## 🧪 Tests

```bash
npm test
```

Assurez-vous d’avoir toutes les dépendances installées et `jest` configuré correctement.

---

## 💡 Bonnes pratiques

* Vérifier `isAuthenticated` avant de rendre des contenus protégés.
* Utiliser `error` pour afficher les messages d’erreur.
* Ne jamais exposer directement le `token` dans l’UI pour des raisons de sécurité.
* Synchroniser régulièrement le front-end avec le backend Laravel.
