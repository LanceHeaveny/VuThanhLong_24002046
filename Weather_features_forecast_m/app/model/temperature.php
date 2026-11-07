<?php

class Temperature
{
    public int $id;
    public string $sensor_id;
    public float $value;
    public string $unit;
    public string $recorded_at;
    public string $location;
    /** Difference from the previous reading, expressed in the current unit. */
    public ?float $temperature_change = null;
    /** Converted temperature in degrees Fahrenheit. */
    public float $fahrenheit;

    public function __construct(
        int $id,
        string $sensor_id,
        float $value,
        string $unit,
        string $recorded_at,
        string $location,
        ?float $previous_value = null
    ) {
        $this->validateTemperature($value, $unit);

        if ($previous_value !== null) {
            $this->validateTemperature($previous_value, $unit);
        }

        $this->id = $id;
        $this->sensor_id = $sensor_id;
        $this->value = $value;
        $this->unit = $unit;
        $this->recorded_at = $recorded_at;
        $this->location = $location;
        $this->fahrenheit = $this->convertToFahrenheit($value, $unit);
        $this->temperature_change = $previous_value === null ? null : $value - $previous_value;
    }

    public function updateMeasurement(float $value, string $recorded_at, ?string $unit = null): void
    {
        $unit = $unit ?? $this->unit;
        $this->validateTemperature($value, $unit);
        $previousValue = $this->convertTemperature($this->value, $this->unit, $unit);
        $fahrenheit = $this->convertToFahrenheit($value, $unit);

        $this->temperature_change = $value - $previousValue;
        $this->value = $value;
        $this->unit = $unit;
        $this->fahrenheit = $fahrenheit;
        $this->recorded_at = $recorded_at;
    }

    private function validateTemperature(float $value, string $unit): void
    {
        if (!is_finite($value)) {
            throw new InvalidArgumentException('Giá trị nhiệt độ phải là một số hữu hạn.');
        }

        if (!in_array($unit, ['°C', 'C', '°F', 'F'], true)) {
            throw new InvalidArgumentException('Đơn vị nhiệt độ chỉ hỗ trợ °C hoặc °F.');
        }

        $celsius = in_array($unit, ['°F', 'F'], true)
            ? ($value - 32) * 5 / 9
            : $value;

        if ($celsius < -273.15) {
            throw new InvalidArgumentException('Nhiệt độ không thể thấp hơn độ không tuyệt đối.');
        }
    }

    private function convertToFahrenheit(float $value, string $unit): float
    {
        return in_array($unit, ['°F', 'F'], true)
            ? $value
            : ($value * 9 / 5) + 32;
    }

    private function convertTemperature(float $value, string $fromUnit, string $toUnit): float
    {
        $celsius = in_array($fromUnit, ['°F', 'F'], true)
            ? ($value - 32) * 5 / 9
            : $value;

        return in_array($toUnit, ['°F', 'F'], true)
            ? ($celsius * 9 / 5) + 32
            : $celsius;
    }
}