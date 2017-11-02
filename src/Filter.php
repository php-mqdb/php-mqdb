<?php

/**
 * Copyright Romain Cottard
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpMqdb;

use PhpMqdb\Enumerator;

/**
 * Class Filter.
 * Define filter to get corresponding message.
 *
 * @author Romain Cottard
 */
class Filter
{
    /** @var int $offset */
    private $offset = 0;

    /** @var int $maxLimit */
    private $maxLimit = 0;

    /** @var int $limit */
    private $limit = 1;

    /** @var int[] $statuses */
    private $statuses = [Enumerator\Status::IN_QUEUE];

    /** @var int[] $priorities */
    private $priorities = [];

    /** @var int $topic */
    private $topic = '';

    /** @var string|null $entityId */
    private $entityId = null;

    /** @var string|null $dateExpiration Format: Y-m-d H:i:s */
    private $dateTimeCurrent = null;

    /** @var string $dateAvailability Format: Y-m-d H:i:s */
    private $dateTimeAvailability = null;

    /** @var string|null $dateExpiration Format: Y-m-d H:i:s */
    private $dateTimeExpiration = null;

    /**
     * Filter constructor.
     *
     * @param  int $maxLimit
     */
    public function __construct($maxLimit = 1000)
    {
        //~ Set current UTC date time (based on current timestamp)
        $this->setDateTimeCurrent((string) date('Y-m-d H:i:s', time()));

        $this->setMaxLimit($maxLimit);
    }

    /**
     * Get offset filter.
     *
     * @return int
     */
    public function getOffset()
    {
        return $this->offset;
    }

    /**
     * Get limit filter.
     *
     * @return int
     */
    public function getLimit()
    {
        return $this->limit;
    }

    /**
     * Get priorities filter.
     *
     * @return int[]
     */
    public function getPriorities()
    {
        return $this->priorities;
    }

    /**
     * Get statuses filter.
     *
     * @return int[]
     */
    public function getStatuses()
    {
        return $this->statuses;
    }

    /**
     * Get topic filter.
     *
     * @return string
     */
    public function getTopic()
    {
        return $this->topic;
    }

    /**
     * Return current date time.
     *
     * @return string
     */
    public function getDateTimeCurrent()
    {
        return $this->dateTimeCurrent;
    }

    /**
     * Return date of availability.
     *
     * @return string
     */
    public function getDateTimeAvailability()
    {
        return $this->dateTimeAvailability;
    }

    /**
     * Return date of expiration.
     *
     * @return string
     */
    public function getDateTimeExpiration()
    {
        return $this->dateTimeExpiration;
    }

    /**
     * Return entity id.
     *
     * @return string
     */
    public function getEntityId()
    {
        return $this->entityId;
    }

    /**
     * Set limit filter
     *
     * @param  int $limit
     * @return $this
     * @throws \UnderflowException
     * @throws \OverflowException
     */
    public function setLimit($limit)
    {
        $limit = (int) $limit;

        if ($limit < 1) {
            throw new \UnderflowException('Limit must be greater than 0!');
        }

        if ($limit > $this->maxLimit) {
            throw new \OverflowException('Limit cannot be greater than max limit defined (actual max limit: ' . $this->maxLimit . ')');
        }

        $this->limit = $limit;

        return $this;
    }

    /**
     * Set offset filter
     *
     * @param  int $offset
     * @return $this
     * @throws \UnderflowException
     */
    public function setOffset($offset)
    {
        $offset = (int) $offset;

        if ($offset < 0) {
            throw new \UnderflowException('Offset must be equals or greater than 0!');
        }

        $this->offset = $offset;

        return $this;
    }

    /**
     * Set statuses filter
     *
     * @param  int[] $statuses
     * @return $this
     * @throws \UnderflowException
     * @throws \OverflowException
     */
    public function setStatus(array $statuses)
    {
        foreach ($statuses as $status) {
            $status = (int) $status;

            if ($status < Enumerator\Status::IN_QUEUE) {
                throw new \UnderflowException('The lowest status allowed is Status::IN_QUEUE (value: ' . Enumerator\Status::IN_QUEUE . ')!');
            }

            if ($status > Enumerator\Status::ACK_NOT_RECEIVED) {
                throw new \OverflowException('The greatest status allowed is Status::ACK_RECEIVED (value: ' . Enumerator\Status::ACK_RECEIVED . ')!');
            }

            $this->statuses[] = $status;
        }

        return $this;
    }

