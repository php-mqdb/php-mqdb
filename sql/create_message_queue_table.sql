CREATE TABLE `message_queue` (
 `message_id` binary(36) NOT NULL,
 `message_status` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT 'Status of the message. 0: Waiting, 1: Popped, 2: No response, 3: To delete ',
 `message_priority` smallint(5) unsigned NOT NULL DEFAULT '3' COMMENT 'Priority: 1: Very high, 2: High, 3: Medium, 4: Low, 5: Very low',
 `message_topic` varbinary(100) NOT NULL COMMENT 'Topic of the message (ie: publish.content).',
 `message_content` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT 'Message content.',
 `message_content_type` varbinary(10) NOT NULL DEFAULT 'text' COMMENT 'Message type.',
 `message_entity_id` varbinary(50) DEFAULT NULL COMMENT 'Entity identifier linked to the message, if exist.',
 `message_date_expiration` datetime DEFAULT NULL COMMENT 'Expiration date for the message. If null, message does not expire.',
 `message_date_availability` datetime DEFAULT NULL COMMENT 'Availability Date for the message. Before this date, message is not available',
 `message_pending_id` varbinary(8) DEFAULT NULL COMMENT 'Pending id generated to avoid message to be consumed by 2 scripts in parallel',
 `message_date_create` datetime NOT NULL COMMENT 'Creation date for the message.',
 `message_date_update` datetime DEFAULT NULL COMMENT 'Update date for the message.',
 PRIMARY KEY (`message_id`),
 KEY `idx_message_reservation` (`message_priority`,`message_pending_id`,`message_status`,`message_topic`),
 KEY `idx_message_reservation_update` (`message_pending_id`,`message_topic`,`message_status`,`message_entity_id`),
 KEY `idx_message_topic` (`message_topic`,`message_pending_id`,`message_status`,`message_priority`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
