# Single-salon verification checklist

Complete on a backed-up staging copy, then repeat the key checks after deployment.

- [ ] Migration finishes successfully; `SELECT * FROM single_salon_config;` returns one row with `id = 1` and the intended salon ID.
- [ ] Compare pre/post counts for users, bookings, payments, stock items/movements, suppliers, promotions, weekly schedules, and exceptions. Verify revenue totals, stock quantities, booking IDs, and receipt/proof links.
- [ ] Check all four roles. There is no branch menu, selector, assignment field, switch, filter, report column, or promotion scope checkbox. Super Admin has Salon Settings instead.
- [ ] Add/edit an administrator and staff member. Their internal `branch_id` is the configured ID without asking the user to select one. Existing formerly unassigned staff remain accessible.
- [ ] Book a service through Service → Staff → Schedule → Review on desktop and mobile. Confirm existing bookings still block overlapping slots. Check customer ownership and staff ownership restrictions remain enforced.
- [ ] Load old bookmarked URLs with another `branch_id`; the salon does not switch. Existing sessions/drafts do not restore branch selection. The old `superadmin/branches.php` GET redirects, and POST returns 410.
- [ ] Admin can see and update migrated bookings, verify a payment, view its proof, print a receipt, and find its customer feedback.
- [ ] Open staff schedules with and without break times, save weekly availability, and add/remove an exception. Review any existing overlapping bookings or inconsistent schedules before going live.
- [ ] Inventory shows all retained items, including formerly unassigned items. Add stock, subtract stock, add a supplier, and confirm movement history and quantities are correct.
- [ ] Create/edit a promotion and check its homepage popup using a fresh browser and one with an old branch cookie. Date/status/popup controls still work.
- [ ] Review Admin and Super Admin reports and all six CSV exports: bookings, services, staff, payments, feedback, inventory. Compare totals for the same date/status range.
- [ ] Verify Salon Settings contact and GCash details. Saving without a QR upload keeps the existing QR. Updating settings cannot create another salon. Check receipts already paid to old accounts against archived metadata.
- [ ] Confirm services, notifications, account security, uploads, and existing AI pages still load. Do not change or retrain the AI module as part of this installation.
- [ ] Inspect PHP/server logs for errors, hard-refresh browser assets, and reopen the site only after checks pass.

Automated validation already completed: PHP/JavaScript syntax; migration preservation, conflict rejection, and rerun safety; page rendering; all report exports; stale/injected branch inputs; account creation/editing; booking submission; availability; payment verification; inventory updates; weekly schedule save; promotion creation; role/CSRF restrictions on Salon Settings; desktop/mobile booking navigation. Real GCash transactions, external notifications, and AI generation were not exercised.
