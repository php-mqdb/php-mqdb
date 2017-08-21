<?php

/**
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
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get message status.
     *
     * @return int
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Get message priority.
     *
     * @return int
     */
    public function getPriority()
    {
        return $this->priority;
    }

    /**
     * Get message topic.
     *
     * @return string
     */
    public function getTopic()
    {
        return $this->topic;
    }

    /**
     * Get message content.
     *
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * Get message content type ('csv', 'json', 'xml', 'text'...)
     *
     * @return string
     */
    public function getContentType()
    {
        return $this->contentType;
    }

    /**
     * Get Entity identifier linked to the message.
     *
     * @return string
     */
    public function getEntityId()
    {
        return $this->entityId;
    }

    /**
     * Get Entity type linked to the message.
     *
     * @return string
     */
    public function getEntityType()
    {
        return $this->entityType;
    }

    /**
     * Get the expiration date of the message.
     *
     * @return string
     */
    public function getDateExpiration()
    {
        return $this->dateExpiration;
    }

    /**
     * Get availability date of the message.
     *
     * @return string
     */
    public function getDateAvailability()
    {
        return $this->dateAvailability;
    }

    /**
     * Get the creation date of the message.
     *
     * @return string
     */
    public function getDateCreate()
    {
        return $this->dateCreate;
    }

    /**
     * Get the update date of the message.
     *
     * @return string
     */
    public function getDateUpdate()
    {
        return $this->dateUpdate;
    }

    /**
     * Set Message Identifier.
     *
     * @param  string $id
     * @return $this
     */
    public function setId($id)
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
     * @return $this
     * @throws \UnderflowException
     */
    public function setStatus($status)
    {
        $status = (int) $status;

        if ($this->status < 0) {
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
     * @return $this
     * @throws \UnderflowException
     */
    public function setPriority($priority)
    {
        $priority = (int) $priority;

        if ($this->priority < 0) {
            throw new \UnderflowException('Value of "priority" must be greater than 0');
        }

        $this->priority = $priority;

        return $this;
    }

    /**
     * Set message topic.
     *
     * @param  string $topic
     * @return $this
     */
    public function setTopic($topic)
    {
        $this->topic = (string) $topic;

        return $this;
    }

    /**
     * Set message content.
     *
     * @param  string $content
     * @return $this
     */
    public function setContent($content)
    {
        $this->content = (string) $content;

        return $this;
    }

    /**
     * Set message content type
     *
     * @param  string $contentType ('csv', 'json', 'xml', 'text'...)
     * @return $this
     */
    public function setContentType($contentType)
    {
        $this->contentType = (string) $contentType;

        return $this;
    }

    /**
     * Set Entity identifier linked to the message.
     *
     * @param  string $entityId
     * @return $this
     */
    public function setEntityId($entityId)
    {
        $this->entityId = ($entityId === null ? $entityId : (string) $entityId);

        return $this;
    }

    /**
     * Set the expiration date of the message.
     *
     * @param  string $dateExpiration
     * @return $this
     */
    public function setDateExpiration($dateExpiration)
    {
        $this->dateExpiration = ($dateExpiration === null ? $dateExpiration : (string) $dateExpiration);

        return $this;
    }

    /**
     * Set availability date of the message.
     *
     * @param  string $dateAvailability
     * @return $this
     */
    public function setDateAvailability($dateAvailability)
    {
        $this->dateAvailability = ($dateAvailability === null ? $dateAvailability : (string) $dateAvailability);

        return $this;
    }

    /**
     * Set the creation date of the message.
     *
     * @param  string $dateCreate
     * @return $this
     */
    public function setDateCreate($dateCreate)
    {
        $this->dateCreate = (string) $dateCreate;

        return $this;
    }

    /**
     * Set the update date of the message.
     *
     * @param  string $dateUpdate
     * @return $this
     */
    public function setDateUpdate($dateUpdate)
    {
        $this->dateUpdate = ($dateUpdate === null ? $dateUpdate : (string) $dateUpdate);

        return $this;
    }
}
