<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\ServiceRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class CustomerInstallationController extends Controller
{
    public function prepare(Request $request, Order $order)
    {
        $this->authorizeOrder($request, $order);

        abort_unless($order->service === 'installation', 404);

        $service = ServiceRequest::query()
            ->where('order_id', $order->id)
            ->where('type', 'installation')
            ->latest()
            ->first();

        abort_unless($service && $service->external_id, 404);

        $quote = $this->dtzQuote((int) $service->external_id);
        abort_unless($quote['success'] ?? false, 502);

        $payload = is_array($quote['quote_payload'] ?? null) ? $quote['quote_payload'] : [];
        $rolls = is_array($payload['rolls'] ?? null) ? $payload['rolls'] : [];
        $purchasedArea = (float) ($payload['purchased_area'] ?? 0);
        $installationAmount = $purchasedArea > 0 ? $purchasedArea * 385000 : (float) ($quote['total_amount'] ?? 0);

        return view('account.installation', [
            'order' => $order->load('items'),
            'service' => $service,
            'trackingCode' => $quote['tracking_code'] ?? $service->tracking_code,
            'rolls' => $rolls,
            'purchasedArea' => $purchasedArea,
            'installationAmount' => $installationAmount,
            'existingFinal' => $payload['final_quote'] ?? [],
        ]);
    }

    public function complete(Request $request, Order $order)
    {
        $this->authorizeOrder($request, $order);
        abort_unless($order->service === 'installation', 404);

        $data = $request->validate([
            'installation_address' => ['required', 'string', 'max:1000'],
            'measurement' => ['required', 'in:not_needed,needed'],
            'preferred_date' => ['nullable', 'string', 'max:100'],
            'preferred_time' => ['nullable', 'string', 'max:100'],
            'floor' => ['nullable', 'string', 'max:50'],
            'elevator' => ['required', 'in:yes,no'],
            'site_contact' => ['nullable', 'string', 'max:120'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $service = ServiceRequest::query()
            ->where('order_id', $order->id)
            ->where('type', 'installation')
            ->latest()
            ->first();

        abort_unless($service && $service->external_id, 404);

        $quote = $this->dtzQuote((int) $service->external_id);
        abort_unless($quote['success'] ?? false, 502);

        $payload = is_array($quote['quote_payload'] ?? null) ? $quote['quote_payload'] : [];
        $purchasedArea = (float) ($payload['purchased_area'] ?? 0);
        $installationAmount = $purchasedArea > 0
            ? $purchasedArea * 385000
            : (float) ($quote['total_amount'] ?? 0);

        $finalPayload = [
            'customer_flow' => 'palaz_online',
            'installation_address' => $data['installation_address'],
            'measurement' => $data['measurement'],
            'preferred_date' => $data['preferred_date'] ?? null,
            'preferred_time' => $data['preferred_time'] ?? null,
            'floor' => $data['floor'] ?? null,
            'elevator' => $data['elevator'],
            'site_contact' => $data['site_contact'] ?? null,
            'notes' => $data['notes'] ?? null,
            'purchased_area' => $purchasedArea,
            'rolls' => $payload['rolls'] ?? [],
        ];

        $response = Http::withToken((string) config('services.dtz.palaz_token'))
            ->acceptJson()
            ->timeout(15)
            ->post(
                rtrim((string) config('services.dtz.url'), '/') . '/api/palaz/installations/' . (int) $service->external_id . '/complete',
                [
                    'total_amount' => $installationAmount,
                    'payload' => $finalPayload,
                ]
            );

        abort_unless($response->successful() && ($response->json('success') ?? false), 502);

        $result = $response->json();

        $service->update([
            'status' => 'forwarded',
            'description' => trim(($service->description ?? '') . "
آدرس نصب: " . $data['installation_address']),
        ]);

        return redirect()
            ->route('account.installation', ['order' => $order->id])
            ->with('installation_completed', true)
            ->with('installation_tracking', $result['tracking_code'] ?? $quote['tracking_code'] ?? null)
            ->with('installation_amount', (float) ($result['total_amount'] ?? $installationAmount));
    }

    private function dtzQuote(int $installationId): array
    {
        $response = Http::withToken((string) config('services.dtz.palaz_token'))
            ->acceptJson()
            ->timeout(15)
            ->get(
                rtrim((string) config('services.dtz.url'), '/') . '/api/palaz/installations/' . $installationId . '/quote'
            );

        abort_unless($response->successful(), 502);

        return (array) $response->json();
    }

    private function authorizeOrder(Request $request, Order $order): void
    {
        $mobile = (string) $request->session()->get('customer_mobile', '');
        abort_unless($request->session()->has('customer_user_id') && $mobile !== '' && $order->phone === $mobile, 403);
    }
}
