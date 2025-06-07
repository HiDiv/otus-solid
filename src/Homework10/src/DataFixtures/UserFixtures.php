<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture
{
    private UserPasswordHasherInterface $hasher;

    public function __construct(UserPasswordHasherInterface $hasher)
    {
        $this->hasher = $hasher;
    }

    public function load(ObjectManager $manager): void
    {
        $usersData = [
            ['login' => 'admin', 'password' => 'admin123', 'roles' => ['ROLE_ADMIN']],
            ['login' => 'manager', 'password' => 'manager123', 'roles' => ['ROLE_MANAGER']],
            ['login' => 'user', 'password' => 'user123', 'roles' => ['ROLE_USER']],
        ];

        foreach ($usersData as $data) {
            $user = new User();
            $user->setLogin($data['login']);
            $user->setRoles($data['roles']);
            $user->setPassword($this->hasher->hashPassword($user, $data['password']));

            $manager->persist($user);
        }

        $manager->flush();
    }
}
