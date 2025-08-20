# Changelog – andydefer/jwt-auth

Toutes les modifications notables de ce package seront documentées dans ce fichier.

---

## [2.x.x] – 2025-08-20

### 🆕 Nouveautés
- **Callbacks personnalisés** pour la logique `register`, `login`, `user` et `resolve_user`.
  - Permet aux utilisateurs du package de définir leurs propres modèles User (ex: `firstname`, `lastname`) et logiques d’inscription/connexion.
- `JwtAuthController` simplifié pour déléguer toute la logique à `JwtCallbackService`.
- Nouveau service `JwtCallbackService` pour centraliser toutes les callbacks.

### 🔧 Modifications internes
- Les méthodes `register`, `login` et `user` du controller utilisent maintenant les callbacks configurables.
- La résolution du modèle utilisateur peut être surchargée via `resolve_user` dans la configuration.
- Compatible avec tout modèle utilisateur implémentant `JWTSubject`.

### ⚡ Avantages pour les utilisateurs
- Flexibilité totale pour adapter la logique d’inscription et de connexion.
- Possibilité d’utiliser des modèles User personnalisés sans modifier le package.
- Controller plus DRY, facile à maintenir et prêt pour des apps Android / Jetpack Compose utilisant JWT.

### 📦 Exemple de configuration dans `config/jwt.php`

```php
'callbacks' => [
    'resolve_user' => fn() => \App\Models\User::class,

    'register' => function ($request) {
        $validated = $request->validate([
            'firstname' => 'required|string|max:255',
            'lastname' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
        ]);

        return \App\Models\User::create([
            'firstname' => $validated['firstname'],
            'lastname' => $validated['lastname'],
            'email' => $validated['email'],
            'password' => \Illuminate\Support\Facades\Hash::make($validated['password']),
        ]);
    },

    'login' => function ($request, $user) {
        return $user && password_verify($request->password, $user->password);
    },

    'user' => function ($user) {
        return [
            'id' => $user->id,
            'fullname' => $user->firstname . ' ' . $user->lastname,
            'email' => $user->email,
        ];
    },
],
