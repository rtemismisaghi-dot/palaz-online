<?php

namespace App\Http\Controllers;

use App\Agents\PalazAdvisorAgent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

final class AdvisorController extends Controller
{
    public function analyzeSpace(Request $request, PalazAdvisorAgent $agent)
    {
        $request->validate([
            'image' => ['required', 'file', 'image', 'mimes:jpeg,jpg,png,webp', 'max:8192'],
        ]);

        $file = $request->file('image');
        $mime = $file->getMimeType() ?: $file->getClientMimeType();
        $dataUrl = 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($file->getRealPath()));

        return response()->json($agent->analyzeSpace($dataUrl));
    }
    public function analyzeDemo(Request $request, PalazAdvisorAgent $agent)
    {
        $data = $request->validate([
            'url' => ['required', 'url', 'max:2048'],
        ]);

        $url = $data['url'];
        $host = strtolower((string) parse_url($url, PHP_URL_HOST));
        $allowedHosts = [
            'd2xsxph8kpxj0f.cloudfront.net', 'www.welcome-fukuoka.or.jp',
            'alpha-tex.com', 'shawfloors.widen.net', 'embed.widencdn.net',
            'www.floorworld.com', 'thepanipathandloom.com', 'api.kasperkent.be',
            'www.toli.co.jp', 'www.tarketthospitality.com',
        ];
        abort_unless(in_array($host, $allowedHosts, true), 422, 'demo_image_host_not_allowed');

        $response = Http::timeout(20)->accept('*/*')->get($url);
        if (!$response->successful()) {
            return response()->json(['message' => 'demo_image_fetch_failed'], 422);
        }

        $mime = strtolower((string) ($response->header('Content-Type') ?: 'image/jpeg'));
        $mime = str_contains($mime, 'png') ? 'image/png' : (str_contains($mime, 'webp') ? 'image/webp' : 'image/jpeg');
        $dataUrl = 'data:' . $mime . ';base64,' . base64_encode($response->body());

        return response()->json($agent->analyzeSpace($dataUrl));
    }

    /**
     * Generate Persian speech on the server so customers do not need any local voice installed.
     * The provider key and a Persian-capable female voice ID stay in server environment variables.
     */
    public function speech(Request $request)
    {
        $data = $request->validate([
            'text' => ['required', 'string', 'max:1800'],
        ]);

        $apiKey = trim((string) config('services.elevenlabs.key', ''));
        $voiceId = trim((string) config('services.elevenlabs.voice_id', ''));

        if ($apiKey === '' || $voiceId === '') {
            return response()->json(['message' => 'speech_service_not_configured'], 503);
        }

        $text = trim((string) preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $data['text']));
        if ($text === '') {
            return response()->json(['message' => 'speech_text_empty'], 422);
        }

        try {
            $response = Http::withHeaders([
                'xi-api-key' => $apiKey,
                'Accept' => 'audio/mpeg',
            ])
                ->connectTimeout(8)
                ->timeout(45)
                ->post(
                    'https://api.elevenlabs.io/v1/text-to-speech/'.rawurlencode($voiceId).'?output_format=mp3_44100_128',
                    [
                        'text' => $text,
                        'model_id' => (string) config('services.elevenlabs.model', 'eleven_v3'),
                        'language_code' => 'fa',
                    ]
                );

            $contentType = strtolower((string) $response->header('Content-Type'));
            if (
                ! $response->successful()
                || (! str_contains($contentType, 'audio') && ! str_contains($contentType, 'octet-stream'))
                || $response->body() === ''
            ) {
                Log::warning('Palaz advisor speech synthesis failed', [
                    'http_status' => $response->status(),
                    'provider_message' => mb_substr((string) $response->body(), 0, 350),
                    'voice_configured' => $voiceId !== '',
                ]);

                return response()->json(['message' => 'speech_generation_failed'], 503);
            }

            return response($response->body(), 200, [
                'Content-Type' => 'audio/mpeg',
                'Cache-Control' => 'no-store',
                'X-Content-Type-Options' => 'nosniff',
            ]);
        } catch (\Throwable $e) {
            Log::warning('Palaz advisor speech synthesis exception', [
                'exception' => $e->getMessage(),
            ]);
            report($e);

            return response()->json(['message' => 'speech_generation_failed'], 503);
        }
    }

    public function chat(Request $request, PalazAdvisorAgent $agent)
    {
        $data = $request->validate([
            'message' => ['required', 'string', 'max:1200'],
            'messages' => ['nullable', 'array', 'max:20'],
            'messages.*.role' => ['required', 'in:user,assistant'],
            'messages.*.content' => ['required', 'string', 'max:1200'],
            'context' => ['nullable', 'array'],
            'context.surface' => ['nullable', 'string', 'max:40'],
            'context.space_analyzed' => ['nullable', 'boolean'],
            'context.product' => ['nullable', 'array'],
            'context.product.id' => ['nullable'],
            'context.product.name' => ['nullable', 'string', 'max:160'],
            'context.product.tone' => ['nullable', 'string', 'max:80'],
            'context.product.model' => ['nullable', 'string', 'max:120'],
            'context.product.code' => ['nullable', 'string', 'max:100'],
            'context.product.category' => ['nullable', 'string', 'max:80'],
            'context.product.price' => ['nullable', 'numeric', 'min:0'],
            'context.product.unit' => ['nullable', 'string', 'max:80'],
            'context.compare' => ['nullable', 'array', 'max:2'],
            'context.compare.*.id' => ['nullable'],
            'context.compare.*.name' => ['nullable', 'string', 'max:160'],
            'context.compare.*.tone' => ['nullable', 'string', 'max:80'],
            'context.compare.*.model' => ['nullable', 'string', 'max:120'],
            'context.compare.*.code' => ['nullable', 'string', 'max:100'],
            'context.compare.*.category' => ['nullable', 'string', 'max:80'],
            'context.compare.*.price' => ['nullable', 'numeric', 'min:0'],
            'context.compare.*.unit' => ['nullable', 'string', 'max:80'],
        ]);

        try {
            $result = $agent->reply($data['message'], $data['messages'] ?? [], $data['context'] ?? []);

            return response()->json($result);
        } catch (\Throwable $e) {
            report($e);

            return response()->json([
                'reply' => 'ارتباط با سرویس هوشمند موقتاً با مشکل روبه‌رو شد. اما من اینجا هستم؛ لطفاً سؤال‌تان را دوباره بفرستید.',
                'mode' => 'fallback',
                'actions' => [],
            ], 200);
        }
    }
}
