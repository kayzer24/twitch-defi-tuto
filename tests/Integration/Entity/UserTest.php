<?php

declare(strict_types=1);

namespace App\Tests\Integration\Entity;

use App\Entity\User;
use App\Tests\Integration\ValidationRule;
use App\Tests\Integration\ValidationTestCase;
use Generator;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

final class UserTest extends ValidationTestCase
{
    /**
     * @return Generator<string, array<array-key, ValidationRule>>
     */
    public function provideEntities(): Generator
    {
        yield 'valid user' => [
            self::createValidationRule(
                $this->createUser(),
                ['register', 'Default']
            ),
        ];

        yield 'invalid user with empty email' => [
            self::createValidationRule()
                ->setData($this->createUser(''))
                ->addGroups('register', 'Default')
                ->addError('email', NotBlank::class),
        ];

        yield 'email invalid' => [
            self::createValidationRule()
                ->setData($this->createUser('fail'))
                ->addGroups('register', 'Default')
                ->addError('email', Email::class),
        ];

        yield 'existing email' => [
            self::createValidationRule()
                ->setData($this->createUser('user+1@email.com'))
                ->addGroups('register', 'Default')
                ->addError('email', UniqueEntity::class),
        ];

        yield 'empty nickname' => [
            self::createValidationRule()
                ->setData($this->createUser('user+11@email.com', ''))
                ->addGroups('register', 'Default')
                ->addError('nickname', NotBlank::class),
        ];

        yield 'existing nickname' => [
            self::createValidationRule()
                ->setData($this->createUser('user+11@email.com', 'user+1'))
                ->addGroups('register', 'Default')
                ->addError('nickname', UniqueEntity::class),
        ];

        yield 'empty plain password' => [
            self::createValidationRule()
                ->setData($this->createUser('user+11@email.com', 'user+11', ''))
                ->addGroups('register', 'Default')
                ->addError('plainPassword', Length::class),
        ];

        yield 'plain password too short' => [
            self::createValidationRule()
                ->setData($this->createUser('user+11@email.com', 'user+11', 'fail'))
                ->addGroups('register', 'Default')
                ->addError('plainPassword', Length::class),
        ];
    }

    public function createUser(
        string $email = 'user+11@email.com',
        string $nickname = 'user+11',
        string $plainPassword = 'password'
    ): User {
        $user = new User();
        $user->setNickname($nickname);
        $user->setEmail($email);
        $user->setPlainPassword($plainPassword);

        return $user;
    }
}
