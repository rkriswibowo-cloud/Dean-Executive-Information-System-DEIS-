<?php
namespace App\Models;

use App\Core\Model;

class AcademicYear extends Model {
    protected string $table = 'academic_years';

    public function getActive(): ?array {
        return $this->whereOne('is_active', 1);
    }
}
