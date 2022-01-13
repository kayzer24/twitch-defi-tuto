<?php

declare(strict_types=1);

namespace App\DataFixtures;


use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final class UserFixtures extends Fixture
{
    /**
     * @var UserPasswordHasherInterface
     */
    private UserPasswordHasherInterface $userPasswordHasher;

    public function __construct(UserPasswordHasherInterface $userPasswordHasher)
    {
        $this->userPasswordHasher = $userPasswordHasher;
    }

    public function load(ObjectManager $manager)
    {
        for ($i=0; $i<10; $i++) {
            $manager->persist($this->createUser($i));
        }

        $manager->flush();
    }

    private function createUser(int $index)
    {
        $user = new User();
        $user->setEmail(sprintf('user+%d@email.com', $index));
        $user->setNickname(sprintf('user+%d', $index));
        $user->setPassword($this->userPasswordHasher->hashPassword($user, 'password'));
        return $user;
    }
}