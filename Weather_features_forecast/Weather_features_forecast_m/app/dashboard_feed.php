<?php
require_once __DIR__ . '/common/functions.php';
require_once __DIR__ . '/common/weather_queue.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');

$locations = require __DIR__ . '/common/weather_locations.php';
$locationList = [];

foreach ($locations as $key => $location) {
    $locationList[] = [
        'key' => $key,
        'name' => $location['name'],
        'latitude' => $location['latitude'],
        'longitude' => $location['longitude'],
    ];
}

if (($_GET['action'] ?? '') === 'locations') {
    $preferredLocationKey = 'ha-noi';
    $userAddress = $_SESSION['user']['address'] ?? null;
    if (is_string($userAddress)) {
        $normalizeAddress = static function (string $address): string {
            $address = trim($address);
            return function_exists('mb_strtolower')
                ? mb_strtolower($address, 'UTF-8')
                : strtolower($address);
        };
        $normalizedUserAddress = $normalizeAddress($userAddress);
        $legacyAddressToCurrentProvince = [
            'bà rịa - vũng tàu' => 'Hồ Chí Minh',
            'bạc liêu' => 'Cà Mau',
            'bắc giang' => 'Bắc Ninh',
            'bắc kạn' => 'Thái Nguyên',
            'bến tre' => 'Vĩnh Long',
            'bình định' => 'Gia Lai',
            'bình dương' => 'Hồ Chí Minh',
            'bình phước' => 'Đồng Nai',
            'bình thuận' => 'Lâm Đồng',
            'đắk nông' => 'Lâm Đồng',
            'hà giang' => 'Tuyên Quang',
            'hà nam' => 'Ninh Bình',
            'hải dương' => 'Hải Phòng',
            'hậu giang' => 'Cần Thơ',
            'hòa bình' => 'Phú Thọ',
            'kiên giang' => 'An Giang',
            'kon tum' => 'Quảng Ngãi',
            'long an' => 'Tây Ninh',
            'nam định' => 'Ninh Bình',
            'ninh thuận' => 'Khánh Hòa',
            'phú yên' => 'Đắk Lắk',
            'quảng bình' => 'Quảng Trị',
            'quảng nam' => 'Đà Nẵng',
            'sóc trăng' => 'Cần Thơ',
            'thái bình' => 'Hưng Yên',
            'tiền giang' => 'Đồng Tháp',
            'tp. hồ chí minh' => 'Hồ Chí Minh',
            'trà vinh' => 'Vĩnh Long',
            'vĩnh phúc' => 'Phú Thọ',
            'yên bái' => 'Lào Cai',
        ];
        $normalizedUserAddress = $normalizeAddress(
            $legacyAddressToCurrentProvince[$normalizedUserAddress] ?? $userAddress
        );

        foreach ($locations as $key => $location) {
            if ($normalizeAddress($location['name']) === $normalizedUserAddress) {
                $preferredLocationKey = $key;
                break;
            }
        }
    }

    echo json_encode([
        'locations' => $locationList,
        'preferred_location_key' => $preferredLocationKey,
    ], JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
    exit;
}

$locationKey = $_GET['location'] ?? 'ha-noi';
if (!is_string($locationKey) || !isset($locations[$locationKey])) {
    http_response_code(400);
    echo json_encode(['error' => 'Vị trí không hợp lệ.'], JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    $db = getDatabaseConnection();
    $sourceStatus = 'online';
    try {
        ensureWeatherQueues($db, $locations);
    } catch (WeatherProviderException $exception) {
        error_log($exception->getMessage());
        $sourceStatus = 'stale';
    }

    $location = $locations[$locationKey];
    $readingsStatement = $db->prepare(
        'SELECT recorded_at, temperature, humidity, weather_type
         FROM weather_minute_queue
         WHERE location = :location
         ORDER BY recorded_at DESC
         LIMIT 720'
    );
    $readingsStatement->execute([':location' => $location['name']]);
    $readings = array_reverse($readingsStatement->fetchAll());
    $latestReading = $readings[count($readings) - 1] ?? null;

    if ($latestReading === null) {
        throw new RuntimeException('Hàng đợi chưa có dữ liệu thời tiết.');
    }

    echo json_encode([
        'location' => [
            'key' => $locationKey,
            'name' => $location['name'] . ', Việt Nam',
            'latitude' => $location['latitude'],
            'longitude' => $location['longitude'],
        ],
        'temperature' => (float) $latestReading['temperature'],
        'humidity' => (float) $latestReading['humidity'],
        'weather_type' => $latestReading['weather_type'],
        'recorded_at' => $latestReading['recorded_at'],
        'source_status' => $sourceStatus,
        'readings' => array_map(static function (array $reading): array {
            return [
                'temperature' => (float) $reading['temperature'],
                'humidity' => (float) $reading['humidity'],
                'recorded_at' => $reading['recorded_at'],
            ];
        }, $readings),
    ], JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
} catch (Throwable $exception) {
    error_log($exception->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Không thể cập nhật hàng đợi dữ liệu thời tiết.'], JSON_UNESCAPED_UNICODE);
}
