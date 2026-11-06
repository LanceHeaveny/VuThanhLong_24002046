<?php

const WEATHER_QUEUE_MINUTES = 720;

class WeatherProviderException extends RuntimeException
{
}

function weatherTypeFromCode(int $code): string
{
    return match ($code) {
        0 => 'trời quang',
        1 => 'ít mây',
        2 => 'mây rải rác',
        3 => 'nhiều mây',
        45, 48 => 'sương mù',
        51, 53, 55, 56, 57 => 'mưa phùn',
        61, 63 => 'mưa',
        65, 66, 67 => 'mưa lớn',
        71, 73, 75, 77, 85, 86 => 'tuyết',
        80, 81 => 'mưa rào',
        82 => 'mưa rào lớn',
        95, 96, 99 => 'dông',
        default => throw new WeatherProviderException('Dữ liệu thời tiết trả về mã không hợp lệ.'),
    };
}

function fetchOnlineWeatherReadings(
    array $locations,
    DateTimeImmutable $currentMinute,
    bool $includeHistory = false
): array
{
    $coordinates = array_values($locations);
    $parameters = [
        'latitude' => implode(',', array_column($coordinates, 'latitude')),
        'longitude' => implode(',', array_column($coordinates, 'longitude')),
        'current' => 'temperature_2m,relative_humidity_2m,weather_code',
        'timezone' => 'Asia/Ho_Chi_Minh',
    ];
    if ($includeHistory) {
        $parameters['hourly'] = 'temperature_2m,relative_humidity_2m,weather_code';
        $parameters['past_days'] = 1;
        $parameters['forecast_days'] = 1;
    }

    $url = 'https://api.open-meteo.com/v1/forecast?' . http_build_query($parameters);
    $context = stream_context_create([
        'http' => [
            'method' => 'GET',
            'timeout' => 20,
            'ignore_errors' => true,
            'header' => "Accept: application/json\r\nUser-Agent: WeatherBoard/1.0\r\n",
        ],
    ]);
    $response = @file_get_contents($url, false, $context);
    $statusLine = $http_response_header[0] ?? '';

    if ($response === false || !preg_match('/\s2\d\d\s/', $statusLine)) {
        throw new WeatherProviderException('Không thể kết nối dịch vụ thời tiết trực tuyến.');
    }

    try {
        $payload = json_decode($response, true, 512, JSON_THROW_ON_ERROR);
    } catch (JsonException $exception) {
        throw new WeatherProviderException('Dịch vụ thời tiết trả về dữ liệu không hợp lệ.', 0, $exception);
    }

    if (!is_array($payload) || count($payload) !== count($locations)) {
        throw new WeatherProviderException('Dịch vụ thời tiết không trả đủ dữ liệu cho các tỉnh, thành phố.');
    }

    $readings = [];
    foreach (array_keys($locations) as $index => $key) {
        $current = $payload[$index]['current'] ?? null;
        if (!is_array($current)) {
            throw new WeatherProviderException('Thiếu số liệu hiện tại cho một tỉnh, thành phố.');
        }

        $temperature = $current['temperature_2m'] ?? null;
        $humidity = $current['relative_humidity_2m'] ?? null;
        $weatherCode = $current['weather_code'] ?? null;
        if (!is_numeric($temperature)
            || !is_numeric($humidity)
            || !is_numeric($weatherCode)
            || !is_finite((float) $temperature)
            || !is_finite((float) $humidity)
            || (float) $humidity < 0
            || (float) $humidity > 100
        ) {
            throw new WeatherProviderException('Dịch vụ thời tiết trả về số liệu không hợp lệ.');
        }

        $reading = [
            'temperature' => round((float) $temperature, 2),
            'humidity' => round((float) $humidity, 2),
            'weather_type' => weatherTypeFromCode((int) $weatherCode),
        ];

        if ($includeHistory) {
            $hourly = $payload[$index]['hourly'] ?? null;
            if (!is_array($hourly)
                || !isset($hourly['time'], $hourly['temperature_2m'], $hourly['relative_humidity_2m'], $hourly['weather_code'])
                || count($hourly['time']) !== count($hourly['temperature_2m'])
                || count($hourly['time']) !== count($hourly['relative_humidity_2m'])
                || count($hourly['time']) !== count($hourly['weather_code'])
            ) {
                throw new WeatherProviderException('Dữ liệu thời tiết lịch sử không đầy đủ.');
            }

            $hourlyReadings = [];
            foreach ($hourly['time'] as $hourIndex => $hour) {
                $hourTemperature = $hourly['temperature_2m'][$hourIndex];
                $hourHumidity = $hourly['relative_humidity_2m'][$hourIndex];
                $hourCode = $hourly['weather_code'][$hourIndex];
                if (!is_numeric($hourTemperature)
                    || !is_numeric($hourHumidity)
                    || !is_numeric($hourCode)
                    || (float) $hourHumidity < 0
                    || (float) $hourHumidity > 100
                ) {
                    throw new WeatherProviderException('Dữ liệu thời tiết lịch sử không hợp lệ.');
                }

                $hourlyReadings[] = [
                    'time' => new DateTimeImmutable($hour),
                    'temperature' => round((float) $hourTemperature, 2),
                    'humidity' => round((float) $hourHumidity, 2),
                    'weather_type' => weatherTypeFromCode((int) $hourCode),
                ];
            }

            $historyStart = $currentMinute->modify('-' . (WEATHER_QUEUE_MINUTES - 1) . ' minutes');
            $history = [];
            $hourIndex = 0;
            $lastHourlyReading = null;
            for ($minuteOffset = 0; $minuteOffset < WEATHER_QUEUE_MINUTES; $minuteOffset++) {
                $minute = $historyStart->modify('+' . $minuteOffset . ' minutes');
                while (isset($hourlyReadings[$hourIndex]) && $hourlyReadings[$hourIndex]['time'] <= $minute) {
                    $lastHourlyReading = $hourlyReadings[$hourIndex];
                    $hourIndex++;
                }

                if ($lastHourlyReading === null) {
                    throw new WeatherProviderException('Không đủ dữ liệu lịch sử để khởi tạo hàng đợi 720 phút.');
                }

                $history[] = [
                    'temperature' => $lastHourlyReading['temperature'],
                    'humidity' => $lastHourlyReading['humidity'],
                    'weather_type' => $lastHourlyReading['weather_type'],
                ];
            }
            $history[WEATHER_QUEUE_MINUTES - 1] = $reading;
            $reading['history'] = $history;
        }

        $readings[$key] = $reading;
    }

    return $readings;
}

