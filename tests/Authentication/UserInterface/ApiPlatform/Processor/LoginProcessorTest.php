<?php

declare(strict_types=1);

namespace Tests\Authentication\UserInterface\ApiPlatform\Processor;

use ApiPlatform\Metadata\Operation;
use App\Authentication\Application\DTO\AuthTokenDTO;
use App\Authentication\Application\UseCase\Login\LoginCommand;
use App\Authentication\UserInterface\ApiPlatform\Output\JWT;
use App\Authentication\UserInterface\ApiPlatform\Payload\Login;
use App\Authentication\UserInterface\ApiPlatform\Processor\LoginProcessor;
use App\Common\Application\Command\CommandBus;
use PHPUnit\Framework\TestCase;
use Webmozart\Assert\InvalidArgumentException;

final class LoginProcessorTest extends TestCase
{
    private $commandBus;
    private LoginProcessor $processor;

    protected function setUp(): void
    {
        $this->commandBus = $this->createMock(CommandBus::class);
        $this->processor = new LoginProcessor($this->commandBus);
    }

    public function testProcessValidLogin(): void
    {
        $email = 'test@example.com';
        $password = 'password123';
        $authToken = AuthTokenDTO::fromString('token123');
        $loginPayload = new Login($email, $password);

        $this->commandBus
            ->method('dispatch')
            ->with(new LoginCommand($email, $password))
            ->willReturn($authToken);

        $operation = $this->createMock(Operation::class);

        $result = $this->processor->process($loginPayload, $operation);

        $this->assertInstanceOf(JWT::class, $result);
        $this->assertSame('token123', $result->token);
    }

    public function testProcessInvalidData(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $operation = $this->createMock(Operation::class);

        $this->processor->process('invalid_data', $operation);
    }
}
