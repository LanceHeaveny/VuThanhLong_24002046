<?php

class Humidity
{
    public int $id;
    public string $sensor_id;
    public float $value;
    public string $unit;
    public string $recorded_at;
    public string $location;
    /** Change from the previous reading, in percentage points. */
    public ?float $humidity_change = null;
    /** Calculated in grams per cubic meter (g/m³). */
    public ?float $absolute_humidity = null;
    public ?float $relative_humidity = null;
    /** Temperature in degrees Celsius, used to calculate absolute humidity. */
    public ?float $temperature = null;

    public function __construct(
        int $id,
        string $sensor_id,
        float $value,
        string $unit,
        string $recorded_at,
        string $location,
        ?float $temperature = null,
        ?float $previous_humidity = null
    ) {
        $this->id = $id;
        $this->sensor_id = $sensor_id;
        $this->value = $value;
        $this->unit = $unit;
        $this->recorded_at = $recorded_at;
        $this->location = $location;
        $this->temperature = $temperature;

        if ($unit === '%') {
            $this->validateRelativeHumidity($value);
            if ($previous_humidity !== null) {
                $this->validateRelativeHumidity($previous_humidity);
            }
            $this->relative_humidity = $value;
            $this->humidity_change = $previous_humidity === null ? null : $value - $previous_humidity;
            $this->absolute_humidity = $temperature === null
                ? null
                : $this->calculateAbsoluteHumidity($value, $temperature);
        }
    }

    public function updateMeasurement(float $relative_humidity, float $temperature, string $recorded_at): void
    {
        $this->validateRelativeHumidity($relative_humidity);
        $absolute_humidity = $this->calculateAbsoluteHumidity($relative_humidity, $temperature);
        $this->humidity_change = $this->relative_humidity === null
            ? null
            : $relative_humidity - $this->relative_humidity;
        $this->value = $relative_humidity;
        $this->unit = '%';
        $this->relative_humidity = $relative_humidity;
        $this->temperature = $temperature;
        $this->recorded_at = $recorded_at;
        $this->absolute_humidity = $absolute_humidity;
    }

    public function updateTemperature(float $temperature, string $recorded_at): void
    {
        $absolute_humidity = $this->relative_humidity === null
            ? null
            : $this->calculateAbsoluteHumidity($this->relative_humidity, $temperature);
        $this->temperature = $temperature;
        $this->recorded_at = $recorded_at;
        $this->absolute_humidity = $absolute_humidity;
    }

    private function validateRelativeHumidity(float $relative_humidity): void
    {
        if ($relative_humidity < 0 || $relative_humidity > 100) {
            throw new InvalidArgumentException('Độ ẩm tương đối phải nằm trong khoảng 0–100%.');
        }
    }

    private function calculateAbsoluteHumidity(float $relative_humidity, float $temperature): float
    {
        if ($temperature <= -243.5) {
            throw new InvalidArgumentException('Nhiệt độ phải lớn hơn -243.5°C để tính độ ẩm tuyệt đối.');
        }

        $saturationVaporPressure = 6.112 * exp(
            (17.67 * $temperature) / ($temperature + 243.5)
        );
        $vaporPressure = ($relative_humidity / 100) * $saturationVaporPressure;

        return 216.7 * $vaporPressure / ($temperature + 273.15);
    }
}