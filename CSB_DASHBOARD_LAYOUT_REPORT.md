# CSB Dashboard Layout Issue Report

## Summary
On the CSB institution dashboard, the **Recent Transactions** card renders beneath the fixed side navigation instead of in the main content column beside it. The mobile layout is unaffected.

## Affected Files
- `resources/views/frontend/default/layouts/user.blade.php` — CSB layout branch (sidebar + `main-content-area`)
- `resources/views/frontend/default/user/dashboard.blade.php` — dashboard content (`row g-4 centering-fix`, two `col-lg-6` columns)
- `css/frontfield-custom.css` (and mirrored `assets/...` / `public/...` copies) — sidebar/main content spacing rules

## Root Cause
The CSB desktop layout uses a **fixed** `.sidebar` (`position: fixed; left: 0`) with the main content offset by a constant margin:

```css
@media (min-width: 992px) {
  .main-content-area {
    margin-left: 272px !important;
  }
}
```

The dashboard then splits its body into two `col-lg-6` columns. Because the left column containing Recent Transactions is placed inside a container that is already offset by the fixed sidebar but the row/column widths are calculated against the full viewport rather than the available main-content width, the first column is pushed under the sidebar footprint. The sidebar is not part of the Bootstrap grid flow, so the grid columns do not account for the 272px sidebar offset, causing the Recent Transactions block to wrap below the sidebar on desktop.

Additionally, there is no `.sidebar-collapsed` / full-width toggle handling: when the user closes the sidebar, `.main-content-area` keeps `margin-left: 272px`, so the main body does not expand to full width as required.

## Why Mobile Looks Correct
On viewports `< 992px`, the sidebar is translated off-canvas and `.main-content-area` uses `margin-left: 0`, so the dashboard grid flows normally and Recent Transactions appears in the expected position.

## Recommended Fix
1. Make the desktop sidebar participate in layout flow or reduce the grid container to the main-content width:
   - Option A: change `.sidebar` from `position: fixed` to `position: sticky` within a flex row, and remove the fixed `margin-left` on `.main-content-area`.
   - Option B: keep fixed sidebar but apply the 272px offset to the dashboard container, not to each column; use `max-width: calc(100vw - 272px)` on `.container-fluid` inside `main-content-area`.
2. Add sidebar toggle state:
   - When `.csb-sidebar` is collapsed/closed, add a body class (e.g. `sidebar-collapsed`) and set `.main-content-area { margin-left: 0 !important; }`.
3. Ensure dashboard columns use the available main-content width:
   - Change `col-lg-6` to `col-12 col-lg-6` and avoid `centering-fix` negative margins that assume full viewport width.
4. Sync all three CSS copies (`css/`, `assets/.../css/`, `public/.../css/`) or consolidate to a single source.

## Reference
Compare with FFCU layout in `frontfield-backup-pre-render-migration-2026-11-20` where the sidebar and main content share a flex container and the dashboard grid is constrained to the content area.
```
<|end|><|start|><|start|>assistant to=bash<|channel|>analysis <|constrain|>xml<|message|><rfl:parameter name="command">
Test-Path "C:\Users\USER\Downloads\frontfield-remodel pinelas fcu\CSB_DASHBOARD_LAYOUT_REPORT.md"