<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture
{
    private $userPasswordHasher;

    public function __construct(UserPasswordHasherInterface $userPasswordHasher)
    {
        $this->userPasswordHasher = $userPasswordHasher;
    }

    public function load(ObjectManager $manager): void
    {

        $usersData = [
            ['name' => 'John', 'lastname' => 'Doe', 'email' => 'john.doe@example.com', 'password' => 'User_password123', 'role' => 'user'],
            ['name' => 'Jane', 'lastname' => 'Smith', 'email' => 'jane.smith@example.com', 'password' => 'User_password123', 'role' => 'user'],
            ['name' => 'Marion', 'lastname' => 'Bailleux', 'email' => 'marion.bailleux@example.com', 'password' => 'Admin_password123', 'role' => 'admin'],
        ];

        foreach ($usersData as $userData) {

            $role = $userData['role'];

            $user = new User();
            $user->setName($userData['name']);
            $user->setLastname($userData['lastname']);
            $user->setEmail($userData['email']);
            $user->setPassword($this->userPasswordHasher->hashPassword($user, $userData['password']));
            if ($role === 'admin') {
                $user->setRoles(['ROLE_ADMIN']);
            } elseif ($role === 'user') {
                $user->setRoles(['ROLE_USER']);
            } else {
                throw new \InvalidArgumentException(sprintf('Invalid role "%s" for user %s.', $role, $userData['email']));
            }

            $manager->persist($user);
        }

        $manager->flush();
    }
}