function ensureWeatherQueues(PDO $db, array $locations): void
{
    date_default_timezone_set('Asia/Ho_Chi_Minh');
    $db->exec("SET time_zone = '+07:00'");

    $lock = $db->query("SELECT GET_LOCK('weather_queue_generation', 10)")->fetchColumn();
    if ((int) $lock !== 1) {
        throw new RuntimeException('Không lấy được khóa cập nhật hàng đợi thời tiết.');
    }

    try {
        $db->beginTransaction();
        $currentMinute = new DateTimeImmutable($db->query("SELECT DATE_FORMAT(NOW(), '%Y-%m-%d %H:%i:00')")->fetchColumn());
        $ensureLocationStatement = $db->prepare(
            'INSERT IGNORE INTO weather_locations (name) VALUES (:name)'
        );
        foreach ($locations as $location) {
            $ensureLocationStatement->execute([':name' => $location['name']]);
        }

        $latestStatement = $db->prepare(
            'SELECT recorded_at, sensor_id, temperature, humidity, weather_type
             FROM weather_minute_queue
             WHERE location = :location
             ORDER BY recorded_at DESC
             LIMIT 1'
        );
        $needsOnlineReadings = false;
        $needsHistorySeed = false;
        $oldestRetainedMinute = $currentMinute->modify('-' . (WEATHER_QUEUE_MINUTES - 1) . ' minutes');
        foreach ($locations as $location) {
            $latestStatement->execute([':location' => $location['name']]);
            $latest = $latestStatement->fetch();
            if ($latest === false) {
                $needsOnlineReadings = true;
                $needsHistorySeed = true;
                continue;
            }

            if ($latest['recorded_at'] < $currentMinute->format('Y-m-d H:i:s')
                || strpos($latest['sensor_id'], 'ONLINE-') !== 0
            ) {
                $needsOnlineReadings = true;
            }
            if ($latest['recorded_at'] < $oldestRetainedMinute->format('Y-m-d H:i:s')
                || strpos($latest['sensor_id'], 'ONLINE-') !== 0
            ) {
                $needsHistorySeed = true;
            }
        }
        $onlineReadings = $needsOnlineReadings
            ? fetchOnlineWeatherReadings($locations, $currentMinute, $needsHistorySeed)
            : [];

        $insertStatement = $db->prepare(
            'INSERT INTO weather_minute_queue
                (location, recorded_at, sensor_id, temperature, humidity, temperature_amplitude, weather_type)
             VALUES
                (:location, :recorded_at, :sensor_id, :temperature, :humidity, :amplitude, :weather_type)
             ON DUPLICATE KEY UPDATE
                sensor_id = VALUES(sensor_id),
                temperature = VALUES(temperature),
                humidity = VALUES(humidity),
                temperature_amplitude = VALUES(temperature_amplitude),
                weather_type = VALUES(weather_type)'
        );
        $countStatement = $db->prepare(
            'SELECT COUNT(*) FROM weather_minute_queue WHERE location = :location'
        );
        $deleteOldMinutesStatement = $db->prepare(
            'DELETE FROM weather_minute_queue
             WHERE location = :location
             ORDER BY recorded_at ASC
             LIMIT :excess'
        );
        $clearLocationStatement = $db->prepare('DELETE FROM weather_minute_queue WHERE location = :location');
        $clearHourlyHistoryStatement = $db->prepare('DELETE FROM weather_hourly_history WHERE location = :location');

        foreach ($locations as $key => $location) {
            $latestStatement->execute([':location' => $location['name']]);
            $latest = $latestStatement->fetch() ?: null;
            $sensorId = 'ONLINE-' . strtoupper(str_replace('-', '', $key));

            if ($latest !== null
                && (strpos($latest['sensor_id'], 'ONLINE-') !== 0
                    || $latest['recorded_at'] < $oldestRetainedMinute->format('Y-m-d H:i:s'))
            ) {
                $clearLocationStatement->execute([':location' => $location['name']]);
                $clearHourlyHistoryStatement->execute([':location' => $location['name']]);
                $latest = null;
            }

            if ($latest === null) {
                $history = $onlineReadings[$key]['history'] ?? [$onlineReadings[$key]];
                $historyStart = $currentMinute->modify('-' . (count($history) - 1) . ' minutes');
                $nextMinute = $historyStart;
                $previousTemperature = $history[0]['temperature'];
                $previousHumidity = $history[0]['humidity'];
                $previousWeatherType = $history[0]['weather_type'];
                $isSeedingHistory = true;
            } else {
                $latestMinute = new DateTimeImmutable($latest['recorded_at']);
                $nextMinute = $latestMinute->modify('+1 minute');
                $previousTemperature = (float) $latest['temperature'];
                $previousHumidity = (float) $latest['humidity'];
                $previousWeatherType = $latest['weather_type'];
                $isSeedingHistory = false;
                if ($latestMinute == $currentMinute && strpos($latest['sensor_id'], 'ONLINE-') !== 0) {
                    $nextMinute = $currentMinute;
                }
                $oldestRetainedMinute = $currentMinute->modify('-' . (WEATHER_QUEUE_MINUTES - 1) . ' minutes');

                if ($nextMinute < $oldestRetainedMinute) {
                    $clearLocationStatement->execute([':location' => $location['name']]);
                    $clearHourlyHistoryStatement->execute([':location' => $location['name']]);
                    $nextMinute = $currentMinute;
                    $previousTemperature = $onlineReadings[$key]['temperature'];
                    $previousHumidity = $onlineReadings[$key]['humidity'];
                }
            }

            while ($nextMinute <= $currentMinute) {
                $isCurrentMinute = $nextMinute == $currentMinute;
                if ($isSeedingHistory) {
                    $historyOffset = (int) (($nextMinute->getTimestamp() - $historyStart->getTimestamp()) / 60);
                    $reading = $history[$historyOffset];
                } elseif ($isCurrentMinute) {
                    $reading = $onlineReadings[$key];
                } else {
                    $reading = [
                        'temperature' => $previousTemperature,
                        'humidity' => $previousHumidity,
                        'weather_type' => $previousWeatherType,
                    ];
                }
                $insertStatement->execute([
                    ':location' => $location['name'],
                    ':recorded_at' => $nextMinute->format('Y-m-d H:i:s'),
                    ':sensor_id' => $sensorId,
                    ':temperature' => $reading['temperature'],
                    ':humidity' => $reading['humidity'],
                    ':amplitude' => round(abs($reading['temperature'] - $previousTemperature), 2),
                    ':weather_type' => $reading['weather_type'],
                ]);
                $previousTemperature = $reading['temperature'];
                $previousHumidity = $reading['humidity'];
                $previousWeatherType = $reading['weather_type'];
                $nextMinute = $nextMinute->modify('+1 minute');
            }

            $countStatement->execute([':location' => $location['name']]);
            $excess = (int) $countStatement->fetchColumn() - WEATHER_QUEUE_MINUTES;
            if ($excess > 0) {
                $deleteOldMinutesStatement->bindValue(':location', $location['name']);
                $deleteOldMinutesStatement->bindValue(':excess', $excess, PDO::PARAM_INT);
                $deleteOldMinutesStatement->execute();
            }

            updateHourlyWeatherHistory($db, $location['name'], $currentMinute);
        }

        $db->commit();
    } catch (Throwable $exception) {
        if ($db->inTransaction()) {
            $db->rollBack();
        }

        throw $exception;
    } finally {
        $db->query("SELECT RELEASE_LOCK('weather_queue_generation')");
    }
}

