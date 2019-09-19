<?php declare(strict_types=1);

/*
 * Copyright (c) Romain Cottard
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpMqdb\Message;

use PhpMqdb\Enumerator;

/**
 * Trait Message. Implementation of MessageInterface
 *
 * @author  Romain Cottard
 */
trait MessageTrait
{
    /**
     * @var string $id
     */
    protected $id = '';

    /**
     * @var int $status
     */
    protected $status = Enumerator\Status::IN_QUEUE;

    /**
     * @var int $priority
     */
    protected $priority = Enumerator\Priority::MEDIUM;

    /**
     * @var string $topic
     */
    protected $topic = '';

    /**
     * @var string $content
     */
    protected $content = '';

    /**
     * @var string $contentType
     */
    protected $contentType = 'text';

    /**
     * @var string $entityId
     */
    protected $entityId = null;

    /**
     * @var string $dateExpiration
     */
    protected $dateExpiration = null;

    /**
     * @var string $dateAvailability
     */
    protected $dateAvailability = null;

    /**
     * @var string $dateCreate
     */
    protected $dateCreate = '0000-00-00 00:00:00';

    /**
     * @var string $dateUpdate
     */
    protected $dateUpdate = null;

    /**
     * Get Message Identifier.
     *
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * Get message status.
     *
     * @return int
     */
    public function getStatus(): int
    {
        return $this->status;
    }

    /**
     * Get message priority.
     *
     * @return int
     */
    public function getPriority(): int
    {
        return $this->priority;
    }

    /**
     * Get message topic.
     *
     * @return string
     */
    public function getTopic(): string
    {
        return $this->topic;
    }

    /**
     * Get message content.
     *
     * @return string
     */
    public function getContent(): string
    {
        return $this->content;
    }

    /**
     * Get message content type ('csv', 'json', 'xml', 'text'...)
     *
     * @return string
     */
    public function getContentType(): string
    {
        return $this->contentType;
    }

    /**
     * Get Entity identifier linked to the message.
     *
     * @return string
     */
    public function getEntityId(): ?string
    {
        return $this->entityId;
    }

    /**
     * Get the expiration date of the message.
     *
     * @return string
     */
    public function getDateExpiration(): ?string
    {
        return $this->dateExpiration;
    }

    /**
     * Get availability date of the message.
     *
     * @return string
     */
    public function getDateAvailability(): ?string
    {
        return $this->dateAvailability;
    }

    /**
     * Get the creation date of the message.
     *
     * @return string
     */
    public function getDateCreate(): string
    {
        return $this->dateCreate;
    }

    /**
     * Get the update date of the message.
     *
     * @return string
     */
    public function getDateUpdate(): ?string
    {
        return $this->dateUpdate;
    }

    /**
     * Set Message Identifier.
     *
     * @param  string $id
     * @return MessageInterface
     */
    public function setId(string $id): MessageInterface
    {
        $this->id = (string) $id;

        return $this;
    }

    /**
     * Set message status.
     * Status list:
     *  0: In queue (Waiting to be consumed)
     *  1: pending ack
     *  2: pending ack (no response after a delay, can be deleted) ?
     *  3: ack received (treated, cannot be retreated)
     *
     * @param  int $status
     * @return MessageInterface
     * @throws \UnderflowException
     */
    public function setStatus(int $status): MessageInterface
    {
        if ($status < 0) {
            throw new \UnderflowException('Value of "status" must be greater than 0');
        }

        $this->status = $status;

        return $this;
    }

    /**
     * Set message priority.
     * Number from 1 to 5
     *  1: Very High
     *  2: High
     *  3: Medium
     *  4: Low
     *  5: Very Low
     *
     * @param  int $priority
     * @return MessageInterface
     * @throws \UnderflowException
     */
    public function setPriority(int $priority): MessageInterface
    {
        if ($priority < 0) {
            throw new \UnderflowException('Value of "priority" must be greater than 0');
        }

        $this->priority = $priority;

        return $this;
    }

    /**
     * Set message topic.
     *
     * @param  string $topic
     * @return MessageInterface
     */
    public function setTopic(string $topic): MessageInterface
    {
        $this->topic = $topic;

        return $this;
    }

    /**
     * Set message content.
     *
     * @param  string $content
     * @return MessageInterface
     */
    public function setContent(string $content): MessageInterface
    {
        $this->content = $content;

        return $this;
    }

    /**
     * Set message content type
     *
     * @param  string $contentType ('csv', 'json', 'xml', 'text'...)
     * @return MessageInterface
     */
    public function setContentType(string $contentType): MessageInterface
    {
        $this->contentType = $contentType;

        return $this;
    }

    /**
     * Set Entity identifier linked to the message.
     *
     * @param  string $entityId
     * @return MessageInterface
     */
    public function setEntityId(?string $entityId): MessageInterface
    {
        $this->entityId = $entityId;

        return $this;
    }

    /**
     * Set the expiration date of the message.
     *
     * @param  string $dateExpiration
     * @return MessageInterface
     */
    public function setDateExpiration(?string $dateExpiration): MessageInterface
    {
        $this->dateExpiration = $dateExpiration;

        return $this;
    }

    /**
     * Set availability date of the message.
     *
     * @param  string $dateAvailability
     * @return MessageInterface
     */
    public function setDateAvailability(?string $dateAvailability): MessageInterface
    {
        $this->dateAvailability = $dateAvailability;

        return $this;
    }

    /**
     * Set the creation date of the message.
     *
     * @param  string $dateCreate
     * @return MessageInterface
     */
    public function setDateCreate(string $dateCreate): MessageInterface
    {
        $this->dateCreate = $dateCreate;

        return $this;
    }

    /**
     * Set the update date of the message.
     *
     * @param  string $dateUpdate
     * @return MessageInterface
     */
    public function setDateUpdate(?string $dateUpdate): MessageInterface
    {
        $this->dateUpdate = $dateUpdate;

        return $this;
    }
}
