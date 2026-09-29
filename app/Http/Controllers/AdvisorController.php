<?php

namespace App\Http\Controllers;

use App\Agents\PalazAdvisorAgent;
use Illuminate\Http\Request;

final class AdvisorController extends Controller
{
    public function chat(Request $request, PalazAdvisorAgent $agent)
    {
        $data = $request->validate([
            'message' => ['required', 'string', 'max:1200'],
            'messages' => ['nullable', 'array', 'max:12'],
            'messages.*.role' => ['required', 'in:user,assistant'],
            'messages.*.content' => ['required', 'string', 'max:1200'],
        ]);

        $result = $agent->reply($data['message'], $data['messages'] ?? []);

        return response()->json($result);
    }
}
