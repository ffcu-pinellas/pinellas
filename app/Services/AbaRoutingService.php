<?php

namespace App\Services;

use App\Models\OthersBank;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class AbaRoutingService
{
    /**
     * Verified Federal Reserve / ABA Directory with headquarters City & State.
     */
    protected const DIRECTORY = [
        '021000021' => ['name' => 'JPMorgan Chase Bank, N.A.', 'city' => 'New York', 'state' => 'NY'],
        '071000013' => ['name' => 'JPMorgan Chase Bank, N.A.', 'city' => 'Chicago', 'state' => 'IL'],
        '026009593' => ['name' => 'Bank of America, N.A.', 'city' => 'Charlotte', 'state' => 'NC'],
        '121000358' => ['name' => 'Bank of America, N.A.', 'city' => 'San Francisco', 'state' => 'CA'],
        '011000138' => ['name' => 'Bank of America, N.A.', 'city' => 'Boston', 'state' => 'MA'],
        '121000248' => ['name' => 'Wells Fargo Bank, N.A.', 'city' => 'San Francisco', 'state' => 'CA'],
        '121140399' => ['name' => 'Wells Fargo Bank, N.A.', 'city' => 'San Francisco', 'state' => 'CA'],
        '021000089' => ['name' => 'Citibank, N.A.', 'city' => 'New York', 'state' => 'NY'],
        '091000022' => ['name' => 'U.S. Bank, N.A.', 'city' => 'Minneapolis', 'state' => 'MN'],
        '071921891' => ['name' => 'PNC Bank, N.A.', 'city' => 'Pittsburgh', 'state' => 'PA'],
        '043000096' => ['name' => 'PNC Bank, N.A.', 'city' => 'Pittsburgh', 'state' => 'PA'],
        '051405515' => ['name' => 'Capital One, N.A.', 'city' => 'McLean', 'state' => 'VA'],
        '056073573' => ['name' => 'Capital One, N.A.', 'city' => 'McLean', 'state' => 'VA'],
        '011103093' => ['name' => 'TD Bank, N.A.', 'city' => 'Wilmington', 'state' => 'DE'],
        '031101266' => ['name' => 'TD Bank, N.A.', 'city' => 'Cherry Hill', 'state' => 'NJ'],
        '061000104' => ['name' => 'Truist Bank', 'city' => 'Charlotte', 'state' => 'NC'],
        '063100277' => ['name' => 'Regions Bank', 'city' => 'Birmingham', 'state' => 'AL'],
        '071000301' => ['name' => 'BMO Bank, N.A.', 'city' => 'Chicago', 'state' => 'IL'],
        '042000013' => ['name' => 'Fifth Third Bank, N.A.', 'city' => 'Cincinnati', 'state' => 'OH'],
        '041000124' => ['name' => 'KeyBank, N.A.', 'city' => 'Cleveland', 'state' => 'OH'],
        '121202211' => ['name' => 'Charles Schwab Bank, SSB', 'city' => 'Westlake', 'state' => 'TX'],
        '031000503' => ['name' => 'Fidelity Investments (UMB Bank)', 'city' => 'Kansas City', 'state' => 'MO'],
        '256074974' => ['name' => 'Navy Federal Credit Union', 'city' => 'Vienna', 'state' => 'VA'],
        '255078009' => ['name' => 'State Employees\' Credit Union', 'city' => 'Raleigh', 'state' => 'NC'],
        '321171184' => ['name' => 'Golden 1 Credit Union', 'city' => 'Sacramento', 'state' => 'CA'],
        '021001208' => ['name' => 'BNY Mellon, N.A.', 'city' => 'New York', 'state' => 'NY'],
        '124001545' => ['name' => 'Goldman Sachs Bank USA', 'city' => 'Salt Lake City', 'state' => 'UT'],
        '021214897' => ['name' => 'Morgan Stanley Bank, N.A.', 'city' => 'Salt Lake City', 'state' => 'UT'],
        '263182817' => ['name' => 'Suncoast Credit Union', 'city' => 'Tampa', 'state' => 'FL'],
        '263177327' => ['name' => 'VyStar Credit Union', 'city' => 'Jacksonville', 'state' => 'FL'],
        '263184048' => ['name' => 'Achieva Credit Union', 'city' => 'Dunedin', 'state' => 'FL'],
        '263179260' => ['name' => 'MIDFLORIDA Credit Union', 'city' => 'Lakeland', 'state' => 'FL'],
        '263182367' => ['name' => 'Pinellas County Teachers CU', 'city' => 'Largo', 'state' => 'FL'],
        '122105278' => ['name' => 'SoFi Bank, N.A.', 'city' => 'Cottonwood Heights', 'state' => 'UT'],
        '124003116' => ['name' => 'Ally Bank', 'city' => 'Sandy', 'state' => 'UT'],
        '031101279' => ['name' => 'The Bancorp Bank, N.A.', 'city' => 'Wilmington', 'state' => 'DE'],
    ];

    /**
     * Federal Reserve ABA 9-digit mod-10 checksum validator.
     */
    public function isValidAbaChecksum(string $routingNumber): bool
    {
        $clean = preg_replace('/\D/', '', $routingNumber);
        if (!preg_match('/^\d{9}$/', $clean)) {
            return false;
        }

        $d = array_map('intval', str_split($clean));
        $sum = (3 * ($d[0] + $d[3] + $d[6]))
             + (7 * ($d[1] + $d[4] + $d[7]))
             + (1 * ($d[2] + $d[5] + $d[8]));

        return $sum % 10 === 0;
    }

    /**
     * Lookup routing directory metadata (name, city, state) without throwing.
     */
    public function lookupDirectoryMeta(string $routingNumber): ?array
    {
        $routingNumber = preg_replace('/\D/', '', $routingNumber);

        if (isset(self::DIRECTORY[$routingNumber])) {
            $entry = self::DIRECTORY[$routingNumber];
            return [
                'bank_name' => $entry['name'],
                'city' => $entry['city'],
                'state' => $entry['state'],
                'location' => "{$entry['city']}, {$entry['state']}",
            ];
        }

        return Cache::remember('aba_dir_meta_' . $routingNumber, now()->addHours(24), function () use ($routingNumber) {
            try {
                $response = Http::timeout(4)->acceptJson()->get("https://bankrouting.io/api/v1/aba/{$routingNumber}");
                if ($response->successful()) {
                    $name = trim((string) data_get($response->json(), 'data.bank_name', ''));
                    if ($name !== '') {
                        $city = ucwords(strtolower(trim((string) data_get($response->json(), 'data.city', ''))));
                        $state = strtoupper(trim((string) data_get($response->json(), 'data.state', '')));
                        $loc = trim($city . ($city && $state ? ', ' : '') . $state);
                        return [
                            'bank_name' => $name,
                            'city' => $city ?: null,
                            'state' => $state ?: null,
                            'location' => $loc ?: null,
                        ];
                    }
                }
            } catch (\Throwable $e) {
            }

            try {
                $response2 = Http::timeout(4)->acceptJson()->get("https://routingnumbers.info/api/data.json?rn={$routingNumber}");
                if ($response2->successful()) {
                    $name = trim((string) data_get($response2->json(), 'customer_name', ''));
                    if ($name !== '') {
                        $city = ucwords(strtolower(trim((string) data_get($response2->json(), 'city', ''))));
                        $state = strtoupper(trim((string) data_get($response2->json(), 'state', '')));
                        $loc = trim($city . ($city && $state ? ', ' : '') . $state);
                        return [
                            'bank_name' => $name,
                            'city' => $city ?: null,
                            'state' => $state ?: null,
                            'location' => $loc ?: null,
                        ];
                    }
                }
            } catch (\Throwable $e) {
            }

            return null;
        });
    }

    /**
     * Resolve or auto-create an OthersBank row by ABA routing number.
     */
    public function resolveOrCreateOtherBank(string $routingNumber, ?string $providedBankName = null): OthersBank
    {
        $routingNumber = preg_replace('/\D/', '', $routingNumber);
        $providedBankName = trim((string) $providedBankName);

        $byRouting = OthersBank::where('code', $routingNumber)->first();
        if ($byRouting) {
            return $byRouting;
        }

        $meta = $this->lookupDirectoryMeta($routingNumber);
        $bankName = $providedBankName !== '' ? $providedBankName : ($meta['bank_name'] ?? 'External Bank');

        $cleanName = strtolower(trim($bankName));
        $byName = OthersBank::whereRaw('LOWER(name) = ?', [$cleanName])->first();
        if ($byName) {
            if (empty($byName->code)) {
                $byName->code = $routingNumber;
                $byName->save();
            }
            return $byName;
        }

        if ($providedBankName !== '') {
            $byNameLike = OthersBank::where('name', 'LIKE', '%' . $providedBankName . '%')->first();
            if ($byNameLike) {
                if (empty($byNameLike->code)) {
                    $byNameLike->code = $routingNumber;
                    $byNameLike->save();
                }
                return $byNameLike;
            }
        }

        $baseCode = $routingNumber !== '' ? $routingNumber : 'BANK-' . now()->timestamp;
        $codeCandidate = $baseCode;
        $suffix = 1;
        while (OthersBank::where('code', $codeCandidate)->exists()) {
            $codeCandidate = $baseCode . '-' . $suffix;
            $suffix++;
        }

        $type = strtolower((string) setting('fund_transfer_charge_type', 'fee'));
        $chargeType = in_array($type, ['percentage', 'percent'], true) ? 'percentage' : 'fixed';
        $charge = (float) setting('fund_transfer_charge', 'fee');

        return OthersBank::create([
            'name' => $bankName,
            'code' => $codeCandidate,
            'processing_time' => '0',
            'processing_type' => 'hours',
            'charge' => $charge,
            'charge_type' => $chargeType,
            'minimum_transfer' => setting('min_fund_transfer', 'fee'),
            'maximum_transfer' => setting('max_fund_transfer', 'fee'),
            'daily_limit_maximum_amount' => 999999999,
            'daily_limit_maximum_count' => 1000,
            'monthly_limit_maximum_amount' => 999999999,
            'monthly_limit_maximum_count' => 10000,
            'field_options' => json_encode([]),
            'details' => !empty($meta['location']) ? ('Fedwire / ABA Directory — ' . $meta['location']) : 'Auto-created from routing lookup',
            'status' => 1,
        ]);
    }
}

