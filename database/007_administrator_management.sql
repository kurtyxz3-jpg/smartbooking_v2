USE smartbooking_v2;

-- The Administrator Management module uses the existing users table.
-- Run this only if your users table does not yet contain the admin role.

ALTER TABLE users
MODIFY role ENUM('super_admin', 'admin', 'staff', 'customer') NOT NULL;
