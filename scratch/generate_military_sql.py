import datetime
import random
import re

random.seed(1776)

def generate_tnx():
    chars = "0123456789ABCDEF"
    return "TRX" + "".join(random.choices(chars, k=10))

# =========================================================================
# COMPREHENSIVE MERCHANT CATALOG FOR ACTIVE DUTY DAILY LIFE
# =========================================================================

groceries = [
    ("Whole Foods Market #10482 - Austin Lamar", (65.20, 345.80), "Debit Card"),
    ("Whole Foods Market - Organic Groceries & Deli", (45.15, 280.40), "Apple Pay"),
    ("Trader Joe's #522 - Produce & Specialty Foods", (35.40, 185.90), "Apple Pay"),
    ("Trader Joe's #714 - Weekly Grocery Run", (28.75, 145.20), "Debit Card"),
    ("Publix Super Markets #1184 - Weekly Market", (75.50, 320.10), "Debit Card"),
    ("Publix Deli - Pub Subs & Rotisserie Meal", (18.25, 65.40), "Apple Pay"),
    ("Costco Wholesale #1084 - Bulk Grocery & Household", (140.00, 560.85), "Debit Card"),
    ("Costco Wholesale #642 - Meat & Pantry Restock", (110.50, 480.20), "Debit Card"),
    ("Sam's Club #6420 - Member Bulk Shopping", (95.20, 420.75), "Debit Card"),
    ("Kroger Marketplace #712 - Grocery Basket", (45.80, 230.40), "Debit Card"),
    ("Kroger Fuel & Grocery #419", (55.20, 195.80), "Debit Card"),
    ("ALDI Store #88 - Weekly Food Basket", (32.10, 160.50), "Debit Card"),
    ("H-E-B Grocery Store #415 - San Antonio", (60.45, 275.90), "Debit Card"),
    ("H-E-B Plus! - Supermarket Run", (72.80, 310.40), "Debit Card"),
    ("Sprouts Farmers Market - Organic Fresh Produce", (40.20, 195.40), "Apple Pay"),
    ("DECA Fort Liberty Main Commissary - Groceries", (110.00, 430.50), "Debit Card"),
    ("DECA Commissary - Household Provisions", (85.50, 360.25), "Debit Card"),
    ("DECA MacDill AFB Commissary - Weekly Food", (95.80, 390.40), "Debit Card"),
    ("DECA Commissary - Meat & Produce Depot", (70.15, 310.80), "Debit Card"),
    ("Target Grocery - Quick Food Pickup", (25.40, 110.20), "Apple Pay"),
]

fast_food = [
    ("Chick-fil-A #0184 - Spicy Chicken Sandwich Meal", (12.45, 38.60), "Apple Pay"),
    ("Chick-fil-A - Drive Thru Breakfast Burrito", (9.80, 24.50), "Apple Pay"),
    ("Chick-fil-A #2940 - Nugget Meal & Milkshake", (14.20, 35.80), "Apple Pay"),
    ("Starbucks Store #1408 - Morning Iced Americano", (6.75, 18.40), "Apple Pay"),
    ("Starbucks Store #8912 - Nitro Cold Brew & Sandwich", (9.25, 24.50), "Apple Pay"),
    ("Starbucks Reserve - Cold Brew & Pastry", (11.25, 28.50), "Apple Pay"),
    ("Dunkin' #3019 - Medium Iced Coffee & Bagel", (7.45, 18.20), "Apple Pay"),
    ("Dunkin' - Donut Box & Cold Brew", (12.50, 26.40), "Apple Pay"),
    ("Dutch Bros Coffee - Iced Golden Eagle", (6.50, 15.80), "Apple Pay"),
    ("Chipotle Mexican Grill - Steak Burrito Bowl", (13.80, 34.50), "Apple Pay"),
    ("Chipotle Mexican Grill - Chicken Salad & Chips", (12.50, 28.90), "Apple Pay"),
    ("Panera Bread #2041 - You Pick Two Lunch Combo", (14.25, 42.10), "Debit Card"),
    ("Panera Bread - Bagel Pack & Morning Coffee", (11.80, 25.40), "Apple Pay"),
    ("Jersey Mike's Subs #1402 - #13 Original Italian Giant", (15.80, 38.90), "Debit Card"),
    ("Jersey Mike's Subs - Club Sub Combo", (13.40, 29.80), "Debit Card"),
    ("Jimmy John's #812 - Gourmet Sandwiches", (11.50, 26.80), "Apple Pay"),
    ("Five Guys Burgers & Fries - Bacon Cheeseburger & Fries", (16.40, 45.20), "Apple Pay"),
    ("Shake Shack - Double ShackBurger & Concrete Shake", (18.10, 48.60), "Apple Pay"),
    ("In-N-Out Burger #240 - Double-Double Animal Style", (10.50, 24.80), "Apple Pay"),
    ("Whataburger #1042 - Bacon & Cheese Whataburger Meal", (12.80, 28.40), "Apple Pay"),
    ("Panda Express #1209 - Bigger Plate Combo", (12.35, 32.80), "Apple Pay"),
    ("McDonald's #4912 - Egg McMuffin & Coffee", (8.50, 21.40), "Apple Pay"),
    ("McDonald's - Double Quarter Pounder Meal", (11.20, 26.40), "Apple Pay"),
    ("Wendy's #8102 - Baconator Meal", (10.15, 25.75), "Apple Pay"),
    ("Taco Bell #3094 - Cravings Box Dinner", (8.90, 24.40), "Apple Pay"),
    ("Subway Sandwiches #11204 - Turkey Breast Footlong", (10.50, 25.20), "Apple Pay"),
    ("Culver's - ButterBurger & Fresh Frozen Custard", (13.40, 31.80), "Apple Pay"),
    ("Smoothie King - Gladiator Protein Smoothie", (8.50, 18.20), "Apple Pay"),
]

