<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class RoutingService
{
    /**
     * Look up bank name by ABA routing number.
     * Use bankrouting.io public API with 30-day caching.
     * 
     * @param string $routingNumber
     * @return array|null
     */
    public function lookUp($routingNumber)
    {
        $routingNumber = preg_replace('/[^0-9]/', '', $routingNumber);
        
        if (strlen($routingNumber) !== 9) {
            return [
                'status' => 'error',
                'message' => 'Invalid routing number length.'
            ];
        }

        return Cache::remember("routing_lookup_{$routingNumber}", now()->addDays(30), function () use ($routingNumber) {
            try {
                $response = Http::timeout(5)->get("https://bankrouting.io/api/v1/aba/{$routingNumber}");
                
                if ($response->successful()) {
                    $data = $response->json();
                    if (isset($data['status']) && $data['status'] === 'success') {
                        return [
                            'status' => 'success',
                            'bank_name' => $data['data']['bank_name'] ?? 'Unknown Bank',
                            'city' => $data['data']['city'] ?? '',
                            'state' => $data['data']['state'] ?? ''
                        ];
                    }
                }
                
                return [
                    'status' => 'error',
                    'message' => 'Bank not found or service unavailable.'
                ];
            } catch (\Exception $e) {
                \Log::error("Routing lookup failed for {$routingNumber}: " . $e->getMessage());
                return [
                    'status' => 'error',
                    'message' => 'Service timed out.'
                ];
            }
        });
    }
}
