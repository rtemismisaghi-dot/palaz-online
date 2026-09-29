<?php

namespace App\Http\Controllers;

use App\Agents\PalazAdvisorAgent;
use Illuminate\Http\Request;

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
        ]);

        $result = $agent->reply($data['message'], $data['messages'] ?? [], $data['context'] ?? []);

        return response()->json($result);
    }
}