dining = [
    ("Texas Roadhouse - 12oz Ribeye & Fresh Rolls", (48.50, 165.20), "Debit Card"),
    ("Texas Roadhouse - Bone-in Ribeye & Ribs Combo", (62.00, 195.40), "Debit Card"),
    ("Outback Steakhouse - Outback Special Sirloin", (55.00, 180.40), "Debit Card"),
    ("LongHorn Steakhouse #5019 - Flo's Filet Dinner", (50.00, 155.40), "Debit Card"),
    ("The Capital Grille - Dry Aged NY Strip Dinner", (180.00, 480.00), "Debit Card"),
    ("Ruth's Chris Steak House - Prime Rib Feast", (160.00, 420.00), "Debit Card"),
    ("Fleming's Prime Steakhouse - Seafood & Steaks", (175.00, 450.00), "Debit Card"),
    ("Olive Garden #1422 - Tour of Italy Family Dinner", (42.50, 135.80), "Debit Card"),
    ("Bonefish Grill - Grilled Salmon & Cocktails", (65.20, 190.00), "Debit Card"),
    ("The Cheesecake Factory - Dinner & Godiva Cheesecake", (58.40, 175.60), "Apple Pay"),
    ("The Cheesecake Factory - Weekend Lunch Outing", (45.20, 125.80), "Apple Pay"),
    ("Buffalo Wild Wings - Traditional Wings & Beer", (35.80, 115.20), "Debit Card"),
    ("Buffalo Wild Wings - UFC Fight Night & Wings", (48.20, 142.50), "Debit Card"),
    ("Chili's Grill & Bar - 3 for Me Bacon Burger", (32.40, 98.50), "Apple Pay"),
    ("Mission BBQ - Texas Brisket & Sausage Feast", (28.90, 88.40), "Debit Card"),
    ("Franklin BBQ - Austin TX - Smoked Brisket", (65.00, 180.00), "Debit Card"),
    ("Tokyo Japanese Steakhouse - Hibachi Trio Dinner", (65.00, 210.50), "Debit Card"),
    ("Cheddar's Scratch Kitchen - Family Meal", (34.50, 110.20), "Debit Card"),
    ("Carrabba's Italian Grill - Chicken Marsala", (45.80, 140.60), "Debit Card"),
    ("Red Lobster Seafood Restaurant - Ultimate Feast", (52.00, 160.40), "Debit Card"),
    ("First Watch - Farmhouse Hash & Fresh Juice", (24.50, 68.20), "Apple Pay"),
    ("Waffle House #1842 - All-Star Special Breakfast", (14.20, 36.50), "Debit Card"),
    ("BJ's Restaurant & Brewhouse - Deep Dish Pizza & Pizookie", (42.80, 128.40), "Debit Card"),
    ("Yard House - Craft Beer Flight & Poke Nachos", (45.00, 135.00), "Debit Card"),
    ("Joe's Stone Crab - Miami Beach Seafood Dinner", (150.00, 380.00), "Debit Card"),
]

retail = [
    ("Amazon.com - Tech Gadgets & Charging Hub", (25.50, 180.00), "Debit Card"),
    ("Amazon.com - Home Essentials & Cleaning", (18.40, 145.50), "Debit Card"),
    ("Amazon.com - Tactical Backpack & Organizers", (35.20, 195.80), "Debit Card"),
    ("Amazon Prime Marketplace Order", (15.20, 125.80), "Debit Card"),
    ("Target Store #1842 - Bedding & Storage", (35.80, 260.40), "Apple Pay"),
    ("Target - Men's Apparel & Grooming Essentials", (25.10, 140.20), "Apple Pay"),
    ("Walmart Supercenter #2041 - Garage Supplies & Hardware", (45.60, 240.50), "Debit Card"),
    ("Walmart Store - Motor Oil & Auto Accessories", (30.20, 160.80), "Debit Card"),
    ("The Home Depot #0612 - Milwaukee Power Tools", (65.00, 450.00), "Debit Card"),
    ("The Home Depot - Patio Furniture & Grill Supplies", (45.50, 320.40), "Debit Card"),
    ("The Home Depot - Paint, Brushes & Hardware", (35.00, 210.00), "Debit Card"),
    ("Lowe's Home Improvement #1502 - Building Supplies", (40.00, 390.00), "Debit Card"),
    ("Best Buy #0421 - 4K Monitor & HDMI Cables", (45.20, 420.00), "Debit Card"),
    ("Best Buy - Noise Cancelling Headphones", (75.00, 350.00), "Debit Card"),
    ("Apple Store #R142 - MagSafe Charger & Accessories", (49.00, 190.00), "Apple Pay"),
    ("Apple Store - Apple Watch Ultra Band", (99.00, 149.00), "Apple Pay"),
    ("Nike Factory Store - Air Zoom Running Shoes", (75.00, 195.00), "Debit Card"),
    ("Nike Store - Dri-FIT Training Shirts & Shorts", (45.00, 140.00), "Debit Card"),
    ("Lululemon Athletica - License to Train Shorts & Pants", (88.00, 210.00), "Credit Card"),
    ("Under Armour Factory House - HeatGear Base Layer", (45.00, 135.00), "Debit Card"),
    ("REI Co-op #055 - Trail Hiking Boots & Daypack", (65.00, 340.00), "Debit Card"),
    ("Bass Pro Shops - Fishing Rod & Tackle Box", (55.40, 280.00), "Debit Card"),
    ("Dick's Sporting Goods #210 - Kettlebells & Workout Gear", (45.20, 210.00), "Debit Card"),
    ("PetSmart #1240 - Blue Buffalo Dog Food & Dental Chews", (45.80, 175.40), "Debit Card"),
    ("Petco Animal Supplies - Dog Flea & Tick Prevention", (35.00, 145.60), "Debit Card"),
    ("TJ Maxx #0812 - Home Decor & Apparel", (30.50, 120.20), "Apple Pay"),
    ("Oakley O-Store - Standard Issue Ballistic Eyewear", (120.00, 240.00), "Debit Card"),
    ("GNC Live Well - Gold Standard Whey Protein", (45.00, 95.00), "Debit Card"),
]

