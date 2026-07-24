USE smartbooking_v2;

-- System fallback appearance for first-time visitors.
ALTER TABLE system_settings
    MODIFY COLUMN default_language ENUM(
        'english',
        'filipino',
        'taglish'
    ) NOT NULL DEFAULT 'english',
    ADD COLUMN IF NOT EXISTS default_theme ENUM(
        'default',
        'light',
        'dark'
    ) NOT NULL DEFAULT 'default'
    AFTER default_language;

-- Each registered user may keep an account-level appearance preference.
ALTER TABLE user_language_preferences
    ADD COLUMN IF NOT EXISTS theme_mode ENUM(
        'default',
        'light',
        'dark'
    ) NOT NULL DEFAULT 'default'
    AFTER language_code;

UPDATE system_settings
SET
    default_language = CASE LOWER(default_language)
        WHEN 'filipino' THEN 'filipino'
        WHEN 'taglish' THEN 'taglish'
        ELSE 'english'
    END,
    default_theme = COALESCE(default_theme, 'default')
WHERE id = 1;
