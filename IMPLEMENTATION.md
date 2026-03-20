# External Transfer Modernization Plan (Safe, Backward-Compatible)

## Scope For Phase 1 (Now)

Implement only External Transfer enhancements first, without breaking existing admin/backend processing:

1. Replace external bank dropdown UX with routing-based bank discovery.
2. Add receipt-style "Total Deducted" review in the transfer wizard.
3. Add admin/account-officer email alerts for member + external transfer submissions.
4. Keep existing transaction approval/rejection flow unchanged and compatible.

No risky refactor, no removal of existing fields that backend/admin depends on.

---

## Current System Reality (From Codebase)

- External transfer currently uses:
  - `transfer_type = external`
  - `bank_id` (required by `TransferRequest`)
  - `manual_data[account_name]`, `manual_data[routing_number]`, `manual_data[account_number]`
- Core processing:
  - `app/Http/Controllers/Frontend/FundTransferController.php`
  - `app/Services/TransferService.php`
- Admin review/approval/rejection:
  - `app/Http/Controllers/Backend/FundTransferController.php`
- Bank source:
  - `others_banks` table via `App\Models\OthersBank`
- Existing admin push exists; user email exists; admin/officer email fan-out is not fully implemented for transfer submission.

This plan preserves all of the above.

---

## Recommended Routing Lookup Architecture

### Decision

Use **server-side lookup proxy** (Laravel endpoint) calling `bankrouting.io` (primary) with safe fallback behavior.

### Why this is best

- Avoids browser CORS issues entirely.
- Keeps API dependency hidden from frontend.
- Allows rate-limit handling and caching.
- Lets us normalize discovered bank names into `others_banks` without changing admin flow.

### Provider Strategy

- Primary provider: `https://bankrouting.io/api/v1/aba/{routing}`
- If provider fails/429/timeouts:
  - Keep user flow alive by allowing manual bank name entry.
  - Do not block submission if routing format is valid and user confirms.

---

## Compatibility Rules (Non-Negotiable)

1. `bank_id` remains populated for external transfers to keep current backend logic intact.
2. If routing lookup returns a bank:
   - Resolve/create record in `others_banks`, then submit with that `bank_id`.
3. If lookup fails:
   - User can type bank name manually.
   - System searches `others_banks` by normalized name.
   - If not found, create one safely, then use its `id` as `bank_id`.
4. `manual_data` keys remain unchanged (`routing_number`, `account_number`, `account_name`) so review/approval templates continue working.
5. Existing transfer statuses and admin approval/rejection behavior remain unchanged in phase 1.

---

## UI/UX Changes (External Transfer Only)

Target file:
- `resources/views/frontend/default/fund_transfer/index.blade.php`

### Replace dropdown with:

1. Routing Number input (9 digits)
2. Live bank discovery card:
   - Bank name (and logo if available)
   - Discovery status (Verified / Unverified / Service unavailable)
3. Hidden `bank_id` field (set by lookup/creation step)
4. Manual bank name fallback input (shown only when lookup fails or user overrides)

### Receipt-style Review

In Review step show:
- Transfer Amount
- Fee/Charge
- Total Deducted
- Destination Bank
- Account ending in XXXX
- Routing number

This will use existing charge rules from backend and current form amount/selected source account.

---

## Backend/API Additions (Minimal)

### New frontend AJAX endpoint

Example route:
- `POST /user/fund-transfer/lookup-routing`

Controller behavior:
1. Validate routing format/checksum.
2. Check local cache/table (if introduced) or `others_banks` by code/routing.
3. If no local match, call provider.
4. Normalize bank name.
5. Resolve/create `others_banks` record.
6. Return:
   - `bank_id`
   - `bank_name`
   - `logo` (if available)
   - `verification_status`

### Transfer submit behavior

On external submit:
- Continue requiring `bank_id`.
- If `bank_id` missing but manual bank name provided, resolve/create bank server-side before `TransferService::validate/process`.
- Keep existing service signatures and status handling unchanged.

---

## Admin / Officer Email Notification Plan

Goal: notify admins/account officers on member and external transfer submissions.

Implementation approach:

1. Introduce a small recipient resolver service:
   - Admin emails from configured admin users with relevant permissions (`fund-transfer-approval` or `officer-transfer-manage`).
   - Optional fallback to `setting('site_email', 'global')`.
2. On transfer submission event (member/external only):
   - Send mail to resolved admin/officer recipients.
3. Keep existing user notification emails untouched.

This avoids changing approval logic while adding operational alerts.

---

## Database Impact

Phase 1 can run **without mandatory schema changes** by reusing `others_banks`.

Optional (recommended) optimization in a later small migration:
- Add lookup metadata/cache fields for routing resolution health and timestamps.

If we apply any migration, I will provide:
1. Laravel migration file changes
2. Equivalent raw SQL statements after migration details (as requested)

---

## Rollout Sequence

1. Add routing lookup endpoint + provider client + bank resolve/create logic.
2. Update external transfer frontend fields and live discovery UX.
3. Keep hidden `bank_id` and submit compatibility.
4. Add receipt-style review totals.
5. Add admin/officer email notifications for member/external submissions.
6. Test flows:
   - External submit -> pending -> admin approve/reject
   - Member submit -> pending -> admin approve/reject
   - Routing lookup success and fallback/manual modes
7. Deploy with cache clear/opcache reset checklist.

---

## Test Matrix (Must Pass)

1. External transfer with successful routing lookup:
   - `bank_id` present
   - Transaction saved as `OtherBankTransfer`
   - Admin can approve/reject
2. External transfer with provider unavailable:
   - Manual bank name accepted
   - `bank_id` still resolved/created
   - Admin can approve/reject
3. Member transfer unchanged behavior:
   - Pending and approval lifecycle unchanged
4. Email alerts:
   - Admin/officer recipients receive submission alert for member and external
5. No regressions in wire transfer and self transfer.

---

## Two Confirmations Needed Before Coding Phase 1

1. For provider fallback, do you want:
   - Strict mode: block transfer when lookup fails, or
   - Flexible mode: allow manual bank name and continue? (recommended)

2. For admin/officer alert recipients, should we notify:
   - all admin users with transfer permissions, or
   - a specific configured email list?

