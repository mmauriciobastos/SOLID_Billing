<?php

declare(strict_types=1);

namespace App\ClientManagement\Domain\Repository;

use App\Common\Domain\Repository\Repository;
use App\Common\Domain\ValueObject\Email;
use App\ClientManagement\Domain\Entity\Client;
use App\ClientManagement\Domain\Exception\ClientNotFound;
use App\ClientManagement\Domain\ValueObject\ClientId;

/**
 * @extends Repository<Client>
 */
interface ClientRepository extends Repository
{
    public function add(Client $client): void;

    /**
     * @throws ClientNotFound
     */
    public function get(ClientId $id): Client;

    public function emailExist(Email $email): bool;

    public function search(int $pageNumber, int $itemsPerPage): array;
}
