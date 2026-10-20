CREATE TABLE IF NOT EXISTS guidance_cases (
    id INT AUTO_INCREMENT PRIMARY KEY,
    type ENUM('Skripsi','KKN','PKL') NOT NULL,
    title VARCHAR(255) NOT NULL,
    progress_percentage DECIMAL(5,2) NOT NULL DEFAULT 0,
    status ENUM('Aktif','Selesai','Bermasalah','Terlambat') NOT NULL DEFAULT 'Aktif',
    last_guidance_date DATE NULL,
    notes TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS guidance_case_students (
    guidance_case_id INT NOT NULL,
    obe_student_id INT NOT NULL,
    PRIMARY KEY (guidance_case_id, obe_student_id),
    CONSTRAINT fk_guidance_case_students_case FOREIGN KEY (guidance_case_id)
        REFERENCES guidance_cases(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS guidance_case_lecturers (
    guidance_case_id INT NOT NULL,
    obe_lecturer_id INT NOT NULL,
    supervisor_order TINYINT UNSIGNED NOT NULL DEFAULT 1,
    PRIMARY KEY (guidance_case_id, obe_lecturer_id),
    UNIQUE KEY uq_guidance_supervisor_order (guidance_case_id, supervisor_order),
    CONSTRAINT fk_guidance_case_lecturers_case FOREIGN KEY (guidance_case_id)
        REFERENCES guidance_cases(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
