<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\Course;
use App\Models\ClassModel;
use App\Models\Guidance;
use App\Models\StudyProgram;
use App\Models\AcademicYear;

class AcademicController extends Controller {
    public function index(): void {
        $this->requireAuth();

        $classModel = new ClassModel();
        $ayModel = new AcademicYear();
        $activeAy = $ayModel->getActive();

        $classes = $classModel->allWithDetails($activeAy ? (int)$activeAy['id'] : null);
        $programModel = new StudyProgram();
        $programs = $programModel->all();

        // Calculate statistics
        $totalClasses = count($classes);
        $problemClasses = count(array_filter($classes, fn($c) => $c['problem_flag'] == 1));
        
        $totalHeld = array_sum(array_column($classes, 'total_held_meetings'));
        $totalPlanned = array_sum(array_column($classes, 'total_planned_meetings')) ?: 1;
        $realizationRate = round(($totalHeld / $totalPlanned) * 100, 1);

        $avgAttendance = count($classes) > 0 ? round(array_sum(array_column($classes, 'average_attendance')) / count($classes), 1) : 0;

        $this->render('academic/index', [
            'title'           => 'Monitoring Perkuliahan & Akademik',
            'classes'         => $classes,
            'programs'        => $programs,
            'activeAy'        => $activeAy,
            'totalClasses'    => $totalClasses,
            'problemClasses'  => $problemClasses,
            'realizationRate' => $realizationRate,
            'avgAttendance'   => $avgAttendance
        ]);
    }

    public function courses(): void {
        $this->requireAuth();
        $courseModel = new Course();
        $programModel = new StudyProgram();

        $filterProgram = (int)$this->getQuery('program_id', 0);
        $courses = $courseModel->allWithLecturerAndProgram($filterProgram ?: null);
        $programs = $programModel->all();

        $this->render('academic/courses', [
            'title'         => 'Data Kurikulum & Mata Kuliah',
            'courses'       => $courses,
            'programs'      => $programs,
            'filterProgram' => $filterProgram
        ]);
    }

    public function guidance(): void {
        $this->requireAuth();
        $guidanceModel = new Guidance();

        $filterType = $this->getQuery('type', '');
        $guidances = $guidanceModel->allWithDetails($filterType ?: null);

        $this->render('academic/guidance', [
            'title'      => 'Monitoring Bimbingan (DPA / Skripsi / MBKM)',
            'guidances'  => $guidances,
            'filterType' => $filterType
        ]);
    }
}
