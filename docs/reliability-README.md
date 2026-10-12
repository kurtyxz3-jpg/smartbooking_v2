# SmartBooking V2 — security, booking and homepage update

This patch targets the latest smartbooking_v2.rar supplied from C:/xampp/htdocs on 12 October 2026. Install on that version, not an older unrelated copy. It does not add a payment gateway or change the AI generation module.

## What changed
- Password recovery queues a private email instead of exposing a reset URL on the page. Requests return the same message for known and unknown accounts. CSRF checks, IP/account request limits, hashed one-hour tokens, atomic single-use consumption, and session revocation after a successful reset are included. Older sessions must sign in again after a password reset.
- New bookings, customer changes, administrator assignments, staff confirmations, manual payment status changes and schedule edits share a transaction lock. The final availability check and write happen together. Any-staff bookings reserve a specific available staff member. Duration overlaps, breaks, days off, exceptions and working hours are checked. New bookings snapshot the current service duration; historical bookings fall back to the service duration when no snapshot exists.
- Booking and rescheduling use the same availability rules. Customer ownership, staff ownership and the existing customer change cutoff remain enforced. Cancelled/completed bookings cannot be revived through staff or payment review forms.
- Appointment events are queued in the same database transaction as the booking change. The worker sends customer/staff notifications and admin booking alerts, schedules confirmed-appointment reminders, respects preferences and suppresses superseded messages. Failed requests have bounded retries. Uncertain provider responses are held for manual review to avoid automatic duplicate messages.
- The worker releases expired unpaid reservations; submitted pending or verified payments protect their reservations. This changes qualifying booking statuses to cancelled, without deleting records.
- Visit Us reads the retained salon address, phone and hours from Salon Settings, with a directions link. Historical branch posters on the homepage are replaced by clean photos and service cards using active database prices. Original image files remain in the project. Manually uploaded promotion images are not rewritten.
- Resolves all eight homepage merge-conflict blocks and fixes the uploaded Add Administrator page's leading whitespace and leftover branch selector.

## Back up and install
1. Put the site in maintenance mode and stop scheduled/background writes. Export the full database and copy the entire project, including uploads and configuration, somewhere outside the web root.
2. Confirm database/201_single_salon.sql has already completed. This update requires the existing single-salon setup. If it has not, follow the earlier single-salon migration instructions first.
3. Select the correct database in phpMyAdmin. Import only database/202_booking_reliability.sql from this patch. STOP if any SQL error appears; do not continue to install PHP files until resolved. The migration targets MariaDB as bundled with XAMPP (tested on 10.4.32), requires CREATE/ALTER/TRIGGER and routine privileges, and can be rerun. It adds support tables and estimated_duration if missing; no existing business records are deleted. New booking triggers require InnoDB business tables.
4. Extract the patch into the existing smartbooking_v2 project root, merging folders and replacing matching files. Do not create an extra nested project folder. Database credentials, provider secrets and uploaded assets are not bundled.
5. Configure delivery and the scheduled worker as below. Save the actual salon address/contact/hours in Super Admin > Salon Settings. Sign out/in and refresh the browser with Ctrl+F5.
6. Complete the verification checklist on a staging copy, then run a controlled test using your own email/phone before reopening.

## Email and SMS setup
- This patch uses the existing Brevo account integration. In config/email.php, check from_email and from_name. The sender must be authorized in your provider account.
- Keep the Brevo API key in the existing config/email-credentials.php as brevo_api_key, or provide BREVO_API_KEY through the server/task environment. Never place the key in frontend JavaScript or the public README. Existing secrets are not replaced by the patch.
- In config/reliability.php set base_url to the final HTTPS site URL, including /smartbooking_v2 if hosted in that folder. NBW_BASE_URL can override it. A blank value falls back to config/email.php; localhost links are suitable only for local testing. Public reset links must use the real HTTPS address.
- The timezone defaults to Asia/Manila. PHP and booking-worker database sessions use that salon timezone.
- Email is governed by Notification Settings; password-recovery emails are security messages and do not depend on appointment opt-ins. SMS needs a working Brevo transactional SMS account, credits/approved sender, the SMS toggle, and sms_provider_enabled=true in config/reliability.php (or NBW_SMS_ENABLED=1). SMS stays off at provider level by default. Philippine 09 numbers normalize to +63; other numbers need international format.
- Brevo requests returning success are marked as accepted by the provider, not confirmed inbox/handset delivery. Provider-side delivery should be checked in Brevo. No live email or SMS was sent during development.
- Reference: https://developers.brevo.com/docs/send-a-transactional-email and https://developers.brevo.com/reference/send-async-transactional-sms

## Run the background worker
Password-reset emails and appointment messages need this task running. It is not automatically installed by copying files.

Windows Task Scheduler:
- Program: C:\xampp\php\php.exe
- Arguments: "C:\xampp\htdocs\smartbooking_v2\bin\process-notifications.php" --send
- Start in: C:\xampp\htdocs\smartbooking_v2
- Trigger: repeat every 1 minute indefinitely. Run while the server is available, even when no user is logged in; use an account permitted to read configuration and connect to the database. PHP cURL must be enabled.

A web request cannot run the worker. Running it without --send only reports queue counts and sends nothing. Overlapping worker runs are prevented with a database lock. For Linux hosting, schedule the equivalent PHP CLI command every minute.

Super Admin > Notification Settings shows queue counts and the latest retry/failure/uncertain entries. If entries remain pending, check the task. If retries fail, check credentials, sender permissions, credits and server connectivity. For an uncertain entry, review the provider log before retrying: do not blindly reset every queue row. After confirming a particular message was not accepted, a database administrator may reset that specific delivery ID to pending, attempts=0, available_at=NOW(). Never alter entries already accepted by the provider. An expired recovery request should be requested again through Forgot Password.

## Data notes and recovery
- Existing bookings without a staff assignment conservatively block overlapping slots until an administrator assigns them. Review these before launch; the patch does not silently reassign historical appointments.
- Existing booking IDs, prices/payments, customers, stock and source images are preserved by migration. Normal application/worker actions can update appointment status as described above.
- Take backups that include triggers. The existing table-only backup utility does not itself preserve the new triggers; after a restore, rerun migration 202 in maintenance mode before resuming writes.
- Before reopening, rollback means restoring the complete project and database backup together and stopping the new scheduled task. After new activity, reconcile changes before any restore.
- Existing promotion/payment-receipt/system/push preference controls are not a promise of new integrations: this update implements appointment events, reminders and password recovery. Browser push, marketing broadcasts and payment-gateway integration remain outside this patch.

## Changed/new files
- admin/ajax/staff-schedule.php
- admin/ajax/update-booking.php
- admin/payments.php
- auth/complete-facebook-email.php
- auth/forgot-password.php
- auth/login.php
- auth/reset-password.php
- bin/process-notifications.php
- config/oauth.php
- config/reliability.php
- customer/appointment-details.php
- customer/book.php
- customer/pay-booking.php
- database/202_booking_reliability.sql
- includes/auth_check.php
- includes/booking-management-helper.php
- includes/booking-reliability.php
- includes/message-transport.php
- includes/notification-worker.php
- includes/password-recovery.php
- includes/session-security.php
- includes/staff-schedule-helper.php
- index.php
- staff/bookings.php
- superadmin/add-admin.php
- superadmin/notification-settings.php
- docs/reliability-README.md
- docs/reliability-verification.md