military_spending = [
    ("AAFES PX/BX - Main Base Exchange Shopping", (45.00, 380.00), "Debit Card"),
    ("AAFES PX - Electronics & Gaming Department", (75.00, 490.00), "Debit Card"),
    ("AAFES PX - Home Goods & Small Appliances", (55.00, 290.00), "Debit Card"),
    ("AAFES Express / Gas - Fuel & Energy Drinks", (35.00, 95.00), "Debit Card"),
    ("AAFES Gas Station #04 - Base Unleaded Fuel", (40.00, 110.00), "Debit Card"),
    ("AAFES Express #12 - Morning Monster & Snack", (6.50, 18.50), "Apple Pay"),
    ("MCCS Marine Mart - Camp Lejeune Exchange Mart", (15.50, 68.40), "Debit Card"),
    ("Patriot Express Travel - AMC Terminal Passenger Voucher", (120.00, 650.00), "Debit Card"),
    ("Military Clothing & Sales - OCP Uniform Tops & Trousers", (65.00, 280.00), "Debit Card"),
    ("Military Clothing & Sales - Rank Insignia & Name Tapes", (18.00, 55.00), "Debit Card"),
    ("Military Clothing & Sales - Corcoran Jump Boots", (140.00, 220.00), "Debit Card"),
    ("Base Recreation Center (MWR) - 18-Hole Golf Round & Cart", (25.00, 85.00), "Debit Card"),
    ("Base Bowling Center (MWR) - Squad Bowling Night", (20.00, 65.00), "Debit Card"),
    ("USAA P&C Auto Insurance Policy #849201", (145.00, 285.00), "ACH"),
    ("USAA Property & Renters Insurance Policy #441029", (65.00, 140.00), "ACH"),
    ("Navy Federal Credit Union ATM Cash Withdrawal", (40.00, 300.00), "Debit Card"),
    ("Navy Federal ATM #0421 - Base Branch Cash Withdrawal", (60.00, 250.00), "Debit Card"),
    ("5.11 Tactical Gear Store - Duty Pack & RUSH 72 Backpack", (85.00, 290.00), "Debit Card"),
    ("Propper International - Combat Uniform Blouse", (70.00, 190.00), "Debit Card"),
    ("Base Barber Shop - Regs Low Fade Haircut", (18.00, 28.00), "Apple Pay"),
    ("Base Automotive Skills Center - Lift Rental & Disposal", (20.00, 65.00), "Debit Card"),
    ("DoD Lodging Facility - Temporary Lodging Facility (TLF)", (85.00, 240.00), "Debit Card"),
]

subscriptions = [
    ("Netflix Premium 4K - Monthly Subscription", (22.99, 22.99), "Debit Card"),
    ("Spotify Family Premium - Music Streaming", (16.99, 19.99), "Apple Pay"),
    ("Apple iCloud+ 2TB Storage Plan", (9.99, 9.99), "Apple Pay"),
    ("Apple Music Family Subscription", (16.99, 16.99), "Apple Pay"),
    ("Disney+ Bundle (Disney+, Hulu, ESPN+)", (14.99, 19.99), "Debit Card"),
    ("Amazon Prime Monthly Membership Fee", (14.99, 14.99), "Debit Card"),
    ("YouTube Premium - Family Plan", (22.99, 22.99), "Apple Pay"),
    ("PlayStation Network - PS Plus 12-Month Access", (14.99, 14.99), "Debit Card"),
    ("Audible Membership - Monthly Audiobook Credit", (14.95, 14.95), "Debit Card"),
    ("The Wall Street Journal - Digital Edition", (12.00, 24.00), "Debit Card"),
]

utilities_and_services = [
    ("T-Mobile - 5G Military Magenta Plan", (120.00, 185.00), "ACH"),
    ("Verizon Wireless - Family Unlimited 5G Plan", (135.00, 210.00), "ACH"),
    ("AT&T Fiber 1Gbps Internet Service", (75.00, 90.00), "ACH"),
    ("Duke Energy - Home Electricity & Utility", (110.00, 260.00), "ACH"),
    ("Florida Power & Light (FPL) - Residential Power", (95.00, 240.00), "ACH"),
    ("City Water & Sewer Utility Bill", (45.00, 95.00), "ACH"),
    ("Waste Management - Residential Trash & Recycling", (85.00, 135.00), "ACH"),
    ("Shell Oil 57201 - Fuel & Car Wash", (38.00, 88.00), "Debit Card"),
    ("Chevron Fuel & Techron Premium", (42.00, 94.00), "Credit Card"),
    ("ExxonMobil Station #4021 - Unleaded Fuel", (36.00, 85.00), "Apple Pay"),
    ("SunPass Florida - Toll Auto-Replenish", (25.00, 50.00), "Debit Card"),
    ("Valvoline Instant Oil Change - Full Synthetic Oil & Filter", (68.00, 115.00), "Debit Card"),
    ("Jiffy Lube #1104 - Signature Service Oil Change", (58.00, 98.00), "Debit Card"),
    ("Discount Tire - Tire Rotation & Balance", (25.00, 75.00), "Debit Card"),
    ("Mister Car Wash - Titanium Unlimited Wash Club", (32.00, 45.00), "Debit Card"),
    ("AutoZone Auto Parts - Wiper Blades & Accessories", (24.00, 68.00), "Debit Card"),
    ("CVS Pharmacy #3812 - Rx Co-pay & Toiletries", (15.00, 85.00), "Debit Card"),
    ("Walgreens #5019 - Vitamins & Prescription Pickup", (18.00, 92.00), "Apple Pay"),
    ("Planet Fitness - Black Card Gym Membership", (24.99, 24.99), "ACH"),
    ("Anytime Fitness - 24/7 Key Fob Membership", (39.99, 39.99), "ACH"),
    ("Dry Cleaning Express - OCP Uniform Starch & Press", (35.00, 95.00), "Debit Card"),
    ("Executive Grooming Barbershop - Haircut & Trim", (28.00, 45.00), "Apple Pay"),
    ("Stanley Steemer - Carpet & Tile Cleaning", (140.00, 260.00), "Debit Card"),
]

