<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

class PalazDtzClient
{
    public function createInstallation(array $data): array
    {
        $baseUrl = rtrim((string) config('services.dtz.url'), '/');
        $token = (string) config('services.dtz.integration_token');

        if ($baseUrl === '' || $token === '') {
            return [
                'success' => false,
                'message' => 'DTZ integration is not configured.',
            ];
        }

        try {
            $response = Http::withToken($token)
                ->acceptJson()
                ->timeout(20)
                ->post($baseUrl . '/api/palaz/installations', $data);

            if (! $response->successful()) {
                return [
                    'success' => false,
                    'message' => (string) data_get($response->json(), 'message', 'ارتباط با سیستم خدمات برقرار نشد.'),
                ];
            }

            return $response->json();
        } catch (ConnectionException $e) {
            report($e);

            return [
                'success' => false,
                'message' => 'سیستم خدمات در دسترس نیست.',
            ];
        } catch (\Throwable $e) {
            report($e);

            return [
                'success' => false,
                'message' => 'ایجاد درخواست نصب انجام نشد.',
            ];
        }
    }
}
