<?php declare(strict_types=1);

/*
 * Copyright (c) Romain Cottard
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpMqdb\Query;

use PhpMqdb\Config\TableConfig;
use PhpMqdb\Enumerator;
use PhpMqdb\Exception\EmptySetValuesException;
use PhpMqdb\Exception\PhpMqdbConfigurationException;
use PhpMqdb\Filter;
use PhpMqdb\Message;
use PhpMqdb\Repository\MessageRepositoryInterface;

/**
 * Class QueryBuilder
 *
 * @author Romain Cottard
 */
class QueryBuilder
{
    /** @var TableConfig $tableConfig */
    private $tableConfig;

    /** @var string $query */
    protected $query = '';

    /** @var mixed[] $bind */
    protected $bind = [];

    /** @var string[] $where */
    private $where = [];

    /**
     * QueryBuilder constructor.
     *
     * @param TableConfig $tableConfig
     */
    public function __construct(TableConfig $tableConfig)
    {
        $this->tableConfig = $tableConfig;
    }

    /**
     * @return string
     */
    public function getQuery(): string
    {
        return $this->query;
    }

    /**
     * @return array
     */
    public function getBind(): array
    {
        return $this->bind;
    }

    /**
     * @param Message\MessageInterface $message
     * @param bool $isNew
     * @param bool $allowStatusUpdate
     * @return QueryBuilder
     * @throws EmptySetValuesException
     * @throws PhpMqdbConfigurationException
     */
    public function buildQueryPublish(Message\MessageInterface $message, bool $isNew, bool $allowStatusUpdate): self
    {
        if ($isNew) {
            $this->query = 'INSERT INTO ' . $this->tableConfig->getTable() . ' ' . $this->buildQuerySet($message);

            return $this;
        }

        $notToUpdateFields = ['id', 'date_create'];

        if (!$allowStatusUpdate) {
            $notToUpdateFields[] = 'status';
            $notToUpdateFields[] = 'pending_id';
        }

        $this->query =
            'UPDATE ' . $this->tableConfig->getTable() . ' ' .
            $this->buildQuerySet($message, $notToUpdateFields) .
            ' WHERE ' . $this->tableConfig->getField('id') . ' = :existing_id';

        $this->bind[':existing_id'] = $message->getId();

        return $this;
    }

    /**
     * @param Filter $filter
     * @param string|null $pendingId
     * @return self
     * @throws PhpMqdbConfigurationException
     */
    public function buildQueryGet(Filter $filter, string $pendingId = null): self
    {
        if (empty($pendingId)) {
            $fields = $this->tableConfig->getField('id');
        } else {
            $fields = implode(', ', array_values($this->tableConfig->getFields()));
        }

        $order = $this->tableConfig->getOrders();
        $table = $this->tableConfig->getTable();

        $this->query =
            'SELECT ' . $fields  .
            '  FROM `' . $table . '` ' .
            $this->buildWhere($filter, $pendingId) .
            $this->buildOrder($order, $filter) .
            $this->buildLimit($filter)
        ;

        return $this;
    }

    /**
     * @param int $deleteBitmask
     * @param string $date
     * @return QueryBuilder
     * @throws \Exception
     */
    public function buildQueryClean(int $deleteBitmask, string $date): self
    {
        $status = [];

        if (($deleteBitmask & MessageRepositoryInterface::DELETE_ACK_RECEIVED) === MessageRepositoryInterface::DELETE_ACK_RECEIVED) {
            $status[':status_ack'] = Enumerator\Status::ACK_RECEIVED;
        }

        if (($deleteBitmask & MessageRepositoryInterface::DELETE_NACK_RECEIVED) === MessageRepositoryInterface::DELETE_NACK_RECEIVED) {
            $status[':status_nack'] = Enumerator\Status::NACK_RECEIVED;
        }

        if (($deleteBitmask & MessageRepositoryInterface::DELETE_ACK_NOT_RECEIVED) === MessageRepositoryInterface::DELETE_ACK_NOT_RECEIVED) {
            $status[':status_ackn'] = Enumerator\Status::ACK_NOT_RECEIVED;
        }

        if (($deleteBitmask & MessageRepositoryInterface::DELETE_ACK_PENDING) === MessageRepositoryInterface::DELETE_ACK_PENDING) {
            $status[':status_ackp'] = Enumerator\Status::ACK_PENDING;
        }

        $this->query =
            'DELETE FROM ' . $this->tableConfig->getTable() .
            ' WHERE ' . $this->tableConfig->getField('status') . ' IN ( ' .
            implode(', ', array_keys($status)) . ') AND ' .
            $this->tableConfig->getField('date_update') . ' <= :date_update AND ' .
            $this->tableConfig->getField('date_update') . ' IS NOT NULL';

        $this->bind                 = $status;
        $this->bind[':date_update'] = $date;

        return $this;
    }

