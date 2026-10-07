<?php

namespace App\Http\Controllers;

use App\Models\InstallationQuote;
use App\Models\Order;
use App\Services\DtzInstallationCalculator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;

class InstallationController extends Controller
{
    public function prepare(Request $request, InstallationQuote $quote)
    {
        abort_unless($request->hasValidSignature(), 403);
        abort_unless($quote->order && $quote->order->service === 'installation', 404);

        $quote->load('order.items');

        return view('store.installation', [
            'quote' => $quote,
            'order' => $quote->order,
            'rolls' => data_get($quote->payload, 'rolls', []),
            'completeUrl' => URL::temporarySignedRoute(
                'checkout.installation.complete',
                now()->addHours(4),
                ['quote' => $quote->id]
            ),
        ]);
    }

    public function complete(Request $request, InstallationQuote $quote, DtzInstallationCalculator $calculator)
    {
        abort_unless($request->hasValidSignature(), 403);
        abort_unless($quote->order && $quote->order->service === 'installation', 404);

        $validated = $request->validate([
            'payload' => ['required', 'array'],
            'payload.rolls' => ['nullable', 'array'],
            'payload.floors' => ['nullable', 'array'],
            'payload.glue' => ['nullable', 'array'],
            'payload.stairs' => ['nullable', 'array'],
            'payload.fish' => ['nullable', 'array'],
            'payload.special' => ['nullable', 'array'],
            'payload.location' => ['nullable', 'array'],
            'payload.options' => ['nullable', 'array'],
        ]);

        $storedRolls = collect(data_get($quote->payload, 'rolls', []))
            ->map(fn (array $roll) => [
                'width' => (float) ($roll['width'] ?? 3),
                'length' => (float) ($roll['length'] ?? 1),
                'quantity' => (int) ($roll['quantity'] ?? 1),
                'area' => (float) ($roll['area'] ?? 0),
                'code' => $roll['code'] ?? null,
                'name' => $roll['name'] ?? null,
                'model' => $roll['model'] ?? null,
            ])->values()->all();

        // Customer input can describe the installation, but can never change
        // the purchased rolls that determine the installation price.
        $payload = $validated['payload'];
        $payload['rolls'] = $storedRolls;

        $result = $calculator->calculate($payload);

        DB::transaction(function () use ($quote, $payload, $result) {
            $quote->update([
                'payload' => array_merge($quote->payload ?? [], [
                    'calculation' => $result,
                    'customer_input' => $payload,
                ]),
                'total_amount' => $result['total_amount'],
                'status' => 'completed',
            ]);

            $order = $quote->order()->lockForUpdate()->firstOrFail();
            $productsTotal = (float) $order->items->sum(fn ($item) => (float) ($item->line_total ?? 0));

            // Product prices/order totals are stored in تومان; installation
            // tariffs are stored in ریال, so convert before adding to the order.
            $installationAmountInTomans = (float) $result['total_amount'] / 10;

            $order->update([
                'total' => $productsTotal + $installationAmountInTomans,
                'status' => 'received',
            ]);
        });

        return response()->json([
            'success' => true,
            'total_amount' => (float) $result['total_amount'],
            'installation_amount' => (float) $result['amounts']['installation'],
            'tracking_code' => $quote->tracking_code,
            'callback_url' => URL::temporarySignedRoute(
                'checkout.installation.success',
                now()->addHours(4),
                ['quote' => $quote->id]
            ),
        ]);
    }

    public function success(Request $request, InstallationQuote $quote)
    {
        abort_unless($request->hasValidSignature(), 403);
        abort_unless($quote->status === 'completed', 409);

        $order = $quote->order()->with('items')->firstOrFail();

        return view('store.order-success', [
            'order' => $order,
            'installationAmount' => (float) $quote->total_amount,
            'installationTrackingCode' => $quote->tracking_code,
        ]);
    }
}
