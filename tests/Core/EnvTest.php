<?php

declare(strict_types=1);

namespace App\Tests\Core;

use App\Core\Env;
use PHPUnit\Framework\TestCase;

final class EnvTest extends TestCase
{
    public function testLoadsDotenvFromProjectRoot(): void
    {
        $this->assertSame('g1', Env::get('DB_NAME'));
    }

    public function testMissingKeyReturnsDefault(): void
    {
        $this->assertNull(Env::get('__DHKGRID_NOT_SET__'));
        $this->assertSame('fallback', Env::get('__DHKGRID_NOT_SET__', 'fallback'));
    }
}