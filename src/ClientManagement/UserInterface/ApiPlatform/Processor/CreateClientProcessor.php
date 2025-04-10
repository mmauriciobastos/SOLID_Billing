<?php

declare(strict_types=1);

namespace App\ClientManagement\UserInterface\ApiPlatform\Processor;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Common\Application\Command\CommandBus;
use App\Common\Domain\Exception\InvalidFormat;
use App\ClientManagement\Application\DTO\ClientDTO;
use App\ClientManagement\Application\UseCase\CreateClient\RegisterClientCommand;
use App\ClientManagement\Domain\Exception\EmailAlreadyUsed;
use App\ClientManagement\UserInterface\ApiPlatform\Resource\ClientResource;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Webmozart\Assert\Assert;

final class CreateClientProcessor implements ProcessorInterface
{
    public function __construct(
        private readonly CommandBus $commandBus,
    ) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): ClientResource
    {
        Assert::isInstanceOf($data, ClientResource::class);
        /** @var ClientResource $clientResource */
        $clientResource = $data;

        try {
            $clientDTO = $this->createClientAndReturnClientDTO($clientResource);
        } catch (EmailAlreadyUsed|InvalidFormat $exception) {
            throw new BadRequestException($exception->getMessage());
        }

        return ClientResource::fromClientDTO($clientDTO);
    }

    /**
     * @throws EmailAlreadyUsed|InvalidFormat
     */
    private function createClientAndReturnClientDTO(ClientResource $clientResource): ClientDTO
    {
        return $this->commandBus
            ->dispatch(
                new RegisterClientCommand(
                    firstName: $clientResource->firstName,
                    lastName: $clientResource->lastName,
                    email: $clientResource->email,
                )
            );
    }
}