function updateHourlyWeatherHistory(PDO $db, string $location, DateTimeImmutable $currentMinute): void
{
    $latestHourStatement = $db->prepare(
        'SELECT MAX(recorded_hour)
         FROM weather_hourly_history
         WHERE location = :location'
    );
    $latestHourStatement->execute([':location' => $location]);
    $latestHour = $latestHourStatement->fetchColumn();
    $currentHour = $currentMinute->setTime((int) $currentMinute->format('H'), 0, 0);
    $firstHour = $currentHour->modify('-' . (WEATHER_QUEUE_MINUTES / 60 - 1) . ' hours');
    $latestHourTime = $latestHour === false || $latestHour === null
        ? null
        : new DateTimeImmutable($latestHour);
    $nextHour = $latestHourTime === null || $latestHourTime < $firstHour
        ? $firstHour
        : $latestHourTime->modify('+1 hour');

    $hourReadingStatement = $db->prepare(
        'SELECT temperature, humidity, weather_type
         FROM weather_minute_queue
         WHERE location = :location AND recorded_at = :recorded_at
         LIMIT 1'
    );
    $rangeStatement = $db->prepare(
        'SELECT MAX(temperature) - MIN(temperature)
         FROM weather_minute_queue
         WHERE location = :location
           AND recorded_at > :range_start
           AND recorded_at <= :range_end'
    );
    $insertHourStatement = $db->prepare(
        'INSERT INTO weather_hourly_history
            (location, recorded_hour, temperature, humidity, temperature_amplitude, weather_type)
         VALUES
            (:location, :recorded_hour, :temperature, :humidity, :amplitude, :weather_type)
         ON DUPLICATE KEY UPDATE
            temperature = VALUES(temperature),
            humidity = VALUES(humidity),
            temperature_amplitude = VALUES(temperature_amplitude),
            weather_type = VALUES(weather_type)'
    );
    $deleteOldHoursStatement = $db->prepare(
        'DELETE FROM weather_hourly_history
         WHERE location = :location
         ORDER BY recorded_hour ASC
         LIMIT 1'
    );

    while ($nextHour <= $currentHour) {
        $recordedHour = $nextHour->format('Y-m-d H:i:s');
        $hourReadingStatement->execute([
            ':location' => $location,
            ':recorded_at' => $recordedHour,
        ]);
        $reading = $hourReadingStatement->fetch();

        if ($reading !== false) {
            $rangeStatement->execute([
                ':location' => $location,
                ':range_start' => $nextHour->modify('-1 hour')->format('Y-m-d H:i:s'),
                ':range_end' => $recordedHour,
            ]);
            $amplitude = $rangeStatement->fetchColumn();

            $insertHourStatement->execute([
                ':location' => $location,
                ':recorded_hour' => $recordedHour,
                ':temperature' => $reading['temperature'],
                ':humidity' => $reading['humidity'],
                ':amplitude' => $amplitude === null ? 0 : $amplitude,
                ':weather_type' => $reading['weather_type'],
            ]);
        }

        $nextHour = $nextHour->modify('+1 hour');
    }

    $hourCountStatement = $db->prepare(
        'SELECT COUNT(*) FROM weather_hourly_history WHERE location = :location'
    );
    $hourCountStatement->execute([':location' => $location]);
    $excessHours = (int) $hourCountStatement->fetchColumn() - 12;

    for ($index = 0; $index < $excessHours; $index++) {
        $deleteOldHoursStatement->execute([':location' => $location]);
    }
}
