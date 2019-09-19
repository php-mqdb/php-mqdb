<?php declare(strict_types=1);

/*
 * Copyright (c) Romain Cottard
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpMqdb\Message;

/**
 * Interface for the message.
 *
 * @author  Romain Cottard
 */
interface MessageInterface
{
    /**
     * Get Message Identifier.
     *
     * @return string
     */
    public function getId(): string;

    /**
     * Get message status.
     *
     * @return int
     */
    public function getStatus(): int;

    /**
     * Get message priority.
     *
     * @return int
     */
    public function getPriority(): int;

    /**
     * Get message topic.
     *
     * @return string
     */
    public function getTopic(): string;

    /**
     * Get message content.
     *
     * @return string
     */
    public function getContent(): string;

    /**
     * Get message content type ('csv', 'json', 'xml', 'text'...)
     *
     * @return string
     */
    public function getContentType(): string;

    /**
     * Get Entity identifier linked to the message.
     *
     * @return string
     */
    public function getEntityId(): ?string;

    /**
     * Get the expiration date of the message.
     *
     * @return string
     */
    public function getDateExpiration(): ?string;

    /**
     * Get availability date of the message.
     *
     * @return string
     */
    public function getDateAvailability(): ?string;

    /**
     * Get the creation date of the message.
     *
     * @return string
     */
    public function getDateCreate(): string;

    /**
     * Get the update date of the message.
     *
     * @return string
     */
    public function getDateUpdate(): ?string;

    /**
     * Set Message Identifier.
     *
     * @param  string $id
     * @return $this
     */
    public function setId(string $id);

    /**
     * Set message status.
     * Status list:
     *  0: In queue (Waiting to be consumed)
     *  1: pending ack
     *  2: pending ack (no response after a delay, can be deleted) ?
     *  3: ack received (treated, cannot be retreated)
     *
     * @param  int $status
     * @return $this
     * @throws \UnderflowException
     */
    public function setStatus(int $status);

    /**
     * Set message priority.
     * Priority value list:
     *  1: Very High
     *  2: High
     *  3: Medium
     *  4: Low
     *  5: Very Low
     *
     * @param  int $priority
     * @return $this
     * @throws \UnderflowException
     */
    public function setPriority(int $priority);

    /**
     * Set message topic.
     *
     * @param  string $topic
     * @return $this
     */
    public function setTopic(string $topic);

    /**
     * Set message content.
     *
     * @param  string $content
     * @return $this
     */
    public function setContent(string $content);

    /**
     * Set message content type
     *
     * @param  string $contentType ('csv', 'json', 'xml', 'text'...)
     * @return $this
     */
    public function setContentType(string $contentType);

    /**
     * Set Entity identifier linked to the message.
     *
     * @param  string|null $entityId
     * @return $this
     */
    public function setEntityId(?string $entityId);

    /**
     * Set the expiration date of the message.
     *
     * @param  string|null $dateExpiration
     * @return $this
     */
    public function setDateExpiration(?string $dateExpiration);

    /**
     * Set availability date of the message.
     *
     * @param  string|null $dateAvailability
     * @return $this
     */
    public function setDateAvailability(?string $dateAvailability);

    /**
     * Set the creation date of the message.
     *
     * @param  string $dateCreate
     * @return $this
     */
    public function setDateCreate(string $dateCreate);

    /**
     * Set the update date of the message.
     *
     * @param  string|null $dateUpdate
     * @return $this
     */
    public function setDateUpdate(?string $dateUpdate);
}