third_party_out = [
    ("Zelle payment to Marcus Vance (Rent & Utilities Share)", (350.00, 850.00), "Zelle"),
    ("Zelle payment to Brian Miller (Golf Weekend Cabin Split)", (65.00, 180.00), "Zelle"),
    ("Zelle payment to Sgt. Dave Miller (Platoon MWR Cookout)", (40.00, 120.00), "Zelle"),
    ("Zelle payment to Apex Auto Detailing (Truck Ceramic Coating)", (120.00, 250.00), "Zelle"),
    ("Zelle payment to Emily Clark (Birthday Gift)", (50.00, 150.00), "Zelle"),
    ("Zelle payment to Capt. Henderson (Tailgate & Tickets)", (65.00, 175.00), "Zelle"),
    ("Cash App* Dave Reynolds (Hunting Lease Share)", (45.00, 160.00), "ACH"),
    ("Cash App* Mike Larson (MWR Deep Sea Fishing Charter)", (75.00, 220.00), "ACH"),
    ("Cash App* Sarah B (Concert Tickets & Parking)", (60.00, 175.00), "ACH"),
    ("Cash App* Tyler W (Gun Range Ammo Split)", (45.00, 130.00), "ACH"),
    ("PayPal *Steam Games Store", (25.00, 90.00), "PayPal"),
    ("PayPal *eBay Motors F-150 Truck Parts", (65.00, 280.00), "PayPal"),
    ("PayPal *Bose SoundSport Wireless Earbuds", (120.00, 240.00), "PayPal"),
    ("PayPal *Home Depot Online Order", (55.00, 210.00), "PayPal"),
    ("Venmo payment to Chris (Concert Tickets & Tailgate)", (55.00, 160.00), "Apple Pay"),
    ("Venmo payment to Brandon K (Fantasy Football League Dues)", (50.00, 100.00), "Apple Pay"),
    ("Venmo payment to Alex T (BBQ Brisket & Beer Share)", (35.00, 95.00), "Apple Pay"),
    ("Uber Technologies - Airport Ride to Terminal", (28.00, 75.00), "Apple Pay"),
    ("Uber Eats - Weekend Sushi Dinner Delivery", (35.00, 85.00), "Apple Pay"),
    ("DoorDash - Chipotle Lunch Group Order", (24.00, 62.00), "Apple Pay"),
    ("Lyft Ride - Downtown Transit & Return", (18.00, 48.00), "Debit Card"),
]

incoming_daily_pre = [
    ("Zelle transfer from Sarah Jenkins (Dinner & Drinks Split)", (25.00, 85.00), "Zelle"),
    ("Zelle transfer from Lt. Ryan Carter (Fishing Charter Split)", (60.00, 190.00), "Zelle"),
    ("Zelle transfer from Marcus Vance (Monthly Utility Share)", (100.00, 350.00), "Zelle"),
    ("Zelle transfer from Sgt. Miller (Range Ammo Reimbursement)", (45.00, 120.00), "Zelle"),
    ("Zelle transfer from Capt. Reynolds (Squad BBQ Share)", (35.00, 95.00), "Zelle"),
    ("Cash App Received from Tyler W (Equipment Sale)", (40.00, 150.00), "ACH"),
    ("Cash App Received from Brandon K (Sports Bet Payout)", (50.00, 200.00), "ACH"),
    ("Venmo Cashout to Checking Account", (65.00, 240.00), "ACH"),
    ("Amazon.com - Return Refund Credit", (24.00, 185.00), "Debit Card"),
    ("The Home Depot - Hardware Return Credit", (35.00, 160.00), "Debit Card"),
    ("DECA Commissary - Checkout Correction Refund", (15.00, 65.00), "Debit Card"),
    ("AAFES PX - Merchandise Return Credit", (30.00, 140.00), "Debit Card"),
    ("USAA Insurance - Annual Safe Pilot Driver Dividend", (85.00, 240.00), "ACH"),
]

front_images = [
    "assets/global/images/dPRvsvDOYKvSpZxv5v2d.jpeg",
    "assets/global/images/zNjuO2j8WwsAITAFTHrV6L8tz18N8Pv2fvuwZSMT.png",
]
back_images = [
    "assets/global/images/iyNrvi2xrsARYoV6tTEr.jpeg",
    "assets/global/images/xtUz7qGjgfPnfqBMXaIFwpMXjcXyYnovn6FiUyOf.png",
]

transactions = []
remote_deposits = []

def add_txn(dt, desc, amt, t_type, method, wallet_type="default", status="success", transfer_type=None):
    amt_str = f"{amt:.2f}"
    transactions.append({
        "tnx": generate_tnx(),
        "description": desc,
        "amount": amt_str,
        "type": t_type,
        "final_amount": amt_str,
        "method": method,
        "wallet_type": wallet_type,
        "status": status,
        "transfer_type": transfer_type,
        "created_at": dt.strftime("%Y-%m-%d %H:%M:%S"),
        "updated_at": dt.strftime("%Y-%m-%d %H:%M:%S")
    })

# =========================================================================
# GENERATION: PERIOD 1 (2022-01-01 to 2025-03-23)
# Active Duty Daily Life & Military Operations (Incoming & Outgoing)
# HIGH TRANSACTION DENSITY FOR PRIMARY EVERYDAY ACCOUNT (~30-45 txns/month)
# =========================================================================

cutoff_dt = datetime.datetime(2025, 3, 23, 19, 15, 0)
current_month_dt = datetime.datetime(2022, 1, 1)

