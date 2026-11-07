<?php
require_once __DIR__ . '/common/administrative_locations.php';
require_once __DIR__ . '/common/weather_queue.php';
require_once __DIR__ . '/common/forecast_queue.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');

$locations = require __DIR__ . '/common/weather_locations.php';
$locationKey = $_GET['location'] ?? 'ha-noi';
if (!is_string($locationKey)) {
    http_response_code(400);
    echo json_encode(['error' => 'Vị trí không hợp lệ.'], JSON_UNESCAPED_UNICODE);
    exit;
}

$location = $locations[$locationKey] ?? null;
if ($location === null && strpos($locationKey, 'ward-') === 0) {
    $location = getAdministrativeWards()[$locationKey] ?? null;
}
if ($location === null) {
    http_response_code(400);
    echo json_encode(['error' => 'Vị trí không hợp lệ.'], JSON_UNESCAPED_UNICODE);
    exit;
}

$locationName = isset($location['address'])
    ? $location['address']
    : $location['name'];
$locationData = [
    'key' => $locationKey,
    'name' => $locationName . ', Việt Nam',
    'latitude' => $location['latitude'],
    'longitude' => $location['longitude'],
];
$timezone = new DateTimeZone('Asia/Ho_Chi_Minh');
$now = new DateTimeImmutable('now', $timezone);
$nextHour = $now
    ->setTime((int) $now->format('H'), 0)
    ->modify('+1 hour');
$parameters = [
    'latitude' => $location['latitude'],
    'longitude' => $location['longitude'],
    'hourly' => 'temperature_2m,relative_humidity_2m,precipitation_probability,weather_code',
    'daily' => 'weather_code,temperature_2m_max,temperature_2m_min,precipitation_probability_max',
    'forecast_days' => 7,
    'timezone' => 'Asia/Ho_Chi_Minh',
];
$url = 'https://api.open-meteo.com/v1/forecast?' . http_build_query($parameters);
$context = stream_context_create([
    'http' => [
        'method' => 'GET',
        'timeout' => 20,
        'ignore_errors' => true,
        'header' => "Accept: application/json\r\nUser-Agent: WeatherBoard/1.0\r\n",
    ],
]);

