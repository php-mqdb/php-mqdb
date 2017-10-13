<?php

/**
 * Copyright Romain Cottard
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpMqdb\Repository;

use PhpMqdb\Exception\EmptySetValuesException;
use PhpMqdb\Filter;
use PhpMqdb\Message;
use PhpMqdb\Enumerator;

/**
 * Interface for Message Repository
 *
 * @author Romain Cottard
 */
abstract class AbstractDatabaseMessageRepository implements MessageRepositoryInterface
{
    /** @var array $bind */
    protected $bind = [];

    /** @var string $classFactory */
    private $classFactory = Message\MessageFactory::class;

    /** @var array $where */
    private $where = [];

    /** @var string $table */
    private static $table = 'message_queue';

    /** @var array $fields */
    private static $fields = [
        'id'                => 'message_id',
        'status'            => 'message_status',
        'priority'          => 'message_priority',
        'topic'             => 'message_topic',
        'content'           => 'message_content',
        'content_type'      => 'message_content_type',
        'entity_id'         => 'message_entity_id',
        'date_expiration'   => 'message_date_expiration',
        'date_availability' => 'message_date_availability',
        'pending_id'        => 'message_pending_id',
        'date_create'       => 'message_date_create',
        'date_update'       => 'message_date_update',
    ];