while current_month_dt < datetime.datetime(2025, 4, 1):
    year = current_month_dt.year
    month = current_month_dt.month
    
    # 1st of the month: DFAS Salary + BAH + BAS
    d1 = datetime.datetime(year, month, 1, random.randint(6, 8), random.randint(10, 50), random.randint(10, 59))
    if d1 <= cutoff_dt:
        salary_amt = round(random.uniform(4250.00, 4950.00), 2)
        add_txn(d1, "DFAS-IN FED SALARY DIRECT DEP", salary_amt, "deposit", "ACH", "default")
        
        bah_dt = d1 + datetime.timedelta(hours=random.randint(1, 3), minutes=random.randint(5, 30))
        bah_amt = round(random.uniform(2750.00, 3450.00), 2)
        add_txn(bah_dt, "BAH HOUSING ALLOWANCE - WITH DEP", bah_amt, "deposit", "ACH", "default")

        bas_dt = d1 + datetime.timedelta(hours=random.randint(4, 6))
        add_txn(bas_dt, "BAS FOOD & SUBSISTENCE ALLOWANCE", 452.56, "deposit", "ACH", "default")

    # 15th of the month: Mid-month DFAS-MIL PAY + Hazardous Duty Pay
    d15 = datetime.datetime(year, month, 15, random.randint(6, 8), random.randint(10, 50), random.randint(10, 59))
    if d15 <= cutoff_dt:
        mid_salary = round(random.uniform(4250.00, 4950.00), 2)
        add_txn(d15, "DFAS-MIL PAY DIRECT DEP", mid_salary, "deposit", "ACH", "default")

        hdip_dt = d15 + datetime.timedelta(hours=3, minutes=30)
        add_txn(hdip_dt, "HAZARDOUS DUTY INCENTIVE PAY (HDIP)", 350.00, "deposit", "ACH", "default")

    # 25th of the month: Foreign Language Proficiency Bonus (Active Duty Pay)
    d25 = datetime.datetime(year, month, 25, random.randint(7, 10), random.randint(10, 50), random.randint(10, 59))
    if d25 <= cutoff_dt:
        flpb_amt = round(random.uniform(750.00, 1000.00), 2)
        add_txn(d25, "DOD FOREIGN LANGUAGE PROFICIENCY BONUS (FLPB)", flpb_amt, "deposit", "ACH", "primary_savings")

    # Occasional high-value bonuses, reimbursements, TSP distributions in Period 1
    if month in [3, 9] and year in [2022, 2023, 2024]:
        tsp_dt = datetime.datetime(year, month, random.randint(10, 24), random.randint(10, 16), random.randint(10, 50))
        tsp_amt = round(random.uniform(35000.00, 78000.00), 2)
        add_txn(tsp_dt, "THRIFT SAVINGS PLAN (TSP) DISTRIBUTION", tsp_amt, "deposit", "Wire", "primary_savings", transfer_type="other_bank_transfer")

    if month == 6 and year in [2022, 2024]:
        pcs_dt = datetime.datetime(year, month, random.randint(8, 20), random.randint(11, 15), random.randint(10, 50))
        pcs_amt = round(random.uniform(6200.00, 12800.00), 2)
        add_txn(pcs_dt, "PCS TRAVEL REIMBURSEMENT & DISLOCATION ALLOWANCE", pcs_amt, "deposit", "ACH", "default")

    if month == 10 and year in [2022, 2023]:
        bonus_dt = datetime.datetime(year, month, random.randint(5, 18), random.randint(10, 16), random.randint(10, 50))
        bonus_amt = round(random.uniform(25000.00, 50000.00), 2)
        add_txn(bonus_dt, "DFAS OFFICER CONTINUATION & RETENTION BONUS", bonus_amt, "deposit", "ACH", "primary_savings")

    if month == 4 and year in [2022, 2023, 2024]:
        tax_dt = datetime.datetime(year, 4, random.randint(12, 22), random.randint(8, 14), random.randint(10, 50))
        tax_amt = round(random.uniform(5200.00, 8900.00), 2)
        add_txn(tax_dt, f"IRS TREASURY 310 TAX REFUND (FY {year-1})", tax_amt, "deposit", "ACH", "default")

    # Mobile check deposits (Remote Deposits)
    if random.random() < 0.75:
        rd_day = random.randint(3, 27)
        if not (year == 2025 and month == 3 and rd_day > 22):
            rd_dt = datetime.datetime(year, month, rd_day, random.randint(10, 19), random.randint(10, 55))
            if rd_dt <= cutoff_dt:
                rd_acc = random.choice(["Checking", "Savings"])
                rd_wtype = "default" if rd_acc == "Checking" else "primary_savings"
                rd_amt = round(random.uniform(650.00, 4800.00), 2)
                
                remote_deposits.append({
                    "amount": f"{rd_amt:.2f}",
                    "front_image": random.choice(front_images),
                    "back_image": random.choice(back_images),
                    "status": "approved",
                    "account_name": rd_acc,
                    "type": "checking" if rd_acc == "Checking" else "savings",
                    "created_at": rd_dt.strftime("%Y-%m-%d %H:%M:%S"),
                    "updated_at": rd_dt.strftime("%Y-%m-%d %H:%M:%S")
                })
                add_txn(rd_dt, f"Mobile Check Deposit - Check #{random.randint(1020, 9890)}", rd_amt, "deposit", "Mobile", rd_wtype)

    # HIGH-FREQUENCY DAILY LIFE OUTGOING & INCOMING
    # We generate 28 to 38 realistic transactions per month (1-3 transactions per day)
    days_in_month = 28 if month == 2 else (30 if month in [4, 6, 9, 11] else 31)
    if year == 2025 and month == 3:
        days_in_month = 23 # strict cutoff on March 23

    num_txns = random.randint(28, 38)
    if year == 2025 and month == 3:
        num_txns = random.randint(24, 30)

    for _ in range(num_txns):
        d = random.randint(1, days_in_month)
        hour = random.randint(6, 22)
        minute = random.randint(0, 59)
        second = random.randint(0, 59)
        tx_dt = datetime.datetime(year, month, d, hour, minute, second)
        if tx_dt > cutoff_dt:
            continue

        cat_roll = random.random()
        if cat_roll < 0.24:
            item, span, mthd = random.choice(fast_food)
        elif cat_roll < 0.44:
            item, span, mthd = random.choice(groceries)
        elif cat_roll < 0.58:
            item, span, mthd = random.choice(retail)
        elif cat_roll < 0.70:
            item, span, mthd = random.choice(dining)
        elif cat_roll < 0.82:
            item, span, mthd = random.choice(military_spending)
        elif cat_roll < 0.90:
            item, span, mthd = random.choice(utilities_and_services)
        elif cat_roll < 0.96:
            item, span, mthd = random.choice(third_party_out)
        else:
            item, span, mthd = random.choice(subscriptions)

        amt = round(random.uniform(span[0], span[1]), 2)
        wtype = "primary_savings" if (random.random() < 0.12 and amt > 120) else "default"
        add_txn(tx_dt, item, amt, "subtract", mthd, wtype)

        # Peer reimbursements / Returns / CashApp received
        if random.random() < 0.15:
            ref_item, ref_span, ref_mthd = random.choice(incoming_daily_pre)
            ref_amt = round(random.uniform(ref_span[0], ref_span[1]), 2)
            ref_dt = tx_dt + datetime.timedelta(hours=random.randint(1, 36))
            if ref_dt <= cutoff_dt:
                add_txn(ref_dt, ref_item, ref_amt, "deposit", ref_mthd, "default")

    if month == 12:
        current_month_dt = datetime.datetime(year + 1, 1, 1)
    else:
        current_month_dt = datetime.datetime(year, month + 1, 1)

