<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RoutingService
{
    /**
     * Lookup a US ABA routing number.
     * Uses a 3-tier fallback chain:
     *   1. bankrouting.io (free, no key)
     *   2. api-ninjas.com (free tier, key from .env)
     *   3. Returns null → UI shows manual bank name input
     *
     * Results are cached for 30 days to avoid rate-limiting.
     *
     * @param  string  $routingNumber  9-digit ABA routing number
     * @return array{status: string, bank_name: string|null, source: string}
     */
    public function lookup(string $routingNumber): array
    {
        // Validate format first (must be exactly 9 digits)
        if (!preg_match('/^\d{9}$/', $routingNumber)) {
            return [
                'status'    => 'invalid',
                'bank_name' => null,
                'source'    => 'validation',
            ];
        }

        $cacheKey = 'routing_lookup_' . $routingNumber;

        // Return from cache if available
        if ($cached = Cache::get($cacheKey)) {
            return array_merge($cached, ['source' => 'cache']);
        }

        // --- Tier 1: bankrouting.io ---
        $result = $this->tryBankRoutingIo($routingNumber);
        if ($result['status'] === 'success') {
            Cache::put($cacheKey, $result, now()->addDays(30));
            return $result;
        }

        // --- Tier 2: api-ninjas.com ---
        $result = $this->tryApiNinjas($routingNumber);
        if ($result['status'] === 'success') {
            Cache::put($cacheKey, $result, now()->addDays(30));
            return $result;
        }

        // --- Tier 3: Not found – instruct UI to show manual input ---
        return [
            'status'    => 'not_found',
            'bank_name' => null,
            'source'    => 'exhausted',
        ];
    }

    /**
     * Tier 1: bankrouting.io (free, no authentication required)
     */
    private function tryBankRoutingIo(string $routingNumber): array
    {
        try {
            $response = Http::timeout(5)
                ->withHeaders(['Accept' => 'application/json'])
                ->get("https://bankrouting.io/api/v1/aba/{$routingNumber}");

            if ($response->successful()) {
                $data = $response->json();
                $bankName = data_get($data, 'data.bank_name')
                         ?? data_get($data, 'bank_name')
                         ?? null;

                if ($bankName) {
                    return [
                        'status'    => 'success',
                        'bank_name' => ucwords(strtolower($bankName)),
                        'source'    => 'bankrouting.io',
                    ];
                }
            }
        } catch (\Exception $e) {
            Log::warning("RoutingService Tier1 (bankrouting.io) failed for {$routingNumber}: " . $e->getMessage());
        }

        return ['status' => 'error', 'bank_name' => null, 'source' => 'bankrouting.io'];
    }

    /**
     * Tier 2: api-ninjas.com (free tier, API key from .env: NINJAS_API_KEY)
     */
    private function tryApiNinjas(string $routingNumber): array
    {
        $apiKey = config('services.api_ninjas.key', env('NINJAS_API_KEY'));

        if (empty($apiKey)) {
            Log::debug("RoutingService Tier2: NINJAS_API_KEY not set, skipping.");
            return ['status' => 'skipped', 'bank_name' => null, 'source' => 'api-ninjas'];
        }

        try {
            $response = Http::timeout(5)
                ->withHeaders([
                    'X-Api-Key' => $apiKey,
                    'Accept'    => 'application/json',
                ])
                ->get('https://api.api-ninjas.com/v1/routingnumber', [
                    'routing_number' => $routingNumber,
                ]);

            if ($response->successful()) {
                $data = $response->json();
                // api-ninjas returns an array of matches
                $bankName = data_get($data, '0.name') ?? null;

                if ($bankName) {
                    return [
                        'status'    => 'success',
                        'bank_name' => ucwords(strtolower($bankName)),
                        'source'    => 'api-ninjas',
                    ];
                }
            }
        } catch (\Exception $e) {
            Log::warning("RoutingService Tier2 (api-ninjas) failed for {$routingNumber}: " . $e->getMessage());
        }

        return ['status' => 'error', 'bank_name' => null, 'source' => 'api-ninjas'];
    }
}
