<?php

declare(strict_types=1);

namespace App\Tests\Core;

use App\Core\Config;
use PHPUnit\Framework\TestCase;

final class ConfigTest extends TestCase
{
    public function testDbResolvesAllCredentialsFromEnv(): void
    {
        $db = Config::db();

        $this->assertSame('g1', $db['name']);
        $this->assertNotEmpty($db['host']);
        $this->assertNotEmpty($db['user']);
        $this->assertIsString($db['pass']);
        $this->assertSame('3306', $db['port']);
    }
}