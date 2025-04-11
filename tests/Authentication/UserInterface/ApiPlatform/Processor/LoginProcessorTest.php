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
    private const VALID_EMAIL = 'test@example.com';
    private const VALID_PASSWORD = 'password123';
    private const VALID_TOKEN = 'token123';

    private $commandBus;
    private LoginProcessor $processor;
    private Operation $operation;

    protected function setUp(): void
    {
        $this->commandBus = $this->createMock(CommandBus::class);
        $this->processor = new LoginProcessor($this->commandBus);
        $this->operation = $this->createMock(Operation::class);
    }

    /**
     * @test
     * @group authentication
     * @group api
     */
    public function should_return_jwt_token_when_login_is_valid(): void
    {
        // Arrange
        $authToken = AuthTokenDTO::fromString(self::VALID_TOKEN);
        $loginPayload = new Login(self::VALID_EMAIL, self::VALID_PASSWORD);

        $this->commandBus
            ->method('dispatch')
            ->with(new LoginCommand(self::VALID_EMAIL, self::VALID_PASSWORD))
            ->willReturn($authToken);

        // Act
        $result = $this->processor->process($loginPayload, $this->operation);

        // Assert
        $this->assertInstanceOf(
            JWT::class, 
            $result,
            'Login processor should return a JWT instance'
        );
        $this->assertSame(
            self::VALID_TOKEN,
            $result->token,
            'JWT token should match the expected token value'
        );
    }

    /**
     * @test
     * @group authentication
     * @group api
     * @group validation
     */
    public function should_throw_exception_when_input_data_is_invalid(): void
    {
        // Arrange
        $this->expectException(InvalidArgumentException::class);

        // Act & Assert
        $this->processor->process('invalid_data', $this->operation);
    }
}
