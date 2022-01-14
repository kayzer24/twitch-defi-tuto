<?php

declare(strict_types=1);

namespace App\Tests\Functional\Security;

use Generator;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Bundle\SecurityBundle\DataCollector\SecurityDataCollector;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Profiler\Profile;

class AuthenticationTest extends WebTestCase
{
    /**
     * @test
     */
    public function shouldAuthenticate(): void
    {
        $client = self::createClient();
        $client->request(Request::METHOD_GET, '/login');

        self::assertResponseIsSuccessful();

        $client->enableProfiler();
        $client->submitForm('Se connecter', [
            '_username' => 'user+1@email.com',
            '_password' => 'password',
        ]);

        self::assertResponseStatusCodeSame(Response::HTTP_FOUND);

        if (($profile = $client->getProfile()) instanceof Profile) {
            /** @var SecurityDataCollector $securityCollector */
            $securityCollector = $profile->getCollector('security');

            self::assertTrue($securityCollector->isAuthenticated());
        }
    }

    /**
     * @param array{_username:string, _password: string} $formData
     * @test
     * @dataProvider provideInvalidData
     */
    public function shouldNotAuthenticateDueToInvalidData(array $formData): void
    {
        $client = self::createClient();
        $client->request(Request::METHOD_GET, '/login');

        self::assertResponseIsSuccessful();

        $client->enableProfiler();
        $client->submitForm('Se connecter', $formData);

        self::assertResponseStatusCodeSame(Response::HTTP_FOUND);

        if (($profile = $client->getProfile()) instanceof Profile) {
            /** @var SecurityDataCollector $securityCollector */
            $securityCollector = $profile->getCollector('security');

            self::assertFalse($securityCollector->isAuthenticated());
        }
    }

    /**
     * @return Generator<string, array<array-key, array<string, string>>>
     */
    public function provideInvalidData(): iterable
    {
        yield 'wrong email' => [$this->createData(['_username' => 'fail@email.com'])];
        yield 'empty email' => [$this->createData(['_username' => ''])];
        yield 'wrong password' => [$this->createData(['_password' => 'fail'])];
        yield 'empty password' => [$this->createData(['_password' => ''])];
        yield 'empty csrf' => [$this->createData(['_csrf_token' => ''])];
        yield 'wrong csrf' => [$this->createData(['_csrf_token' => 'fail'])];
    }

    /**
     * @param array<string, string> $extra
     *
     * @return array<string, string>
     */
    private function createData(array $extra)
    {
        return $extra + [
                '_username' => 'user+1@email.com',
                '_password' => 'password',
            ];
    }
}
