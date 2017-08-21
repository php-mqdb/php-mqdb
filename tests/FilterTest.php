<?php

/*
 * Copyright Romain Cottard
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpMqdb\Tests;

use PhpMqdb\Filter;
use PhpMqdb\Enumerator\Priority;
use PhpMqdb\Enumerator\Status;
use PHPUnit\Framework\TestCase;

class FilterTest extends TestCase
{
    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Topic filter given cannot be empty!
     */
    public function testSetTopicEmptyValueThatThrowAnException()
    {
        (new Filter())->setTopic('');
    }

    /**
     * @param string $topic
     *
     * @dataProvider validTopicsDataProvider
     */
    public function testValidTopic($topic)
    {
        self::assertEquals($topic, (new Filter())->setTopic($topic)->getTopic(), $topic);
    }

    /**
     * @param string $topic
     *
     * @dataProvider invalidTopicsDataProvider
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Topic filter must contain only alphanums, "." & "*" characters!
     */
    public function testInvalidTopicThatThrowAnException($topic)
    {
        (new Filter())->setTopic($topic);
    }

    /**
     * @expectedException  \UnderflowException
     */
    public function testPriorityUnderflowException()
    {
        (new filter())->setPriority(0);
    }

    /**
     * @expectedException \OverflowException
     */
    public function testPriorityOverflowException()
    {
        (new filter())->setPriority(6);
    }

    /**
     * @return void
     */
    public function testPriorityAllowedValue()
    {
        $filter = new filter();

        self::assertEquals(Priority::VERY_LOW, $filter->setPriority(Priority::VERY_LOW)->getPriority());
        self::assertEquals(Priority::MEDIUM, $filter->setPriority(Priority::MEDIUM)->getPriority());
        self::assertEquals(Priority::VERY_HIGH, $filter->setPriority(Priority::VERY_HIGH)->getPriority());
    }

    /**
     * @return void
     * @expectedException \UnderflowException
     */
    public function testStatusUnderflowException()
    {
        (new filter())->setStatus(-1);
    }

    /**
     * @return void
     * @expectedException \OverflowException
     */
    public function testStatusOverflowException()
    {
        (new filter())->setStatus(6);
    }

    /**
     * @return void
     */
    public function testStatusAllowedValue()
    {
        $filter = new filter();

        self::assertEquals(Status::IN_QUEUE, $filter->setStatus(Status::IN_QUEUE)->getStatus());
        self::assertEquals(Status::ACK_PENDING, $filter->setStatus(Status::ACK_PENDING)->getStatus());
        self::assertEquals(Status::ACK_RECEIVED, $filter->setStatus(Status::ACK_RECEIVED)->getStatus());
        self::assertEquals(Status::NACK_RECEIVED, $filter->setStatus(Status::NACK_RECEIVED)->getStatus());
        self::assertEquals(Status::ACK_NOT_RECEIVED, $filter->setStatus(Status::ACK_NOT_RECEIVED)->getStatus());
    }

    /**
     * @expectedException \UnderflowException
     */
    public function testOffsetUnderflowException()
    {
        (new filter())->setOffset(-1);
    }

    /**
     * @return void
     */
    public function testOffsetAllowedValue()
    {
        $filter = new filter();

        self::assertEquals(0, $filter->setOffset(0)->getOffset());
        self::assertEquals(1000, $filter->setOffset(1000)->getOffset());
        self::assertEquals(100000, $filter->setOffset(100000)->getOffset());
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testInvalidExpirationDateTime()
    {
        $tomorrow = (new \DateTimeImmutable('now', new \DateTimeZone('UTC')))->add(new \DateInterval('P1D'));
        (new filter())->setDateTimeExpiration($tomorrow->format('Y-m-d'));
    }

    /**
     * @expectedException \UnderflowException
     */
    public function testExpirationDateTimeIsPriorToCurrentDateTime()
    {
        $yesterday = (new \DateTimeImmutable('now', new \DateTimeZone('UTC')))->sub(new \DateInterval('P1D'));
        (new filter())->setDateTimeExpiration($yesterday->format('Y-m-d H:i:s'));
    }

    /**
     * @return void
     */
    public function testExpirationDateTimeIsValid()
    {
        $tomorrow = (new \DateTimeImmutable('now', new \DateTimeZone('UTC')))->add(new \DateInterval('P1D'));
        $filter = (new filter())->setDateTimeExpiration($tomorrow->format('Y-m-d H:i:s'));

        self::assertEquals($tomorrow->format('Y-m-d H:i:s'), $filter->getDateTimeExpiration());
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testInvalidAvailabilityDateTime()
    {
        $tomorrow = (new \DateTimeImmutable('now', new \DateTimeZone('UTC')))->add(new \DateInterval('P1D'));
        (new filter())->setDateTimeAvailability($tomorrow->format('Y-m-d'));
    }

    /**
     * @return void
     */
    public function testAvailabilityDateTimeIsValid()
    {
        $tomorrow = (new \DateTimeImmutable('now', new \DateTimeZone('UTC')))->add(new \DateInterval('P1D'));
        $filter = (new filter())->setDateTimeAvailability($tomorrow->format('Y-m-d H:i:s'));

        self::assertEquals($tomorrow->format('Y-m-d H:i:s'), $filter->getDateTimeAvailability());
    }

    /**
     * @expectedException \UnderflowException
     */
    public function testLimitUnderflowException()
    {
        (new filter())->setLimit(0);
    }

    /**
     * @expectedException \OverflowException
     */
    public function testLimitOverflowException()
    {
        (new filter())->setLimit(1001); // Default limit is 1000
    }

    /**
     * @return void
     */
    public function testLimitAllowedValue()
    {
        $filter = new filter();

        self::assertEquals(1, $filter->setLimit(1)->getLimit());
        self::assertEquals(50, $filter->setLimit(50)->getLimit());
        self::assertEquals(1000, $filter->setLimit(1000)->getLimit());
    }

    /**
     * @return void
     */
    public function testLimitAllowedValueWithIncreasedDefaultMaxLimit()
    {
        $filter = new filter(10000);

        self::assertEquals(5000, $filter->setLimit(5000)->getLimit());
        self::assertEquals(10000, $filter->setLimit(10000)->getLimit());
    }

    /**
     * @expectedException \UnderflowException
     */
    public function testUnderflowMaxLimit()
    {
        (new filter(0));
    }

    /**
     * Topic Data provider
     *
     * @return array
     */
    public function validTopicsDataProvider()
    {
        return [
            ['topic'],
            ['topic.subtopic'],
            ['topic.subtopic.2'],
            ['topic.subtopic.subsubtopic'],
            ['topic.subtopic.*'],
            ['topic.subtopic.subsubtopic.*'],
        ];
    }

    /**
     * Topic Data provider
     *
     * @return array
     */
    public function invalidTopicsDataProvider()
    {
        return [
            ['topic.subt_opic*'],
            ['topic.subtopic.'],
            ['topic.subtopic*'],
            ['topic.subto*pic'],
            ['topic.subtopic**'],
            ['topic.subtopic..'],
            ['topic..subtopic'],
            ['topic.subtopic.subsubtopic*'],
        ];
    }
}
