<?php

/*
 * Copyright Romain Cottard
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace PhpMqdb\Tests\Integration;

use PHPUnit\Framework\TestCase;

class DummyTest extends TestCase
{
    public function testICanRunIntegrationDummyTest(): void
    {
        $this->assertTrue(true);
    }
}