# Ensure the EXACT LAST OUTGOING transaction is placed on March 23, 2025 (Final deployment staging)
last_outgoing_dt = datetime.datetime(2025, 3, 23, 18, 42, 15)
add_txn(last_outgoing_dt, "AAFES Express / Gas Station #04 - Base Fuel", 58.45, "subtract", "Debit Card", "default")
add_txn(last_outgoing_dt + datetime.timedelta(minutes=15), "DECA Commissary - Pre-Deployment Travel Provisions", 84.20, "subtract", "Debit Card", "default")

# =========================================================================
# GENERATION: PERIOD 2 (2025-03-24 to 2026-08-16)
# ACTIVE DUTY FOREIGN DEPLOYMENT - 100% INCOMING PAYMENTS ONLY
# ZERO Outgoing spending due to restricted operational location overseas
# (DFAS Salary, Combat Pay, BAH, Hardship Pay, Family Separation, Retention, TSP, Treasury)
# NO disability or veteran-only retired pays.
# =========================================================================

p2_start_dt = datetime.datetime(2025, 3, 25, 9, 15, 0)
p2_end_dt = datetime.datetime(2026, 8, 16, 20, 0, 0)

curr_dt = p2_start_dt

while curr_dt <= p2_end_dt:
    year = curr_dt.year
    month = curr_dt.month
    day = curr_dt.day

    # 1st of the month: Active Duty DFAS Salary + BAH + BAS + Combat Zone Tax Exclusion + Hardship Duty Pay
    if day == 1:
        dfas_amt = round(random.uniform(6650.00, 7450.00), 2)
        add_txn(curr_dt, "DFAS-IN FED SALARY DIRECT DEP", dfas_amt, "deposit", "ACH", "default")

        # BAH Housing Allowance (for family back home)
        bah_amt = round(random.uniform(3650.00, 4250.00), 2)
        bah_dt = curr_dt + datetime.timedelta(hours=2, minutes=15)
        add_txn(bah_dt, "BAH HOUSING ALLOWANCE - WITH DEPENDENTS", bah_amt, "deposit", "ACH", "default")

        # BAS Subsistence Allowance
        bas_dt = curr_dt + datetime.timedelta(hours=4)
        add_txn(bas_dt, "BAS FOOD & SUBSISTENCE ALLOWANCE", 485.20, "deposit", "ACH", "default")

        # Combat Zone Tax Exclusion (CZTE) / Imminent Danger Pay (IDP)
        cz_dt = curr_dt + datetime.timedelta(hours=6, minutes=30)
        cz_amt = round(random.uniform(450.00, 750.00), 2)
        add_txn(cz_dt, "COMBAT ZONE TAX EXCLUSION (CZTE) / IMMINENT DANGER PAY", cz_amt, "deposit", "ACH", "default")

        # Hardship Duty Pay - Location (HDP-L)
        hdp_dt = curr_dt + datetime.timedelta(hours=8, minutes=10)
        hdp_amt = round(random.uniform(250.00, 500.00), 2)
        add_txn(hdp_dt, "HARDSHIP DUTY PAY - LOCATION (HDP-L)", hdp_amt, "deposit", "ACH", "default")

    # 5th of the month: Special Duty Assignment Pay (SDAP) & Overseas COLA/OHA
    elif day == 5:
        sdap_amt = round(random.uniform(450.00, 650.00), 2)
        add_txn(curr_dt, "SPECIAL DUTY ASSIGNMENT PAY (SDAP)", sdap_amt, "deposit", "ACH", "default")
        
        # Overseas Housing / Cost of Living Stipend
        if month in [4, 6, 8, 10, 12, 2]:
            oha_dt = curr_dt + datetime.timedelta(hours=3, minutes=20)
            oha_amt = round(random.uniform(2800.00, 3650.00), 2)
            add_txn(oha_dt, "OVERSEAS COST OF LIVING ALLOWANCE (OCONUS COLA)", oha_amt, "deposit", "ACH", "default")

    # 10th of the month: Investment Yields & US Treasury Interest
    elif day == 10:
        if month in [3, 6, 9, 12]:
            v_amt = round(random.uniform(18500.00, 36000.00), 2)
            add_txn(curr_dt, "VANGUARD S&P 500 ALLOCATION FUND - QUARTERLY DIVIDEND", v_amt, "deposit", "ACH", "primary_savings")
        else:
            yield_amt = round(random.uniform(6200.00, 11400.00), 2)
            add_txn(curr_dt, "FIDELITY US GOVERNMENT MONEY MARKET FUND - MONTHLY YIELD", yield_amt, "deposit", "ACH", "primary_savings")

    # 15th of the month: Mid-month Active Duty Military Pay + Hazardous Duty Pay + Family Separation Allowance
    elif day == 15:
        mid_pay = round(random.uniform(6650.00, 7450.00), 2)
        add_txn(curr_dt, "DFAS-MIL PAY DIRECT DEP", mid_pay, "deposit", "ACH", "default")

        hdip_dt = curr_dt + datetime.timedelta(hours=3, minutes=45)
        hdip_amt = round(random.uniform(350.00, 550.00), 2)
        add_txn(hdip_dt, "HAZARDOUS DUTY INCENTIVE PAY (HDIP)", hdip_amt, "deposit", "ACH", "default")

        # Family Separation Allowance (standard $250 - $400 for deployed troops)
        fsa_dt = curr_dt + datetime.timedelta(hours=5, minutes=15)
        add_txn(fsa_dt, "FAMILY SEPARATION ALLOWANCE (FSA)", 350.00, "deposit", "ACH", "default")

    # 20th of the month: Major Active Duty Officer Bonuses, TSP Disbursements, Treasury Redemptions
    elif day == 20:
        if year == 2025:
            if month == 4:
                # Active Officer Retention & Continuation Bonus
                add_txn(curr_dt, "DFAS OFFICER CONTINUATION & RETENTION BONUS", 85000.00, "deposit", "Wire", "primary_savings", transfer_type="other_bank_transfer")
            elif month == 6:
                # Active Special Operations / Aviation Incentive Bonus
                add_txn(curr_dt, "DEFENSE FINANCE - SPECIAL AVIATION WARFARE INCENTIVE", 65000.00, "deposit", "Wire", "primary_savings", transfer_type="other_bank_transfer")
            elif month == 8:
                # TSP Distribution
                add_txn(curr_dt, "THRIFT SAVINGS PLAN (TSP) DISTRIBUTION / DISBURSEMENT", 125000.00, "deposit", "Wire", "primary_savings", transfer_type="other_bank_transfer")
            elif month == 10:
                # Critical Skills Retention Bonus
                add_txn(curr_dt, "DFAS CRITICAL SKILLS ACCESSION & RETENTION BONUS", 55000.00, "deposit", "Wire", "primary_savings", transfer_type="other_bank_transfer")
            elif month == 12:
                # US Treasury T-Bill Maturity Redemption
                add_txn(curr_dt, "US TREASURY DIRECT - T-BILL MATURITY REDEMPTION", 175000.00, "deposit", "Wire", "primary_savings", transfer_type="other_bank_transfer")
        elif year == 2026:
            if month == 1:
                # Deployment Per Diem & Mission Accrual
                add_txn(curr_dt, "DOD DEPLOYMENT ACCRUAL & PER DIEM DISBURSEMENT", 24500.00, "deposit", "ACH", "default")
            elif month == 3:
                # US Treasury Series I Savings Bond Redemption
                add_txn(curr_dt, "US TREASURY DIRECT - SERIES I SAVINGS BOND REDEMPTION", 95000.00, "deposit", "Wire", "primary_savings", transfer_type="other_bank_transfer")
            elif month == 5:
                # Critical Mission Continuation Incentive
                add_txn(curr_dt, "DEFENSE FINANCE - CRITICAL MISSION CONTINUATION INCENTIVE", 75000.00, "deposit", "Wire", "primary_savings", transfer_type="other_bank_transfer")
            elif month == 7:
                # TSP Mid-Year Distribution
                add_txn(curr_dt, "THRIFT SAVINGS PLAN (TSP) DISTRIBUTION / DISBURSEMENT", 145000.00, "deposit", "Wire", "primary_savings", transfer_type="other_bank_transfer")

    # 25th of the month: Foreign Language Bonus & DoD SDP (Savings Deposit Program)
    elif day == 25:
        flpb_amt = round(random.uniform(750.00, 1000.00), 2)
        add_txn(curr_dt, "DOD FOREIGN LANGUAGE PROFICIENCY BONUS (FLPB)", flpb_amt, "deposit", "ACH", "primary_savings")

        if month in [5, 11] and year == 2025:
            # Active Duty Special Operational Duty Stipend
            stipend_dt = curr_dt + datetime.timedelta(hours=2, minutes=30)
            stipend_amt = round(random.uniform(18500.00, 28500.00), 2)
            add_txn(stipend_dt, "DOD SPECIAL OPERATIONS MISSION PER DIEM DISBURSEMENT", stipend_amt, "deposit", "ACH", "primary_savings")

        if month == 9 and year == 2025:
            # DoD Savings Deposit Program (SDP) Maturity + 10% Interest
            sdp_dt = curr_dt + datetime.timedelta(hours=4)
            add_txn(sdp_dt, "DOD SAVINGS DEPOSIT PROGRAM (SDP) MATURITY & INTEREST", 11000.00, "deposit", "ACH", "primary_savings")

        if month == 4 and year == 2026:
            # IRS Treasury Tax Refund 2026
            tax_dt = curr_dt + datetime.timedelta(hours=3)
            add_txn(tax_dt, "IRS TREASURY 310 TAX REFUND (FY 2025)", 9450.00, "deposit", "ACH", "default")

    # 28th of the month: Hostile Fire / Imminent Danger Pay (HF/IDP)
    elif day == 28:
        hfp_amt = round(random.uniform(225.00, 350.00), 2)
        add_txn(curr_dt, "HOSTILE FIRE / IMMINENT DANGER PAY (HFP/IDP)", hfp_amt, "deposit", "ACH", "default")

    curr_dt += datetime.timedelta(days=1)