try {
    $response = @file_get_contents($url, false, $context);
    $statusLine = $http_response_header[0] ?? '';
    if ($response === false || !preg_match('/\s2\d\d\s/', $statusLine)) {
        throw new RuntimeException('Không thể kết nối dịch vụ dự báo Open-Meteo. ' . $statusLine);
    }

    $payload = json_decode($response, true, 512, JSON_THROW_ON_ERROR);
    $hourly = $payload['hourly'] ?? null;
    $daily = $payload['daily'] ?? null;
    if (!is_array($hourly)
        || !is_array($daily)
        || !isset(
            $hourly['time'],
            $hourly['temperature_2m'],
            $hourly['relative_humidity_2m'],
            $hourly['precipitation_probability'],
            $hourly['weather_code'],
            $daily['time'],
            $daily['weather_code'],
            $daily['temperature_2m_max'],
            $daily['temperature_2m_min'],
            $daily['precipitation_probability_max']
        )
        || !is_array($hourly['time'])
        || !is_array($hourly['temperature_2m'])
        || !is_array($hourly['relative_humidity_2m'])
        || !is_array($hourly['precipitation_probability'])
        || !is_array($hourly['weather_code'])
        || !is_array($daily['time'])
        || !is_array($daily['weather_code'])
        || !is_array($daily['temperature_2m_max'])
        || !is_array($daily['temperature_2m_min'])
        || !is_array($daily['precipitation_probability_max'])
        || count($hourly['time']) !== count($hourly['temperature_2m'])
        || count($hourly['time']) !== count($hourly['relative_humidity_2m'])
        || count($hourly['time']) !== count($hourly['weather_code'])
        || count($hourly['time']) !== count($hourly['precipitation_probability'])
        || count($daily['time']) !== count($daily['weather_code'])
        || count($daily['time']) !== count($daily['temperature_2m_max'])
        || count($daily['time']) !== count($daily['temperature_2m_min'])
        || count($daily['time']) !== count($daily['precipitation_probability_max'])
    ) {
        throw new RuntimeException('Dữ liệu dự báo Open-Meteo không đầy đủ.');
    }

    $hourlyForecast = [];
    foreach ($hourly['time'] as $index => $time) {
        $hour = new DateTimeImmutable($time, $timezone);
        if ($hour < $nextHour) {
            continue;
        }

        $temperature = $hourly['temperature_2m'][$index];
        $humidity = $hourly['relative_humidity_2m'][$index];
        $precipitation = $hourly['precipitation_probability'][$index];
        $weatherCode = $hourly['weather_code'][$index];
        if (!is_numeric($temperature)
            || !is_numeric($humidity)
            || !is_numeric($weatherCode)
            || ($precipitation !== null && !is_numeric($precipitation))
            || !is_finite((float) $temperature)
            || !is_finite((float) $humidity)
            || (float) $humidity < 0
            || (float) $humidity > 100
            || ($precipitation !== null && ((float) $precipitation < 0 || (float) $precipitation > 100))
        ) {
            throw new RuntimeException('Open-Meteo trả về một mốc dự báo không hợp lệ.');
        }

        $hourlyForecast[] = [
            'time' => $hour->format(DateTimeInterface::ATOM),
            'temperature' => round((float) $temperature, 1),
            'humidity' => (int) round((float) $humidity),
            'precipitation_probability' => $precipitation === null ? null : (int) $precipitation,
            'weather_type' => weatherTypeFromCode((int) $weatherCode),
        ];
        if (count($hourlyForecast) === 12) {
            break;
        }
    }

    $weeklyForecast = [];
    foreach ($daily['time'] as $index => $date) {
        $maximum = $daily['temperature_2m_max'][$index];
        $minimum = $daily['temperature_2m_min'][$index];
        $precipitation = $daily['precipitation_probability_max'][$index];
        $weatherCode = $daily['weather_code'][$index];
        if (!is_numeric($maximum)
            || !is_numeric($minimum)
            || !is_numeric($weatherCode)
            || ($precipitation !== null && !is_numeric($precipitation))
            || !is_finite((float) $maximum)
            || !is_finite((float) $minimum)
            || ($precipitation !== null && ((float) $precipitation < 0 || (float) $precipitation > 100))
        ) {
            throw new RuntimeException('Open-Meteo trả về một ngày dự báo không hợp lệ.');
        }

        $weeklyForecast[] = [
            'date' => $date,
            'temperature_max' => round((float) $maximum, 1),
            'temperature_min' => round((float) $minimum, 1),
            'precipitation_probability' => $precipitation === null ? null : (int) $precipitation,
            'weather_type' => weatherTypeFromCode((int) $weatherCode),
        ];
        if (count($weeklyForecast) === 7) {
            break;
        }
    }

    if (count($hourlyForecast) !== 12 || count($weeklyForecast) !== 7) {
        throw new RuntimeException('Open-Meteo không trả đủ 12 mốc giờ hoặc 7 ngày dự báo.');
    }
} catch (Throwable $exception) {
    error_log('Open-Meteo forecast request failed: ' . $exception->getMessage());

    try {
        $db = require __DIR__ . '/common/DBconnect.php';
        $cachedForecast = loadForecastQueues($db, $locationKey);
    } catch (Throwable $cacheException) {
        error_log('Unable to load cached forecast: ' . $cacheException->getMessage());
        $cachedForecast = null;
    }

    if ($cachedForecast !== null) {
        echo json_encode([
            'location' => $locationData,
            'timezone' => 'Asia/Ho_Chi_Minh',
            'hourly' => $cachedForecast['hourly'],
            'daily' => $cachedForecast['daily'],
            'source' => 'cache',
            'cached_at' => $cachedForecast['cached_at'],
        ], JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
        exit;
    }

    http_response_code(502);
    echo json_encode([
        'error' => 'Open-Meteo hiện không khả dụng và chưa có dữ liệu dự báo đã lưu cho vị trí này.',
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$result = [
    'location' => $locationData,
    'timezone' => 'Asia/Ho_Chi_Minh',
    'hourly' => $hourlyForecast,
    'daily' => $weeklyForecast,
    'source' => 'open-meteo',
];

try {
    $db = require __DIR__ . '/common/DBconnect.php';
    saveForecastQueues($db, $locationKey, $hourlyForecast, $weeklyForecast);
    $result['cached_at'] = (new DateTimeImmutable('now', $timezone))->format(DateTimeInterface::ATOM);
} catch (Throwable $exception) {
    error_log('Unable to save forecast queue: ' . $exception->getMessage());
    $result['cache_warning'] = 'Không lưu được dự báo vào cơ sở dữ liệu để dùng khi mất kết nối.';
}

echo json_encode($result, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
