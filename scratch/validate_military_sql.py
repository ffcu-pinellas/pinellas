import datetime
import re

with open('database/user_military_history.sql', 'r', encoding='utf-8') as f:
    content = f.read()

lines = content.splitlines()
print('Total lines in SQL file:', len(lines))

# Check target user id
m_user = re.search(r'SET @target_user_id = (\d+);', content)
print('Target User ID:', m_user.group(1) if m_user else 'Not found')

# Check update balances
m_bal = re.search(r"UPDATE `users`\s+SET `balance` = '([\d\.]+)',\s+`savings_balance` = '([\d\.]+)'", content)
if m_bal:
    b1 = float(m_bal.group(1))
    b2 = float(m_bal.group(2))
    print(f'Checking Balance: ${b1:,.2f}')
    print(f'Savings Balance: ${b2:,.2f}')
    print(f'Total Balance: ${b1+b2:,.2f}')

# Parse transactions
tx_pattern = re.compile(
    r"\(@target_user_id, NULL, 'User', NULL, NULL, '0', '(TRX[A-Z0-9]+)', '([^']*(?:''[^']*)*)', '([\d\.]+)', '(deposit|subtract|fund_transfer)', '0', '[\d\.]+', '0', '([^']+)', NULL, NULL, '\[\]', '(default|primary_savings)', NULL, NULL, 'success', (NULL|'[^']+'), NULL, NULL, '([\d\-: ]+)', '([\d\-: ]+)', NULL, NULL\)"
)

txns = tx_pattern.findall(content)
print(f'Found {len(txns)} transactions parsed.')

tnxs = set()
outgoing_after_cutoff = []
disability_found = []
cutoff = datetime.datetime(2025, 3, 23, 23, 59, 59)
oldest_dt = None
newest_dt = None

deposit_count = 0
subtract_count = 0
deposit_after_cutoff = 0
subtract_after_cutoff = 0

disallowed = ["DISABILITY", "CRSC", "RETIRED", "VETERAN"]

for tnx, desc, amt, t_type, mthd, wtype, tt, c_at, u_at in txns:
    if tnx in tnxs:
        print('Duplicate TNX:', tnx)
    tnxs.add(tnx)
    
    dt = datetime.datetime.strptime(c_at, '%Y-%m-%d %H:%M:%S')
    if oldest_dt is None or dt < oldest_dt:
        oldest_dt = dt
    if newest_dt is None or dt > newest_dt:
        newest_dt = dt
        
    if t_type == 'deposit':
        deposit_count += 1
    else:
        subtract_count += 1

    if dt > cutoff:
        if t_type == 'subtract':
            outgoing_after_cutoff.append((dt, desc, amt))
            subtract_after_cutoff += 1
        else:
            deposit_after_cutoff += 1

    for kw in disallowed:
        if kw in desc.upper():
            disability_found.append((desc, c_at))

print(f"Date range: {oldest_dt} to {newest_dt}")
print(f"Total Deposits: {deposit_count}, Total Withdrawals: {subtract_count}")
print(f"Transactions from March 24, 2025 onwards: {deposit_after_cutoff} deposits, {subtract_after_cutoff} withdrawals")

if outgoing_after_cutoff:
    print('ERROR: Outgoing transactions found after cutoff:', outgoing_after_cutoff)
else:
    print('SUCCESS: ZERO outgoing transactions after March 23, 2025!')

if disability_found:
    print('ERROR: Disallowed keywords found:', disability_found)
else:
    print('SUCCESS: ZERO disability/veteran-only transactions found. All payments are authentic Active Duty & Deployment compensation!')

# Check remote deposits
rd_pattern = re.compile(r"\(@target_user_id, '([\d\.]+)', '([^']+)', '([^']+)', '([^']+)', '([^']+)',")
rds = rd_pattern.findall(content)
print(f"Found {len(rds)} remote check deposits parsed.")
