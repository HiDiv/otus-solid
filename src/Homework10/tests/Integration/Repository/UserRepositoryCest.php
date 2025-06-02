<?php

namespace App\Tests\Integration\Repository;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Tests\Support\IntegrationTester;
use Doctrine\ORM\EntityManagerInterface;

class UserRepositoryCest
{
    public function testFindOneByLogin(IntegrationTester $I): void
    {
        /** @var EntityManagerInterface $em */
        $em = $I->grabService(EntityManagerInterface::class);

        $user = new User();
        $user->setLogin('integration_user');
        $user->setPassword('dummy');
        $em->persist($user);
        $em->flush();
        $em->clear();

        $userRepo = $I->grabService(UserRepository::class);

        $found = $userRepo->findOneByLogin('integration_user');

        $I->assertInstanceOf(User::class, $found);
        $I->assertSame('integration_user', $found->getLogin());

        $notFound = $userRepo->findOneByLogin('nope');

        $I->assertNull($notFound);
    }
}