# Final Top Payout on August 16, 2026 (Active Duty Critical Retention Quarterly Incentive)
add_txn(datetime.datetime(2026, 8, 16, 14, 20, 0), "DEFENSE FINANCE - CRITICAL RETENTION QUARTERLY INCENTIVE", 25000.00, "deposit", "Wire", "primary_savings", transfer_type="other_bank_transfer")

# =========================================================================
# SORTING & STRICT VERIFICATION
# =========================================================================

# Sort descending by date (newest first)
transactions.sort(key=lambda x: x["created_at"], reverse=True)
remote_deposits.sort(key=lambda x: x["created_at"], reverse=True)

# Strict check: Zero outgoing after March 23, 2025
for t in transactions:
    t_dt = datetime.datetime.strptime(t["created_at"], "%Y-%m-%d %H:%M:%S")
    if t_dt > cutoff_dt and t["type"] == "subtract":
        raise ValueError(f"CRITICAL ERROR: Outgoing transaction found after cutoff date! {t}")

# Strict check: No disability or non-active duty phrases
disallowed_keywords = ["DISABILITY", "CRSC", "RETIRED", "VETERAN"]
for t in transactions:
    for kw in disallowed_keywords:
        if kw in t["description"].upper():
            raise ValueError(f"CRITICAL ERROR: Disallowed active duty keyword '{kw}' found in {t['description']}")

print(f"Total transactions: {len(transactions)}")
print(f"Total remote check deposits: {len(remote_deposits)}")
print("Validation passed: ZERO outgoing transactions after March 23, 2025.")
print("Validation passed: ZERO disability or non-active-duty transactions present.")

# =========================================================================
# BUILD STRUCTURED SQL WITH MONTH HEADERS
# =========================================================================

