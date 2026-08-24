<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\Accreditation;
use App\Models\StudyProgram;
use App\Services\AuditService;

class AccreditationController extends Controller {
    public function index(): void {
        $this->requireAuth();

        $accreditationModel = new Accreditation();
        $accreditations = $accreditationModel->allWithProgram();

        $this->render('accreditation/index', [
            'title'          => 'Monitoring Akreditasi Program Studi',
            'accreditations' => $accreditations
        ]);
    }
}
