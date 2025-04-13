<?php

declare(strict_types=1);

namespace App\ClientManagement\UserInterface\ApiPlatform\Processor;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Common\Application\Command\CommandBus;
use App\Common\Domain\Exception\InvalidFormat;
use App\ClientManagement\Application\DTO\ClientDTO;
use App\ClientManagement\Application\UseCase\UpdateProfile\UpdateProfileCommand;
use App\ClientManagement\Domain\Exception\ClientNotFound;
use App\ClientManagement\Domain\Exception\NewEmailProvided;
use App\ClientManagement\UserInterface\ApiPlatform\Resource\ClientResource;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Webmozart\Assert\Assert;

final class UpdateProfileProcessor implements ProcessorInterface
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
            $clientDTO = $this->updateClientAndReturnClientDTO($clientResource, $uriVariables['id']);
        } catch (ClientNotFound $exception) {
            throw new NotFoundHttpException($exception->getMessage());
        } catch (NewEmailProvided|InvalidFormat $exception) {
            throw new BadRequestException($exception->getMessage());
        }

        return ClientResource::fromClientDTO($clientDTO);
    }

    /**
     * @throws ClientNotFound
     * @throws NewEmailProvided
     * @throws InvalidFormat
     */
    private function updateClientAndReturnClientDTO(ClientResource $clientResource, string $clientId): ClientDTO
    {
        return $this->commandBus
            ->dispatch(
                new UpdateProfileCommand(
                    clientId: $clientId,
                    firstName: $clientResource->firstName,
                    lastName: $clientResource->lastName,
                    email: $clientResource->email,
                )
            );
    }
}