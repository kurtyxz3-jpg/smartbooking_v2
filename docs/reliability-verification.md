# Verification checklist and results

## Automated checks completed
- All 169 project PHP files pass PHP 8.0.30 syntax validation. No unresolved PHP/JS/CSS merge markers remain.
- 25 core checks: duration snapshots, partial overlap, adjacent slots, breaks, closing times, invalid times, day-off/custom-hours rules, any-staff assignment, self-exclusion for rescheduling, event rollback, notification preferences, reminder deduplication, stale-event suppression, reset rate limits, token hashing, expiry and single-use.
- 7 notification failure/lifecycle checks: retry delay, uncertain-send handling, no automatic uncertain resend, retry exhaustion, expired unpaid reservation release and protection for submitted payments. Transport was simulated; no real recipients contacted.
- Concurrent processes: one of two same-staff requests succeeds; three available staff accept exactly three of four simultaneous any-staff requests; a reset token can be consumed by only one of two simultaneous attempts.
- 22 HTTP checks: affected pages render without PHP errors, reset responses do not reveal tokens, form flows work, bookings can be created/rescheduled/cancelled and revoked sessions lose access.
- 11 operational HTTP checks: administrator assignment overlap checks, staff ownership and transitions, manual payment confirmation, exception overlap and custom-hours handling.
- Chrome desktop/mobile checks: all gallery tabs load; Visit Us and service links render; recovery form, four-step booking navigation and notification status screen work without JavaScript errors.
- Migration runs successfully and can be repeated without changing the existing business/queue rows captured for comparison.

Tests used an isolated MariaDB database with synthetic records and a separate preview site. The live XAMPP project and business database were not updated. Live provider acceptance/delivery, actual customer records, final domain/TLS and operating-system scheduling still need the checks below.

## Verify after installation
- [ ] Migration 202 completes; preserve a full backup and confirm existing record counts.
- [ ] Homepage loads without conflict markers. Visit Us shows the actual salon address, contact and hours. Service cards link to the correct active service.
- [ ] Forgot Password gives the same response for existing/nonexistent accounts and never displays a token. Your own test email arrives through the worker, points to the real HTTPS site, expires after one hour and works only once.
- [ ] Resetting a test password signs out its older sessions. Normal password and social login still work with your deployment configuration.
- [ ] Try two browsers booking the same staff/time. Only one succeeds. Try a partially overlapping service, staff break, day off and appointment ending after closing; all should be rejected.
- [ ] Reschedule a future booking to a free slot, then to a conflicting slot. Confirm ownership restrictions and the existing change cutoff.
- [ ] Confirm/change/cancel an appointment through Admin and Staff. Check the queue and your own customer/staff notification preferences.
- [ ] Schedule a confirmed test appointment within the reminder window and run the worker twice. Verify one reminder per enabled recipient/channel. Cancel/reschedule before processing and check that the outdated reminder is suppressed.
- [ ] Verify SMS with your own phone only after enabling/configuring the provider. Inspect provider delivery logs; acceptance alone is not delivery confirmation.
- [ ] Confirm the background task runs every minute and review failed/uncertain entries. Do not bulk-resend the queue.
- [ ] Check manual payment proof submission/review, other roles and existing uploads; AI generation and payment-gateway code are outside this update.
