<?php
namespace App\Models;

use App\Core\Model;

class StudyProgram extends Model {
    protected string $table = 'study_programs';

    public function allWithFaculty(): array {
        $sql = "SELECT sp.*, f.name as faculty_name, f.code as faculty_code 
                FROM study_programs sp 
                JOIN faculties f ON sp.faculty_id = f.id 
                ORDER BY sp.id ASC";
        return $this->rawFetch($sql);
    }
}
