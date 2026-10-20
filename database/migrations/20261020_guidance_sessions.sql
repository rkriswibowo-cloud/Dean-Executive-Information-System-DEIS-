CREATE TABLE IF NOT EXISTS guidance_sessions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    guidance_case_id INT NOT NULL,
    guidance_date DATE NOT NULL,
    topic VARCHAR(255) NOT NULL,
    notes TEXT NULL,
    progress_after DECIMAL(5,2) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_guidance_sessions_case FOREIGN KEY (guidance_case_id)
        REFERENCES guidance_cases(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS guidance_session_students (
    guidance_session_id INT NOT NULL,
    obe_student_id INT NOT NULL,
    PRIMARY KEY (guidance_session_id, obe_student_id),
    CONSTRAINT fk_guidance_session_students_session FOREIGN KEY (guidance_session_id)
        REFERENCES guidance_sessions(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
