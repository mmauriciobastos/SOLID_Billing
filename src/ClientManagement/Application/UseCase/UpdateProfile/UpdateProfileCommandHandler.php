<?php

declare(strict_types=1);

namespace App\ClientManagement\Application\UseCase\UpdateProfile;

use App\ClientManagement\Domain\Exception\NewEmailProvided;
use App\Common\Application\Command\CommandHandler;
use App\ClientManagement\Application\DTO\ClientDTO;
use App\ClientManagement\Domain\Exception\ClientNotFound;
use App\ClientManagement\Domain\Exception\EmailAlreadyUsed;
use App\ClientManagement\Domain\Repository\ClientRepository;
use App\ClientManagement\Domain\ValueObject\ClientId;
use App\Common\Domain\ValueObject\Email;
use App\Common\Domain\ValueObject\FirstName;
use App\Common\Domain\ValueObject\LastName;

final class UpdateProfileCommandHandler implements CommandHandler
{
    public function __construct(
        private readonly ClientRepository $clientRepository,
    ) {
    }

    /**
     * @throws ClientNotFound|NewEmailProvided
     */
    public function __invoke(UpdateProfileCommand $command): ClientDTO
    {
        $clientId = ClientId::fromString($command->clientId);
        $email = Email::fromString($command->email);
        $firstName = FirstName::fromString($command->firstName);
        $lastName = LastName::fromString($command->lastName);

        $client = $this->clientRepository->get($clientId);

        if ((string)$client->email() !== (string)$email)
        {
            throw new NewEmailProvided();
        }

        $client->updateProfile($firstName, $lastName);
        $this->clientRepository->save($client);

        return ClientDTO::fromEntity($client);
    }
}