USE smartbooking_v2;

CREATE TABLE IF NOT EXISTS ai_nail_generations (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    customer_id INT NOT NULL,
    color_palette VARCHAR(150) NOT NULL,
    design_style VARCHAR(100) NOT NULL,
    nail_shape VARCHAR(50) NOT NULL,
    nail_length VARCHAR(50) NOT NULL,
    occasion VARCHAR(100) DEFAULT NULL,
    add_ons_json TEXT DEFAULT NULL,
    instructions TEXT DEFAULT NULL,
    prompt_text TEXT DEFAULT NULL,
    image_1_url VARCHAR(500) NOT NULL,
    image_2_url VARCHAR(500) NOT NULL,
    image_3_url VARCHAR(500) NOT NULL,
    selected_image_number TINYINT DEFAULT NULL,
    selected_image_url VARCHAR(500) DEFAULT NULL,
    provider VARCHAR(50) NOT NULL DEFAULT 'demo',
    status ENUM('generated','selected','archived') NOT NULL DEFAULT 'generated',
    selected_at DATETIME DEFAULT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY idx_ai_generation_customer_date (customer_id, created_at),
    KEY idx_ai_generation_status (status)
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;
