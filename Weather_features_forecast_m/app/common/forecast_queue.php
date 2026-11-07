<?php

function saveForecastQueues(PDO $db, string $locationKey, array $hourly, array $daily): void
{
    if (count($hourly) !== 12 || count($daily) !== 7) {
        throw new RuntimeException('Không thể lưu queue dự báo chưa đầy đủ.');
    }

    $snapshotAt = (new DateTimeImmutable('now', new DateTimeZone('Asia/Ho_Chi_Minh')))
        ->format('Y-m-d H:i:s');

    try {
        $db->beginTransaction();
        $deleteDay = $db->prepare('DELETE FROM weather_forecast_day_queue WHERE location_key = :location_key');
        $deleteDay->execute([':location_key' => $locationKey]);
        $deleteWeek = $db->prepare('DELETE FROM weather_forecast_week_queue WHERE location_key = :location_key');
        $deleteWeek->execute([':location_key' => $locationKey]);

        $insertDay = $db->prepare(
            'INSERT INTO weather_forecast_day_queue
                (location_key, queue_position, forecast_time, temperature, humidity,
                 precipitation_probability, weather_type, snapshot_at)
             VALUES
                (:location_key, :queue_position, :forecast_time, :temperature, :humidity,
                 :precipitation_probability, :weather_type, :snapshot_at)'
        );
        foreach ($hourly as $position => $item) {
            $forecastTime = new DateTimeImmutable($item['time']);
            $insertDay->execute([
                ':location_key' => $locationKey,
                ':queue_position' => $position,
                ':forecast_time' => $forecastTime->format('Y-m-d H:i:s'),
                ':temperature' => $item['temperature'],
                ':humidity' => $item['humidity'],
                ':precipitation_probability' => $item['precipitation_probability'],
                ':weather_type' => $item['weather_type'],
                ':snapshot_at' => $snapshotAt,
            ]);
        }

        $insertWeek = $db->prepare(
            'INSERT INTO weather_forecast_week_queue
                (location_key, queue_position, forecast_date, temperature_max, temperature_min,
                 precipitation_probability, weather_type, snapshot_at)
             VALUES
                (:location_key, :queue_position, :forecast_date, :temperature_max, :temperature_min,
                 :precipitation_probability, :weather_type, :snapshot_at)'
        );
        foreach ($daily as $position => $item) {
            $insertWeek->execute([
                ':location_key' => $locationKey,
                ':queue_position' => $position,
                ':forecast_date' => $item['date'],
                ':temperature_max' => $item['temperature_max'],
                ':temperature_min' => $item['temperature_min'],
                ':precipitation_probability' => $item['precipitation_probability'],
                ':weather_type' => $item['weather_type'],
                ':snapshot_at' => $snapshotAt,
            ]);
        }

        $db->commit();
    } catch (Throwable $exception) {
        if ($db->inTransaction()) {
            $db->rollBack();
        }

        throw $exception;
    }
}

function loadForecastQueues(PDO $db, string $locationKey): ?array
{
    $dayStatement = $db->prepare(
        "SELECT queue_position, DATE_FORMAT(forecast_time, '%Y-%m-%dT%H:%i:%s+07:00') AS forecast_time,
                temperature, humidity, precipitation_probability, weather_type, snapshot_at
         FROM weather_forecast_day_queue
         WHERE location_key = :location_key
         ORDER BY queue_position"
    );
    $dayStatement->execute([':location_key' => $locationKey]);
    $hourlyRows = $dayStatement->fetchAll();

    $weekStatement = $db->prepare(
        "SELECT queue_position, DATE_FORMAT(forecast_date, '%Y-%m-%d') AS forecast_date,
                temperature_max, temperature_min, precipitation_probability, weather_type, snapshot_at
         FROM weather_forecast_week_queue
         WHERE location_key = :location_key
         ORDER BY queue_position"
    );
    $weekStatement->execute([':location_key' => $locationKey]);
    $dailyRows = $weekStatement->fetchAll();

    if (count($hourlyRows) !== 12 || count($dailyRows) !== 7) {
        return null;
    }

    $snapshotAt = $hourlyRows[0]['snapshot_at'];
    foreach ($hourlyRows as $position => $row) {
        if ((int) $row['queue_position'] !== $position || $row['snapshot_at'] !== $snapshotAt) {
            return null;
        }
    }
    foreach ($dailyRows as $position => $row) {
        if ((int) $row['queue_position'] !== $position || $row['snapshot_at'] !== $snapshotAt) {
            return null;
        }
    }

    $hourly = [];
    foreach ($hourlyRows as $row) {
        $hourly[] = [
            'time' => $row['forecast_time'],
            'temperature' => (float) $row['temperature'],
            'humidity' => (int) $row['humidity'],
            'precipitation_probability' => $row['precipitation_probability'] === null
                ? null
                : (int) $row['precipitation_probability'],
            'weather_type' => $row['weather_type'],
        ];
    }

    $daily = [];
    foreach ($dailyRows as $row) {
        $daily[] = [
            'date' => $row['forecast_date'],
            'temperature_max' => (float) $row['temperature_max'],
            'temperature_min' => (float) $row['temperature_min'],
            'precipitation_probability' => $row['precipitation_probability'] === null
                ? null
                : (int) $row['precipitation_probability'],
            'weather_type' => $row['weather_type'],
        ];
    }

    $cachedAt = new DateTimeImmutable($snapshotAt, new DateTimeZone('Asia/Ho_Chi_Minh'));

    return [
        'hourly' => $hourly,
        'daily' => $daily,
        'cached_at' => $cachedAt->format(DateTimeInterface::ATOM),
    ];
}
