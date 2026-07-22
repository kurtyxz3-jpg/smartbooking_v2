USE smartbooking_v2;

CREATE TABLE IF NOT EXISTS localization_settings (
    id TINYINT UNSIGNED NOT NULL PRIMARY KEY,
    language_code ENUM('english', 'filipino', 'taglish')
        NOT NULL DEFAULT 'english',
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP
);

INSERT INTO localization_settings (id, language_code)
VALUES (1, 'english')
ON DUPLICATE KEY UPDATE language_code = language_code;

CREATE TABLE IF NOT EXISTS user_language_preferences (
    user_id INT UNSIGNED NOT NULL PRIMARY KEY,
    language_code ENUM('english', 'filipino', 'taglish')
        NOT NULL DEFAULT 'english',
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP
);