    /**
     * @param string $date
     * @return QueryBuilder
     * @throws \Exception
     */
    public function buildQueryCleanPending(string $date): self
    {
        $this->query =
            'UPDATE ' . $this->tableConfig->getTable() .
            ' SET ' . $this->tableConfig->getField('status') . ' = :new_message_status WHERE ' .
            $this->tableConfig->getField('status') . ' = :message_status AND ' .
            $this->tableConfig->getField('date_update') . ' <= :date_update AND ' .
            $this->tableConfig->getField('date_update') . ' IS NOT NULL';

        $this->bind[':new_message_status'] = Enumerator\Status::ACK_NOT_RECEIVED;
        $this->bind[':message_status']     = Enumerator\Status::ACK_PENDING;
        $this->bind[':date_update']        =$date;

        return $this;
    }

    /**
     * @param Filter $filter
     * @return self
     * @throws PhpMqdbConfigurationException
     */
    public function buildQueryCount(Filter $filter): self
    {
        $field = $this->tableConfig->getField('id');
        $table = $this->tableConfig->getTable();

        $this->query = 'SELECT COUNT(`' . $field . '`) FROM `' . $table . '` ' . $this->buildWhere($filter);

        return $this;
    }

    /**
     * Build query to update message(s) before to get it.
     *
     * @param Filter $filter
     * @param string|null $pendingId
     * @return self
     * @throws PhpMqdbConfigurationException
     */
    public function buildQueryProtect(Filter $filter, string $pendingId = null): self
    {
        $order = $this->tableConfig->getOrders();
        $table = $this->tableConfig->getTable();

        $this->query =
            'UPDATE `' . $table . '` SET ' .
            $this->tableConfig->getField('status') . ' = :new_status, ' .
            $this->tableConfig->getField('date_update') . ' = :new_date_update, ' .
            $this->tableConfig->getField('pending_id') . ' = :new_pending_id ' .
            $this->buildWhere($filter) .
            $this->buildOrder($order, $filter) .
            $this->buildLimit($filter)
        ;

        $this->bind[':new_status']     = Enumerator\Status::ACK_PENDING;
        $this->bind[':new_date_update'] = (new \DateTimeImmutable('now', new \DateTimeZone('UTC')))->format(
            'Y-m-d H:i:s'
        );
        $this->bind[':new_pending_id'] = $pendingId;

        return $this;
    }

    /**
     * Build query to update message(s) before to get it.
     *
     * @param string $id Message id
     * @param int $status Message status
     * @return self
     * @throws \Exception
     */
    public function buildQueryUpdate(string $id, int $status): self
    {
        $table = $this->tableConfig->getTable();
        $this->query =
            'UPDATE `' . $table . '` SET ' .
            $this->tableConfig->getField('status') . ' = :new_status, ' .
            $this->tableConfig->getField('date_update') . ' = :new_date_update, ' .
            $this->tableConfig->getField('pending_id') . ' = :new_pending_id
             WHERE ' . $this->tableConfig->getField('id') . ' = :id'
        ;

        $this->bind[':new_status']      = (int) $status;
        $this->bind[':new_date_update'] = (new \DateTimeImmutable('now', new \DateTimeZone('UTC')))->format(
            'Y-m-d H:i:s'
        );
        $this->bind[':new_pending_id']  = null;
        $this->bind[':id']              = $id;

        return $this;
    }

    /**
     * @param string $field
     * @param $value
     * @param string $sign
     * @param bool $orNull
     * @return QueryBuilder
     * @throws PhpMqdbConfigurationException
     */
    private function addWhere(string $field, $value, string $sign = '=', bool $orNull = false): self
    {
        $field = $this->tableConfig->getField($field);
        if ($value === null) {
            $this->where[] = "`${field}` IS NULL";
        } else {
            $this->where[]            = ($orNull ? '(' : '') . "`${field}` $sign :${field}" . ($orNull ? " OR `${field}` IS NULL)" : '');
            $this->bind[':' . $field] = $value;
        }

        return $this;
    }

    /**
     * @param string $field
     * @param array $values
     * @return QueryBuilder
     * @throws PhpMqdbConfigurationException
     */
    private function addWhereIn(string $field, array $values): QueryBuilder
    {
        if (empty($values)) {
            return $this;
        }

        if (!is_array($values)) {
            throw new \InvalidArgumentException('Values must be an array of value to filter.');
        }

        //~ When have only one value, use normal addWhere method
        if (count($values) === 1) {
            return $this->addWhere($field, current($values));
        }

        $field = $this->tableConfig->getField($field);
        $where = [];
        foreach ($values as $index => $value) {
            $name = ':' . $field . '_' . $index;

            $this->bind[$name] = $value;
            $where[]           = $name;
        }

        $this->where[] = "`${field}` IN (" . implode(', ', $where) . ")";

        return $this;
    }

