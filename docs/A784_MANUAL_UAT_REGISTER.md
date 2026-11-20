# A7.8.4 — Controlled Manual UAT Result Register

## Context

A7.8.3 passed the supporting pre-UAT checks: 15 scenarios, 32 route checks, 20 evidence files. These are only evidence of tooling readiness. All 15 manual scenarios remained `NOT_EXECUTED`; two policy decisions remain HOLD. The A7.8.2 data-readiness audit found 33/34 table samples and 7/7 sampled cross-module chains. These facts are not a proof of business UAT success.

## Execution sequence

1. **Before any data-changing UAT**: prepare a non-production database and approved A7.9 synthetic dataset, confirm backup/snapshot and record its reference. Never run test writes on production or current valuable CRM data.
2. Make a local working copy of `docs/uat/a784_uat_execution_template.json` under `storage/app/` (untracked, private). Do not commit it if it could contain sensitive observations.
3. Record each case separately. `NOT_RUN` initially. Set `PASS` only after the actual authorized/unauthorized UI/HTTP and postcondition checks, with `role`, `observed_result`, and `evidence_reference`. A test filename is **not** proof of a manually executed scenario.
4. Set `FAIL` with a defect reference when actual != expected; `BLOCKED` with a reason when the scenario cannot safely be exercised. Re-test after fixes.
5. UAT-13 (Announcement public attachments) and UAT-14 (Reports/multi-role privacy) require an explicit policy decision. The ledger starts with `HOLD`. `APPROVAL_RECORDED` must carry a decision reference, but **the validator does not independently verify authority or implementation**.
6. Run the ledger validator. Even with 15 recorded PASS, the decision is `READY_FOR_EXTERNAL_SIGNOFF_NOT_RELEASE_APPROVED`. Stakeholder review, manual evidence inspection, and production release approval are separate gates.

## Scenario references

See `docs/A781_UAT_MATRIX.md` and `docs/A783_MANUAL_UAT_EXECUTION.md` for expected results of UAT-01 through UAT-15, including the required PATCH/POST transitions and data-integrity checks.

## Known limitations

- The ledger only validates a self-reported JSON record, not screenshots, HTTP responses, external approvals, or SQL postconditions.
- A7.9 dataset installation is not included here. This package **does not perform UAT** and does not reset a database.
- The existing historical release HOLDs remain independent; this tool cannot waive them.
- Never record names, emails, tokens, PHI, or raw downloaded files in the ledger; use non-sensitive evidence references.
