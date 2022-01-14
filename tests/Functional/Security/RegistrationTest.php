<?php

declare(strict_types=1);

namespace App\Tests\Functional\Security;

use Generator;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class RegistrationTest extends WebTestCase
{
    /**
     * @test
     */
    public function shouldRegister(): void
    {
        $client = self::createClient();

        $client->request(Request::METHOD_GET, '/registration');

        self::assertResponseIsSuccessful();

        $client->submitForm('S\'inscrire', $this->createData());

        self::assertResponseStatusCodeSame(Response::HTTP_FOUND);

        $client->followRedirect();

        self::assertRouteSame('security_login');
    }

    /**
     * @param array<string, string> $formData
     *
     * @test
     *
     * @dataProvider provideInvalidData
     */
    public function shouldNotRegisterDueToInvalidData(array $formData): void
    {
        $client = self::createClient();

        $client->request(Request::METHOD_GET, '/registration');

        self::assertResponseIsSuccessful();

        $client->submitForm('S\'inscrire', $formData);

        self::assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    /**
     * @return Generator<string, array<array-key, array<string, string>>>
     */
    public function provideInvalidData(): Generator
    {
        yield 'empty email' => [$this->createData(['registration[email]' => ''])];
        yield 'email invalid' => [$this->createData(['registration[email]' => 'fail'])];
        yield 'existing email' => [$this->createData(['registration[email]' => 'user+1@email.com'])];
        yield 'empty nickname' => [$this->createData(['registration[nickname]' => ''])];
        yield 'existing nickname' => [$this->createData(['registration[nickname]' => 'user+1'])];
        yield 'empty plain password' => [$this->createData(['registration[plainPassword]' => ''])];
        yield 'plain password too short' => [$this->createData(['registration[plainPassword]' => 'fail'])];
    }

    /**
     * @param array<string, string> $extra
     *
     * @return array<string, string>
     */
    private function createData(array $extra = []): array
    {
        return $extra + [
                'registration[email]' => 'user+11@email.com',
                'registration[plainPassword]' => 'password',
                'registration[nickname]' => 'user+11',
            ];
    }
}
