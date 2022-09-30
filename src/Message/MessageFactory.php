<?php

declare(strict_types=1);

/*
 * Copyright (c) Romain Cottard
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpMqdb\Message;

use PhpMqdb\Config\TableConfig;
use PhpMqdb\Enumerator;

/**
 * Class MessageFactory
 *
 * @author Romain Cottard
 */
class MessageFactory implements MessageFactoryInterface
{
    /** @var TableConfig $tableConfig */
    private $tableConfig;

    /**
     * MessageFactory constructor.
     *
     * @param TableConfig $tableConfig
     */
    public function __construct(TableConfig $tableConfig)
    {
        $this->tableConfig = $tableConfig;
    }

    /**
     * @param \stdClass|null $data
     * @return MessageInterface
     * @throws \Exception
     */
    public function create(\stdClass $data = null): MessageInterface
    {
        $contentType      = Enumerator\ContentType::TEXT;
        $contentTypeField = $this->tableConfig->getField('content_type');

        if ($data !== null && !empty($data->{$contentTypeField})) {
            $contentType = $data->{$contentTypeField};
        }

        switch ($contentType) {
            case Enumerator\ContentType::JSON:
                $message = new MessageJson();
                break;
            case Enumerator\ContentType::TEXT:
            default:
                $message = new Message();
        }

        if (!empty($data)) {
            $this->hydrateMessage($message, $data);
        }

        return $message;
    }

    /**
     * @param MessageInterface $message
     * @param \stdClass $data
     * @return MessageFactory
     */
    private function hydrateMessage(MessageInterface $message, \stdClass $data): self
    {
        $fields = $this->tableConfig->getFields();

        $message
            ->setId($data->{$fields['id']})
            ->setStatus((int) $data->{$fields['status']})
            ->setPriority((int) $data->{$fields['priority']})
            ->setTopic($data->{$fields['topic']})
            ->setContent($data->{$fields['content']})
            ->setContentType($data->{$fields['content_type']})
            ->setDateCreate($data->{$fields['date_create']})
            ->setDateUpdate($data->{$fields['date_update']} ?? null)
        ;

        if (array_key_exists('entity_id', $fields)) {
            $message->setEntityId($data->{$fields['entity_id']});
        }

        if (array_key_exists('date_expiration', $fields)) {
            $message->setDateExpiration($data->{$fields['date_expiration']});
        }

        if (array_key_exists('date_availability', $fields)) {
            $message->setDateAvailability($data->{$fields['date_availability']});
        }

        return $this;
    }
}
