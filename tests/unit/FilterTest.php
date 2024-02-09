<?php

/*
 * Copyright Romain Cottard
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace PhpMqdb\Tests\Unit;

use PhpMqdb\Enumerator\Priority;
use PhpMqdb\Enumerator\Status;
use PhpMqdb\Filter;
use PHPUnit\Framework\TestCase;

class FilterTest extends TestCase
{
    public function testSetTopicEmptyValueThatThrowAnException(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Topic filter given cannot be empty!');

        (new Filter())->setTopic('');
    }

    /**
     * @param string $topic
     *
     * @dataProvider validTopicsDataProvider
     */
    public function testValidTopic(string $topic): void
    {
        self::assertEquals($topic, (new Filter())->setTopic($topic)->getTopic(), $topic);
    }

    /**
     * @param string $topic
     *
     * @dataProvider invalidTopicsDataProvider
     */
    public function testInvalidTopicThatThrowAnException(string $topic): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Topic filter must contain only alphanums, ".", "_" & "*" characters!');

        (new Filter())->setTopic($topic);
    }

    public function testPriorityUnderflowException(): void
    {
        $this->expectException(\UnderflowException::class);

        (new Filter())->setPriorities([0]);
    }

    public function testPriorityOverflowException(): void
    {
        $this->expectException(\OverflowException::class);

        (new Filter())->setPriorities([6]);
    }

    /**
     * @return void
     */
    public function testPriorityAllowedValue(): void
    {
        $filter = new Filter();

        self::assertEquals([Priority::VERY_LOW], $filter->setPriorities([Priority::VERY_LOW])->getPriorities());
        self::assertEquals([Priority::MEDIUM], $filter->setPriorities([Priority::MEDIUM])->getPriorities());
        self::assertEquals([Priority::VERY_HIGH], $filter->setPriorities([Priority::VERY_HIGH])->getPriorities());
    }

    public function testStatusUnderflowException(): void
    {
        $this->expectException(\UnderflowException::class);

        (new Filter())->setStatuses([-1]);
    }

    public function testStatusOverflowException(): void
    {
        $this->expectException(\OverflowException::class);

        (new Filter())->setStatuses([6]);
    }

    public function testStatusAllowedValue(): void
    {
        $filter = new Filter();

        self::assertEquals([Status::IN_QUEUE], $filter->setStatuses([Status::IN_QUEUE])->getStatuses());
        self::assertEquals([Status::ACK_PENDING], $filter->setStatuses([Status::ACK_PENDING])->getStatuses());
        self::assertEquals([Status::ACK_RECEIVED], $filter->setStatuses([Status::ACK_RECEIVED])->getStatuses());
        self::assertEquals([Status::NACK_RECEIVED], $filter->setStatuses([Status::NACK_RECEIVED])->getStatuses());
        self::assertEquals([Status::ACK_NOT_RECEIVED], $filter->setStatuses([Status::ACK_NOT_RECEIVED])->getStatuses());
    }

    public function testOffsetUnderflowException(): void
    {
        $this->expectException(\UnderflowException::class);

        (new Filter())->setOffset(-1);
    }

    /**
     * @return void
     */
    public function testOffsetAllowedValue(): void
    {
        $filter = new Filter();

        self::assertEquals(0, $filter->setOffset(0)->getOffset());
        self::assertEquals(1000, $filter->setOffset(1000)->getOffset());
        self::assertEquals(100000, $filter->setOffset(100000)->getOffset());
    }

    /**
     * @return void
     * @throws \Exception
     */
    public function testInvalidExpirationDateTime(): void
    {
        $this->expectException(\RuntimeException::class);

        $tomorrow = (new \DateTimeImmutable('now', new \DateTimeZone('UTC')))->add(new \DateInterval('P1D'));
        (new Filter())->setDateTimeExpiration($tomorrow->format('Y-m-d'));
    }

    /**
     * @return void
     * @throws \Exception
     * @expectedException \UnderflowException
     */
    public function testExpirationDateTimeIsPriorToCurrentDateTime(): void
    {
        $this->expectException(\RuntimeException::class);

        $yesterday = (new \DateTimeImmutable('now', new \DateTimeZone('UTC')))->sub(new \DateInterval('P1D'));
        (new Filter())->setDateTimeExpiration($yesterday->format('Y-m-d H:i:s'));
    }

    /**
     * @throws \Exception
     */
    public function testExpirationDateTimeIsValid(): void
    {
        $tomorrow = (new \DateTimeImmutable('now', new \DateTimeZone('UTC')))->add(new \DateInterval('P1D'));
        $filter = (new Filter())->setDateTimeExpiration($tomorrow->format('Y-m-d H:i:s'));

        self::assertEquals($tomorrow->format('Y-m-d H:i:s'), $filter->getDateTimeExpiration());
    }

    /**
     * @throws \Exception
     */
    public function testInvalidAvailabilityDateTime(): void
    {
        $this->expectException(\RuntimeException::class);

        $tomorrow = (new \DateTimeImmutable('now', new \DateTimeZone('UTC')))->add(new \DateInterval('P1D'));
        (new Filter())->setDateTimeAvailability($tomorrow->format('Y-m-d'));
    }

    /**
     * @throws \Exception
     */
    public function testAvailabilityDateTimeIsValid(): void
    {
        $tomorrow = (new \DateTimeImmutable('now', new \DateTimeZone('UTC')))->add(new \DateInterval('P1D'));
        $filter = (new Filter())->setDateTimeAvailability($tomorrow->format('Y-m-d H:i:s'));

        self::assertEquals($tomorrow->format('Y-m-d H:i:s'), $filter->getDateTimeAvailability());
    }

    public function testLimitUnderflowException(): void
    {
        $this->expectException(\UnderflowException::class);

        (new Filter())->setLimit(0);
    }

    public function testLimitOverflowException(): void
    {
        $this->expectException(\OverflowException::class);

        (new Filter())->setLimit(1001); // Default limit is 1000
    }

    public function testLimitAllowedValue(): void
    {
        $filter = new Filter();

        self::assertEquals(1, $filter->setLimit(1)->getLimit());
        self::assertEquals(50, $filter->setLimit(50)->getLimit());
        self::assertEquals(1000, $filter->setLimit(1000)->getLimit());
    }

    public function testLimitAllowedValueWithIncreasedDefaultMaxLimit(): void
    {
        $filter = new Filter(10000);

        self::assertEquals(5000, $filter->setLimit(5000)->getLimit());
        self::assertEquals(10000, $filter->setLimit(10000)->getLimit());
    }

    public function testUnderflowMaxLimit(): void
    {
        $this->expectException(\UnderflowException::class);

        (new Filter(0));
    }

    /**
     * Topic Data provider
     *
     * @return string[][]
     */
    public static function validTopicsDataProvider(): array
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
     * @return string[][]
     */
    public static function invalidTopicsDataProvider(): array
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
