# Detailed Improvement Plan (Newbie-Friendly)

## Security: CSRF Protection
**Plain English:** CSRF stops bad websites from making your users submit forms without permission.
**Plan:**
1. List all POST/PUT/DELETE routes.
2. Ensure `@csrf` exists in every Blade form.
3. Add middleware verification in routes.
4. Test with a simple cross-site form attempt.

## Security: Input Validation & Sanitization
**Plain English:** Check and clean user input so bad data cannot break the app.
**Plan:**
1. Create Form Request classes for each form.
2. Add rules (required, email, numeric, max length).
3. Sanitize HTML/text inputs.
4. Return clear validation errors.

## Telegram: Alert Routing by Severity & Institution
**Plain English:** Send important alerts to the right people based on urgency and bank.
**Plan:**
1. Define severity levels (info, warning, critical).
2. Store institution-specific Telegram chat IDs.
3. Create routing service to choose chat ID by severity + institution.
4. Log every alert sent.

## Backend: Idempotency Keys for Transfers/Deposits/Wires
**Plain English:** Prevent the same payment from being processed twice if the user clicks twice.
**Plan:**
1. Add `idempotency_key` column to transactions.
2. Require key in request header or form.
3. Check if key exists before processing.
4. Return existing result if duplicate.

## Implementation Order
1. Security (CSRF + validation)
2. Idempotency keys
3. Telegram routing
4. Tests + monitoring
```
filePath">
C:\Users\USER\Downloads\frontfield-remodel pinelas fcu\DETAILED_IMPLEMENTATION_PLAN.md