<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api', name: 'api_')]
class AuthController extends AbstractController
{
    /**
     * Register a new user with ROLE_USER.
     *
     * Request Body:
     * {
     *   "name": "string",
     *   "lastname": "string",
     *   "email": "string",
     *   "password": "string"
     * }
     *
     * Response Codes:
     * 201: User created successfully.
     * 400: Invalid input data.
     * 409: Email already registered.
     */
    #[Route('/register', name: 'register')]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager): JsonResponse
    {
        try {
            $data = $request->toArray();
        } catch (\JsonException $e) {
            return $this->json(['message' => 'Invalid JSON'], JsonResponse::HTTP_BAD_REQUEST);
        }

        // Vérification des champs obligatoires
        $requiredFields = ['name', 'lastname', 'email', 'password'];
        foreach ($requiredFields as $field) {
            if (empty($data[$field] ?? null)) {
                return $this->json(['message' => "Field '$field' is required"], JsonResponse::HTTP_BAD_REQUEST);
            }
        }


        // Définitions des expressions régulières
        $namePattern = '/^[a-zA-Z\s\-]+$/';
        $passwordPattern = '/^(?=.*[A-Z])(?=.*[0-9])(?=.*[\W]).{8,}$/';


        $name = htmlspecialchars(strtolower(trim($data['name'] ?? '')));
        $lastname = htmlspecialchars(strtolower(trim($data['lastname'] ?? '')));
        $email = htmlspecialchars(trim($data['email'] ?? ''));
        $password = $data['password'] ?? '';

        // Validation des champs `name` et `lastname`
        if (!preg_match($namePattern, $name) || !preg_match($namePattern, $lastname)) {
            return $this->json(['error' => 'Name and lastname must only contain letters, spaces, or hyphens'], JsonResponse::HTTP_BAD_REQUEST);
        }

        // Validation de l'email
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $this->json(['message' => 'Invalid email format'], JsonResponse::HTTP_BAD_REQUEST);
        }

        // Vérification de l'unicité de l'email
        $existingUser = $entityManager->getRepository(User::class)->findOneBy(['email' => $email]);
        if ($existingUser) {
            return $this->json(['message' => 'Email already registered'], JsonResponse::HTTP_CONFLICT);
        }

        // Validation du mot de passe
        if (!preg_match($passwordPattern, $password)) {
            return $this->json([
                'message' => 'Password must be at least 8 characters long and include at least one uppercase letter, one number, and one special character'
            ], JsonResponse::HTTP_BAD_REQUEST);
        }

        $user = new User();
        $user->setName($name);
        $user->setLastname($lastname);
        $user->setEmail($email);
        $user->setRoles(["ROLE_USER"]);
        $user->setPassword($userPasswordHasher->hashPassword($user, $password));
        $entityManager->persist($user);
        $entityManager->flush();


        return $this->json(["message" => "User created successfully", "data" => [
            'name' => $user->getName(),
            'lastname' => $user->getLastname(),
            'email' => $user->getEmail(),
        ]], 201);
    }
}
