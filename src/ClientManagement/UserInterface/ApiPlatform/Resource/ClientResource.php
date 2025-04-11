<?php

declare(strict_types=1);

namespace App\ClientManagement\UserInterface\ApiPlatform\Resource;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\ClientManagement\Application\DTO\ClientDTO;
use App\ClientManagement\UserInterface\ApiPlatform\Processor\CreateClientProcessor;
use App\ClientManagement\UserInterface\ApiPlatform\Processor\UpdateProfileProcessor;
use App\ClientManagement\UserInterface\ApiPlatform\Provider\ClientProvider;
use App\ClientManagement\UserInterface\ApiPlatform\Provider\ClientsProvider;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ApiResource(
    shortName: 'Client',
    operations: [
        new GetCollection(
            extraProperties: ['openapi_context' => ['summary' => 'Search clients']],
            provider: ClientsProvider::class,
        ),
        new Get(
            extraProperties: ['openapi_context' => ['summary' => 'Get client']],
            provider: ClientProvider::class,
        ),
        new Post(
            extraProperties: ['openapi_context' => ['summary' => 'Create client']],
            denormalizationContext: ['groups' => ['create']],
            validationContext: ['groups' => ['create']],
            processor: CreateClientProcessor::class,
        ),
        new Put(
            extraProperties: ['openapi_context' => ['summary' => 'Update client']],
            denormalizationContext: ['groups' => ['update']],
            validationContext: ['groups' => ['update']],
            processor: UpdateProfileProcessor::class,
        ),
    ],
)]
final class ClientResource
{
    public function __construct(
        #[ApiProperty(readable: true, writable: false, identifier: true)]
        #[Groups(groups: ['read', 'update'])]
        public readonly string $id = '',

        #[Assert\Length(min: 1, max: 255)]
        #[Assert\NotNull(groups: ['register', 'create', 'update'])]
        #[Groups(groups: ['read', 'create', 'update'])]
        public readonly string $firstName = '',

        #[Assert\Length(min: 1, max: 255)]
        #[Assert\NotNull(groups: ['register', 'create', 'update'])]
        #[Groups(groups: ['read', 'create', 'update'])]
        public readonly string $lastName = '',

        #[Assert\Length(min: 1, max: 255)]
        #[Assert\Email]
        #[Assert\NotNull(groups: ['register', 'create', 'update'])]
        #[Groups(groups: ['read', 'create', 'update'])]
        public readonly string $email = '',
    ) {
    }

    public static function fromClientDTO(ClientDTO $clientDTO): ClientResource
    {
        return new self(
            id: $clientDTO->id,
            firstName: $clientDTO->firstName,
            lastName: $clientDTO->lastName,
            email: $clientDTO->email,
        );
    }
}
