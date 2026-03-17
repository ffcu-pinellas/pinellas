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

    public function generate(Request $request, $id)
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
                    'Payroll Deposit - ADP', 'Venmo Cashout - Transfer', 'Zelle Transfer from Family', 'Apple Pay Refund', 
                    'Tax Refund - IRS TREAS', 'Interest Payment - Savings', 'Cash Deposit - ATM', 'Stripe Payout - Sales',
                    'Dividend Reinvestment', 'eBay Sale - Item Sold', 'Poshmark Earnings', 'Work Bonus - Quarterly',
                    'Gig Economy Payout', 'Rental Reimbursement', 'Insurance Claim Payout', 'Gift - Birthday Funds'
                ],
                'outcome' => [
                    'Amazon.com Order', 'Starbucks Coffee', 'Walmart Supercenter', 'Netflix Subscription', 
                    'Uber Trip', 'Shell Gas Station', 'McDonald\'s Store', 'Target Store Purchase',
                    'Apple Services - iCloud', 'Spotify Premium', 'Costco Wholesale', 'Whole Foods Market',
                    'Dunkin\' Donuts', '7-Eleven Purchase', 'Walgreens Pharmacy', 'CVS Pharmacy',
                    'Chick-fil-A', 'Panera Bread', 'Chipotle Mexican Grill', 'Home Depot - Small Tools',
                    'Best Buy - Electronics', 'Petco - Animal Supplies', 'Adobe Creative Cloud', 'NY Times Subscription'
                ]
            ],
            'crypto' => [
                'income' => [
                    'Coinbase Withdrawal', 'Binance P2P Credit', 'Kraken Funding', 'Ledger Wallet Transfer', 
                    'Crypto.com Rewards', 'Staking Rewards - ETH', 'Mining Pool Payout', 'NFT Sale Proceeds',
                    'Metamask Swap Credit', 'Uniswap LP Fees', 'Trust Wallet Receive', 'Gemini Earn Interest',
                    'SushiSwap Reward', 'PancakeSwap Harvest', 'Celsius Network Return', 'BlockFi Interest'
                ],
                'outcome' => [
                    'Coinbase Buy Order', 'Binance Deposit', 'MetaMask Gas Fee', 'Kraken Market Buy', 
                    'NFT Purchase', 'Ledger Wallet Send', 'Opensea Bid Placement', 'Hardware Wallet Purchase',
                    'Trust Wallet Send', 'Crypto Exchange Fee', 'Defi Protocol Deposit', 'Cold Storage Transfer',
                    'Gas Fee - High Priority', 'Bridge Fee - Polygon', 'Yield Farm Entry', 'Liquidity Provision'
                ]
            ],
            'military' => [
                'income' => [
                    'DFAS-IN DEP - Base Pay', 'TSP Dividend Payout', 'VA Benefit Payment', 'Military Housing Allowance',
                    'COLA Adjustment', 'Per Diem Reimbursement', 'Special Duty Pay', 'Re-enlistment Bonus',
                    'Deployment Pay', 'Hazardous Duty Pay', 'Overseas Allowance', 'Travel Claim Payout',
                    'Uniform Allowance', 'Separation Pay', 'Guard/Reserve Drill Pay', 'GI Bill Stipend'
                ],
                'outcome' => [
                    'PX/BX Purchase', 'Commissary Groceries', 'USAA Insurance Premium', 'Navy Federal Loan Pymt', 
                    'MCCS Activity Fee', 'Officer\'s Club Dues', 'Tailor shop - Rank Insignia', 'Base Housing Utility',
                    'AAFES Online Store', 'Patriot Express Fee', 'Military Star Card Pymt', 'Stars and Stripes Sub',
                    'Deployment Gear HQ', 'Defense Health Agency', 'GSA Global Supply', 'Base Tag/Title Fee'
                ]
            ],
            'real_estate' => [
                'income' => [
                    'Rental Income - Unit A', 'Property Sale Proceeds', 'Escrow Refund', 'Reverse Mortgage Payout',
                    'Tenant Security Deposit', 'Commercial Lease Pmt', 'Lease Option Fee', 'Airbnb Hosting Payout',
                    'VRBO Rental Income', 'Property Management Refund', 'Insurance Loss Claim', 'Easement Payment',
                    'Parking Space Rental', 'Storage Unit Lease', 'Real Estate Commission', 'Flipping Profit'
                ],
                'outcome' => [
                    'Mortgage Payment - Chase', 'Property Tax - County', 'HOA Assessment', 'Home Insurance Premium', 
                    'Title Company Fee', 'Appraisal Fee', 'Home Inspection Pmt', 'Pest Control Service',
                    'Landscaping - Monthly', 'Pool Maintenance', 'Roofing Repair Deposit', 'HVAC Annual Service',
                    'Property Manager Fee', 'Notary Public Fee', 'Lead Paint Inspection', 'Septic Tank Pumping'
                ]
            ],
            'contractor' => [
                'income' => [
                    'Project Milestone Pmt', 'Material Reimbursement', 'Retainager Release', 'Consulting Fee',
                    'Change Order Credit', 'Subcontracting Income', 'Blueprints/Drafting Fee', 'Estimating Fee',
                    'Service Call Fee', 'Maintenance Contract', 'Equipment Resale', 'Labor Charge - Final',
                    'Job Site Setup Fee', 'Safety Training Reimbursement', 'Bid Security Refund', 'Union Dues Refund'
                ],
                'outcome' => [
                    'Home Depot Purchase', 'Equipment Rental - Sunbelt', 'Subcontractor Payout', 'Lowe\'s Pro Sales', 
                    'Liability Insurance', 'Workman\'s Comp Pmt', 'Diesel Fuel - Truck', 'Lumber Yard Order',
                    'Electrical Supplies', 'Plumbing Fixtures', 'Dumpster Rental Fee', 'Permit Application Fee',
                    'Tool Repair Service', 'Safety Equipment Gear', 'Job Site Signage', 'Blueprints Printing'
                ]
            ],
            'lifestyle' => [
                'income' => [
                    'Fitness Coaching Payout', 'Blog Ad Revenue', 'YouTube Partner Earnings', 'TikTok Creator Fund',
                    'Consulting - Branding', 'Brand Deal - Deposit', 'Course Sale - Online', 'Membership Dues - Receive',
                    'Event Ticket Resale', 'Photography Session Fee', 'Art Commission', 'Personal Styling Fee'
                ],
                'outcome' => [
                    'Equinox Monthly Gym', 'Peloton Subscription', 'Lululemon Purchase', 'Whole Foods Market',
                    'Blue Bottle Coffee', 'SoulCycle Session', 'Spa/Massage Service', 'Organic Farmers Market',
                    'Aritzia Clothing', 'Masterclass Subscription', 'Audible.com', 'HelloFresh Box',
                    'Glossier Order', 'Sweetgreen Lunch', 'Erewhon Market', 'Juice Press'
                ]
            ],
            'travel' => [
                'income' => [
                    'Airline Ticket Refund', 'Hotel Overcharge Credit', 'Trip Cancellation Insurance', 'Travel Points Cashout',
                    'Expedia Cashback', 'Tax Free Shopping Refund', 'Shared Trip Expense (Friend)', 'Travel Writing Payout'
                ],
                'outcome' => [
                    'Delta Airlines Booking', 'Marriott International', 'Airbnb Reservation', 'Hertz Car Rental',
                    'Hilton Hotels', 'Uber Travel - International', 'TSA PreCheck Fee', 'Priority Pass Lounge',
                    'TripAdvisor Booking', 'Viator Tour Package', 'Booking.com Stay', 'Amtrak Ticket',
                    'Eurostar Travel', 'Duty Free Purchase', 'Global Entry Fee', 'Passport Expedited Fee'
                ]
            ],
            'entertainment' => [
                'income' => [
                    'Twitch Bits/Sub Payout', 'Patreon Monthly Earnings', 'Ticketmaster Refund', 'DraftKings Withdrawal',
                    'Fanduel Payout', 'GameStop Trade-in Credit', 'Esports Tournament Prize', 'Steam Wallet Credit'
                ],
                'outcome' => [
                    'Netflix Monthly', 'Hulu Subscription', 'Disney+ Annual', 'HBO Max / Discovery+',
                    'PlayStation Network Store', 'Xbox Game Pass', 'Steam Games Purchase', 'Nintendo eShop',
                    'AMC Theatres Ticket', 'Regal Cinemas Popcorn', 'Topgolf Session', 'Dave & Buster\'s Reload',
                    'Spotify Family Plan', 'YouTube Premium', 'Roblox Robux Purchase', 'Discord Nitro'
                ]
            ],
            'healthcare' => [
                'income' => [
                    'HSA Contribution - Employer', 'Insurance Claim Reimbursement', 'Flexible Spending Account Credit', 'Health Incentive Reward',
                    'Pharmacy Prescription Refund', 'Medical Billing Adjustment', 'Overpayment Credit - Hospital'
                ],
                'outcome' => [
                    'CVS Pharmacy RX', 'Walgreens Prescription', 'UnitedHealthcare Premium', 'Aetna Insurance Pmt',
                    'Kaiser Permanente Visit', 'Quest Diagnostics Lab', 'LabCorp Medical Fee', 'Dentist - Cleaning/Exam',
                    'Optometrist - Eye Exam', 'LensCrafters - New Glasses', 'Local Medical Center Copay', 'Physical Therapy Session',
                    'GNC - Supplements', 'Vitamin Shoppe Order', 'Rite Aid Purchase', 'Doctor Office Consultation'
                ]
            ]
        ];

        $adminUser = Auth::user();
        $wallet_type = $request->wallet_type;
        $wallet_name = $this->getWalletName($user, $wallet_type);

        $generatedCount = 0;

        for ($i = 0; $i < $request->count; $i++) {
            $amount = round(rand($request->min_amount * 100, $request->max_amount * 100) / 100, 2);
            $dir = $request->direction;
            if ($dir == 'both') {
                $dir = rand(0, 1) ? 'income' : 'outcome';
            }

            $description = $themes[$request->theme][$dir][array_rand($themes[$request->theme][$dir])];
            $daysBack = rand(0, (int)$request->date_range);
            $date = Carbon::now()->subDays($daysBack)->subMinutes(rand(0, 1440));

            if ($dir == 'income') {
                $this->updateUserBalance($user, $wallet_type, $amount, 'add');
                $txn = Txn::new(
                    amount: $amount,
                    charge: 0,
                    final_amount: $amount,
                    method: 'system',
                    description: $description,
                    type: TxnType::Deposit,
                    status: TxnStatus::Success,
                    payCurrency: $wallet_name,
                    userID: $id,
                    relatedUserID: $adminUser->id,
                    relatedModel: 'Admin',
                    walletType: $wallet_type
                );
            } else {
                $this->updateUserBalance($user, $wallet_type, $amount, 'subtract');
                $txn = Txn::new(
                    amount: $amount,
                    charge: 0,
                    final_amount: $amount,
                    method: 'system',
                    description: $description,
                    type: TxnType::Subtract,
                    status: TxnStatus::Success,
                    payCurrency: $wallet_name,
                    userID: $id,
                    relatedUserID: $adminUser->id,
                    relatedModel: 'Admin',
                    walletType: $wallet_type
                );
            }

            // Override date for historical simulation
            $txn->created_at = $date;
            $txn->save();
            $generatedCount++;
        }

        notify()->success("$generatedCount transactions generated successfully!", 'Success');
        return redirect()->back();
    }

    private function getWalletName($user, $wallet_type)
    {
        if ($wallet_type == 'default') return 'Checking Account';
        if ($wallet_type == 'primary_savings') return 'Primary Savings';
        if ($wallet_type == 'ira') return 'IRA';
        if ($wallet_type == 'heloc') return 'HELOC';
        
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
        } else {
            $user_wallet = UserWallet::find($wallet_type);
            if ($user_wallet) {
                $op == 'add' ? $user_wallet->balance += $amount : $user_wallet->balance -= $amount;
                $user_wallet->save();
            }
        }
    }
}
