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
     * @expectedExceptionMessage Topic filter must contain only alphanums, ".", "_" & "*" characters!
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
        (new Filter())->setPriorities([0]);
    }

    /**
     * @expectedException \OverflowException
     */
    public function testPriorityOverflowException()
    {
        (new Filter())->setPriorities([6]);
    }

    /**
     * @return void
     */
    public function testPriorityAllowedValue()
    {
        $filter = new Filter();

        self::assertEquals([Priority::VERY_LOW], $filter->setPriorities([Priority::VERY_LOW])->getPriorities());
        self::assertEquals([Priority::MEDIUM], $filter->setPriorities([Priority::MEDIUM])->getPriorities());
        self::assertEquals([Priority::VERY_HIGH], $filter->setPriorities([Priority::VERY_HIGH])->getPriorities());
    }

    /**
     * @return void
     * @expectedException \UnderflowException
     */
    public function testStatusUnderflowException()
    {
        (new Filter())->setStatuses([-1]);
    }

    /**
     * @return void
     * @expectedException \OverflowException
     */
    public function testStatusOverflowException()
    {
        (new Filter())->setStatuses([6]);
    }

    /**
     * @return void
     */
    public function testStatusAllowedValue()
    {
        $filter = new Filter();

        self::assertEquals([Status::IN_QUEUE], $filter->setStatuses([Status::IN_QUEUE])->getStatuses());
        self::assertEquals([Status::ACK_PENDING], $filter->setStatuses([Status::ACK_PENDING])->getStatuses());
        self::assertEquals([Status::ACK_RECEIVED], $filter->setStatuses([Status::ACK_RECEIVED])->getStatuses());
        self::assertEquals([Status::NACK_RECEIVED], $filter->setStatuses([Status::NACK_RECEIVED])->getStatuses());
        self::assertEquals([Status::ACK_NOT_RECEIVED], $filter->setStatuses([Status::ACK_NOT_RECEIVED])->getStatuses());
    }

    /**
     * @expectedException \UnderflowException
     */
    public function testOffsetUnderflowException()
    {
        (new Filter())->setOffset(-1);
    }

    /**
     * @return void
     */
    public function testOffsetAllowedValue()
    {
        $filter = new Filter();

        self::assertEquals(0, $filter->setOffset(0)->getOffset());
        self::assertEquals(1000, $filter->setOffset(1000)->getOffset());
        self::assertEquals(100000, $filter->setOffset(100000)->getOffset());
    }

    /**
     * @return void
     * @throws \Exception
     * @expectedException \RuntimeException
     */
    public function testInvalidExpirationDateTime()
    {
        $tomorrow = (new \DateTimeImmutable('now', new \DateTimeZone('UTC')))->add(new \DateInterval('P1D'));
        (new Filter())->setDateTimeExpiration($tomorrow->format('Y-m-d'));
    }

    /**
     * @return void
     * @throws \Exception
     * @expectedException \UnderflowException
     */
    public function testExpirationDateTimeIsPriorToCurrentDateTime()
    {
        $yesterday = (new \DateTimeImmutable('now', new \DateTimeZone('UTC')))->sub(new \DateInterval('P1D'));
        (new Filter())->setDateTimeExpiration($yesterday->format('Y-m-d H:i:s'));
    }

    /**
     * @return void
     * @throws \Exception
     */
    public function testExpirationDateTimeIsValid()
    {
        $tomorrow = (new \DateTimeImmutable('now', new \DateTimeZone('UTC')))->add(new \DateInterval('P1D'));
        $filter = (new Filter())->setDateTimeExpiration($tomorrow->format('Y-m-d H:i:s'));

        self::assertEquals($tomorrow->format('Y-m-d H:i:s'), $filter->getDateTimeExpiration());
    }

    /**
     * @return void
     * @throws \Exception
     * @expectedException \RuntimeException
     */
    public function testInvalidAvailabilityDateTime()
    {
        $tomorrow = (new \DateTimeImmutable('now', new \DateTimeZone('UTC')))->add(new \DateInterval('P1D'));
        (new Filter())->setDateTimeAvailability($tomorrow->format('Y-m-d'));
    }

    /**
     * @return void
     * @throws \Exception
     */
    public function testAvailabilityDateTimeIsValid()
    {
        $tomorrow = (new \DateTimeImmutable('now', new \DateTimeZone('UTC')))->add(new \DateInterval('P1D'));
        $filter = (new Filter())->setDateTimeAvailability($tomorrow->format('Y-m-d H:i:s'));

        self::assertEquals($tomorrow->format('Y-m-d H:i:s'), $filter->getDateTimeAvailability());
    }

    /**
     * @expectedException \UnderflowException
     */
    public function testLimitUnderflowException()
    {
        (new Filter())->setLimit(0);
    }

    /**
     * @expectedException \OverflowException
     */
    public function testLimitOverflowException()
    {
        (new Filter())->setLimit(1001); // Default limit is 1000
    }

    /**
     * @return void
     */
    public function testLimitAllowedValue()
    {
        $filter = new Filter();

        self::assertEquals(1, $filter->setLimit(1)->getLimit());
        self::assertEquals(50, $filter->setLimit(50)->getLimit());
        self::assertEquals(1000, $filter->setLimit(1000)->getLimit());
    }

    /**
     * @return void
     */
    public function testLimitAllowedValueWithIncreasedDefaultMaxLimit()
    {
        $filter = new Filter(10000);

        self::assertEquals(5000, $filter->setLimit(5000)->getLimit());
        self::assertEquals(10000, $filter->setLimit(10000)->getLimit());
    }

    /**
     * @expectedException \UnderflowException
     */
    public function testUnderflowMaxLimit()
    {
        (new Filter(0));
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
            ['topic.sub_topic.*'],
            ['other_topic'],
            ['other_topic.sub_topic.*'],
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
