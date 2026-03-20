<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use App\Models\OthersBank;

class RoutingService
{
    private $apiUrl = 'https://bankrouting.io/api/v1/aba/';

    /**
     * Resolve bank information by routing number.
     * Checks local database first, then tries external API.
     */
    public function resolve($routingNumber)
    {
        $routingNumber = preg_replace('/[^0-9]/', '', $routingNumber);
        
        if (strlen($routingNumber) !== 9) {
            return ['status' => 'error', 'message' => 'Invalid routing number length.'];
        }

        // 1. Check if we already have this bank in our "OthersBank" records
        // Note: Code should match the routing/aba if it was entered there.
        $existingBank = OthersBank::where('code', $routingNumber)->first();
        if ($existingBank) {
            return [
                'status' => 'success',
                'id' => $existingBank->id,
                'name' => $existingBank->name,
                'logo' => $existingBank->logo,
                'is_local' => true,
                'charge' => $existingBank->charge,
                'charge_type' => $existingBank->charge_type
            ];
        }

        // 2. Try Cache for External Lookup
        $cacheKey = "routing_lookup_{$routingNumber}";
        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        // 3. Call external API (bankrouting.io)
        try {
            $response = Http::get($this->apiUrl . $routingNumber);
            
            if ($response->successful()) {
                $data = $response->json();
                
                if (isset($data['status']) && $data['status'] === 'success') {
                    $bankData = [
                        'status' => 'success',
                        'id' => 0, // Not in our DB yet
                        'name' => $data['data']['bank_name'] ?? 'Unknown Bank',
                        'logo' => null, // We could add a generic logo later
                        'is_local' => false,
                        'charge' => setting('fund_transfer_charge', 'fee'),
                        'charge_type' => setting('fund_transfer_charge_type', 'fee')
                    ];
                    
                    Cache::put($cacheKey, $bankData, now()->addDays(30));
                    return $bankData;
                }
            }
            
            return ['status' => 'error', 'message' => 'Bank not found or service unavailable.'];

        } catch (\Exception $e) {
            \Log::error("RoutingService API Error: " . $e->getMessage());
            return ['status' => 'error', 'message' => 'Verification failed. Please check the number manually.'];
        }
    }
}
