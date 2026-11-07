<?php

function getAdministrativeWards(): array
{
    static $wards;
    if ($wards !== null) {
        return $wards;
    }

    $dataPath = __DIR__ . '/../data/vietnam_wards.json';
    $contents = file_get_contents($dataPath);
    if ($contents === false) {
        throw new RuntimeException('Không thể đọc danh sách xã, phường.');
    }

    try {
        $records = json_decode($contents, true, 512, JSON_THROW_ON_ERROR);
    } catch (JsonException $exception) {
        throw new RuntimeException('Danh sách xã, phường không hợp lệ.', 0, $exception);
    }
    if (!is_array($records)) {
        throw new RuntimeException('Danh sách xã, phường không hợp lệ.');
    }

    $wards = [];
    foreach ($records as $record) {
        if (!is_array($record)
            || !isset(
                $record['id'],
                $record['name'],
                $record['province'],
                $record['latitude'],
                $record['longitude']
            )
            || !is_string($record['id'])
            || !is_string($record['name'])
            || !is_string($record['province'])
            || !is_numeric($record['latitude'])
            || !is_numeric($record['longitude'])
        ) {
            throw new RuntimeException('Một mục trong danh sách xã, phường không hợp lệ.');
        }

        $key = 'ward-' . $record['id'];
        if (isset($wards[$key])) {
            throw new RuntimeException('Mã xã, phường bị trùng trong danh sách.');
        }

        $wards[$key] = [
            'key' => $key,
            'name' => $record['name'],
            'province' => $record['province'],
            'address' => $record['name'] . ', ' . $record['province'],
            'latitude' => (float) $record['latitude'],
            'longitude' => (float) $record['longitude'],
        ];
    }

    return $wards;
}

function findNearestWeatherLocation(float $latitude, float $longitude, array $locations): array
{
    $nearestKey = null;
    $nearestLocation = null;
    $nearestDistance = INF;

    foreach ($locations as $key => $location) {
        $latitudeDelta = deg2rad($location['latitude'] - $latitude);
        $longitudeDelta = deg2rad($location['longitude'] - $longitude);
        $latitudeRadians = deg2rad($latitude);
        $locationLatitudeRadians = deg2rad($location['latitude']);
        $haversine = sin($latitudeDelta / 2) ** 2
            + cos($latitudeRadians) * cos($locationLatitudeRadians)
                * sin($longitudeDelta / 2) ** 2;
        $distance = 6371 * 2 * asin(min(1, sqrt($haversine)));

        if ($distance < $nearestDistance) {
            $nearestKey = $key;
            $nearestLocation = $location;
            $nearestDistance = $distance;
        }
    }

    if ($nearestLocation === null || $nearestKey === null) {
        throw new RuntimeException('Không có điểm thời tiết để chọn làm nguồn dữ liệu.');
    }

    return [
        'key' => $nearestKey,
        'location' => $nearestLocation,
        'distance_km' => round($nearestDistance, 1),
    ];
}

function resolveWeatherLocationForAddress(string $address, array $locations): array
{
    $normalizedAddress = normalizeLocationName($address);
    foreach ($locations as $key => $location) {
        if (normalizeLocationName($location['name']) === $normalizedAddress) {
            return [
                'key' => $key,
                'location' => $location,
                'distance_km' => 0.0,
            ];
        }
    }

    foreach (getAdministrativeWards() as $ward) {
        if (normalizeLocationName($ward['address']) === $normalizedAddress) {
            return findNearestWeatherLocation(
                $ward['latitude'],
                $ward['longitude'],
                $locations
            );
        }
    }

    throw new InvalidArgumentException('Địa chỉ tài khoản không có trong danh sách địa điểm hỗ trợ.');
}

function normalizeLocationName(string $name): string
{
    $name = trim($name);

    return function_exists('mb_strtolower')
        ? mb_strtolower($name, 'UTF-8')
        : strtolower($name);
}
