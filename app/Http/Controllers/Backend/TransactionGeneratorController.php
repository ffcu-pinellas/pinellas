<?php

namespace App\Http\Controllers\Backend;

use App\Enums\TxnStatus;
use App\Enums\TxnType;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserWallet;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Txn;

class TransactionGeneratorController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:customer-balance-add-or-subtract|officer-balance-manage');
    }

    public function preview(Request $request, $id)
    {
        $user = User::findOrFail($id);

        // Security Check for Account Officer
        if (auth('admin')->user() && auth('admin')->user()->hasAnyRole(['Account Officer', 'Account-Officer'], 'admin') && !auth('admin')->user()->hasAnyRole(['Super-Admin', 'Super Admin'], 'admin')) {
            if ($user->staff_id != auth('admin')->id()) {
                abort(403, 'Unauthorized action.');
            }
        }

        $validator = Validator::make($request->all(), [
            'count' => 'required|integer|min:1|max:20',
            'min_amount' => 'required|numeric|min:0.01',
            'max_amount' => 'required|numeric|min:0.01|gte:min_amount',
            'direction' => 'required|in:income,outcome,both',
            'date_range' => 'required|in:0,3,7,30',
            'theme' => 'required|in:standard,crypto,military,real_estate,contractor,lifestyle,travel,entertainment,healthcare',
            'wallet_type' => 'required',
        ]);

        if ($validator->fails()) {
            notify()->error($validator->errors()->first(), 'Error');
            return redirect()->back();
        }

        $themes = [
            'standard' => [
                'income' => [
                    ['label' => 'Payroll Deposit - ADP', 'cat' => 'large'],
                    ['label' => 'Venmo Cashout - Transfer', 'cat' => 'flex'],
                    ['label' => 'Zelle Transfer from Family', 'cat' => 'medium'],
                    ['label' => 'Apple Pay Refund', 'cat' => 'small'],
                    ['label' => 'Tax Refund - IRS TREAS', 'cat' => 'large'],
                    ['label' => 'Interest Payment - Savings', 'cat' => 'micro'],
                    ['label' => 'Cash Deposit - ATM', 'cat' => 'medium'],
                    ['label' => 'Stripe Payout - Sales', 'cat' => 'large'],
                    ['label' => 'Dividend Reinvestment', 'cat' => 'small'],
                    ['label' => 'eBay Sale - Item Sold', 'cat' => 'medium'],
                    ['label' => 'Poshmark Earnings', 'cat' => 'small'],
                    ['label' => 'Work Bonus - Quarterly', 'cat' => 'large'],
                    ['label' => 'Gig Economy Payout', 'cat' => 'small'],
                    ['label' => 'Rental Reimbursement', 'cat' => 'medium'],
                    ['label' => 'Insurance Claim Payout', 'cat' => 'large'],
                    ['label' => 'Gift - Birthday Funds', 'cat' => 'small']
                ],
                'outcome' => [
                    ['label' => 'Amazon.com Order', 'cat' => 'small'],
                    ['label' => 'Starbucks Coffee', 'cat' => 'micro'],
                    ['label' => 'Walmart Supercenter', 'cat' => 'small'],
                    ['label' => 'Netflix Subscription', 'cat' => 'sub'],
                    ['label' => 'Uber Trip', 'cat' => 'micro'],
                    ['label' => 'Shell Gas Station', 'cat' => 'small'],
                    ['label' => 'McDonald\'s Store', 'cat' => 'micro'],
                    ['label' => 'Target Store Purchase', 'cat' => 'small'],
                    ['label' => 'Apple Services - iCloud', 'cat' => 'sub'],
                    ['label' => 'Spotify Premium', 'cat' => 'sub'],
                    ['label' => 'Costco Wholesale', 'cat' => 'medium'],
                    ['label' => 'Whole Foods Market', 'cat' => 'small'],
                    ['label' => 'Dunkin\' Donuts', 'cat' => 'micro'],
                    ['label' => '7-Eleven Purchase', 'cat' => 'micro'],
                    ['label' => 'Walgreens Pharmacy', 'cat' => 'small'],
                    ['label' => 'CVS Pharmacy', 'cat' => 'small'],
                    ['label' => 'Chick-fil-A', 'cat' => 'micro'],
                    ['label' => 'Panera Bread', 'cat' => 'micro'],
                    ['label' => 'Chipotle Mexican Grill', 'cat' => 'micro'],
                    ['label' => 'Home Depot - Small Tools', 'cat' => 'small'],
                    ['label' => 'Best Buy - Electronics', 'cat' => 'medium'],
                    ['label' => 'Petco - Animal Supplies', 'cat' => 'small'],
                    ['label' => 'Adobe Creative Cloud', 'cat' => 'sub'],
                    ['label' => 'NY Times Subscription', 'cat' => 'sub']
                ]
            ],
            'crypto' => [
                'income' => [
                    ['label' => 'Coinbase Withdrawal', 'cat' => 'large'],
                    ['label' => 'Binance P2P Credit', 'cat' => 'large'],
                    ['label' => 'Kraken Funding', 'cat' => 'medium'],
                    ['label' => 'Ledger Wallet Transfer', 'cat' => 'large'],
                    ['label' => 'Crypto.com Rewards', 'cat' => 'micro'],
                    ['label' => 'Staking Rewards - ETH', 'cat' => 'small'],
                    ['label' => 'Mining Pool Payout', 'cat' => 'medium'],
                    ['label' => 'NFT Sale Proceeds', 'cat' => 'large'],
                    ['label' => 'Metamask Swap Credit', 'cat' => 'medium'],
                    ['label' => 'Uniswap LP Fees', 'cat' => 'small'],
                    ['label' => 'Trust Wallet Receive', 'cat' => 'medium'],
                    ['label' => 'Gemini Earn Interest', 'cat' => 'small'],
                    ['label' => 'SushiSwap Reward', 'cat' => 'micro'],
                    ['label' => 'PancakeSwap Harvest', 'cat' => 'micro'],
                    ['label' => 'Celsius Network Return', 'cat' => 'medium'],
                    ['label' => 'BlockFi Interest', 'cat' => 'small']
                ],
                'outcome' => [
                    ['label' => 'Coinbase Buy Order', 'cat' => 'large'],
                    ['label' => 'Binance Deposit', 'cat' => 'large'],
                    ['label' => 'MetaMask Gas Fee', 'cat' => 'micro'],
                    ['label' => 'Kraken Market Buy', 'cat' => 'medium'],
                    ['label' => 'NFT Purchase', 'cat' => 'large'],
                    ['label' => 'Ledger Wallet Send', 'cat' => 'large'],
                    ['label' => 'Opensea Bid Placement', 'cat' => 'medium'],
                    ['label' => 'Hardware Wallet Purchase', 'cat' => 'small'],
                    ['label' => 'Trust Wallet Send', 'cat' => 'medium'],
                    ['label' => 'Crypto Exchange Fee', 'cat' => 'micro'],
                    ['label' => 'Defi Protocol Deposit', 'cat' => 'medium'],
                    ['label' => 'Cold Storage Transfer', 'cat' => 'large'],
                    ['label' => 'Gas Fee - High Priority', 'cat' => 'micro'],
                    ['label' => 'Bridge Fee - Polygon', 'cat' => 'micro'],
                    ['label' => 'Yield Farm Entry', 'cat' => 'medium'],
                    ['label' => 'Liquidity Provision', 'cat' => 'large']
                ]
            ],
            'military' => [
                'income' => [
                    ['label' => 'DFAS-IN DEP - Base Pay', 'cat' => 'large'],
                    ['label' => 'TSP Dividend Payout', 'cat' => 'medium'],
                    ['label' => 'VA Benefit Payment', 'cat' => 'large'],
                    ['label' => 'Military Housing Allowance', 'cat' => 'large'],
                    ['label' => 'COLA Adjustment', 'cat' => 'small'],
                    ['label' => 'Per Diem Reimbursement', 'cat' => 'medium'],
                    ['label' => 'Special Duty Pay', 'cat' => 'medium'],
                    ['label' => 'Re-enlistment Bonus', 'cat' => 'large'],
                    ['label' => 'Deployment Pay', 'cat' => 'large'],
                    ['label' => 'Hazardous Duty Pay', 'cat' => 'medium'],
                    ['label' => 'Overseas Allowance', 'cat' => 'large'],
                    ['label' => 'Travel Claim Payout', 'cat' => 'medium'],
                    ['label' => 'Uniform Allowance', 'cat' => 'small'],
                    ['label' => 'Separation Pay', 'cat' => 'large'],
                    ['label' => 'Guard/Reserve Drill Pay', 'cat' => 'medium'],
                    ['label' => 'GI Bill Stipend', 'cat' => 'medium']
                ],
                'outcome' => [
                    ['label' => 'PX/BX Purchase', 'cat' => 'small'],
                    ['label' => 'Commissary Groceries', 'cat' => 'small'],
                    ['label' => 'USAA Insurance Premium', 'cat' => 'medium'],
                    ['label' => 'Navy Federal Loan Pymt', 'cat' => 'large'],
                    ['label' => 'MCCS Activity Fee', 'cat' => 'micro'],
                    ['label' => 'Officer\'s Club Dues', 'cat' => 'small'],
                    ['label' => 'Tailor shop - Rank Insignia', 'cat' => 'micro'],
                    ['label' => 'Base Housing Utility', 'cat' => 'medium'],
                    ['label' => 'AAFES Online Store', 'cat' => 'medium'],
                    ['label' => 'Patriot Express Fee', 'cat' => 'small'],
                    ['label' => 'Military Star Card Pymt', 'cat' => 'medium'],
                    ['label' => 'Stars and Stripes Sub', 'cat' => 'sub'],
                    ['label' => 'Deployment Gear HQ', 'cat' => 'medium'],
                    ['label' => 'Defense Health Agency', 'cat' => 'small'],
                    ['label' => 'GSA Global Supply', 'cat' => 'medium'],
                    ['label' => 'Base Tag/Title Fee', 'cat' => 'micro']
                ]
            ],
            'real_estate' => [
                'income' => [
                    ['label' => 'Rental Income - Unit A', 'cat' => 'large'],
                    ['label' => 'Property Sale Proceeds', 'cat' => 'large'],
                    ['label' => 'Escrow Refund', 'cat' => 'large'],
                    ['label' => 'Reverse Mortgage Payout', 'cat' => 'large'],
                    ['label' => 'Tenant Security Deposit', 'cat' => 'large'],
                    ['label' => 'Commercial Lease Pmt', 'cat' => 'large'],
                    ['label' => 'Lease Option Fee', 'cat' => 'large'],
                    ['label' => 'Airbnb Hosting Payout', 'cat' => 'medium'],
                    ['label' => 'VRBO Rental Income', 'cat' => 'medium'],
                    ['label' => 'Property Management Refund', 'cat' => 'medium'],
                    ['label' => 'Insurance Loss Claim', 'cat' => 'large'],
                    ['label' => 'Easement Payment', 'cat' => 'large'],
                    ['label' => 'Parking Space Rental', 'cat' => 'small'],
                    ['label' => 'Storage Unit Lease', 'cat' => 'small'],
                    ['label' => 'Real Estate Commission', 'cat' => 'large'],
                    ['label' => 'Flipping Profit', 'cat' => 'large']
                ],
                'outcome' => [
                    ['label' => 'Chase Mortgage Payment', 'cat' => 'large'],
                    ['label' => 'County Property Tax', 'cat' => 'large'],
                    ['label' => 'HOA Assessment', 'cat' => 'medium'],
                    ['label' => 'Home Insurance Premium', 'cat' => 'medium'],
                    ['label' => 'Title Company Fee', 'cat' => 'large'],
                    ['label' => 'Appraisal Fee', 'cat' => 'medium'],
                    ['label' => 'Home Inspection Pmt', 'cat' => 'medium'],
                    ['label' => 'Pest Control Service', 'cat' => 'small'],
                    ['label' => 'Landscaping - Monthly', 'cat' => 'small'],
                    ['label' => 'Pool Maintenance', 'cat' => 'small'],
                    ['label' => 'Roofing Repair Deposit', 'cat' => 'large'],
                    ['label' => 'HVAC Annual Service', 'cat' => 'small'],
                    ['label' => 'Property Manager Fee', 'cat' => 'medium'],
                    ['label' => 'Notary Public Fee', 'cat' => 'micro'],
                    ['label' => 'Lead Paint Inspection', 'cat' => 'small'],
                    ['label' => 'Septic Tank Pumping', 'cat' => 'medium']
                ]
            ],
            'contractor' => [
                'income' => [
                    ['label' => 'Project Milestone Pmt', 'cat' => 'large'],
                    ['label' => 'Material Reimbursement', 'cat' => 'medium'],
                    ['label' => 'Retainager Release', 'cat' => 'large'],
                    ['label' => 'Consulting Fee', 'cat' => 'medium'],
                    ['label' => 'Change Order Credit', 'cat' => 'large'],
                    ['label' => 'Subcontracting Income', 'cat' => 'large'],
                    ['label' => 'Blueprints/Drafting Fee', 'cat' => 'medium'],
                    ['label' => 'Estimating Fee', 'cat' => 'medium'],
                    ['label' => 'Service Call Fee', 'cat' => 'small'],
                    ['label' => 'Maintenance Contract', 'cat' => 'medium'],
                    ['label' => 'Equipment Resale', 'cat' => 'large'],
                    ['label' => 'Labor Charge - Final', 'cat' => 'large'],
                    ['label' => 'Job Site Setup Fee', 'cat' => 'medium'],
                    ['label' => 'Safety Training Reimbursement', 'cat' => 'small'],
                    ['label' => 'Bid Security Refund', 'cat' => 'large'],
                    ['label' => 'Union Dues Refund', 'cat' => 'small']
                ],
                'outcome' => [
                    ['label' => 'Home Depot Purchase', 'cat' => 'small'],
                    ['label' => 'Equipment Rental - Sunbelt', 'cat' => 'medium'],
                    ['label' => 'Subcontractor Payout', 'cat' => 'large'],
                    ['label' => 'Lowe\'s Pro Sales', 'cat' => 'medium'],
                    ['label' => 'Liability Insurance', 'cat' => 'large'],
                    ['label' => 'Workman\'s Comp Pmt', 'cat' => 'large'],
                    ['label' => 'Diesel Fuel - Truck', 'cat' => 'small'],
                    ['label' => 'Lumber Yard Order', 'cat' => 'medium'],
                    ['label' => 'Electrical Supplies', 'cat' => 'medium'],
                    ['label' => 'Plumbing Fixtures', 'cat' => 'medium'],
                    ['label' => 'Dumpster Rental Fee', 'cat' => 'medium'],
                    ['label' => 'Permit Application Fee', 'cat' => 'small'],
                    ['label' => 'Tool Repair Service', 'cat' => 'small'],
                    ['label' => 'Safety Gear Pro', 'cat' => 'small'],
                    ['label' => 'Job Site Signage', 'cat' => 'small'],
                    ['label' => 'Blueprints Printing', 'cat' => 'small']
                ]
            ],
            'lifestyle' => [
                'income' => [
                    ['label' => 'Fitness Coaching Payout', 'cat' => 'medium'],
                    ['label' => 'Blog Ad Revenue', 'cat' => 'medium'],
                    ['label' => 'YouTube Partner Earnings', 'cat' => 'large'],
                    ['label' => 'TikTok Creator Fund', 'cat' => 'medium'],
                    ['label' => 'Consulting - Branding', 'cat' => 'large'],
                    ['label' => 'Brand Deal - Deposit', 'cat' => 'large'],
                    ['label' => 'Course Sale - Online', 'cat' => 'medium'],
                    ['label' => 'Membership Dues - Receive', 'cat' => 'small'],
                    ['label' => 'Event Ticket Resale', 'cat' => 'medium'],
                    ['label' => 'Photography Session Fee', 'cat' => 'medium'],
                    ['label' => 'Art Commission', 'cat' => 'medium'],
                    ['label' => 'Personal Styling Fee', 'cat' => 'medium']
                ],
                'outcome' => [
                    ['label' => 'Equinox Monthly Gym', 'cat' => 'medium'],
                    ['label' => 'Peloton Subscription', 'cat' => 'sub'],
                    ['label' => 'Lululemon Purchase', 'cat' => 'small'],
                    ['label' => 'Whole Foods Market', 'cat' => 'small'],
                    ['label' => 'Blue Bottle Coffee', 'cat' => 'micro'],
                    ['label' => 'SoulCycle Session', 'cat' => 'small'],
                    ['label' => 'Spa/Massage Service', 'cat' => 'medium'],
                    ['label' => 'Organic Farmers Market', 'cat' => 'small'],
                    ['label' => 'Aritzia Clothing', 'cat' => 'small'],
                    ['label' => 'Masterclass Subscription', 'cat' => 'sub'],
                    ['label' => 'Audible.com', 'cat' => 'sub'],
                    ['label' => 'HelloFresh Box', 'cat' => 'small'],
                    ['label' => 'Glossier Order', 'cat' => 'small'],
                    ['label' => 'Sweetgreen Lunch', 'cat' => 'micro'],
                    ['label' => 'Erewhon Market', 'cat' => 'small'],
                    ['label' => 'Juice Press', 'cat' => 'micro']
                ]
            ],
            'travel' => [
                'income' => [
                    ['label' => 'Airline Ticket Refund', 'cat' => 'large'],
                    ['label' => 'Hotel Overcharge Credit', 'cat' => 'medium'],
                    ['label' => 'Trip Cancellation Insurance', 'cat' => 'medium'],
                    ['label' => 'Travel Points Cashout', 'cat' => 'medium'],
                    ['label' => 'Expedia Cashback', 'cat' => 'small'],
                    ['label' => 'Tax Free Shopping Refund', 'cat' => 'small'],
                    ['label' => 'Shared Trip Expense (Friend)', 'cat' => 'medium'],
                    ['label' => 'Travel Writing Payout', 'cat' => 'large']
                ],
                'outcome' => [
                    ['label' => 'Delta Airlines Booking', 'cat' => 'large'],
                    ['label' => 'Marriott International', 'cat' => 'large'],
                    ['label' => 'Airbnb Reservation', 'cat' => 'large'],
                    ['label' => 'Hertz Car Rental', 'cat' => 'medium'],
                    ['label' => 'Hilton Hotels', 'cat' => 'large'],
                    ['label' => 'Uber Travel - International', 'cat' => 'medium'],
                    ['label' => 'TSA PreCheck Fee', 'cat' => 'small'],
                    ['label' => 'Priority Pass Lounge', 'cat' => 'small'],
                    ['label' => 'TripAdvisor Booking', 'cat' => 'medium'],
                    ['label' => 'Viator Tour Package', 'cat' => 'medium'],
                    ['label' => 'Booking.com Stay', 'cat' => 'medium'],
                    ['label' => 'Amtrak Ticket', 'cat' => 'small'],
                    ['label' => 'Eurostar Travel', 'cat' => 'medium'],
                    ['label' => 'Duty Free Purchase', 'cat' => 'medium'],
                    ['label' => 'Global Entry Fee', 'cat' => 'small'],
                    ['label' => 'Passport Expedited Fee', 'cat' => 'small']
                ]
            ],
            'entertainment' => [
                'income' => [
                    ['label' => 'Twitch Bits/Sub Payout', 'cat' => 'medium'],
                    ['label' => 'Patreon Monthly Earnings', 'cat' => 'medium'],
                    ['label' => 'Ticketmaster Refund', 'cat' => 'medium'],
                    ['label' => 'DraftKings Withdrawal', 'cat' => 'large'],
                    ['label' => 'Fanduel Payout', 'cat' => 'large'],
                    ['label' => 'GameStop Trade-in Credit', 'cat' => 'small'],
                    ['label' => 'Esports Tournament Prize', 'cat' => 'large'],
                    ['label' => 'Steam Wallet Credit', 'cat' => 'small']
                ],
                'outcome' => [
                    ['label' => 'Netflix Monthly', 'cat' => 'sub'],
                    ['label' => 'Hulu Subscription', 'cat' => 'sub'],
                    ['label' => 'Disney+ Annual', 'cat' => 'small'],
                    ['label' => 'HBO Max / Discovery+', 'cat' => 'sub'],
                    ['label' => 'PlayStation Network Store', 'cat' => 'small'],
                    ['label' => 'Xbox Game Pass', 'cat' => 'sub'],
                    ['label' => 'Steam Games Purchase', 'cat' => 'small'],
                    ['label' => 'Nintendo eShop', 'cat' => 'small'],
                    ['label' => 'AMC Theatres Ticket', 'cat' => 'micro'],
                    ['label' => 'Regal Cinemas Popcorn', 'cat' => 'micro'],
                    ['label' => 'Topgolf Session', 'cat' => 'small'],
                    ['label' => 'Dave & Buster\'s Reload', 'cat' => 'small'],
                    ['label' => 'Spotify Family Plan', 'cat' => 'sub'],
                    ['label' => 'YouTube Premium', 'cat' => 'sub'],
                    ['label' => 'Roblox Robux Purchase', 'cat' => 'micro'],
                    ['label' => 'Discord Nitro', 'cat' => 'sub']
                ]
            ],
            'healthcare' => [
                'income' => [
                    ['label' => 'HSA Contribution - Employer', 'cat' => 'medium'],
                    ['label' => 'Insurance Claim Reimbursement', 'cat' => 'large'],
                    ['label' => 'Flexible Spending Account Credit', 'cat' => 'medium'],
                    ['label' => 'Health Incentive Reward', 'cat' => 'small'],
                    ['label' => 'Pharmacy Prescription Refund', 'cat' => 'small'],
                    ['label' => 'Medical Billing Adjustment', 'cat' => 'medium'],
                    ['label' => 'Overpayment Credit - Hospital', 'cat' => 'large']
                ],
                'outcome' => [
                    ['label' => 'CVS Pharmacy RX', 'cat' => 'small'],
                    ['label' => 'Walgreens Prescription', 'cat' => 'small'],
                    ['label' => 'UnitedHealthcare Premium', 'cat' => 'large'],
                    ['label' => 'Aetna Insurance Pmt', 'cat' => 'large'],
                    ['label' => 'Kaiser Permanente Visit', 'cat' => 'medium'],
                    ['label' => 'Quest Diagnostics Lab', 'cat' => 'small'],
                    ['label' => 'LabCorp Medical Fee', 'cat' => 'small'],
                    ['label' => 'Dentist - Cleaning/Exam', 'cat' => 'medium'],
                    ['label' => 'Optometrist - Eye Exam', 'cat' => 'medium'],
                    ['label' => 'LensCrafters - New Glasses', 'cat' => 'medium'],
                    ['label' => 'Local Medical Center Copay', 'cat' => 'small'],
                    ['label' => 'Physical Therapy Session', 'cat' => 'medium'],
                    ['label' => 'GNC - Supplements', 'cat' => 'small'],
                    ['label' => 'Vitamin Shoppe Order', 'cat' => 'small'],
                    ['label' => 'Rite Aid Purchase', 'cat' => 'small'],
                    ['label' => 'Doctor Office Consultation', 'cat' => 'medium']
                ]
            ]
        ];

        $adminUser = Auth::user();
        $wallet_type = $request->wallet_type;
        $wallet_name = $this->getWalletName($user, $wallet_type);

        $previewData = [];
        $generatedCount = 0;

        for ($i = 0; $i < $request->count; $i++) {
            $dir = $request->direction;
            if ($dir == 'both') {
                $dir = rand(0, 1) ? 'income' : 'outcome';
            }

            $item = $themes[$request->theme][$dir][array_rand($themes[$request->theme][$dir])];
            $description = $item['label'];
            $cat = $item['cat'];

            $amount = $this->getSmartAmount($cat, $request->min_amount, $request->max_amount);
            
            $daysBack = rand(0, (int)$request->date_range);
            $date = Carbon::now()->subDays($daysBack)->subMinutes(rand(0, 1440));

            $type = ($dir == 'income') ? TxnType::Deposit : TxnType::Subtract;

            $previewData[] = [
                'amount' => $amount,
                'description' => $description,
                'type' => $type,
                'date' => $date->toDateTimeString(),
                'wallet_type' => $wallet_type,
                'wallet_name' => $wallet_name,
                'direction' => $dir
            ];
            $generatedCount++;
        }

        session()->put("txn_preview_{$id}", $previewData);
        session()->flash("show_preview_{$id}", true);
        
        return redirect()->back();
    }

    public function commit(Request $request, $id)
    {
        $user = User::findOrFail($id);

        if (auth('admin')->user() && auth('admin')->user()->hasAnyRole(['Account Officer', 'Account-Officer'], 'admin') && !auth('admin')->user()->hasAnyRole(['Super-Admin', 'Super Admin'], 'admin')) {
            if ($user->staff_id != auth('admin')->id()) {
                abort(403, 'Unauthorized action.');
            }
        }

        $transactions = session("txn_preview_{$id}");
        if (!$transactions) {
            notify()->error('No preview data found. Please try generating again.', 'Error');
            return redirect()->back();
        }

        $selectedIndexes = $request->input('txn_indexes', []);
        
        if (empty($selectedIndexes)) {
            session()->forget("txn_preview_{$id}");
            notify()->error('No transactions were selected to be saved.', 'Error');
            return redirect()->back();
        }

        $adminUser = Auth::user();
        $generatedCount = 0;

        foreach ($selectedIndexes as $idx) {
            if (!isset($transactions[$idx])) continue;
            
            $item = $transactions[$idx];
            $this->updateUserBalance($user, $item['wallet_type'], $item['amount'], $item['direction'] == 'income' ? 'add' : 'subtract');
            
            $txn = Txn::new(
                amount: $item['amount'],
                charge: 0,
                final_amount: $item['amount'],
                method: 'system',
                description: $item['description'],
                type: $item['type'],
                status: TxnStatus::Success,
                payCurrency: $item['wallet_name'],
                userID: $id,
                relatedUserID: $adminUser->id,
                relatedModel: 'Admin',
                walletType: $item['wallet_type']
            );
            $txn->created_at = $item['date'];
            $txn->save();
            $generatedCount++;
        }

        session()->forget("txn_preview_{$id}");
        
        notify()->success("$generatedCount transactions generated successfully!", 'Success');
        return redirect()->back();
    }

    public function discard(Request $request, $id)
    {
        session()->forget("txn_preview_{$id}");
        return redirect()->back();
    }

    public function bulkDeletePreview(Request $request, $id)
    {
        $user = User::findOrFail($id);

        if (auth('admin')->user() && auth('admin')->user()->hasAnyRole(['Account Officer', 'Account-Officer'], 'admin') && !auth('admin')->user()->hasAnyRole(['Super-Admin', 'Super Admin'], 'admin')) {
            if ($user->staff_id != auth('admin')->id()) {
                abort(403, 'Unauthorized action.');
            }
        }

        $query = \App\Models\Transaction::where('user_id', $id);

        if ($request->wallet_type !== 'all') {
            $query->where('wallet_type', $request->wallet_type);
        }

        if ($request->date_range !== 'all') {
            if ($request->date_range === 'custom' && $request->filled('custom_date')) {
                $query->whereDate('created_at', Carbon::parse($request->custom_date));
            } else {
                $daysBack = (int)$request->date_range;
                if ($daysBack === 0) {
                    $query->whereDate('created_at', Carbon::today());
                } else {
                    $query->where('created_at', '>=', Carbon::now()->subDays($daysBack));
                }
            }
        }

        if ($request->direction !== 'both') {
            $type = ($request->direction === 'income') ? TxnType::Deposit : TxnType::Subtract;
            $query->where('type', $type);
        }

        if ($request->has('system_only') && $request->system_only) {
            $query->where('method', 'system');
        }

        $transactions = $query->orderBy('created_at', 'desc')->get();

        if ($transactions->isEmpty()) {
            notify()->error('No transactions found matching your criteria.', 'Info');
            return redirect()->back();
        }

        session()->put("bulk_delete_preview_{$id}", $transactions);
        session()->flash("show_bulk_delete_preview_{$id}", true);

        return redirect()->back();
    }

    public function bulkDeleteCommit(Request $request, $id)
    {
        $user = User::findOrFail($id);

        if (auth('admin')->user() && auth('admin')->user()->hasAnyRole(['Account Officer', 'Account-Officer'], 'admin') && !auth('admin')->user()->hasAnyRole(['Super-Admin', 'Super Admin'], 'admin')) {
            if ($user->staff_id != auth('admin')->id()) {
                abort(403, 'Unauthorized action.');
            }
        }

        $txnIds = $request->input('txn_ids', []);
        
        if (empty($txnIds)) {
            notify()->error('No transactions were selected for deletion.', 'Error');
            session()->forget("bulk_delete_preview_{$id}");
            return redirect()->back();
        }

        $transactions = \App\Models\Transaction::where('user_id', $id)
            ->whereIn('id', $txnIds)
            ->get();

        $deletedCount = 0;
        $netAdd = 0;
        $netSub = 0;

        foreach ($transactions as $txn) {
            $amount = $txn->final_amount; // Use the exact same impact it had
            
            // Reversal logic:
            // If it was a Deposit (Income), it artificially increased balance -> therefore we subtract to reverse it.
            // If it was a Subtract (Outcome), it artificially decreased balance -> therefore we add to reverse it.
            if ($txn->type == TxnType::Deposit) {
                $this->updateUserBalance($user, $txn->wallet_type ?? 'default', $amount, 'subtract');
                $netSub += $amount;
            } else {
                $this->updateUserBalance($user, $txn->wallet_type ?? 'default', $amount, 'add');
                $netAdd += $amount;
            }
            
            $txn->delete();
            $deletedCount++;
        }

        session()->forget("bulk_delete_preview_{$id}");

        $msg = "$deletedCount transactions deleted successfully and ledgers correctly reversed.";
        notify()->success($msg, 'Success');

        return redirect()->back();
    }

    private function getSmartAmount($cat, $reqMin, $reqMax)
    {
        $ranges = [
            'sub'    => ['min' => 5,    'max' => 50],
            'micro'  => ['min' => 2,    'max' => 30],
            'small'  => ['min' => 30,   'max' => 300],
            'medium' => ['min' => 300,  'max' => 3000],
            'large'  => ['min' => 3000, 'max' => 1000000], // Use high max for large items
            'flex'   => ['min' => $reqMin, 'max' => $reqMax],
        ];

        $range = $ranges[$cat] ?? $ranges['small'];

        // Intersect category range with request range
        $finalMin = max($range['min'], $reqMin);
        $finalMax = min($range['max'], $reqMax);

        // Safety check: If request min is already higher than category max
        // (e.g. Admin wants a $500 Netflix sub)
        if ($finalMin > $finalMax) {
            $finalMin = $reqMin;
            $finalMax = $reqMax;

            // For subscriptions and micro buys, if the admin range is huge, 
            // keep it very close to the floor to avoid $20,000 coffees.
            if (in_array($cat, ['sub', 'micro', 'small'])) {
                $finalMax = $reqMin + ($reqMin * 0.15); // max 15% variance from the floor
                if ($finalMax > $reqMax) $finalMax = $reqMax;
            }
        }

        // If it's a 'large' or 'flex' category, we more broadly respect the admin's max
        if ($cat === 'large' || $cat === 'flex') {
            $finalMax = $reqMax;
        }

        return round(rand($finalMin * 100, $finalMax * 100) / 100, 2);
    }

    private function getWalletName($user, $wallet_type)
    {
        if ($wallet_type == 'default') return 'Checking Account';
        if ($wallet_type == 'primary_savings') return 'Primary Savings';
        if ($wallet_type == 'ira') return 'IRA';
        if ($wallet_type == 'heloc') return 'HELOC';
        if ($wallet_type == 'cc') return 'Credit Card';
        if ($wallet_type == 'loan') return 'Loan Account';
        
        $user_wallet = UserWallet::find($wallet_type);
        return $user_wallet?->currency?->name ?? 'Wallet';
    }

    private function updateUserBalance($user, $wallet_type, $amount, $op)
    {
        if ($wallet_type == 'default') {
            $op == 'add' ? $user->balance += $amount : $user->balance -= $amount;
            $user->save();
        } elseif ($wallet_type == 'primary_savings') {
            $op == 'add' ? $user->increment('savings_balance', $amount) : $user->decrement('savings_balance', $amount);
        } elseif ($wallet_type == 'ira') {
            $op == 'add' ? $user->increment('ira_balance', $amount) : $user->decrement('ira_balance', $amount);
        } elseif ($wallet_type == 'heloc') {
            $op == 'add' ? $user->increment('heloc_balance', $amount) : $user->decrement('heloc_balance', $amount);
        } elseif ($wallet_type == 'cc') {
            $op == 'add' ? $user->increment('cc_balance', $amount) : $user->decrement('cc_balance', $amount);
        } elseif ($wallet_type == 'loan') {
            $op == 'add' ? $user->increment('loan_balance', $amount) : $user->decrement('loan_balance', $amount);
        } else {
            $user_wallet = UserWallet::find($wallet_type);
            if ($user_wallet) {
                $op == 'add' ? $user_wallet->balance += $amount : $user_wallet->balance -= $amount;
                $user_wallet->save();
            }
        }
    }
}
