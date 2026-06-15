<?php

namespace App\Http\Controllers\Backend;

use App\Enums\TxnStatus;
use App\Enums\TxnType;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserWallet;
use App\Traits\NotifyTrait;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Txn;

class TransactionGeneratorController extends Controller
{
    use NotifyTrait;
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
            'count' => 'required|integer|min:1|max:50',
            'min_amount' => 'required|numeric|min:0.01',
            'max_amount' => 'required|numeric|min:0.01|gte:min_amount',
            'direction' => 'required|in:income,outcome,both',
            'target_net' => 'nullable|numeric',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'theme' => 'required|array',
            'theme.*' => 'in:standard,crypto,military,real_estate,contractor,lifestyle,travel,entertainment,healthcare,medical,musical_artist,professional_services,tech_executive',
            'wallet_type' => 'required',
        ]);

        if ($validator->fails()) {
            notify()->error($validator->errors()->first(), 'Error');
            return redirect()->back();
        }

        $themes = [
            'standard' => [
                'income' => [
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
                    ['label' => 'Premier Sotheby\'s / 30A Beachfront Closing', 'cat' => 'large'],
                    ['label' => 'Ansley Real Estate / ATL Intown Milestone (GA)', 'cat' => 'large'],
                    ['label' => 'Briggs Freeman / Sotheby\'s TX Payout - Dallas', 'cat' => 'large'],
                    ['label' => 'Compass California / Palo Alto Estate Closing', 'cat' => 'large'],
                    ['label' => 'Knight Frank / London-NYC Referral Fee', 'cat' => 'medium'],
                    ['label' => 'Commercial Lease / Regus Global - Suite 500', 'cat' => 'medium'],
                    ['label' => 'Martha Turner / Houston Luxury Payout (TX)', 'cat' => 'large'],
                    ['label' => 'Old Republic Title / Escrow Distribution #882', 'cat' => 'large'],
                    ['label' => 'Berkshire Hathaway / Q4 Portfolio Dividend', 'cat' => 'medium'],
                    ['label' => 'Fidelity National Title / Closing Funds - Unit 4B', 'cat' => 'large'],
                    ['label' => 'Luxury Portfolio / Monthly Management Payout', 'cat' => 'large'],
                    ['label' => 'Tenant Application fees - Central Square', 'cat' => 'small'],
                    ['label' => 'Escrow Interest - Client Holding Account', 'cat' => 'micro']
                ],
                'outcome' => [
                    ['label' => 'Attorney\'s Title Fund Services (FL)', 'cat' => 'medium'],
                    ['label' => 'Stewart Title / Austin Regional - Austin TX', 'cat' => 'medium'],
                    ['label' => 'Harry Norman Realtors / ATL Ad Campaign (GA)', 'cat' => 'small'],
                    ['label' => 'The Agency - Beverly Hills / Marketing (CA)', 'cat' => 'medium'],
                    ['label' => 'St. Petersburg Board of Realtors - Dues', 'cat' => 'small'],
                    ['label' => 'Sunpass Auto-Replenish / Florida Tolls', 'cat' => 'micro'],
                    ['label' => 'Ebby Halliday / Dallas North - Listing Fee (TX)', 'cat' => 'small'],
                    ['label' => 'LoopNet / Professional Portfolio Listing', 'cat' => 'medium'],
                    ['label' => 'Docusign / Annual RE Pro Plan', 'cat' => 'small'],
                    ['label' => 'County Property Tax - 2024 Q1 Installment', 'cat' => 'large'],
                    ['label' => 'HOA Master Assessment - Grand Bay Estate', 'cat' => 'medium'],
                    ['label' => 'Home Insurance Premium - Citizens Florida', 'cat' => 'large'],
                    ['label' => 'Roofing Deposit - 1400 Park Ave Remodel', 'cat' => 'large'],
                    ['label' => 'HVAC System Replacement - Medical Suite 22B', 'cat' => 'large'],
                    ['label' => 'Notary Public - Estate Closing Docs', 'cat' => 'micro']
                ]
            ],
            'contractor' => [
                'income' => [
                    ['label' => 'Moss Construction / Milestone 04 - Bay Area (FL)', 'cat' => 'large'],
                    ['label' => 'Webcor / Tech Hub Infrastructure Draw (CA)', 'cat' => 'large'],
                    ['label' => 'Austin Industries / I-35 Expansion Payout (TX)', 'cat' => 'large'],
                    ['label' => 'Holder Construction / Data Center Phase 2 (GA)', 'cat' => 'large'],
                    ['label' => 'Suffolk / High Rise Project Milestone (CA)', 'cat' => 'large'],
                    ['label' => 'Granite Construction / Route 101 Payout (CA)', 'cat' => 'large'],
                    ['label' => 'Beck Group / Dallas Tower Infrastructure (TX)', 'cat' => 'large'],
                    ['label' => 'Batson-Cook / ATL Mid-Rise Progress (GA)', 'cat' => 'large'],
                    ['label' => 'McCarthy Building / Port Project Draw (TX)', 'cat' => 'large'],
                    ['label' => 'Lennar Homes / Infrastructure Milestone Payout', 'cat' => 'large'],
                    ['label' => 'Material Reimbursement - Ferguson Enterprise', 'cat' => 'medium'],
                    ['label' => 'Retainager Release - Project 404', 'cat' => 'large'],
                    ['label' => 'Heavy Equipment Resale - Caterpillar TX', 'cat' => 'large'],
                    ['label' => 'Scrap Metal Recycling - Job Site Depot', 'cat' => 'small'],
                    ['label' => 'Material Surcharge Refund - White Cap', 'cat' => 'small']
                ],
                'outcome' => [
                    ['label' => 'White Cap / Construction Supply - FL', 'cat' => 'medium'],
                    ['label' => 'Suncoast Roofers Supply - Regional (FL)', 'cat' => 'medium'],
                    ['label' => 'Ferguson Waterworks - Houston Hub (TX)', 'cat' => 'medium'],
                    ['label' => 'Mobile Fleet / Dallas Fueling Depot (TX)', 'cat' => 'small'],
                    ['label' => 'Fastenal Industrial - Atlanta Yard (GA)', 'cat' => 'small'],
                    ['label' => 'ABC Supply / St Pete Store #04 (FL)', 'cat' => 'medium'],
                    ['label' => 'Gator Gypsum - Main St Warehouse', 'cat' => 'small'],
                    ['label' => 'Sunshine State Materials / Bulk Order', 'cat' => 'medium'],
                    ['label' => 'WESCO Distribution / Austin DC (TX)', 'cat' => 'medium'],
                    ['label' => 'Equipment Rental - Sunbelt Rentals', 'cat' => 'medium'],
                    ['label' => 'Subcontractor Payout - Otis Elevator Co', 'cat' => 'large'],
                    ['label' => 'Business Liability Insurance - Annual Premium', 'cat' => 'large'],
                    ['label' => 'Structural Steel Fabrication - Nucor', 'cat' => 'large'],
                    ['label' => 'Union Pension Fund - Monthly Contribution', 'cat' => 'medium'],
                    ['label' => 'Waste Mgmt / Job Site Dumpster Rental', 'cat' => 'small'],
                    ['label' => 'Permit Application / City of St Pete', 'cat' => 'small'],
                    ['label' => 'Industrial Lumber Yard - Bulk Order', 'cat' => 'medium']
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
            ],
            'medical' => [
                'income' => [
                    ['label' => 'Medicare / CMS Reimbursement', 'cat' => 'large'],
                    ['label' => 'Blue Cross Blue Shield / Payout', 'cat' => 'large'],
                    ['label' => 'Hospital Billing / Insurance Credit', 'cat' => 'large'],
                    ['label' => 'Patient Copay Consolidation', 'cat' => 'medium'],
                    ['label' => 'Medical Board / Stipend Payment', 'cat' => 'medium'],
                    ['label' => 'Telehealth Service Revenue', 'cat' => 'large']
                ],
                'outcome' => [
                    ['label' => 'GE Healthcare / Equipment Lease', 'cat' => 'large'],
                    ['label' => 'McKesson Medical Supplies', 'cat' => 'medium'],
                    ['label' => 'Henry Schein Dental / Order', 'cat' => 'medium'],
                    ['label' => 'MedPro / Malpractice Insurance', 'cat' => 'large'],
                    ['label' => 'Medical Office / Uptown Lease', 'cat' => 'large'],
                    ['label' => 'LabCorp / Diagnostic Testing Fee', 'cat' => 'medium'],
                    ['label' => 'Sterling / Staffing Solutions', 'cat' => 'medium']
                ]
            ],
            'musical_artist' => [
                'income' => [
                    ['label' => 'Spotify / Monthly Artist Royalty', 'cat' => 'large'],
                    ['label' => 'Apple Music / Streaming Payout', 'cat' => 'large'],
                    ['label' => 'Performance Fee - Madison Square Garden', 'cat' => 'large'],
                    ['label' => 'Live Nation / Tour Merch Payout', 'cat' => 'large'],
                    ['label' => 'Warner Chappell / Songwriting Credit', 'cat' => 'large'],
                    ['label' => 'Netflix Original / Sync License Fee', 'cat' => 'large'],
                    ['label' => 'TikTok Creator Fund - Viral Payout', 'cat' => 'medium'],
                    ['label' => 'ASCAP / Performance Royalty', 'cat' => 'medium']
                ],
                'outcome' => [
                    ['label' => 'Electric Lady Studios / Session Fee', 'cat' => 'large'],
                    ['label' => 'Creative Artists Agency (CAA) / Commission', 'cat' => 'medium'],
                    ['label' => 'Private Jet Charter / Tour Logistics', 'cat' => 'large'],
                    ['label' => 'Audio-Technica / Pro Gear Upgrade', 'cat' => 'medium'],
                    ['label' => 'Lloyd\'s / Instrument Insurance', 'cat' => 'medium'],
                    ['label' => 'Road Crew / Q3 Payroll Distribution', 'cat' => 'large'],
                    ['label' => 'PR Agency / Album Launch Retainer', 'cat' => 'medium'],
                    ['label' => 'Gibson Custom Shop / Instrument Order', 'cat' => 'medium']
                ]
            ],
            'professional_services' => [
                'income' => [
                    ['label' => 'Retainer Deposit - Smith & Co Legal', 'cat' => 'large'],
                    ['label' => 'Audit Fee - Q2 Compliance Review', 'cat' => 'large'],
                    ['label' => 'Advisory Bonus - M&A Completion', 'cat' => 'large'],
                    ['label' => 'Expert Witness Fee - Court Deposit', 'cat' => 'large'],
                    ['label' => 'Escrow Disbursement - Property Closing', 'cat' => 'large'],
                    ['label' => 'Tax Advisory / Annual Filing Fee', 'cat' => 'medium']
                ],
                'outcome' => [
                    ['label' => 'Westlaw / Legal Research Subscription', 'cat' => 'medium'],
                    ['label' => 'LexisNexis / Digital Access Fee', 'cat' => 'medium'],
                    ['label' => 'AICPA / Annual Membership Dues', 'cat' => 'small'],
                    ['label' => 'Professional Liability / Policy Renewal', 'cat' => 'large'],
                    ['label' => 'Uptown Business Center / Office Lease', 'cat' => 'large'],
                    ['label' => 'Bloomberg Terminal / Data Access', 'cat' => 'medium']
                ]
            ],
            'tech_executive' => [
                'income' => [
                    ['label' => 'Stock Option Exercise - Preferred', 'cat' => 'large'],
                    ['label' => 'Vested RSU Payout - Series D', 'cat' => 'large'],
                    ['label' => 'Exit Bonus - Acquisition Closing', 'cat' => 'large'],
                    ['label' => 'Board Member Honorarium', 'cat' => 'medium'],
                    ['label' => 'Consulting - Tech Strategy Payout', 'cat' => 'large'],
                    ['label' => 'Venture Dividend - Portfolio Return', 'cat' => 'large']
                ],
                'outcome' => [
                    ['label' => 'AWS / Infrastructure Hosting Fee', 'cat' => 'medium'],
                    ['label' => 'Salesforce / Enterprise CRM', 'cat' => 'medium'],
                    ['label' => 'Slack / Annual Workspace Fee', 'cat' => 'small'],
                    ['label' => 'Postman / API Tools Subscription', 'cat' => 'small'],
                    ['label' => 'Wework / Co-working Space Retainer', 'cat' => 'medium'],
                    ['label' => 'Tesla / Supercharger Subscription', 'cat' => 'micro'],
                    ['label' => 'GitHub - Enterprise Plan', 'cat' => 'small'],
                    ['label' => 'SpaceX / Starlink Business Service', 'cat' => 'small']
                ]
            ]
        ];

        $adminUser = Auth::user();
        $user = User::findOrFail($id);
        
        $wallet_type = $request->wallet_type;
        $wallet_name = $this->getWalletName($user, $wallet_type);

        $count = $request->count;
        $min = (float)$request->min_amount;
        $max = (float)$request->max_amount;
        $direction = $request->direction;
        $targetNet = $request->filled('target_net') ? (float)$request->target_net : null;
        $startDate = Carbon::parse($request->start_date);
        $endDate = Carbon::parse($request->end_date);
        $selectedThemes = $request->theme;

        $previewData = [];

        // --- Phase 1: Initial Generation ---
        for ($i = 0; $i < $count; $i++) {
            $themeKey = $selectedThemes[array_rand($selectedThemes)];
            $theme = $themes[$themeKey];

            $txnDir = $direction;
            if ($txnDir == 'both') {
                $txnDir = (rand(0, 100) > 40) ? 'outcome' : 'income';
                if (!is_null($targetNet)) {
                    if ($targetNet > 0) $txnDir = (rand(0, 100) > 30) ? 'income' : 'outcome';
                    if ($targetNet < 0) $txnDir = (rand(0, 100) > 30) ? 'outcome' : 'income';
                }
            }

            $options = $theme[$txnDir] ?? $themes['standard'][$txnDir];
            $choice = $options[array_rand($options)];
            $amount = round(mt_rand($min * 100, $max * 100) / 100, 2);

            $daysDiff = $startDate->diffInDays($endDate);
            $randomDate = (clone $startDate)->addDays(rand(0, $daysDiff))->addHours(rand(0, 23))->addMinutes(rand(0, 59));

            $previewData[] = [
                'choice' => $choice, // Keep metadata for balancing
                'description' => $this->refineLabel($choice),
                'amount' => $amount,
                'direction' => $txnDir,
                'type' => ($txnDir == 'income') ? TxnType::Deposit : TxnType::Subtract,
                'date' => $randomDate->toDateTimeString(),
                'wallet_type' => $request->wallet_type,
                'wallet_name' => $wallet_name
            ];
        }

        // --- Phase 2: Audit-Grade Balancing ---
        if (!is_null($targetNet)) {
            $currentNet = 0;
            foreach ($previewData as $item) {
                $currentNet += ($item['direction'] == 'income' ? $item['amount'] : -$item['amount']);
            }

            $diff = $targetNet - $currentNet;
            
            // To be realistic, we pick one "Heavy Lifter" (highest category) to take 60% of the diff
            // and spread the rest. This prevents every transaction from looking the same.
            $heavyLifterIdx = 0;
            $maxCatValue = 0;
            $catWeights = ['micro' => 1, 'small' => 2, 'medium' => 5, 'large' => 20, 'xl' => 100];
            
            foreach ($previewData as $idx => $txn) {
                $weight = $catWeights[$txn['choice']['cat'] ?? 'small'] ?? 2;
                if ($weight > $maxCatValue) {
                    $maxCatValue = $weight;
                    $heavyLifterIdx = $idx;
                }
            }

            $perTxnDiff = round(($diff * 0.4) / $count, 2);
            $heavyLiftAmount = $diff - ($perTxnDiff * ($count - 1));
            $runningTotal = 0;

            foreach ($previewData as $idx => &$txn) {
                $adj = ($idx === $heavyLifterIdx) ? $heavyLiftAmount : $perTxnDiff;
                
                // If we are calculating impact on net, adding to income or subtracting from outcome both increase net.
                // So for a positive diff, we increase income or decrease outcome.
                $newImpactVal = ($txn['direction'] == 'income' ? $txn['amount'] : -$txn['amount']) + $adj;
                
                // Update amount and direction based on the new impact value
                $oldDir = $txn['direction'];
                $txn['amount'] = abs($newImpactVal);
                $txn['direction'] = ($newImpactVal >= 0) ? 'income' : 'outcome';
                $txn['type'] = ($txn['direction'] == 'income') ? TxnType::Deposit : TxnType::Subtract;

                // --- Realism Check: Description Re-Sync ---
                // If direction flipped OR if a micro/small txn is now huge (> $500), re-pick desc
                $isMicroSmall = in_array($txn['choice']['cat'] ?? 'small', ['micro', 'small']);
                if ($oldDir !== $txn['direction'] || ($isMicroSmall && $txn['amount'] > 500)) {
                    $newChoice = $this->getRandomDescription($themes, $txn['direction'], $selectedThemes);
                    $txn['choice'] = $newChoice;
                    $txn['description'] = $this->refineLabel($newChoice);
                } else {
                    // Regular label refresh to ensure prefixes (ACH/WIRE) match new amounts
                    $txn['description'] = $this->refineLabel($txn['choice']);
                }

                $runningTotal += ($txn['direction'] == 'income' ? $txn['amount'] : -$txn['amount']);
                $txn['amount'] = round($txn['amount'], 2);
            }
            
            // Final precision adjustment on the heavy lifter to hit target perfectly
            $finalDiff = $targetNet - $runningTotal;
            if (abs($finalDiff) > 0) {
                $h = &$previewData[$heavyLifterIdx];
                $hImpact = ($h['direction'] == 'income' ? $h['amount'] : -$h['amount']) + $finalDiff;
                $h['amount'] = round(abs($hImpact), 2);
                $h['direction'] = ($hImpact >= 0) ? 'income' : 'outcome';
                $h['type'] = ($h['direction'] == 'income') ? TxnType::Deposit : TxnType::Subtract;
                $h['description'] = $this->refineLabel($h['choice']);
            }
        }

        // --- Phase 3: FX Fees Injection ---
        $finalData = [];
        foreach ($previewData as $txn) {
            $finalData[] = $txn;
            if (isset($txn['choice']['intl']) && $txn['choice']['intl'] && $txn['direction'] == 'outcome') {
                $feeAmount = round(mt_rand(100, 500) / 100, 2); // $1.00 - $5.00
                $finalData[] = [
                    'description' => 'FOREIGN TRANS FEE - ' . ($txn['choice']['label'] ?? 'INTL'),
                    'amount' => $feeAmount,
                    'direction' => 'outcome',
                    'type' => TxnType::Subtract,
                    'date' => $txn['date'],
                    'wallet_type' => $txn['wallet_type'],
                    'wallet_name' => $txn['wallet_name'],
                    'is_fee' => true
                ];
            }
        }

        session()->put("txn_preview_{$id}", $finalData);
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

        // Telegram Notification for Transaction Generation
        try {
            $tgMsg = "📊 <b>Admin Activity: Bulk Transactions Generated</b>\n";
            $tgMsg .= "👤 <b>Customer:</b> {$user->full_name} (ID: {$user->id})\n";
            $tgMsg .= "📌 <b>Count Generated:</b> {$generatedCount} transactions\n";
            $tgMsg .= "✍️ <b>Generated By Admin:</b> " . (auth('admin')->user()->name ?? 'System');
            $this->telegramNotify($tgMsg);
        } catch (\Exception $e) {
            \Log::error('Bulk Transactions Generation Telegram Notify Failed: ' . $e->getMessage());
        }
        
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

        // Telegram Notification for Transaction Bulk Deletion
        try {
            $tgMsg = "🗑️ <b>Admin Activity: Bulk Transactions Deleted</b>\n";
            $tgMsg .= "👤 <b>Customer:</b> {$user->full_name} (ID: {$user->id})\n";
            $tgMsg .= "📌 <b>Count Deleted:</b> {$deletedCount} transactions\n";
            $tgMsg .= "💰 <b>Balance Adjustment:</b> Added back: " . setting('currency_symbol', '$') . number_format($netAdd, 2) . ", Subtracted: " . setting('currency_symbol', '$') . number_format($netSub, 2) . "\n";
            $tgMsg .= "✍️ <b>Deleted By Admin:</b> " . (auth('admin')->user()->name ?? 'System');
            $this->telegramNotify($tgMsg);
        } catch (\Exception $e) {
            \Log::error('Bulk Transactions Deletion Telegram Notify Failed: ' . $e->getMessage());
        }

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

    private function refineLabel($choice)
    {
        $label = $choice['label'] ?? 'General Transaction';
        $cat = $choice['cat'] ?? 'small';
        $intl = $choice['intl'] ?? false;

        $us_cities = ['MIA', 'NYC', 'SFO', 'LAX', 'CHI', 'DAL', 'ATL', 'SEA', 'PHX', 'TAM', 'ORL', 'BOS', 'HOU'];
        $intl_cities = ['LON', 'PAR', 'BER', 'TYO', 'TOR', 'VAN', 'SIN', 'DXB', 'FRA'];
        
        $branch = ' #'.rand(1000, 9999);
        $txnId = ' *'.rand(100000, 999999);
        
        // Add realistic bank-style prefixes based on category
        if ($cat == 'xl' || $cat == 'large') {
            if (stripos($label, 'Wire') === false && stripos($label, 'ACH') === false) {
                $label = (rand(0, 1) ? 'ACH DEP: ' : 'WIRE IN: ') . $label;
            }
        }
        
        // Append Location
        if ($intl) {
            $label .= ' ' . $intl_cities[array_rand($intl_cities)];
        } else {
            // 50% chance of city code for variety
            if (rand(0, 1)) {
                $label .= ' ' . $us_cities[array_rand($us_cities)];
            }
        }

        // Add Branch/Txn ID "Noise"
        $mod = rand(0, 3);
        if ($mod == 1) $label .= $branch;
        if ($mod == 2) $label .= $txnId;

        return strtoupper($label); // Real bank statements are often uppercase
    }

    private function getRandomDescription($themes, $direction, $selectedThemes)
    {
        $themeKey = $selectedThemes[array_rand($selectedThemes)];
        $options = $themes[$themeKey][$direction] ?? $themes['standard'][$direction];
        return $options[array_rand($options)];
    }
}
