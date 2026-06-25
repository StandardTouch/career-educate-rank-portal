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
}
