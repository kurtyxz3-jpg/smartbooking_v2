CREATE TABLE IF NOT EXISTS users(
id INT AUTO_INCREMENT PRIMARY KEY,
fullname VARCHAR(150),
username VARCHAR(50) UNIQUE,
email VARCHAR(150),
password VARCHAR(255),
role ENUM('super_admin','admin','staff','customer'),
status ENUM('active','inactive') DEFAULT 'active'
);

INSERT INTO users(fullname,username,email,password,role,status)
VALUES('System Super Administrator','SupAdmin','superadmin@nbw.local','SupAdmin','super_admin','active');