    /**
     * {@inheritdoc}
     */
    public function ack($id)
    {
        $this->executeQuery($this->buildQueryUpdate($id, Enumerator\Status::ACK_RECEIVED));

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function nack($id, $requeue = true)
    {
        $status = Enumerator\Status::NACK_RECEIVED;

        if ($requeue) {
            $status = Enumerator\Status::IN_QUEUE;
        }

        $this->executeQuery($this->buildQueryUpdate($id, $status));

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getMessage(Filter $filter)
    {
        try {
            //~ override filter limit to 1
            $filter->setLimit(1);

            $messages = $this->getMessages($filter);

            return array_pop($messages);
        } finally {
            $this->cleanQuery();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getMessages(Filter $filter)
    {
        $messages = [];

        $query = $this->buildQueryGet($filter, $this->protectMessages($filter));

        $stmt = $this->executeQuery($query);

        while (null != ($row = $stmt->fetch(\PDO::FETCH_OBJ))) {

            $message = call_user_func_array([$this->classFactory, 'create'], [$row->{self::$fields['content_type']}]);
            $message->setId($row->{self::$fields['id']})
                ->setStatus($row->{self::$fields['status']})
                ->setPriority($row->{self::$fields['priority']})
                ->setTopic($row->{self::$fields['topic']})
                ->setContent($row->{self::$fields['content']})
                ->setContentType($row->{self::$fields['content_type']})
                ->setEntityId($row->{self::$fields['entity_id']})
                ->setDateExpiration($row->{self::$fields['date_expiration']})
                ->setDateAvailability($row->{self::$fields['date_availability']})
                ->setDateCreate($row->{self::$fields['date_create']})
                ->setDateUpdate($row->{self::$fields['date_update']});

            $messages[] = $message;
        }

        return $messages;
    }

    /**
     * {@inheritdoc}
     */
    public function countMessages(Filter $filter)
    {
        $query = $this->buildQueryCount($filter);

        $stmt = $this->executeQuery($query);

        $count = $stmt->fetchColumn();

        return $count;
    }

    /**
     * {@inheritdoc}
     */
    public function publishMessage(Message\MessageInterface $message)
    {
        if (empty($message->getId())) {
            $message->setId($this->generateId(4));
            $query = 'INSERT INTO ' . self::$table . ' ' . $this->buildQuerySet($message);
        } else {
            $query = 'UPDATE ' . self::$table . ' ' . $this->buildQuerySet($message, [
                    'id',
                    'date_create',
                    'status',
                ]) . ' WHERE ' . self::$fields['id'] . ' = :existing_id';

            $this->bind[':existing_id'] = $message->getId();
        }
        $this->executeQuery($query);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function cleanMessages(\DateInterval $interval, $deleteBitmask = self::DELETE_SAFE)
    {
        $status = [];

        if (($deleteBitmask & self::DELETE_ACK_RECEIVED) === self::DELETE_ACK_RECEIVED) {
            $status[':status_ack'] = Enumerator\Status::ACK_RECEIVED;
        }

        if (($deleteBitmask & self::DELETE_NACK_RECEIVED) === self::DELETE_NACK_RECEIVED) {
            $status[':status_nack'] = Enumerator\Status::NACK_RECEIVED;
        }

        if (($deleteBitmask & self::DELETE_ACK_NOT_RECEIVED) === self::DELETE_ACK_NOT_RECEIVED) {
            $status[':status_ackn'] = Enumerator\Status::ACK_NOT_RECEIVED;
        }

        if (($deleteBitmask & self::DELETE_ACK_PENDING) === self::DELETE_ACK_PENDING) {
            $status[':status_ackp'] = Enumerator\Status::ACK_PENDING;
        }

        $date = new \DateTimeImmutable('now', new \DateTimeZone('UTC'));
        $date = $date->sub($interval);

        $query = 'DELETE FROM ' . self::$table . ' WHERE ' . self::$fields['status'] . ' IN ( ' . implode(', ', array_keys($status)) . ') AND ' . self::$fields['date_update'] . ' <= :date_update AND ' . self::$fields['date_update'] . ' IS NOT NULL';

        $this->bind                 = $status;
        $this->bind[':date_update'] = $date->format('Y-m-d H:i:s');

        $this->executeQuery($query);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function cleanPendingMessages(\DateInterval $interval)
    {
        $date = new \DateTimeImmutable('now', new \DateTimeZone('UTC'));
        $date = $date->sub($interval);

        $query = 'UPDATE ' . self::$table . ' SET ' . self::$fields['status'] . ' = :new_message_status WHERE ' . self::$fields['status'] . ' = :message_status AND ' . self::$fields['date_update'] . ' <= :date_update AND ' . self::$fields['date_update'] . ' IS NOT NULL';

        $this->bind[':new_message_status'] = Enumerator\Status::ACK_NOT_RECEIVED;
        $this->bind[':message_status']     = Enumerator\Status::ACK_PENDING;
        $this->bind[':date_update']        = $date->format('Y-m-d H:i:s');

        $this->executeQuery($query);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setFields(array $fields)
    {
        foreach ($fields as $key => $field) {
            if (!isset(self::$fields[$key])) {
                continue;
            }

            if ((bool) preg_match('`[^a-zA-Z0-9_-]`', $field)) {
                throw new \LogicException('Invalid field name!');
            }

            self::$fields[$key] = $field;
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setTable($table)
    {
        if (empty($table)) {
            throw new EmptySetValuesException('Empty table name');
        }

        if ((bool) preg_match('`[^a-zA-Z0-9_-]`', $table)) {
            throw new \LogicException('Invalid table name!');
        }

        self::$table = $table;

        return $this;
    }

    /**
     * Set message factory class.
     *
     * @param  string $classFactory
     * @return $this
     * @throws \LogicException
     */
    protected function setClassMessageFactory($classFactory)
    {
        if (!class_exists($classFactory)) {
            throw new \LogicException('Factory class does not exist!');
        }

        if (!method_exists($classFactory, 'create')) {
            throw new \LogicException('Factory must have a "create()" method');
        }

        $this->classFactory = $classFactory;

        return $this;
    }

    /**
     * Protect message from double consuming in parallels scripts.
     *
     * @param  Filter $filter
     * @return string
     */
    private function protectMessages(Filter $filter)
    {
        $pendingId = $this->generateId(1);

        $this->executeQuery($this->buildQueryProtect($filter, $pendingId));

        return $pendingId;
    }

    /**
     * Build query to get a message.
     *
     * @param  Filter $filter
     * @param  string|null $pendingId
     * @return string
     */
    private function buildQueryGet(Filter $filter, $pendingId = null)
    {
        $fields = empty($pendingId) ? self::$fields['id'] : implode(', ', self::$fields);

        $query = 'SELECT ' . $fields . ' FROM ' . self::$table . ' ' . $this->buildWhere($filter, $pendingId) . ' ORDER BY ' . self::$fields['date_create'] . ' ASC ' . $this->buildLimit($filter);

        return $query;
    }

    /**
     * @param Filter $filter
     * @return string
     */
    private function buildQueryCount(Filter $filter)
    {
        $query = 'SELECT COUNT(' . self::$fields['id'] . ') FROM ' . self::$table . ' ' . $this->buildWhere($filter);

        return $query;
    }

    /**
     * Build query to update message(s) before to get it.
     *
     * @param  Filter $filter
     * @param  string $pendingId
     * @return string
     */
    private function buildQueryProtect(Filter $filter, $pendingId = null)
    {
        $query = 'UPDATE ' . self::$table . ' SET ' . self::$fields['pending_id'] . ' = :new_pending_id, ' . self::$fields['status'] . ' = :new_status ' . $this->buildWhere($filter) . ' ORDER BY ' . self::$fields['date_create'] . ' ASC ' . $this->buildLimit($filter);

        $this->bind[':new_status']     = Enumerator\Status::ACK_PENDING;
        $this->bind[':new_pending_id'] = $pendingId;

        return $query;
    }

    /**
     * Build query to update message(s) before to get it.
     *
     * @param  string $id Message id
     * @param  int $status Message status
     * @return string
     */
    private function buildQueryUpdate($id, $status)
    {
        $query = 'UPDATE ' . self::$table . ' SET ' . self::$fields['status'] . ' = :new_status, ' . self::$fields['date_update'] . ' = :new_date_update, ' . self::$fields['pending_id'] . ' = :new_pending_id' . ' WHERE ' . self::$fields['id'] . ' = :id';

        $this->bind[':new_status']      = (int) $status;
        $this->bind[':new_date_update'] = (new \DateTimeImmutable('now', new \DateTimeZone('UTC')))->format('Y-m-d H:i:s');
        $this->bind[':new_pending_id']  = null;
        $this->bind[':id']              = $id;

        return $query;
    }

    /**
     * Build query where.
     *
     * @param  Message\MessageInterface $message
     * @param  string[] $excludeFields
     * @return string
     * @throws \Exception
     */
    private function buildQuerySet(Message\MessageInterface $message, $excludeFields = [])
    {
        $methods = [
            'id'                => $message->getId(),
            'status'            => $message->getStatus(),
            'priority'          => $message->getPriority(),
            'topic'             => $message->getTopic(),
            'content'           => $message->getContent(),
            'content_type'      => $message->getContentType(),
            'entity_id'         => $message->getEntityId(),
            'date_expiration'   => $message->getDateExpiration(),
            'date_availability' => $message->getDateAvailability(),
            'date_create'       => $message->getDateCreate(),
            'date_update'       => $message->getDateUpdate(),
        ];

        $set = [];
        foreach ($methods as $name => $value) {
            if (in_array($name, $excludeFields)) {
                continue;
            }

            if (empty($value)) {
                continue;
            }

            $field                    = self::$fields[$name];
            $set[]                    = "${field} = :${field}";
            $this->bind[':' . $field] = $value;
        }

        if (empty($set)) {
            throw new EmptySetValuesException('Cannot build SET query part! (no value to set)');
        }

        return 'SET ' . implode(', ', $set);
    }

    /**
     * Build query where.
     *
     * @param  Filter $filter
     * @param  string $pendingId
     * @return string
     */
    private function buildWhere(Filter $filter, $pendingId = null)
    {
        //~ If pending id is defined, select on pending id.
        if (!empty($pendingId)) {
            $this->addWhere(self::$fields['pending_id'], $pendingId);

            return 'WHERE ' . implode(' AND ', $this->where);
        }

        $this->addWhere(self::$fields['status'], $filter->getStatus());
        $this->addWhere(self::$fields['date_availability'], $filter->getDateTimeAvailability(), '<=', true);
        $this->addWhere(self::$fields['pending_id'], null);

        if (!empty($filter->getTopic())) {
            $topic = strtr($filter->getTopic(), '*', '%');
            $sign  = (strpos($topic, '%') !== false ? 'LIKE' : '=');
            $this->addWhere(self::$fields['topic'], $topic, $sign);
        }

        if (!empty($filter->getPriority())) {
            $this->addWhere(self::$fields['priority'], $filter->getPriority());
        }

        if (!empty($filter->getDateTimeExpiration())) {
            $this->addWhere(self::$fields['date_expiration'], $filter->getDateTimeExpiration(), '>', true);
        }

        return 'WHERE ' . implode(' AND ', $this->where);
    }

    /**
     * Build query limit.
     *
     * @param Filter $filter
     * @return string
     */
    private function buildLimit(Filter $filter)
    {
        return 'LIMIT ' . $filter->getLimit();
    }

    /**
     * @param  string $field
     * @param  string|int $value
     * @param  string $sign
     * @param  bool $orNull
     * @return $this
     */
    private function addWhere($field, $value, $sign = '=', $orNull = false)
    {
        if ($value === null) {
            $this->where[] = "`${field}` IS NULL";
        } else {
            $this->where[]            = ($orNull ? '(' : '') . "`${field}` $sign :${field}" . ($orNull ? " OR `${field}` IS NULL)" : '');
            $this->bind[':' . $field] = $value;
        }

        return $this;
    }

    /**
     * Generate unique id. Format is: [0-f]{16}-[0-f]{16}-...
     *
     * @param  int $nbChunk Number of "chunk" of 8 hexadecimal chars in generated id.
     * @return string
     */
    private function generateId($nbChunk = 4)
    {
        $chunks = [];
        for ($i = 0; $i < $nbChunk; $i++) {
            $chunks[] = sprintf('%08x', mt_rand(0, 0xffffffff));
        }

        return implode('-', $chunks);
    }

    /**
     * Clean query builder values.
     *
     * @return $this
     */
    protected function cleanQuery()
    {
        $this->where = [];
        $this->bind  = [];

        return $this;
    }

    /**
     * Run a query
     *
     * @param string $query

     */
    abstract protected function executeQuery($query);
}
