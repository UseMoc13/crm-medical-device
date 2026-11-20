# A7.7.3 – Route-Level RBAC Enforcement

## Reason and scope

The current `bootstrap/app.php` web group checked authentication but did not apply the existing module/page/workflow permission catalogs to every mapped route. This patch adds an edge middleware **after** authentication, **before** response privacy. A request denied by the middleware never reaches its controller.

For named routes included in the existing A6 catalogs, the middleware checks all three applicable requirements:

- Module permission (e.g. `delivery.access`, or `catalog.manage` on write routes)
- Page/action permission (`crm_page.deliveries.update`, etc.)
- Workflow permission on specifically catalogued mutations (`crm_workflow.deliveries.status`)

`roles.*` and `users.*` routes are newly restricted to `isSuperAdmin()`—a protected system Super Admin role, not merely a role *named* Super Admin. This change may intentionally revoke access that was incorrectly available before.

Other routes (dashboard, login, public announcements, workspace etc.) continue under their existing policies; **this patch does not prove their authorization is complete**. No data scope feature flags are activated. No grants are automatically added. The historical reporting/privacy HOLD remains in effect.

## Installation

1. Confirm `git branch --show-current` = `Big-Changes`.
2. Confirm `git diff -- bootstrap/app.php` before replacing; preserve any newer local customizations.
3. Back up `bootstrap/app.php` (see README).
4. Extract the package into your Laravel project root. Only `bootstrap/app.php` is replaced; the other files are new.
5. Run tests, then test with genuine role-based accounts on local/staging before production.

## Security cases covered

- Direct GET to a protected module with missing page View => 403
- Direct POST to a protected module with missing Create => 403 *before* validation/business write
- Direct GET to report and invoice CSV without Reports View => 403
- Role/User administration by non-Super Admin => 403
- Delivery status mutation requires module + page Update + workflow entitlement
- Explicitly catalogued nested create/delete routes preserve their action mapping
- Unmapped routes keep their existing independent guards

## Deployment test matrix (manual)

Sign in through browser with a real Super Admin and one account from each role: Sales, Warehouse, Delivery Staff, Technician, Finance, Management. Check pages they should have and pages/actions they must not have. Try typing a forbidden URL directly. Test 403 behavior for privileged POST/PATCH/DELETE in staging with safe fixture data. Check whether existing role templates need explicit permissions—**never auto-grant in a deployment**. Browser testing may discover additional unnamed/unmapped routes requiring separate guards.

**No production readiness claim** until browser/UAT and authorization matrix pass, reporting policy decisions are made, and previous integration HOLD findings are dispositioned.
