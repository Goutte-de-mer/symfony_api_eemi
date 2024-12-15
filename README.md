# Guide d'utilisation de l'API

## Prérequis

- Une base de données **MySQL** configurée.
- Symfony installé localement.
- Postman ou un autre outil pour tester les endpoints de l'API.

## Étapes pour tester l'API

1. **Configurer la base de données**

   - Mettez à jour vos informations de connexion à la base de données dans le fichier `.env` :
     ```env
     DATABASE_URL="mysql://<username>:<password>@<host>:<port>/<database_name>"
     ```

2. **Installer les dépendances**

   - Lancez la commande suivante pour installer toutes les dépendances nécessaires :
     ```bash
     composer install
     ```

3. **Initialiser la base de données**

   - Exécutez les migrations pour créer les tables nécessaires :
     ```bash
     php bin/console doctrine:migrations:migrate
     ```

4. **Charger les fixtures**

   - Pour pré-remplir la base de données avec des données de test (3 chats et 3 utilisateurs) :
     ```bash
     php bin/console doctrine:fixtures:load
     ```
   - Les informations des utilisateurs par défaut sont disponibles dans `src/DataFixtures/UserFixtures.php`.

5. **Démarrer le serveur Symfony**

   - Lancez le serveur local Symfony :
     ```bash
     symfony serve
     ```

6. **Configurer Postman**

   - Définissez les variables d'environnement suivantes :
     - `base_url` : L'URL de base de l'API (e.g., `http://127.0.0.1:8000`).
     - `admin_jwt` : Le token JWT récupéré après connexion pour les admin.
     - `user_jwt` : Le token JWT récupéré après connexion pour les user.

7. **Générer les clés JWT**

   Les clés JWT sont nécessaires pour le fonctionnement de l'API. Voici les étapes pour les générer :

   - **Créer le dossier pour les clés** :

     ```bash
     mkdir config/jwt
     ```

   - **Générer une paire de clés** :

     ```bash
     php bin/console lexik:jwt:generate-keypair
     ```

   - **Configurer les fichiers dans `.env.local`** :
     Ajoutez les chemins et la phrase secrète dans votre fichier `.env.local` :
     ```env
     JWT_SECRET_KEY=%kernel.project_dir%/config/jwt/private.pem
     JWT_PUBLIC_KEY=%kernel.project_dir%/config/jwt/public.pem
     JWT_PASSPHRASE=MaPhraseSecrete
     ```

## Fonctionnement de l'API

### Gestion des utilisateurs

- **Route d'inscription :**

  - Endpoint : `/api/register`
  - Méthode : `POST`
  - Description : Permet aux utilisateurs lambda de s'inscrire. Le rôle `ROLE_USER` est attribué par défaut.
    - Le mot de passe doit faire minimum 8 caractères avec une majuscule, un chiffre et un caractère spécial.
    - L'email ne doit pas déjà être utilisé.
  - Exemple de requête :
    ```json
    {
      "name": "John",
      "lastname": "Doe",
      "email": "john.doe@example.com",
      "password": "User_password123"
    }
    ```

- **Authentification :**

  - Endpoint : `/api/login`
  - Méthode : `POST`
  - Description : Retourne un token JWT si les informations de connexion sont correctes.
  - Exemple de requête :
    ```json
    {
      "email": "admin@example.com",
      "password": "Admin_password123"
    }
    ```

- **Restrictions d'accès :**
  - Les routes sous `/api/admin/` sont accessibles uniquement aux utilisateurs avec le rôle `ROLE_ADMIN`.

### Gestion des chats

- **Créer un chat :**

  - Endpoint : `/api/admin/create_cat`
  - Méthode : `POST`
  - Accès : Admin uniquement.
  - Exemple de requête :
    ```json
    {
      "name": "Zorro",
      "short_description": "Chat joueur",
      "long_description": "Chat noir très actif et affectueux.",
      "age": "1  year",
      "is_vaccinated": true,
      "img": "bengal.jpg"
    }
    ```

- **Lister les chats :**

  - Endpoint : `/api/cats`
  - Méthode : `GET`
  - Accès : Public.
  - Description : Retourne une liste de tous les chats disponibles.

- **Voir un chat spécifique :**

  - Endpoint : `/api/cat/{id}`
  - Méthode : `GET`
  - Accès : Public.
  - Description : Retourne les détails d'un chat par son ID.

- **Mettre à jour un chat :**

  - Endpoint : `/api/admin/update_cat/{id}`
  - Méthode : `PUT`
  - Accès : Admin uniquement.
  - Exemple de requête :
    ```json
    {
      "name": "Chatouille",
      "age": "6 months"
    }
    ```

- **Supprimer un chat :**
  - Endpoint : `/api/admin/delete_cat/{id}`
  - Méthode : `DELETE`
  - Accès : Admin uniquement.

### Informations sur les Fixtures

- Les utilisateurs par défaut incluent (non exaustif):

  - **Admin** :
    - Nom : `Marion`
    - Prénom : `Bailleux`
    - Email : `marion.bailleux@example.com`
    - Mot de passe : `Admin_password123`
    - Rôle : `ROLE_ADMIN`
  - **Utilisateur lambda :**
    - Nom : `John`
    - Prénom : `Doe`
    - Email : `john.doe@example.com`
    - Mot de passe : `User_password123`
    - Rôle : `ROLE_USER`

- Les chats par défaut incluent :
  - Nom : `Minty`, `Mikasa`, `Tommy`
  - Informations supplémentaires disponibles dans `src/DataFixtures/CatFixtures.php`.

### Sécurité JWT

- Lors de l'envoi des requêtes nécessitant une authentification :
  - Ajouter le token JWT dans les en-têtes :
    ```
    Authorization: Bearer <admin_jwt>
    ```
