<?php

namespace App\Services;

/**
 * Server-side copy of the current DTZ Palaz installation calculation.
 *
 * Current DTZ behavior:
 * - installation = purchased roll area × 500,000 ریال/m², with a 30 m² minimum (15,000,000 ریال)
 * - glue prices currently resolve to 0 in getGluePrice()
 * - floor/stair/fish/cut/elevator/carry/worker UI currently has no
 *   active pricing formula, so those amounts remain 0.
 *
 * The UI fields are still accepted and stored so adding the missing DTZ
 * tariffs later does not require changing the order structure.
 */
class DtzInstallationCalculator
{
    public const INSTALLATION_RATE = 500000;
    public const MIN_INSTALLATION_AMOUNT = 15000000;
    public const MIN_INSTALLATION_AREA = 30;

    public function calculate(array $payload): array
    {
        $rolls = collect($payload['rolls'] ?? [])
            ->map(function (array $roll): array {
                $width = max(1, min(4, (float) ($roll['width'] ?? 3)));
                $length = max(1, min(15, (float) ($roll['length'] ?? 1)));
                $quantity = max(1, min(1000, (int) ($roll['quantity'] ?? 1)));

                return [
                    'width' => $width,
                    'length' => $length,
                    'quantity' => $quantity,
                    'area' => $width * $length * $quantity,
                    'code' => $roll['code'] ?? null,
                    'name' => $roll['name'] ?? null,
                    'model' => $roll['model'] ?? null,
                ];
            })
            ->values();

        $purchasedArea = (float) $rolls->sum('area');
        $installationArea = $purchasedArea;

        $installationAmount = max(
            $installationArea * self::INSTALLATION_RATE,
            self::MIN_INSTALLATION_AMOUNT
        );

        // These are deliberately zero because that is what the current DTZ
        // code actually calculates today.
        $glueAmount = 0;
        $floorAmount = 0;
        $floorCarryAmount = 0;
        $workerAmount = 0;
        $otherAmount = 0;

        return [
            'rolls' => $rolls->all(),
            'purchased_area' => $purchasedArea,
            'installation_area' => $installationArea,
            'minimum_area' => self::MIN_INSTALLATION_AREA,
            'minimum_area_met' => $installationArea >= self::MIN_INSTALLATION_AREA,
            'minimum_amount' => self::MIN_INSTALLATION_AMOUNT,
            'rates' => [
                'installation_per_m2' => self::INSTALLATION_RATE,
                'minimum_installation_amount' => self::MIN_INSTALLATION_AMOUNT,
                'glue' => 0,
                'floor' => 0,
                'floor_carry' => 0,
                'worker' => 0,
                'other' => 0,
            ],
            'amounts' => [
                'installation' => $installationAmount,
                'glue' => $glueAmount,
                'floor' => $floorAmount,
                'floor_carry' => $floorCarryAmount,
                'worker' => $workerAmount,
                'other' => $otherAmount,
            ],
            'total_amount' => $installationAmount
                + $glueAmount
                + $floorAmount
                + $floorCarryAmount
                + $workerAmount
                + $otherAmount,
        ];
    }
}
