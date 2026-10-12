# SmartBooking V2 — single Nails & Body Worx salon

This patch targets the supplied `smartbooking_v2.rar`. It preserves the existing page styling and workflows while removing branch creation, editing, activation, assignment, selection, switching, filters, and redundant branch columns from the interface and CSV reports.

## Result

- Booking: Service → Staff → Schedule → Review. No branch selector or branch request parameter is needed.
- Super Admin: account forms and reports have no branch controls. Salon Settings preserves contact details, displayed opening hours, GCash merchant/number, and QR uploads. Staff availability still follows the existing staff schedules.
- Admin and Staff: the existing operational queries transparently use the one persisted salon ID. Existing staff, bookings, inventory, suppliers, movements, promotions, and schedules are consolidated by migration.
- Promotions: all active offers qualify for the existing date/popup rules regardless of old visitor cookies or branch scope.
- Old branch-management GET links redirect to Salon Settings; POST requests return HTTP 410 and cannot create, edit, or toggle records.
- Role checks, staff/customer ownership checks, service catalogue, notifications, payment verification, and other unrelated features remain in place. AI generation code is unchanged. The only AI-related file change is the wording of an existing booking-access error.

## Back up and apply

1. Put the site in maintenance mode and stop background writes. Export the **entire database**, and copy the project including `uploads/`. Keep these backups outside the web root. Test this patch on a copy first.
2. In phpMyAdmin, select the existing SmartBooking database. Inspect `branches` and identify the salon whose contact/GCash details should remain in use. The migration chooses `BR-001`, or the lowest existing ID if that code is absent. IDs are not hard-coded in PHP. To select another existing record, prepend `SET @single_salon_branch_id = 123;` with its actual ID to the migration in the **same SQL execution/session**.
3. Import **only** `database/201_single_salon.sql` into that selected database. The database user needs table and routine creation privileges as well as SELECT/INSERT/UPDATE. The file deliberately contains no `USE` statement so a staging database can be selected safely. Confirm the message **Single salon migration complete** before continuing.
4. Extract `smartbooking_v2_single_salon_patch.zip` directly into the existing `smartbooking_v2` project folder, allowing replacement. ZIP entries begin with `admin/`, `includes/`, etc.; do not create an extra nested project folder. Database connection settings, credentials, uploads, and unchanged files are not in the ZIP.
5. Sign out and back in, then hard-refresh the browser. Verify Super Admin → **Salon Settings**, especially the GCash account and retained QR image. Run the verification checklist before reopening the site.

If migration reports a conflict or missing/unknown dependency, keep maintenance mode on and do not install the PHP patch yet. Business changes are rolled back; empty support tables or the migration procedure may remain. Review the reported schema/schedule issue on the backup copy, resolve it deliberately, and rerun. Do not delete conflicting business records merely to force success.

## Database strategy and recovery

`201_single_salon.sql` retains the `branches` table and all internal `branch_id` columns, keys, and relationships. It records the chosen ID in `single_salon_config`, then remaps assignments in these eight tables: `users`, `bookings`, `inventory`, `promotions`, `suppliers`, `inventory_movements`, `staff_weekly_availability`, and `staff_schedule_exceptions`. It also updates the legacy `staff_profiles.branch` label and the chosen salon name to Nails & Body Worx.

No business rows are deleted or merged. Record IDs, quantities, amounts, statuses, relationships, and original timestamps are preserved. Separate inventory items remain separate, even if their names match. Former branch metadata remains available internally. The old assignment values are stored in `single_salon_assignment_backup`; `record_id` is the row's `id`, except for `staff_profiles`, where it is `user_id`. A full original copy of branch metadata is retained in `single_salon_branches_backup`, including old GCash details. Existing receipts/proofs are not altered; review payments made to former accounts against those original details.

The migration rejects duplicate weekly rules for a staff/day, multiple custom-hours rules for a staff/date, unexpected branch dependencies, and nontransactional affected tables. Other SQL failures also roll back business updates. Repeating a successful migration does not overwrite its backups or reassign later records. Existing null assignments are included in consolidation.

**No destructive cleanup migration is supplied:** these structures still have runtime dependencies and are not genuinely obsolete yet. Do not drop them, change the pinned ID after migration, or rerun old branch/sample-data scripts such as `068_branch_support.sql`, `071_sample_gcash_details.sql`, or `074_sample_branch_inventory.sql`. Historical SQL files are retained as migration history, not upgrade instructions.

To undo the installation before reopening, restore the complete database and project backups together. After new activity has occurred, do not restore an old database blindly: reconcile those new transactions first. Assignment snapshots aid investigation but are not a substitute for the full backup.

## Validation

Validated with PHP 8.0.30, MariaDB 10.4.32, and headless Chrome on isolated test copies. All 161 PHP files passed syntax checks; all four changed JavaScript files passed syntax checks. Tests covered five migration scenarios, preservation of 28 fixture rows across 13 tables, 51 HTTP checks including 28 affected pages and 12 CSV exports, seven operational checks, and the four-step desktop/mobile booking flow with no JavaScript errors. Nullable schedule time fields are rendered safely.

The archive contains schema/migration files, not a current business-data dump. Testing used the read-only inspected local schema and synthetic data. The existing SmartBooking database was **not migrated**. Production-specific schedules, payment details, and integrations still need the deployment checklist. No live payments, messages, or AI generation requests were sent.

## Changed and new files

The complete project-relative list follows. Only these files are included in the patch ZIP.

- `admin/ajax/inventory-actions.php`
- `admin/ajax/staff-schedule.php`
- `admin/ajax/update-booking.php`
- `admin/booking-ai-design.php`
- `admin/bookings.php`
- `admin/edit-staff.php`
- `admin/inventory.php`
- `admin/payments.php`
- `admin/promotions.php`
- `admin/staff-schedule.php`
- `admin/staff.php`
- `assets/css/booking-v3.css`
- `assets/js/administrator-management.js`
- `assets/js/booking-management.js`
- `assets/js/booking-v3.js`
- `assets/js/branch-management.js`
- `auth/login.php`
- `customer/book.php`
- `customer/pay-booking.php`
- `database/201_single_salon.sql` (new)
- `docs/single-salon-README.md` (new)
- `docs/single-salon-verification.md` (new)
- `includes/booking-management-helper.php`
- `includes/branch-context.php`
- `includes/reporting-helper.php`
- `includes/reports-dashboard-view.php`
- `includes/superadmin-sidebar.php`
- `index.php`
- `staff/schedule.php`
- `superadmin/add-admin.php`
- `superadmin/add-staff.php`
- `superadmin/admins.php`
- `superadmin/branches.php`
- `superadmin/edit-admin.php`
- `superadmin/edit-staff.php`
- `superadmin/employment-records.php`
- `superadmin/reports.php`
- `superadmin/salon-settings.php` (new)
- `superadmin/staff.php`
