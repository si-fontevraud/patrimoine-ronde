<?php

declare(strict_types=1);

namespace App\Tests\Entity;

use App\Entity\Report;
use PHPUnit\Framework\TestCase;

final class ReportTest extends TestCase
{
    public function testConstructorGeneratesUniqueReferencePerReport(): void
    {
        $firstReport = new Report();
        $secondReport = new Report();

        self::assertNotSame($firstReport->getReference(), $secondReport->getReference());
        self::assertStringStartsWith('SIG-', (string) $firstReport->getReference());
        self::assertSame(30, strlen((string) $firstReport->getReference()));
    }
}