    /**
     * Set priorities filter
     *
     * @param  int[] $priorities
     * @return $this
     * @throws \UnderflowException
     * @throws \OverflowException
     */
    public function setPriorities(array $priorities)
    {
        foreach ($priorities as $priority) {
            $priority = (int) $priority;

            if ($priority < Enumerator\Priority::VERY_HIGH) {
                throw new \UnderflowException('The highest priority allowed is Priority::VERY_HIGH (value: ' . Enumerator\Priority::VERY_HIGH . ')!');
            }

            if ($priority > Enumerator\Priority::VERY_LOW) {
                throw new \OverflowException('The lowest status allowed is Priority::VERY_LOW (value: ' . Enumerator\Priority::VERY_LOW . ')!');
            }

            $this->priorities[] = $priority;
        }

        return $this;
    }

    /**
     * Set topic filter.
     *
     * @param  string $topic
     * @return $this
     * @throws \RuntimeException
     */
    public function setTopic($topic)
    {
        $topic = (string) $topic;

        if (empty($topic)) {
            throw new \RuntimeException('Topic filter given cannot be empty!');
        }

        if (!(bool) preg_match('`^([a-z0-9]+\.)*([a-z0-9]+|\*{1})$`', $topic)) {
            throw new \RuntimeException('Topic filter must contain only alphanums, "." & "*" characters!');
        }

        $this->topic = $topic;

        return $this;
    }

    /**
     * Set current date time.
     *
     * @param  string $date Format: Y-m-d H:i:s
     * @return $this
     */
    public function setDateTimeCurrent($date)
    {
        $date = \DateTimeImmutable::createFromFormat('Y-m-d H:i:s', (string) $date, new \DateTimeZone('UTC'));

        if (!$date instanceof \DateTimeImmutable) {
            throw new \RuntimeException();
        }

        $this->dateTimeCurrent = $date->format('Y-m-d H:i:s');

        return $this;
    }

    /**
     * Set availability date filter.
     *
     * @param  string $date Format: Y-m-d H:i:s
     * @return $this
     */
    public function setDateTimeAvailability($date)
    {
        $date = \DateTimeImmutable::createFromFormat('Y-m-d H:i:s', (string) $date, new \DateTimeZone('UTC'));

        if (!$date instanceof \DateTimeImmutable) {
            throw new \RuntimeException();
        }

        $this->dateTimeAvailability = $date->format('Y-m-d H:i:s');

        return $this;
    }

    /**
     * Set expiration date filter.
     *
     * @param  string $date Format: Y-m-d H:i:s
     * @return $this
     */
    public function setDateTimeExpiration($date)
    {
        $utcTimezone = new \DateTimeZone('UTC');
        $date        = \DateTimeImmutable::createFromFormat('Y-m-d H:i:s', (string) $date, $utcTimezone);

        if (!$date instanceof \DateTimeImmutable) {
            throw new \RuntimeException();
        }

        $now = new \DateTimeImmutable('now', $utcTimezone);
        if ($date < $now) {
            throw new \UnderflowException('Expiration date time is prior to the current date time!');
        }

        $this->dateTimeExpiration = $date->format('Y-m-d H:i:s');

        return $this;
    }

    /**
     * Set maximum limit.
     *
     * @param  int $limit
     * @return $this
     */
    private function setMaxLimit($limit)
    {
        $limit = (int) $limit;

        if ($limit < 1) {
            throw new \UnderflowException('Max Limit must be greater than 0!');
        }

        $this->maxLimit = $limit;

        return $this;
    }

    /**
     * Set entity id filter.
     *
     * @param  string $entityId
     * @return $this
     */
    public function setEntityId($entityId)
    {
        $this->entityId = $entityId;

        return $this;
    }
}