    /**
     * Build query where.
     *
     * @param Message\MessageInterface $message
     * @param string[] $excludeFields
     * @return string
     * @throws EmptySetValuesException
     * @throws PhpMqdbConfigurationException
     */
    private function buildQuerySet(Message\MessageInterface $message, array $excludeFields = []): string
    {
        $methods = [
            'id'                => $message->getId(),
            'status'            => $message->getStatus(),
            'priority'          => $message->getPriority(),
            'topic'             => $message->getTopic(),
            'content'           => $message->getContent(),
            'content_type'      => $message->getContentType(),
            'date_create'       => $message->getDateCreate(),
            'date_update'       => $message->getDateUpdate(),
        ];

        //~ Handle optional fields
        if ($this->tableConfig->hasField('entity_id')) {
            $methods['entity_id'] = $message->getEntityId();
        }

        if ($this->tableConfig->hasField('date_expiration')) {
            $methods['date_expiration'] = $message->getDateExpiration();
        }

        if ($this->tableConfig->hasField('date_availability')) {
            $methods['date_availability'] = $message->getDateAvailability();
        }

        $set = [];
        foreach ($methods as $name => $value) {
            if (in_array($name, $excludeFields)) {
                continue;
            }

            if (empty($value) && $value !== 0) { // int(0) should not be seen as empty
                continue;
            }

            $field                    = $this->tableConfig->getField($name);
            $set[]                    = "${field} = :${field}";
            $this->bind[':' . $field] = $value;
        }

        if (!in_array('pending_id', $excludeFields)) { // Reset pending_id if not excluded
            $set[] = $this->tableConfig->getField('pending_id') . ' = NULL';
        }

        if (empty($set)) {
            throw new EmptySetValuesException('Cannot build SET query part! (no value to set)');
        }

        return 'SET ' . implode(', ', $set);
    }

    /**
     * Build query where.
     *
     * @param Filter $filter
     * @param string|null $pendingId
     * @return string
     * @throws PhpMqdbConfigurationException
     */
    private function buildWhere(Filter $filter, string $pendingId = null): string
    {
        //~ If pending id is defined, select on pending id.
        if (!empty($pendingId)) {
            $this->addWhere('pending_id', $pendingId);

            return ' WHERE ' . implode(' AND ', $this->where);
        }

        $this->addWhere('pending_id', null);

        if (!empty($filter->getTopic())) {
            $topic = strtr($filter->getTopic(), '*', '%');
            $sign  = (strpos($topic, '%') !== false ? 'LIKE' : '=');
            $this->addWhere('topic', $topic, $sign);
        }

        $this->addWhereIn('status', $filter->getStatuses());
        $this->addWhereIn('priority', $filter->getPriorities());

        if ($this->tableConfig->hasField('date_availability')) {
            //~ If have no date time availability set as filter, use current datetime.
            if (!empty($filter->getDateTimeAvailability())) {
                $this->addWhere('date_availability', $filter->getDateTimeAvailability(), '<=', true);
            } else {
                $this->addWhere('date_availability', $filter->getDateTimeCurrent(), '<=', true);
            }
        }

        if ($this->tableConfig->hasField('date_expiration')) {
            //~ If have no date time expiration set as filter, use current datetime.
            if (!empty($filter->getDateTimeExpiration())) {
                $this->addWhere('date_expiration', $filter->getDateTimeExpiration(), '>', true);
            } else {
                $this->addWhere('date_expiration', $filter->getDateTimeCurrent(), '>', true);
            }
        }

        if ($filter->getEntityId() !== null) { // Not empty as we may want to filter on entityId === ''
            $this->addWhere('entity_id', $filter->getEntityId());
        }

        return ' WHERE ' . implode(' AND ', $this->where);
    }

    /**
     * Build query limit.
     *
     * @param Filter $filter
     * @return string
     */
    private function buildLimit(Filter $filter): string
    {
        return ' LIMIT ' . $filter->getLimit();
    }

    /**
     * Build query order.
     *
     * @param array $order
     * @param Filter|null $filter
     * @return string
     * @throws PhpMqdbConfigurationException
     */
    private function buildOrder(array $order, Filter $filter = null): string
    {
        $orderBy = [];

        foreach ($order as $field => $direction) {

            //~ Skip ordering by priority when have an unique priority
            if ($field === 'priority' && count($filter->getPriorities()) === 1) {
                continue;
            }

            //~ Skip ordering by priority when have an unique priority
            if ($field === 'status' && count($filter->getStatuses()) === 1) {
                continue;
            }

            $field = $this->tableConfig->getField($field);
            $orderBy[] = $field . ' ' . $direction;
        }

        if (empty($orderBy)) {
            return '';
        }

        return ' ORDER BY ' . implode(', ', $orderBy) . ' ';
    }
}
