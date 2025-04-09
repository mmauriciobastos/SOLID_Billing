<?php

declare(strict_types=1);

namespace App\ClientManagement\Domain\Exception;

use App\Common\Domain\Exception\ResourceNotFound;

abstract class ClientNotFound extends ResourceNotFound
{
}
