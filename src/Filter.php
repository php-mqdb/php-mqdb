<?php

/*
 * Copyright Romain Cottard
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace PhpMqdb;

class Filter
{
    private const DATE_FORMAT_SQL = 'Y-m-d H:i:s';
    private int $offset = 0;
    private int $maxLimit = 0;
    private int $limit = 1;
    private string $topic = '';

    /** @var int[] $statuses */
    private array $statuses = [Enumerator\Status::IN_QUEUE];

    /** @var int[] $priorities */
    private array $priorities = [];

    private ?string $entityId = null;

    /** @var string|null $dateExpiration Format: Y-m-d H:i:s */
    private ?string $dateTimeCurrent = null;

    /** @var string|null $dateAvailability Format: Y-m-d H:i:s */
    private ?string $dateTimeAvailability = null;

    /** @var string|null $dateExpiration Format: Y-m-d H:i:s */
    private ?string $dateTimeExpiration = null;

    public function __construct(int $maxLimit = 1000)
    {
        //~ Set current UTC date time (based on current timestamp)
        $this->setDateTimeCurrent(date(self::DATE_FORMAT_SQL, \time()));

        $this->setMaxLimit($maxLimit);
    }

    public function getOffset(): int
    {
        return $this->offset;
    }

    public function getLimit(): int
    {
        return $this->limit;
    }

    /**
     * @return int[]
     */
    public function getPriorities(): array
    {
        return $this->priorities;
    }

    /**
     * @return int[]
     */
    public function getStatuses(): array
    {
        return $this->statuses;
    }

    public function getTopic(): string
    {
        return $this->topic;
    }

    public function getDateTimeCurrent(): ?string
    {
        return $this->dateTimeCurrent;
    }

    public function getDateTimeAvailability(): ?string
    {
        return $this->dateTimeAvailability;
    }

    public function getDateTimeExpiration(): ?string
    {
        return $this->dateTimeExpiration;
    }

    public function getEntityId(): ?string
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
    public function setLimit(int $limit): self
    {
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
    public function setOffset(int $offset): self
    {
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
    public function setStatuses(array $statuses): self
    {
        $this->statuses = [];

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
    public function setPriorities(array $priorities): self
    {
        $this->priorities = [];

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
    public function setTopic(string $topic): self
    {
        if (empty($topic)) {
            throw new \RuntimeException('Topic filter given cannot be empty!');
        }

        if ((bool) \preg_match('`^([a-z0-9_]+\.)*([a-z0-9_]+|\*)$`', $topic) === false) {
            throw new \RuntimeException('Topic filter must contain only alphanums, ".", "_" & "*" characters!');
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
    public function setDateTimeCurrent(string $date): self
    {
        $date = \DateTimeImmutable::createFromFormat(self::DATE_FORMAT_SQL, $date, new \DateTimeZone('UTC'));

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
    public function setDateTimeAvailability(string $date): self
    {
        $date = \DateTimeImmutable::createFromFormat(self::DATE_FORMAT_SQL, $date, new \DateTimeZone('UTC'));

        if (!$date instanceof \DateTimeImmutable) {
            throw new \RuntimeException();
        }

        $this->dateTimeAvailability = $date->format(self::DATE_FORMAT_SQL);

        return $this;
    }

    /**
     * Set expiration date filter.
     *
     * @param  string $date Format: Y-m-d H:i:s
     * @return $this
     * @throws \Exception
     */
    public function setDateTimeExpiration(string $date): self
    {
        $utcTimezone = new \DateTimeZone('UTC');
        $date        = \DateTimeImmutable::createFromFormat(self::DATE_FORMAT_SQL, $date, $utcTimezone);

        if (!$date instanceof \DateTimeImmutable) {
            throw new \RuntimeException();
        }

        $now = new \DateTimeImmutable('now', $utcTimezone);
        if ($date < $now) {
            throw new \UnderflowException('Expiration date time is prior to the current date time!');
        }

        $this->dateTimeExpiration = $date->format(self::DATE_FORMAT_SQL);

        return $this;
    }

    /**
     * Set maximum limit.
     *
     * @param  int $limit
     * @return $this
     */
    private function setMaxLimit(int $limit): self
    {
        if ($limit < 1) {
            throw new \UnderflowException('Max Limit must be greater than 0!');
        }

        $this->maxLimit = $limit;

        return $this;
    }

    /**
     * Set entity id filter.
     *
     * @param  string|null $entityId
     * @return $this
     */
    public function setEntityId(?string $entityId): self
    {
        $this->entityId = $entityId;

        return $this;
    }
}
