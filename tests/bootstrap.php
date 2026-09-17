<?php

declare(strict_types=1);

// PHPUnit bootstrap: composer autoload only. Each test opens its own PDO via
// DbProvider so the real g1 database is available (transactional/cleanup
// strategy is per-test; see tests/Support/Db.php).

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/Support/Db.php';