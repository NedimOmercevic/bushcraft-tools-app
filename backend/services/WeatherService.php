<?php

declare(strict_types=1);

namespace App\Services;

final class WeatherService
{
    public function current(mixed $city): array
    {
        $city = Validation::text($city, 'city', 100);
        $configPath = __DIR__ . '/../config/config.php';
        $config = is_readable($configPath) ? require $configPath : [];
        $weather = $config['openweather'] ?? [];
        $key = $weather['api_key'] ?? '';
        if ($key === '' || $key === 'your_openweather_api_key' || !function_exists('curl_init')) {
            $this->fail('OpenWeather key or PHP cURL extension is missing.', 503);
        }

        $url = rtrim($weather['base_url'] ?? 'https://api.openweathermap.org/data/2.5', '/')
            . '/weather?' . http_build_query(['q' => $city, 'appid' => $key, 'units' => 'metric']);
        $request = curl_init($url);
        if ($request === false) {
            $this->fail('Could not initialize HTTP request.', 503);
        }
        curl_setopt_array($request, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => 3,
            CURLOPT_TIMEOUT => 6,
        ]);
        $body = curl_exec($request);
        $status = curl_getinfo($request, CURLINFO_RESPONSE_CODE);
        $error = curl_errno($request);
        curl_close($request);

        if ($error === CURLE_OPERATION_TIMEDOUT) {
            $this->fail('Request timed out.', 504);
        }
        if ($status === 404) {
            $this->fail('City not found (upstream HTTP 404).', 404);
        }
        if ($body === false || $status !== 200) {
            $this->fail("OpenWeather request failed (HTTP $status, cURL $error).", 502);
        }

        $data = json_decode($body, true);
        $temperature = $data['main']['temp'] ?? null;
        $wind = $data['wind']['speed'] ?? null;
        $condition = $data['weather'][0]['description'] ?? null;
        $location = $data['name'] ?? null;
        if (!is_numeric($temperature) || !is_numeric($wind) || !is_string($condition) || !is_string($location)) {
            $this->fail('OpenWeather returned an invalid weather payload.', 502);
        }

        $precipitation = (float) ($data['rain']['1h'] ?? 0) + (float) ($data['snow']['1h'] ?? 0);
        $tips = [];
        if ($wind >= 8) {
            $tips[] = 'Pick a sheltered campsite and secure loose equipment.';
        }
        if ($precipitation > 0 || in_array($data['weather'][0]['main'] ?? '', ['Rain', 'Drizzle', 'Snow', 'Thunderstorm'], true)) {
            $tips[] = 'Pack a waterproof layer and keep spare clothing dry.';
        }
        if ($temperature <= 5) {
            $tips[] = 'Bring an insulated sleeping pad and warm layers.';
        }
        if ($tips === []) {
            $tips[] = 'Check the local forecast before setting out and pack a spare layer.';
        }

        return [
            'city' => $location,
            'temperature_c' => (float) $temperature,
            'wind_kmh' => round((float) $wind * 3.6, 1),
            'precipitation_mm_1h' => $precipitation,
            'condition' => $condition,
            'tips' => $tips,
        ];
    }

    private function fail(string $reason, int $status): never
    {
        $message = '[OpenWeather] ' . $reason;
        $entry = '[' . date('Y-m-d H:i:s') . '] ERROR ' . $message . PHP_EOL;
        if (!@error_log($entry, 3, __DIR__ . '/../logs/error.log')) {
            error_log($message);
        }

        throw new WeatherException($status === 404
            ? 'City not found. Try a city name with its country code, such as Sarajevo,BA.'
            : 'Weather is temporarily unavailable. Please try again later.', $status);
    }
}
