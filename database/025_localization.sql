CREATE TABLE IF NOT EXISTS system_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) UNIQUE,
    setting_value VARCHAR(255)
);

INSERT INTO system_settings (setting_key, setting_value)
VALUES ('default_language', 'english')
ON DUPLICATE KEY UPDATE
setting_value = VALUES(setting_value);