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
    public function callsForExophone(string $exophone, int $page = 0, int $pageSize = 100, array $filters = []): array
    {
        $page = max(0, $page);
        $pageSize = max(10, min(100, $pageSize));
        $apiPageSize = 100;
        $apiPage = intdiv($page * $pageSize, $apiPageSize);
        $pageOffset = ($page * $pageSize) % $apiPageSize;
        $variants = $this->phoneVariants($exophone);
        $baseQuery = $this->callFilterQuery($filters, $exophone);
        $queryFields = ['VirtualNumber', 'Exophone', 'PhoneNumber', 'To', 'From'];
        $queryEntries = collect($queryFields)
            ->flatMap(fn ($field) => collect($variants)->map(fn ($phone) => [
                'trusted' => in_array($field, ['VirtualNumber', 'Exophone', 'PhoneNumber'], true),
                'query' => array_merge($baseQuery, [
                    $field => $phone,
                    'PageSize' => $apiPageSize,
                    'Page' => $apiPage,
                ]),
            ]))
            ->push([
                'trusted' => false,
                'query' => array_merge($baseQuery, [
                    'PageSize' => $apiPageSize,
                    'Page' => $apiPage,
                ]),
            ])
            ->unique(fn ($entry) => json_encode($entry['query']))
            ->values();

        $responses = $queryEntries->map(function ($entry) {
            $response = $this->calls($entry['query']);
            $response['trusted'] = $entry['trusted'];

            return $response;
        });

        $matchedCalls = $responses
            ->flatMap(fn ($response) => collect($response['calls'])->map(fn ($call) => [
                'call' => $call,
                'trusted' => $response['trusted'],
            ]))
            ->filter(fn ($item) => ($item['trusted'] && $this->callLooksLikeCareerEducate($item['call'])) || $this->callMatchesExophone($item['call'], $variants))
            ->pluck('call')
            ->filter(fn ($call) => $this->callMatchesFilters($call, $filters))
            ->unique(fn ($call) => $call['Sid'] ?? $call['CallSid'] ?? md5(json_encode($call)))
            ->sortByDesc(fn ($call) => strtotime((string) ($call['StartTime'] ?? $call['DateCreated'] ?? '')) ?: 0)
            ->values();

        $calls = $matchedCalls
            ->slice($pageOffset, $pageSize)
            ->values()
            ->all();

        $incomingTotal = $matchedCalls->filter(fn ($call) => str_contains(strtolower((string) ($call['Direction'] ?? '')), 'in'))->count();
        $outgoingTotal = $matchedCalls->filter(fn ($call) => str_contains(strtolower((string) ($call['Direction'] ?? '')), 'out'))->count();
        $hasNextPage = $responses->contains(fn ($response) => !empty($response['metadata']['NextPageUri'] ?? null));
        $total = ($apiPage * $apiPageSize) + $matchedCalls->count();
        $hasMoreLocalPages = $pageOffset + $pageSize < $matchedCalls->count();

        return [
            'calls' => $calls,
            'metadata' => [
                'Total' => $total,
                'PageSize' => $pageSize,
                'Page' => $page,
                'IncomingTotal' => $incomingTotal,
                'OutgoingTotal' => $outgoingTotal,
                'NextPageUri' => ($hasMoreLocalPages || $hasNextPage) ? 'next' : null,
                'Start' => $total > 0 ? ($page * $pageSize) + 1 : 0,
                'End' => min(($page * $pageSize) + count($calls), $total),
            ],
            'raw' => [
                'queries' => $queryEntries->pluck('query')->all(),
                'responses' => $responses->pluck('raw')->all(),
            ],
        ];
    }

    public function callsForPhone(string $phone, int $page = 0, int $pageSize = 100): array
    {
        return $this->calls([
            'From' => $phone,
            'PageSize' => $pageSize,
            'Page' => max(0, $page),
        ]);
    }

    private function calls(array $query): array
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
                ->get("{$baseUrl}/v1/Accounts/{$accountSid}/Calls.json", $query)
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

    private function callFilterQuery(array $filters, string $exophone): array
    {
        $query = [];
        $searchParts = ['vn:' . $this->phoneDigits($exophone)];

        if (filled($filters['start_date'] ?? null) || filled($filters['end_date'] ?? null)) {
            $start = filled($filters['start_date'] ?? null)
                ? date('d-m-Y 00:00:00', strtotime((string) $filters['start_date']))
                : '01-01-1970 00:00:00';
            $end = filled($filters['end_date'] ?? null)
                ? date('d-m-Y 23:59:59', strtotime((string) $filters['end_date']))
                : date('d-m-Y 23:59:59');

            $searchParts[] = "created:{$start}..{$end}";
            $query['DateCreated>'] = date('Y-m-d 00:00:00', strtotime((string) ($filters['start_date'] ?? '1970-01-01')));
            $query['DateCreated<'] = date('Y-m-d 23:59:59', strtotime((string) ($filters['end_date'] ?? date('Y-m-d'))));
            $query['StartTime'] = date('Y-m-d 00:00:00', strtotime((string) ($filters['start_date'] ?? '1970-01-01')));
            $query['EndTime'] = date('Y-m-d 23:59:59', strtotime((string) ($filters['end_date'] ?? date('Y-m-d'))));
        }

        if (filled($filters['status'] ?? null)) {
            $query['Status'] = (string) $filters['status'];
        }

        if (filled($filters['direction'] ?? null)) {
            $query['Direction'] = (string) $filters['direction'];
        }

        $query['Search'] = implode(',', $searchParts);

        return array_filter($query, fn ($value) => filled($value));
    }

    private function callMatchesFilters(array $call, array $filters): bool
    {
        if (filled($filters['phone'] ?? null)) {
            $needle = $this->phoneDigits((string) $filters['phone']);
            $from = $this->phoneDigits((string) ($call['From'] ?? ''));
            $to = $this->phoneDigits((string) ($call['To'] ?? ''));

            if ($needle !== '' && !str_contains($from, $needle) && !str_contains($to, $needle)) {
                return false;
            }
        }

        if (filled($filters['status'] ?? null) && strcasecmp((string) ($call['Status'] ?? ''), (string) $filters['status']) !== 0) {
            return false;
        }

        if (filled($filters['direction'] ?? null)) {
            $direction = strtolower((string) ($call['Direction'] ?? ''));
            $expected = strtolower((string) $filters['direction']);

            if (!str_contains($direction, $expected)) {
                return false;
            }
        }

        $timestamp = strtotime((string) ($call['StartTime'] ?? $call['DateCreated'] ?? ''));

        if (filled($filters['start_date'] ?? null) && $timestamp && $timestamp < strtotime((string) $filters['start_date'] . ' 00:00:00')) {
            return false;
        }

        if (filled($filters['end_date'] ?? null) && $timestamp && $timestamp > strtotime((string) $filters['end_date'] . ' 23:59:59')) {
            return false;
        }

        return true;
    }

    private function callMatchesExophone(array $call, array $variants): bool
    {
        $needles = collect($variants)->map(fn ($phone) => $this->phoneDigits($phone))->filter()->unique();
        $fields = [
            'From',
            'To',
            'PhoneNumber',
            'PhoneNumberSid',
            'VirtualNumber',
            'Exophone',
            'DialWhomNumber',
        ];

        foreach ($fields as $field) {
            $digits = $this->phoneDigits((string) ($call[$field] ?? ''));

            if ($digits !== '' && $needles->contains($digits)) {
                return true;
            }
        }

        return false;
    }

    private function callLooksLikeCareerEducate(array $call): bool
    {
        $careerFields = Arr::only($call, [
            'AppName',
            'ApplicationName',
            'App',
            'Flow',
            'FlowName',
            'CallFlow',
            'CallFlowName',
            'Exophone',
            'VirtualNumber',
            'PhoneNumber',
        ]);
        $filledCareerFields = array_filter($careerFields, fn ($value) => filled($value));

        if ($filledCareerFields === []) {
            return true;
        }

        $haystack = strtolower(json_encode($filledCareerFields) ?: '');

        return str_contains($haystack, 'careereducate') || str_contains($haystack, 'career educate');
    }

    private function phoneVariants(string $phone): array
    {
        $digits = $this->phoneDigits($phone);
        $variants = collect([$phone, $digits]);

        if (strlen($digits) === 11 && str_starts_with($digits, '0')) {
            $withoutTrunk = substr($digits, 1);
            $variants->push(substr($digits, 0, 3) . '-' . substr($digits, 3, 3) . '-' . substr($digits, 6));
            $variants->push($withoutTrunk);
            $variants->push('91' . $withoutTrunk);
            $variants->push('+91' . $withoutTrunk);
        }

        return $variants
            ->map(fn ($value) => trim((string) $value))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    private function phoneDigits(string $phone): string
    {
        return preg_replace('/\D+/', '', $phone) ?? '';
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
        $format = (string) config('services.exotel.voice_analyze_format', 'json');
        $method = strtoupper((string) config('services.exotel.voice_analyze_method', 'POST'));
        $callbackUrl = (string) config('services.exotel.voice_analyze_callback_url');
        $tasks = $this->voiceAnalyzeTasks();
        $extraParams = $this->voiceAnalyzeExtraParams();

        if ($apiKey === '' || $apiToken === '') {
            throw new RuntimeException('Exotel API credentials are not configured.');
        }

        if ($url === '') {
            throw new RuntimeException('Exotel Voice Analyze URL is not configured. Set EXOTEL_VOICE_ANALYZE_URL to the endpoint provided by Exotel.');
        }

        $callSid = $call['call_sid'] ?? null;

        if (!filled($callSid)) {
            throw new RuntimeException('Call SID is required for transcript analysis.');
        }

        if (!filled($callbackUrl)) {
            throw new RuntimeException('Exotel Voice Analyze callback URL is not configured. Set EXOTEL_VOICE_ANALYZE_CALLBACK_URL.');
        }

        $payload = array_merge([
            'callback_url' => $callbackUrl,
            'insight_tasks' => $tasks,
            'task_id' => $this->voiceAnalyzeTaskId((string) $callSid),
        ], $extraParams);

        $url = strtr($url, [
            '{CallSid}' => rawurlencode((string) $callSid),
            '{callSid}' => rawurlencode((string) $callSid),
            '{call_sid}' => rawurlencode((string) $callSid),
            '{Sid}' => rawurlencode((string) $callSid),
            '{sid}' => rawurlencode((string) $callSid),
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

    private function voiceAnalyzeTasks(): array
    {
        $raw = config('services.exotel.voice_analyze_tasks', 'transcript');

        if (is_array($raw)) {
            return array_values(array_filter($raw, fn ($task) => filled($task)));
        }

        $decoded = is_string($raw) ? json_decode($raw, true) : null;

        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            return array_values(array_filter($decoded, fn ($task) => filled($task)));
        }

        return collect(explode(',', (string) $raw))
            ->map(fn ($task) => trim($task))
            ->filter()
            ->values()
            ->all() ?: ['transcript'];
    }

    private function voiceAnalyzeTaskId(string $callSid): string
    {
        return 'transcript_' . Str::slug($callSid, '_') . '_' . now()->format('YmdHis');
    }
}
