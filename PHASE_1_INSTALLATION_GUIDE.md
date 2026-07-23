# SmartBooking V2 — Phase 1 Final

## Installation
1. Copy this folder to `C:\xampp\htdocs\smartbooking_v2`.
2. Start Apache and MySQL in XAMPP.
3. Import `database/SMARTBOOKING_V2_PHASE1_FINAL.sql` into phpMyAdmin.
4. Confirm `config/database.php` uses database `smartbooking_v2`.
5. Open `http://localhost/smartbooking_v2/` and press `Ctrl + F5`.

## Default Super Admin
- Username: `SupAdmin`
- Password: `SupAdmin`
- Change this password immediately after first login.

## Phase 1 features
- Public image-rich salon website and real service catalog
- Customer registration, login, Google/Facebook OAuth foundation
- Forgot/reset password token workflow
- Email-verification database foundation
- Customer dashboard and modern booking wizard
- Booking draft restoration after login
- Super Admin, Admin, Staff, and Customer role protection
- Settings, audit logs, reporting, analytics, and backup/restore
- English, Filipino, and Taglish localization foundation

## OAuth
Add actual provider credentials to `config/oauth.php`. Never upload real secrets to a public repository.

## Password-reset email
For local XAMPP, `forgot-password.php` displays the generated reset URL. Before deployment, replace this with SMTP delivery using PHPMailer or another mail provider.

## Backup warning
Only restore SQL files created by SmartBooking and only after downloading a fresh backup. Test restoration on a copied database before production deployment.

## Phase 2 boundary
AI model integration, production SMS/push delivery, deployment, advanced forecasting, and the final mobile application belong to Phase 2.
