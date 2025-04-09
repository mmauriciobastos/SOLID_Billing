<?php

declare(strict_types=1);

namespace App\Messaging\Domain\Entity;

use App\Messaging\Domain\Exception\ConversationAlreadyArchivedByParticipant;
use App\Messaging\Domain\ValueObject\ParticipantId;
use App\Messaging\Domain\ValueObject\ParticipantName;
use App\ClientManagement\Application\DTO\ClientDTO;
use App\ClientManagement\Domain\ValueObject\ClientId;

class Participant
{
    private ParticipantId $id;
    private ParticipantName $name;
    private ClientId $clientId;
    private bool $isArchived;

    private function __construct(
        private readonly ClientDTO $clientDTO,
        private readonly Conversation $conversation,
    ) {
        $this->id = ParticipantId::generate();
        $this->clientId = ClientId::fromString($this->clientDTO->id);
        $this->name = ParticipantName::fromClientDTO($this->clientDTO);
        $this->isArchived = false;
    }

    public static function create(
        ClientDTO $clientDTO,
        Conversation $conversation,
    ): self {
        return new self($clientDTO, $conversation);
    }

    /**
     * @throws ConversationAlreadyArchivedByParticipant
     */
    public function archive(): void
    {
        $this->ensureIsNotArchived();

        $this->isArchived = true;
    }

    public function id(): ParticipantId
    {
        return $this->id;
    }

    public function clientId(): ClientId
    {
        return $this->clientId;
    }

    public function name(): ParticipantName
    {
        return $this->name;
    }

    public function conversation(): Conversation
    {
        return $this->conversation;
    }

    public function isArchived(): bool
    {
        return $this->isArchived;
    }

    /**
     * @throws ConversationAlreadyArchivedByParticipant
     */
    private function ensureIsNotArchived(): void
    {
        if ($this->isArchived()) {
            throw new ConversationAlreadyArchivedByParticipant($this->conversation());
        }
    }
}
