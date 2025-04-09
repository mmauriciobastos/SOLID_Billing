<?php

declare(strict_types=1);

namespace App\Messaging\Domain\Entity;

use App\Authentication\Application\DTO\AuthClientDTO;
use App\Common\Domain\Entity\AggregateRoot;
use App\Common\Domain\ValueObject\DateTime;
use App\Messaging\Domain\Exception\NotEnoughParticipants;
use App\Messaging\Domain\Exception\ParticipantNotFoundInConversation;
use App\Messaging\Domain\Exception\ClientIsNotParticipantOfConversation;
use App\Messaging\Domain\ValueObject\ConversationId;
use App\Messaging\Domain\ValueObject\MessageContent;
use App\ClientManagement\Application\DTO\ClientDTO;
use App\ClientManagement\Domain\ValueObject\ClientId;
use Carbon\Carbon;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

class Conversation extends AggregateRoot
{
    public const MIN_PARTICIPANTS = 2;

    private ConversationId $id;

    private DateTime $createdAt;

    /** @var Collection<int, Message> */
    private Collection $messages;

    /** @var Collection<int, Participant> */
    private Collection $participants;

    /**
     * @param ClientDTO[] $clientsDTOs
     */
    private function __construct(DateTime $createdAt, array $clientsDTOs)
    {
        $this->ensureHasEnoughParticipants($clientsDTOs);

        $this->id = ConversationId::generate();
        $this->createdAt = $createdAt;
        $this->messages = new ArrayCollection();
        $this->participants = new ArrayCollection();

        foreach ($clientsDTOs as $clientDTO) {
            $this->addParticipant(Participant::create($clientDTO, $this));
        }
    }

    /**
     * @param ClientDTO[] $clientsDTOs
     */
    public static function create(DateTime $createdAt, array $clientsDTOs): self
    {
        return new self(
            createdAt: $createdAt,
            clientsDTOs: $clientsDTOs,
        );
    }

    public function postMessage(Participant $participant, MessageContent $content): void
    {
        $this->ensureParticipantIsInConversation($participant);

        $this->messages
            ->add(
                Message::create(
                    $this,
                    $content,
                    Carbon::now(),
                    $participant,
                )
            );

        // TODO Put here the event about message sent
    }

    public function addParticipant(Participant $participant): void
    {
        $this->participants->add($participant);
    }

    public function archiveForParticipant(Participant $participant): void
    {
        $this->ensureParticipantIsInConversation($participant);

        $participant->archive();
    }

    public function id(): ConversationId
    {
        return $this->id;
    }

    public function createdAt(): DateTime
    {
        return $this->createdAt;
    }

    public function participants(): array
    {
        return $this->participants->toArray();
    }

    public function participantFromAuthClient(AuthClientDTO $authClient): Participant
    {
        foreach ($this->participants as $participant) {
            if ($participant->clientId()->equals($authClient->clientId)) {
                return $participant;
            }
        }

        throw new ClientIsNotParticipantOfConversation(
            clientId: ClientId::fromString($authClient->clientId),
            conversationId: $this->id(),
        );
    }

    private function ensureParticipantIsInConversation(Participant $participant): void
    {
        if (!$participant->conversation()->id()->equals($this->id())) {
            throw new ParticipantNotFoundInConversation($this->id(), $participant->id());
        }
    }

    private function ensureHasEnoughParticipants(array $clients): void
    {
        if (count($clients) < self::MIN_PARTICIPANTS) {
            throw new NotEnoughParticipants(self::MIN_PARTICIPANTS, count($clients));
        }
    }
}
