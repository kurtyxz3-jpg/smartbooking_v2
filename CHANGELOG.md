# Registration Security Update

## Added

- Registration security configuration.
- Email-delivery configuration with safe localhost development mode.
- Registration attempt and verification-send throttling.
- Hashed, expiring email-verification tokens.
- Verification-pending and resend page.
- Manual-account verification checks during login and booking.
- Registration CSRF token and bot honeypot.

## Changed

- Manual registration no longer signs the account in immediately.
- Manual accounts proceed only after email ownership is verified.
- Password minimum increased from 8 to 12 characters.
- Duplicate-email registration response no longer confirms account existence.

## Preserved

- Google Login.
- Facebook Login and the Facebook-ID fallback.
- Theme, language, branch, and folder structure.
# Brevo OTP Update

- Added secure Cancel registration and Already have an account sign-in actions to social profile setup.
- Cancellation clears temporary OAuth onboarding data without creating or deleting database records.
- Deferred new Google and Facebook account creation until the required profile form is submitted successfully.
- Closing or leaving social profile setup now leaves no new customer record.
- Separated Facebook login and registration intent, matching the Google flow.
- Separated Google login from Google registration: login no longer creates unknown accounts.
- Rebuilt the OTP and success card spacing, alignment, and responsive sizing.
- Fixed a stale-session bypass that could open the customer dashboard before OTP verification.
- Added verification enforcement to every page using the shared customer authentication include.
- Added six-digit email verification codes delivered through Brevo's transactional API.
- Added a dedicated code-entry and resend experience.
- Added 10-minute expiry, five-attempt limit, hashed code storage, and single-use enforcement.
- Added a private credentials file and Git ignore rule.
- Added migration `082_email_otp.sql` for existing installations.
