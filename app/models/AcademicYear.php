<?php
namespace App\Models;

use App\Core\Model;
use App\Services\Obe\AcademicYearProjectionService;

class AcademicYear extends Model {
    protected string $table = 'academic_years';

    public function getActive(): ?array {
        return (new AcademicYearProjectionService())->active();
    }
}
