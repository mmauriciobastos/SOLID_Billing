<?php

declare(strict_types=1);

namespace App\Common\Application\Session;

use App\Authentication\Application\DTO\AuthClientDTO;

interface Security
{
    public function isAuthenticated(): bool;

    public function connectedClient(): ?AuthClientDTO;
}
