# A7.7.7.1 — Focused regression test maintenance

Evidence: `CrmCsvExportSourceTest` expected `foreach ($query->cursor() as $order)` but Sales Order CSV uses `foreach ($query->orderByDesc('sales_order_id')->lazy(200) as $order)`. The CSV sanitizer remains. `CrmReportAuthorizationSourceTest` used a 350-character snippet; an additional privacy gate before CSV date validation made the snippet incomplete.

Decision: fix test expectations **without modifying app code**. For Reports, continue to assert the authorization guard is at the beginning of each entrypoint and precedes validation **and** `DB::table` business queries. The updated test uses explicit method boundaries, not arbitrary number of bytes.

Residual gates: earlier Announcement public-disk visibility review, Finance/Reports Privacy Pilot holds, historical dispatch/installation provenance and unrecognized invoice states remain unresolved until dedicated review. The test-only compatibility fix does not waive them. Full regression results must be observed on local VS Code/PowerShell.
