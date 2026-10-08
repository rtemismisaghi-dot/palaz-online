<?php

namespace App\Http\Controllers;

use App\Agents\PalazAdvisorAgent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

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

    public function chat(Request $request, PalazAdvisorAgent $agent)
    {
        $data = $request->validate([
            'message' => ['required', 'string', 'max:1200'],
            'messages' => ['nullable', 'array', 'max:12'],
            'messages.*.role' => ['required', 'in:user,assistant'],
            'messages.*.content' => ['required', 'string', 'max:1200'],
            'context' => ['nullable', 'array'],
            'context.surface' => ['nullable', 'string', 'max:40'],
            'context.space_analyzed' => ['nullable', 'boolean'],
            'context.product' => ['nullable', 'array'],
            'context.product.id' => ['nullable', 'string', 'max:100'],
            'context.product.name' => ['nullable', 'string', 'max:160'],
            'context.product.tone' => ['nullable', 'string', 'max:80'],
            'context.product.model' => ['nullable', 'string', 'max:120'],
            'context.product.code' => ['nullable', 'string', 'max:100'],
            'context.product.category' => ['nullable', 'string', 'max:80'],
            'context.product.price' => ['nullable', 'numeric', 'min:0'],
            'context.product.unit' => ['nullable', 'string', 'max:80'],
            'context.compare' => ['nullable', 'array', 'max:2'],
            'context.compare.*.id' => ['nullable', 'string', 'max:100'],
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
