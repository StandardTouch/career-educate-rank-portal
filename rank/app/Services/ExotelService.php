<?php

namespace App\Services;

use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use InvalidArgumentException;
use RuntimeException;

class ExotelService
{
    public function callsForPhone(string $phone, int $page = 0, int $pageSize = 100): array
    {
        $accountSid = (string) config('services.exotel.account_sid', 'retailcenter1');
        $apiKey = (string) config('services.exotel.api_key');
        $apiToken = (string) config('services.exotel.api_token');
        $baseUrl = rtrim((string) config('services.exotel.base_url', 'https://api.exotel.com'), '/');

        if ($apiKey === '' || $apiToken === '') {
            throw new RuntimeException('Exotel API credentials are not configured.');
        }

        try {
            $response = Http::withBasicAuth($apiKey, $apiToken)
                ->acceptJson()
                ->timeout(20)
                ->get("{$baseUrl}/v1/Accounts/{$accountSid}/Calls.json", [
                    'From' => $phone,
                    'PageSize' => $pageSize,
                    'Page' => max(0, $page),
                ])
                ->throw();
        } catch (RequestException $exception) {
            $message = $exception->response?->json('RestException.Message')
                ?? $exception->response?->json('message')
                ?? $exception->getMessage();

            throw new RuntimeException($message, previous: $exception);
        }

        $payload = $response->json() ?? [];

        return [
            'calls' => collect(Arr::wrap($payload['Calls'] ?? $payload['Call'] ?? []))
                ->map(fn ($call) => is_array($call) ? $call : [])
                ->values()
                ->all(),
            'metadata' => $payload['Metadata'] ?? [],
            'raw' => $payload,
        ];
    }

    public function recording(string $recordingUrl): Response
    {
        $apiKey = (string) config('services.exotel.api_key');
        $apiToken = (string) config('services.exotel.api_token');
        $host = parse_url($recordingUrl, PHP_URL_HOST);

        if ($apiKey === '' || $apiToken === '') {
            throw new RuntimeException('Exotel API credentials are not configured.');
        }

        if (!in_array($host, ['recordings.exotel.com', 'api.exotel.com'], true)) {
            throw new InvalidArgumentException('Invalid Exotel recording URL.');
        }

        try {
            return Http::withBasicAuth($apiKey, $apiToken)
                ->timeout(30)
                ->get($recordingUrl)
                ->throw();
        } catch (RequestException $exception) {
            $message = $exception->response?->json('RestException.Message')
                ?? $exception->response?->json('message')
                ?? $exception->getMessage();

            throw new RuntimeException($message, previous: $exception);
        }
    }

    public function analyzeCall(array $call): array
    {
        $accountSid = (string) config('services.exotel.account_sid', 'retailcenter1');
        $apiKey = (string) config('services.exotel.api_key');
        $apiToken = (string) config('services.exotel.api_token');
        $baseUrl = rtrim((string) config('services.exotel.base_url', 'https://api.exotel.com'), '/');
        $url = (string) (config('services.exotel.voice_analyze_url') ?: "{$baseUrl}/v1/Accounts/{$accountSid}/ExoVoiceAnalyze.json");
        $format = (string) config('services.exotel.voice_analyze_format', 'form');

        if ($apiKey === '' || $apiToken === '') {
            throw new RuntimeException('Exotel API credentials are not configured.');
        }

        $payload = array_filter([
            'CallSid' => $call['call_sid'] ?? null,
            'RecordingUrl' => $call['recording_url'] ?? null,
            'From' => $call['from'] ?? null,
            'To' => $call['to'] ?? null,
            'Direction' => $call['direction'] ?? null,
            'Status' => $call['status'] ?? null,
            'Duration' => $call['duration'] ?? null,
            'StartTime' => $call['start_time'] ?? null,
            'EndTime' => $call['end_time'] ?? null,
        ], fn ($value) => filled($value));

        if (empty($payload['CallSid']) && empty($payload['RecordingUrl'])) {
            throw new RuntimeException('Call SID or recording URL is required for transcript analysis.');
        }

        try {
            $request = Http::withBasicAuth($apiKey, $apiToken)
                ->acceptJson()
                ->timeout(60);

            $response = ($format === 'json' ? $request->asJson() : $request->asForm())
                ->post($url, $payload)
                ->throw();
        } catch (RequestException $exception) {
            $message = $exception->response?->json('RestException.Message')
                ?? $exception->response?->json('message')
                ?? $exception->getMessage();

            throw new RuntimeException($message, previous: $exception);
        }

        return $response->json() ?? ['raw' => $response->body()];
    }
}
