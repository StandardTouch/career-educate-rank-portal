<?php

namespace App\Services;

use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
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
        $apiKey = (string) config('services.exotel.api_key');
        $apiToken = (string) config('services.exotel.api_token');
        $url = (string) config('services.exotel.voice_analyze_url');
        $format = (string) config('services.exotel.voice_analyze_format', 'form');
        $method = strtoupper((string) config('services.exotel.voice_analyze_method', 'POST'));
        $extraParams = $this->voiceAnalyzeExtraParams();

        if ($apiKey === '' || $apiToken === '') {
            throw new RuntimeException('Exotel API credentials are not configured.');
        }

        if ($url === '') {
            throw new RuntimeException('Exotel Voice Analyze URL is not configured. Set EXOTEL_VOICE_ANALYZE_URL to the endpoint provided by Exotel.');
        }

        $callSid = $call['call_sid'] ?? null;
        $recordingUrl = $call['recording_url'] ?? null;

        $payload = array_filter([
            'CallSid' => $callSid,
            'callSid' => $callSid,
            'call_sid' => $callSid,
            'callsid' => $callSid,
            'Sid' => $callSid,
            'sid' => $callSid,
            'CallUUID' => $callSid,
            'call_uuid' => $callSid,
            'RecordingUrl' => $recordingUrl,
            'RecordingURL' => $recordingUrl,
            'recordingUrl' => $recordingUrl,
            'recordingURL' => $recordingUrl,
            'recording_url' => $recordingUrl,
            'recordingurl' => $recordingUrl,
            'Recording' => $recordingUrl,
            'recording' => $recordingUrl,
            'AudioUrl' => $recordingUrl,
            'AudioURL' => $recordingUrl,
            'audioUrl' => $recordingUrl,
            'audioURL' => $recordingUrl,
            'audio_url' => $recordingUrl,
            'audio' => $recordingUrl,
            'FileUrl' => $recordingUrl,
            'file_url' => $recordingUrl,
            'MediaUrl' => $recordingUrl,
            'media_url' => $recordingUrl,
            'Url' => $recordingUrl,
            'URL' => $recordingUrl,
            'url' => $recordingUrl,
            'From' => $call['from'] ?? null,
            'from' => $call['from'] ?? null,
            'To' => $call['to'] ?? null,
            'to' => $call['to'] ?? null,
            'Direction' => $call['direction'] ?? null,
            'direction' => $call['direction'] ?? null,
            'Status' => $call['status'] ?? null,
            'status' => $call['status'] ?? null,
            'Duration' => $call['duration'] ?? null,
            'duration' => $call['duration'] ?? null,
            'StartTime' => $call['start_time'] ?? null,
            'start_time' => $call['start_time'] ?? null,
            'EndTime' => $call['end_time'] ?? null,
            'end_time' => $call['end_time'] ?? null,
        ], fn ($value) => filled($value));

        $payload = array_merge($payload, $extraParams);

        if (empty($payload['CallSid']) && empty($payload['RecordingUrl'])) {
            throw new RuntimeException('Call SID or recording URL is required for transcript analysis.');
        }

        $url = strtr($url, [
            '{CallSid}' => rawurlencode((string) ($payload['CallSid'] ?? '')),
            '{callSid}' => rawurlencode((string) ($payload['CallSid'] ?? '')),
            '{call_sid}' => rawurlencode((string) ($payload['CallSid'] ?? '')),
            '{Sid}' => rawurlencode((string) ($payload['CallSid'] ?? '')),
            '{sid}' => rawurlencode((string) ($payload['CallSid'] ?? '')),
            '{RecordingUrl}' => rawurlencode((string) ($payload['RecordingUrl'] ?? '')),
            '{recordingUrl}' => rawurlencode((string) ($payload['RecordingUrl'] ?? '')),
            '{recording_url}' => rawurlencode((string) ($payload['RecordingUrl'] ?? '')),
        ]);

        if (preg_match('#/Calls(?:\.json)?/?$#', parse_url($url, PHP_URL_PATH) ?: '') === 1) {
            throw new RuntimeException('EXOTEL_VOICE_ANALYZE_URL points to the regular Calls endpoint, which does not accept transcript requests. Use the ExoVoiceAnalyze endpoint provided by Exotel, not /Calls.');
        }

        try {
            $request = Http::withBasicAuth($apiKey, $apiToken)
                ->acceptJson()
                ->retry(2, 1000)
                ->timeout(60);

            $request = $format === 'json' ? $request->asJson() : $request->asForm();
            $response = match ($method) {
                'GET' => $request->get($url, $payload),
                'PUT' => $request->put($url, $payload),
                default => $request->post($url, $payload),
            };

            $response->throw();
        } catch (RequestException $exception) {
            $message = $this->errorMessage($exception, array_keys($payload));

            throw new RuntimeException($message, previous: $exception);
        }

        return $response->json() ?? ['raw' => $response->body()];
    }

    private function errorMessage(RequestException $exception, array $payloadKeys = []): string
    {
        $response = $exception->response;
        $json = $response?->json();

        if (is_array($json)) {
            if (!empty($json['cloudflare_error'])) {
                $retryAfter = $json['retry_after'] ?? null;
                $retryText = $retryAfter ? " Retry after {$retryAfter} seconds." : '';

                return 'Exotel Voice Analyze is temporarily unavailable or the endpoint is incorrect.' . $retryText;
            }

            $message = $json['RestException']['Message']
                ?? $json['RestException.Message']
                ?? $json['message']
                ?? $json['detail']
                ?? $json['title']
                ?? $exception->getMessage();

            if (Str::contains($message, 'Mandatory Parameter missing', true)) {
                $keys = implode(', ', $payloadKeys);

                return "Exotel says a mandatory parameter is missing. Sent parameter keys: {$keys}. Confirm the exact required parameter name with Exotel for this ExoVoiceAnalyze endpoint.";
            }

            return $message;
        }

        $body = (string) $response?->body();

        if (Str::contains($body, 'Method not allowed', true) || Str::contains($body, 'Method not al', true)) {
            return 'Exotel rejected the transcript request method. Check EXOTEL_VOICE_ANALYZE_URL and EXOTEL_VOICE_ANALYZE_METHOD; the regular /Calls endpoint is not the ExoVoiceAnalyze transcript endpoint.';
        }

        if (Str::contains($body, 'cloudflare', true) || Str::contains($body, 'Bad gateway', true)) {
            return 'Exotel Voice Analyze is temporarily unavailable or the endpoint is incorrect. Please retry after a minute.';
        }

        return $exception->getMessage();
    }

    private function voiceAnalyzeExtraParams(): array
    {
        $raw = config('services.exotel.voice_analyze_extra_params');

        if (!is_string($raw) || trim($raw) === '') {
            return [];
        }

        $decoded = json_decode($raw, true);

        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            return array_filter($decoded, fn ($value) => filled($value));
        }

        return collect(explode(',', $raw))
            ->mapWithKeys(function (string $pair) {
                [$key, $value] = array_pad(explode('=', $pair, 2), 2, null);

                return trim((string) $key) !== '' ? [trim((string) $key) => trim((string) $value)] : [];
            })
            ->filter(fn ($value) => filled($value))
            ->all();
    }
}
