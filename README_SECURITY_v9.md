# SmartBooking V2 — Free Registration Security Update v9

Copy this package into the existing `smartbooking_v2` folder and allow the listed files to replace their older copies.

## Included protections

- Cloudflare Turnstile on manual registration and social profile completion.
- Server-side CAPTCHA validation (the browser result is never trusted by itself).
- Manual sign-in throttling after repeated invalid attempts.
- Duplicate mobile-number rejection for manual and social registration.
- Social registrations remain temporary until the profile form is completed.
- Incomplete legacy social profiles are redirected to profile completion.
- New accounts are limited to one active booking during their first 24 hours.
- Existing account suspension/status checks remain enforced.
- Security events are recorded in `registration_security_events`.

## Turnstile setup

The included configuration uses Cloudflare's official localhost test keys, so development tests pass without payment or a Cloudflare account.

Before public deployment:

1. Create a free Turnstile widget for the live domain.
2. Copy `config/turnstile-credentials.example.php` to `config/turnstile-credentials.php`.
3. Put the live site key and secret in that private file.
4. Do not commit `config/turnstile-credentials.php` to GitHub.

## Database

No new migration is required if migrations `081_registration_security.sql` and `082_email_verification_code.sql` were already imported. This update uses the existing security-events and verification tables.

## Important testing note

Manual accounts still receive the six-digit email verification code. Google and Facebook accounts rely on the provider's verified identity and must finish the SmartBooking profile form before the account is created or used.
