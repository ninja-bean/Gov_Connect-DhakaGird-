<?php

declare(strict_types=1);

namespace App\Tests\Service;

use App\Services\ProblemService;
use App\Tests\Support\Db;
use InvalidArgumentException;
use PDO;
use PHPUnit\Framework\TestCase;

final class ProblemServiceTest extends TestCase
{
    private PDO $pdo;
    private ProblemService $service;
    private int $userId;

    /** @var list<int> created during the test and removed in tearDown */
    private array $createdIds = [];

    protected function setUp(): void
    {
        $this->pdo = Db::connect();
        $this->service = new ProblemService($this->pdo);

        $userId = $this->pdo->query("SELECT user_id FROM users WHERE role = 'user' LIMIT 1")->fetchColumn();
        $this->assertNotFalse($userId, 'Seed citizen account is required for these tests.');
        $this->userId = (int) $userId;
    }

    protected function tearDown(): void
    {
        $deleteLogs = $this->pdo->prepare('DELETE FROM logs WHERE problem_id = ?');
        $deleteProblem = $this->pdo->prepare('DELETE FROM problems WHERE problem_id = ?');

        foreach ($this->createdIds as $id) {
            $deleteLogs->execute([$id]);
            $deleteProblem->execute([$id]);
        }
    }

    public function testSosOutsideDhakaThrows(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('SOS reports allowed only inside Dhaka.');

        $this->service->submitSos($this->userId, 24.0, 90.0);
    }

    public function testSosCreatesNormalisedProblemAndLog(): void
    {
        $id = $this->service->submitSos($this->userId, 23.7808, 90.3896);
        $this->createdIds[] = $id;

        $problem = $this->pdo->prepare('SELECT category, priority, status, location_name FROM problems WHERE problem_id = ?');
        $problem->execute([$id]);
        $row = $problem->fetch(PDO::FETCH_ASSOC);

        $this->assertSame('police', $row['category']);
        $this->assertSame('sos', $row['priority']);
        $this->assertSame('pending', $row['status']);
        $this->assertNotEmpty($row['location_name']);

        $log = $this->pdo->prepare("SELECT COUNT(*) FROM logs WHERE problem_id = ? AND notification_type = 'SOS'");
        $log->execute([$id]);
        $this->assertSame(1, (int) $log->fetchColumn());
    }

    public function testReportRejectsUnknownCategory(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid category selected.');

        $this->service->submitReport(
            userId: $this->userId,
            category: 'water',
            description: 'test',
            suggestion: null,
            lat: 23.78,
            lng: 90.38,
            mediaPath: null
        );
    }

    public function testReportInsertsPendingMedium(): void
    {
        $id = $this->service->submitReport(
            userId: $this->userId,
            category: 'other',
            description: 'phpunit report',
            suggestion: 'check it',
            lat: 23.78,
            lng: 90.38,
            mediaPath: null
        );
        $this->createdIds[] = $id;

        $stmt = $this->pdo->prepare('SELECT status, priority, location_name FROM problems WHERE problem_id = ?');
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        $this->assertSame('pending', $row['status']);
        $this->assertSame('medium', $row['priority']);
        $this->assertNotEmpty($row['location_name']);
    }
}