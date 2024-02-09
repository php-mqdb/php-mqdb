<?php

/*
 * Copyright (c) Romain Cottard
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace PhpMqdb\Config;

use PhpMqdb\Exception\PhpMqdbConfigurationException;

final class TableConfig
{
    private string $table = 'message_queue';

    /** @var string[] $fields */
    private array $fields = [
        'id'                => 'message_id',
        'status'            => 'message_status',
        'priority'          => 'message_priority',
        'topic'             => 'message_topic',
        'content'           => 'message_content',
        'content_type'      => 'message_content_type',
        'pending_id'        => 'message_pending_id',
        'date_create'       => 'message_date_create',
        'date_update'       => 'message_date_update',
        //~ optional fields
        'entity_id'         => 'message_entity_id',
        'date_expiration'   => 'message_date_expiration',
        'date_availability' => 'message_date_availability',
    ];

    /** @var string[] $orders */
    private array $orders = [
        'priority'          => 'ASC',
        'date_availability' => 'ASC',
        'date_create'       => 'ASC',
    ];

    /**
     * @param string $table
     * @return TableConfig
     * @throws PhpMqdbConfigurationException
     */
    public function setTable(string $table): self
    {
        if (empty($table)) {
            throw new PhpMqdbConfigurationException('Empty table name');
        }

        if ((bool) \preg_match('`[^a-zA-Z0-9_-]`', $table) === true) {
            throw new PhpMqdbConfigurationException('Invalid table name!');
        }

        $this->table = $table;

        return $this;
    }

    /**
     * @param string[] $fields
     * @return TableConfig
     * @throws PhpMqdbConfigurationException
     */
    public function setFields(array $fields): self
    {
        $this->fields = [];

        foreach ($fields as $key => $field) {
            if ((bool) \preg_match('`[^a-zA-Z0-9_-]`', $field) === true) {
                throw new PhpMqdbConfigurationException('Invalid field name!');
            }

            $this->fields[$key] = $field;
        }

        return $this;
    }

    /**
     * @param string[] $fields
     * @return TableConfig
     * @throws PhpMqdbConfigurationException
     */
    public function setOrders(array $fields): self
    {
        $this->orders = [];

        foreach ($fields as $key => $order) {
            if (!isset($this->fields[$key])) {
                continue;
            }

            if (!\in_array($order, ['ASC', 'DESC'], true)) {
                throw new PhpMqdbConfigurationException('Invalid order direction!');
            }

            $this->orders[$key] = $order;
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getTable(): string
    {
        return $this->table;
    }

    /**
     * @param string $key
     * @return bool
     */
    public function hasField(string $key): bool
    {
        return isset($this->fields[$key]);
    }

    /**
     * @param string $key
     * @return string
     * @throws PhpMqdbConfigurationException
     */
    public function getField(string $key): string
    {
        if (!$this->hasField($key)) {
            throw new PhpMqdbConfigurationException('Asked field does not exist: (field: "' . $key . '")');
        }

        return $this->fields[$key];
    }

    /**
     * @return string[]
     */
    public function getFields(): array
    {
        return $this->fields;
    }

    /**
     * @return string[]
     */
    public function getOrders(): array
    {
        return $this->orders;
    }
}
