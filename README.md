# SmartBooking Brevo Email OTP Verification

## Install

1. Extract this package into `C:\xampp\htdocs\smartbooking_v2\` and allow replacement.
2. If the earlier registration-security update was never installed, import `database/081_registration_security.sql` first.
3. Always import `database/082_email_otp.sql`. It safely adds the OTP fields to an existing installation.
4. Keep your existing private `config/email-credentials.php`. This package deliberately does not overwrite it. If it does not exist, copy `config/email-credentials.example.php` to `config/email-credentials.php`, then replace `PASTE_YOUR_BREVO_API_KEY_HERE` with your saved Brevo API key.
5. Do not commit `config/email-credentials.php` to GitHub. The included `.gitignore` protects it.
6. Keep the verified Brevo sender as `Nails & Body Worx <nbwsmartbooking@gmail.com>` unless you change it in `config/email.php`.

## Test

Register with a new email address. SmartBooking will open the verification-code page and Brevo will send a six-digit code. Enter it within 10 minutes. Check Spam and Brevo Transactional Logs if it does not arrive.

## Security controls

- Codes are random, six digits, stored only as SHA-256 hashes, single-use, and expire after 10 minutes.
- Five incorrect attempts invalidate the current code.
- Resends have a 60-second cooldown and a three-per-hour limit.
- Manual accounts cannot sign in or book before verification.
- A global customer-page guard removes stale authenticated sessions and redirects unverified accounts to the OTP page.
- Google and Facebook accounts retain their provider-specific verification behavior.
- New social-provider details stay only in the server session during profile setup. The database account is created only after the required profile form is submitted successfully.
- CSRF, registration rate limiting, password hashing, and generic duplicate-account responses remain enabled.

## Cost

No payment is required while usage stays within Brevo's current free-plan allowance. Brevo may change plan limits; monitor Usage and Plan in the Brevo dashboard.
