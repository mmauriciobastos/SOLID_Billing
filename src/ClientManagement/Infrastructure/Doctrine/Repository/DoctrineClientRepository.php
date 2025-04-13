<?php

declare(strict_types=1);

namespace App\ClientManagement\Infrastructure\Doctrine\Repository;

use App\Common\Domain\ValueObject\Email;
use App\ClientManagement\Domain\Entity\Client;
use App\ClientManagement\Domain\Exception\ClientNotFound;
use App\ClientManagement\Domain\Exception\ClientNotFoundWithId;
use App\ClientManagement\Domain\Repository\ClientRepository;
use App\ClientManagement\Domain\ValueObject\ClientId;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\OrderBy;
use Doctrine\Persistence\ManagerRegistry;


/**
 * @template-extends ServiceEntityRepository<Client>
 */
final class DoctrineClientRepository extends ServiceEntityRepository implements ClientRepository
{
    private const ALIAS = 'client';

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Client::class);
    }

    public function save(Client $client): void
    {
        $this->getEntityManager()->persist($client);
    }

    /**
     * @throws ClientNotFound
     */
    public function get(ClientId $id): Client
    {
        /** @var ?Client $client */
        $client = $this->find($id);
        if (null === $client) {
            throw new ClientNotFoundWithId($id);
        }

        return $client;
    }

    public function emailExist(Email $email): bool
    {
        $clientWithEmail = $this->findOneBy(['email' => $email]);

        return (null !== $clientWithEmail);
    }

    public function search(int $pageNumber, int $itemsPerPage): array
    {
        $queryBuilder = $this->createQueryBuilder(self::ALIAS);

        $queryBuilder
            ->setFirstResult(($pageNumber - 1) * $itemsPerPage)
            ->setMaxResults($itemsPerPage)
            ->orderBy(new OrderBy(self::ALIAS . '.createdAt'));

        return $queryBuilder->getQuery()->getResult();
    }
}
