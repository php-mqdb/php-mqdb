<?php

/**
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
    public function getId();

    /**
     * Get message status.
     *
     * @return int
     */
    public function getStatus();

    /**
     * Get message priority.
     *
     * @return int
     */
    public function getPriority();

    /**
     * Get message topic.
     *
     * @return string
     */
    public function getTopic();

    /**
     * Get message content.
     *
     * @return string
     */
    public function getContent();

    /**
     * Get message content type ('csv', 'json', 'xml', 'text'...)
     *
     * @return string
     */
    public function getContentType();

    /**
     * Get Entity identifier linked to the message.
     *
     * @return string
     */
    public function getEntityId();

    /**
     * Get the expiration date of the message.
     *
     * @return string
     */
    public function getDateExpiration();

    /**
     * Get availability date of the message.
     *
     * @return string
     */
    public function getDateAvailability();

    /**
     * Get the creation date of the message.
     *
     * @return string
     */
    public function getDateCreate();

    /**
     * Get the update date of the message.
     *
     * @return string
     */
    public function getDateUpdate();

    /**
     * Set Message Identifier.
     *
     * @param  string $id
     * @return $this
     */
    public function setId($id);

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
    public function setStatus($status);

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
    public function setPriority($priority);

    /**
     * Set message topic.
     *
     * @param  string $topic
     * @return $this
     */
    public function setTopic($topic);

    /**
     * Set message content.
     *
     * @param  string $content
     * @return $this
     */
    public function setContent($content);

    /**
     * Set message content type
     *
     * @param  string $contentType ('csv', 'json', 'xml', 'text'...)
     * @return $this
     */
    public function setContentType($contentType);

    /**
     * Set Entity identifier linked to the message.
     *
     * @param  string $entityId
     * @return $this
     */
    public function setEntityId($entityId);

    /**
     * Set the expiration date of the message.
     *
     * @param  string $dateExpiration
     * @return $this
     */
    public function setDateExpiration($dateExpiration);

    /**
     * Set availability date of the message.
     *
     * @param  string $dateAvailability
     * @return $this
     */
    public function setDateAvailability($dateAvailability);

    /**
     * Set the creation date of the message.
     *
     * @param  string $dateCreate
     * @return $this
     */
    public function setDateCreate($dateCreate);

    /**
     * Set the update date of the message.
     *
     * @param  string $dateUpdate
     * @return $this
     */
    public function setDateUpdate($dateUpdate);
}
