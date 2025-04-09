<?php

declare(strict_types=1);

namespace App\Messaging\Domain\Exception;

use App\Messaging\Domain\ValueObject\ConversationId;
use App\ClientManagement\Domain\ValueObject\ClientId;

final class ClientIsAlreadyParticipantOfConversation extends \DomainException
{
    public function __construct(ClientId $clientId, ConversationId $conversationId)
    {
        parent::__construct(sprintf('The client "%s" was already participant of conversation "%s".', (string) $clientId, (string) $conversationId));
    }
}
