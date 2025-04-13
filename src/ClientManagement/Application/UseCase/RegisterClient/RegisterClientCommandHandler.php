<?php

declare(strict_types=1);

namespace App\ClientManagement\Application\UseCase\RegisterClient;

use App\Common\Application\Command\CommandHandler;
use App\Common\Domain\Exception\InvalidFormat;
use App\Common\Domain\ValueObject\Email;
use App\Common\Domain\ValueObject\FirstName;
use App\Common\Domain\ValueObject\LastName;
use App\ClientManagement\Application\DTO\ClientDTO;
use App\ClientManagement\Domain\Entity\Client;
use App\ClientManagement\Domain\Exception\EmailAlreadyUsed;
use App\ClientManagement\Domain\Repository\ClientRepository;

final class RegisterClientCommandHandler implements CommandHandler
{
    public function __construct(
        private readonly ClientRepository $clientRepository,
    ) {
    }

    /**
     * @throws EmailAlreadyUsed|InvalidFormat
     */
    public function __invoke(RegisterClientCommand $command): ClientDTO
    {
        $email = Email::fromString($command->email);
        $this->ensureEmailNotExist($email);

        $client = Client::create(
            firstName: FirstName::fromString($command->firstName),
            lastName: LastName::fromString($command->lastName),
            email: $email,
        );

        $this->clientRepository->save($client);

        return ClientDTO::fromEntity($client);
    }

    /**
     * @throws EmailAlreadyUsed
     */
    private function ensureEmailNotExist(Email $email): void
    {
        if ($this->clientRepository->emailExist($email)) {
            throw new EmailAlreadyUsed($email);
        }
    }
}