sql_header = """SET @target_user_id = 2;

/* =========================================================================
 * ACTIVE DUTY MILITARY PROFILE FINANCIAL HISTORY
 * Timeline: January 2022 - August 2026 (4.5 Years of Continuous Active Duty)
 * Profile: Active Duty Senior Military Officer (OCONUS Forward Deployed)
 * 
 * Operational Context & Account Rules:
 * 1. Jan 2022 - March 23, 2025 (CONUS / Primary Everyday Account):
 *    Rich daily life civilian and military transactions (Commissary, PX/BX, Dining, Fast Food,
 *    Groceries, Retail, Subscriptions, Utilities, Zelle, CashApp, ApplePay, PayPal, Venmo, Mobile Deposits)
 *    alongside bi-monthly DFAS-IN Active Duty salaries, BAH, BAS & military bonuses.
 * 2. March 23, 2025: STRICT LAST OUTGOING TRANSACTION DATE (Final staging before deployment).
 * 3. March 24, 2025 - August 2026 (OCONUS Forward Deployment / Foreign Duty Location):
 *    100% INCOMING DIRECT DEPOSITS ONLY. The service member is forward-deployed in a remote
 *    foreign operational theater where domestic US bank cards/accounts cannot be utilized for daily
 *    spending. All transactions represent authentic Active Duty compensation:
 *    - DFAS-IN Active Duty Federal Salary & DFAS-MIL Pay (1st & 15th of every month)
 *    - Basic Allowance for Housing (BAH) & Subsistence Allowance (BAS)
 *    - Combat Zone Tax Exclusion (CZTE), Imminent Danger Pay (IDP), Hardship Duty Pay (HDP-L)
 *    - Hazardous Duty Incentive Pay (HDIP), Special Duty Assignment Pay (SDAP)
 *    - Family Separation Allowance (FSA) for troops deployed away from dependents
 *    - DoD Foreign Language Proficiency Bonuses (FLPB) & Deployment Per Diem Accruals
 *    - Active Duty Officer Continuation, Retention & Aviation Warfare Bonuses ($35k - $95k)
 *    - DoD Savings Deposit Program (SDP) Combat Zone Maturity & Guaranteed Interest
 *    - Thrift Savings Plan (TSP) Disbursements & US Treasury T-Bill/Bond Redemptions ($75k - $175k)
 *    - US Government Money Market & Allocation Index Investment Yields
 * 4. Ending Portfolio Balance:
 *    - Checking Account (`balance`): $2,268,412.35
 *    - Primary Savings Account (`savings_balance`): $1,915,638.90
 *    - Combined Balance: $4,184,051.25 (Exceeds $4,000,000+)
 * ========================================================================= */

/* UPDATE USER PROFILE BALANCES (Total: $4,184,051.25) */
UPDATE `users` 
SET `balance` = '2268412.35', 
    `savings_balance` = '1915638.90' 
WHERE `id` = @target_user_id;

/* CLEANUP PREVIOUS TRANSACTION & DEPOSIT HISTORY */
DELETE FROM `transactions` WHERE `user_id` = @target_user_id;
DELETE FROM `remote_deposits` WHERE `user_id` = @target_user_id;

/* INSERT ALL ACTIVE DUTY TRANSACTIONS (2022 - 2026) */
INSERT INTO `transactions` (`user_id`, `from_user_id`, `from_model`, `target_id`, `target_type`, `is_level`, `tnx`, `description`, `amount`, `type`, `charge`, `final_amount`, `points`, `method`, `pay_currency`, `pay_amount`, `manual_field_data`, `wallet_type`, `card_id`, `approval_cause`, `status`, `transfer_type`, `beneficiery_id`, `bank_id`, `created_at`, `updated_at`, `action_message`, `purpose`) VALUES
"""

# Group transactions with comments by Month
formatted_tx_blocks = []
current_month_label = None

for i, t in enumerate(transactions):
    t_dt = datetime.datetime.strptime(t["created_at"], "%Y-%m-%d %H:%M:%S")
    month_label = t_dt.strftime("%B %Y").upper()
    
    prefix = ""
    if month_label != current_month_label:
        current_month_label = month_label
        if t_dt.year > 2025 or (t_dt.year == 2025 and t_dt.month > 3):
            note = "OCONUS DEPLOYED - 100% INCOMING ONLY (Active Salary, Combat Pay, Allowances, Bonuses & Treasury)"
        elif t_dt.year == 2025 and t_dt.month == 3:
            note = "Deployment Transition Period - Final CONUS Outgoing on March 23, 2025"
        else:
            note = "CONUS Active Duty Primary Account (Daily Life & Military Operations - Incoming & Outgoing)"
            
        prefix = f"\n-- =========================================================================\n-- {current_month_label} ({note})\n-- =========================================================================\n"

    desc_escaped = t['description'].replace("'", "''")
    tt_val = f"'{t['transfer_type']}'" if t['transfer_type'] else "NULL"
    is_last = (i == len(transactions) - 1)
    ending = ";" if is_last else ","
    
    row_str = f"(@target_user_id, NULL, 'User', NULL, NULL, '0', '{t['tnx']}', '{desc_escaped}', '{t['amount']}', '{t['type']}', '0', '{t['final_amount']}', '0', '{t['method']}', NULL, NULL, '[]', '{t['wallet_type']}', NULL, NULL, '{t['status']}', {tt_val}, NULL, NULL, '{t['created_at']}', '{t['updated_at']}', NULL, NULL){ending}"
    
    formatted_tx_blocks.append(prefix + row_str)

sql_transactions = "\n".join(formatted_tx_blocks) + "\n\n"

sql_rd_header = """/* =========================================================================
 * REMOTE CHECK DEPOSITS (Synchronized Mobile Check Records)
 * ========================================================================= */
INSERT INTO `remote_deposits` (`user_id`, `amount`, `front_image`, `back_image`, `status`, `account_name`, `account_number`, `note`, `created_at`, `updated_at`) VALUES
"""

rd_rows = []
for i, r in enumerate(remote_deposits):
    acc_sub = "(SELECT savings_account_number FROM users WHERE id = @target_user_id)" if r['type'] == 'savings' else "(SELECT account_number FROM users WHERE id = @target_user_id)"
    is_last = (i == len(remote_deposits) - 1)
    ending = ";" if is_last else ","
    rd_row = f"(@target_user_id, '{r['amount']}', '{r['front_image']}', '{r['back_image']}', '{r['status']}', '{r['account_name']}', {acc_sub}, NULL, '{r['created_at']}', '{r['updated_at']}'){ending}"
    rd_rows.append(rd_row)

sql_remote_deposits = "\n".join(rd_rows) + "\n"

full_sql = sql_header + sql_transactions + sql_rd_header + sql_remote_deposits

target_file = r"c:\Users\USER\Downloads\frontfield-remodel pinelas fcu\database\user_military_history.sql"
with open(target_file, "w", encoding="utf-8") as f:
    f.write(full_sql)

print(f"Successfully generated and formatted active-duty user_military_history.sql at:\n{target_file}")
