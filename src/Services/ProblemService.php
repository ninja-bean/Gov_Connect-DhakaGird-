<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Enums\Category;
use App\Core\Enums\Priority;
use App\Core\Enums\Status;
use InvalidArgumentException;
use PDO;
use RuntimeException;

/**
 * Business rules for citizen problem reports.
 *
 * Owns all problem-table writes and their validation so controllers stay thin.
 * SOS reports are normalised to category=police / priority=sos and always
 * carry a geocoded address and a row in the logs/notification table.
 */
final class ProblemService
{
    /** Soft Dhaka bounds used for report/SOS validation. */
    private const BOUNDS = [
        'minLat' => 23.65,
        'maxLat' => 23.90,
        'minLng' => 90.30,
        'maxLng' => 90.55,
    ];

    private const NOMINATIM_URL = 'https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat=%s&lon=%s';

    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Record an SOS alert: write the problem row and the notification log row.
     *
     * @return int id of the created problem
     */
    public function submitSos(int $userId, float $lat, float $lng): int
    {
        if (!self::isInDhaka($lat, $lng)) {
            throw new InvalidArgumentException('SOS reports allowed only inside Dhaka.');
        }

        $locationName = $this->geocode($lat, $lng);

        $this->pdo->beginTransaction();

        try {
            $stmt = $this->pdo->prepare(
                "INSERT INTO problems
                    (user_id, category, description, location, location_name, latitude, longitude, status, priority, created_at)
                 VALUES (:user_id, :category, :description, :location, :location_name, :lat, :lng, :status, :priority, NOW())"
            );
            $stmt->execute([
                ':user_id'      => $userId,
                ':category'     => Category::Police->value,
                ':description'  => 'Emergency SOS triggered by user.',
                ':location'     => $locationName,
                ':location_name'=> $locationName,
                ':lat'          => $lat,
                ':lng'          => $lng,
                ':status'       => Status::Pending->value,
                ':priority'     => Priority::Sos->value,
            ]);

            $problemId = (int) $this->pdo->lastInsertId();

            $logStmt = $this->pdo->prepare(
                "INSERT INTO logs (problem_id, user_id, notification_type, message, created_at)
                 VALUES (:problem_id, :user_id, 'SOS', 'Emergency SOS alert triggered', NOW())"
            );
            $logStmt->execute([
                ':problem_id' => $problemId,
                ':user_id'    => $userId,
            ]);

            $this->pdo->commit();
        } catch (\Throwable $e) {
            $this->pdo->rollBack();
            throw new RuntimeException('Failed to save SOS report.', 0, $e);
        }

        return $problemId;
    }

    /**
     * @param list<string> $invalidCategories guard against unknown categories
     *
     * @throws InvalidArgumentException when category/bounds/location fail validation
     */
    public function submitReport(
        int $userId,
        string $category,
        string $description,
        ?string $suggestion,
        float $lat,
        float $lng,
        ?string $mediaPath
    ): int {
        if (!in_array($category, self::categories(), true)) {
            throw new InvalidArgumentException('Invalid category selected.');
        }

        if (!self::isInDhaka($lat, $lng)) {
            throw new InvalidArgumentException('Reports must be inside Dhaka.');
        }

        $locationName = $this->geocode($lat, $lng);

        $stmt = $this->pdo->prepare(
            "INSERT INTO problems
                (user_id, category, description, suggestion, location, location_name, latitude, longitude, status, priority, media_path, created_at)
             VALUES (:user_id, :category, :description, :suggestion, :location, :location_name, :lat, :lng, :status, :priority, :media_path, NOW())"
        );

        try {
            $stmt->execute([
                ':user_id'      => $userId,
                ':category'     => $category,
                ':description'  => $description,
                ':suggestion'   => $suggestion,
                ':location'     => $locationName,
                ':location_name'=> $locationName,
                ':lat'          => $lat,
                ':lng'          => $lng,
                ':status'       => Status::Pending->value,
                ':priority'     => Priority::Medium->value,
                ':media_path'   => $mediaPath,
            ]);
        } catch (\Throwable $e) {
            throw new RuntimeException('Failed to save report.', 0, $e);
        }

        return (int) $this->pdo->lastInsertId();
    }

    /**
     * Resolve a human-readable address for coordinates via Nominatim.
     *
     * @throws RuntimeException when the geocoder cannot be reached or returns nothing
     */
    public function geocode(float $lat, float $lng): string
    {
        $url = sprintf(self::NOMINATIM_URL, $lat, $lng);
        $context = stream_context_create([
            'http' => ['header' => "User-Agent: DhakaGrid/1.0\r\n", 'timeout' => 10],
        ]);

        $response = @file_get_contents($url, false, $context);

        if ($response === false) {
            throw new RuntimeException('Failed to resolve location.');
        }

        $decoded = json_decode($response, true);

        if (empty($decoded['display_name'])) {
            throw new RuntimeException('Failed to resolve location.');
        }

        return $decoded['display_name'];
    }

    private static function isInDhaka(float $lat, float $lng): bool
    {
        return $lat >= self::BOUNDS['minLat']
            && $lat <= self::BOUNDS['maxLat']
            && $lng >= self::BOUNDS['minLng']
            && $lng <= self::BOUNDS['maxLng'];
    }

    /** @return list<string> */
    private static function categories(): array
    {
        return array_map(static fn (Category $category): string => $category->value, Category::cases());
    }
}