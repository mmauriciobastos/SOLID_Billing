<?php

declare(strict_types=1);

namespace App\Authentication\Infrastructure\Doctrine\Repository;

use App\Authentication\Domain\Entity\ClientCredential;
use App\Authentication\Domain\Exception\CredentialNotFoundForUsername;
use App\Authentication\Domain\Repository\ClientCredentialRepository;
use App\Authentication\Domain\ValueObject\Username;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @template-extends ServiceEntityRepository<ClientCredential>
 */
final class DoctrineClientCredentialRepository extends ServiceEntityRepository implements ClientCredentialRepository
{
    private const ALIAS = 'client_credential';

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ClientCredential::class);
    }

    public function getByUsername(Username $username): ClientCredential
    {
        $expressionBuilder = $this->getEntityManager()->getExpressionBuilder();
        $queryBuilder = $this->createQueryBuilder(self::ALIAS);

        $queryBuilder
            ->where($expressionBuilder->eq(self::ALIAS.'.username', ':username'))
            ->setParameter('username', $username);

        /** @var ?ClientCredential $clientCredential */
        $clientCredential = $queryBuilder->getQuery()->getOneOrNullResult();
        if (null === $clientCredential) {
            throw new CredentialNotFoundForUsername($username);
        }

        return $clientCredential;
    }

    public function save(ClientCredential $clientCredential): void
    {
        $this->getEntityManager()->persist($clientCredential);
    }
}
