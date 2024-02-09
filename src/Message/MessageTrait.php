<?php

/*
 * Copyright (c) Romain Cottard
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace PhpMqdb\Message;

use PhpMqdb\Enumerator;

trait MessageTrait
{
    protected string $id = '';
    protected int $status = Enumerator\Status::IN_QUEUE;
    protected int $priority = Enumerator\Priority::MEDIUM;
    protected string $topic = '';
    protected string $content = '';
    protected string $contentType = 'text';
    protected ?string $entityId = null;
    protected ?string $dateExpiration = null;
    protected ?string $dateAvailability = null;
    protected string $dateCreate = '0000-00-00 00:00:00';
    protected ?string $dateUpdate = null;

    public function getId(): string
    {
        return $this->id;
    }

    public function getStatus(): int
    {
        return $this->status;
    }

    public function getPriority(): int
    {
        return $this->priority;
    }

    public function getTopic(): string
    {
        return $this->topic;
    }

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

    public function getDateExpiration(): ?string
    {
        return $this->dateExpiration;
    }

    public function getDateAvailability(): ?string
    {
        return $this->dateAvailability;
    }

    public function getDateCreate(): string
    {
        return $this->dateCreate;
    }

    public function getDateUpdate(): ?string
    {
        return $this->dateUpdate;
    }

    /**
     * Set Message Identifier.
     *
     * @param  string $id
     * @return static
     */
    public function setId(string $id): static
    {
        $this->id = $id;

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
     * @return static
     * @throws \UnderflowException
     */
    public function setStatus(int $status): static
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
     * @return static
     * @throws \UnderflowException
     */
    public function setPriority(int $priority): static
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
     * @return static
     */
    public function setTopic(string $topic): static
    {
        $this->topic = $topic;

        return $this;
    }

    /**
     * Set message content.
     *
     * @param  string $content
     * @return static
     */
    public function setContent(string $content): static
    {
        $this->content = $content;

        return $this;
    }

    /**
     * Set message content type
     *
     * @param  string $contentType ('csv', 'json', 'xml', 'text'...)
     * @return static
     */
    public function setContentType(string $contentType): static
    {
        $this->contentType = $contentType;

        return $this;
    }

    /**
     * Set Entity identifier linked to the message.
     *
     * @param  string|null $entityId
     * @return static
     */
    public function setEntityId(?string $entityId): static
    {
        $this->entityId = $entityId;

        return $this;
    }

    /**
     * Set the expiration date of the message.
     *
     * @param  string|null $dateExpiration
     * @return static
     */
    public function setDateExpiration(?string $dateExpiration): static
    {
        $this->dateExpiration = $dateExpiration;

        return $this;
    }

    /**
     * Set availability date of the message.
     *
     * @param  string|null $dateAvailability
     * @return static
     */
    public function setDateAvailability(?string $dateAvailability): static
    {
        $this->dateAvailability = $dateAvailability;

        return $this;
    }

    /**
     * Set the creation date of the message.
     *
     * @param  string $dateCreate
     * @return static
     */
    public function setDateCreate(string $dateCreate): static
    {
        $this->dateCreate = $dateCreate;

        return $this;
    }

    /**
     * Set the update date of the message.
     *
     * @param  string|null $dateUpdate
     * @return static
     */
    public function setDateUpdate(?string $dateUpdate): static
    {
        $this->dateUpdate = $dateUpdate;

        return $this;
    }
}
