<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;

class SearchController extends Controller {
    public function search(): void {
        $this->requireAuth();
        $q = trim($this->getQuery('q', ''));

        if (empty($q) || strlen($q) < 2) {
            $this->json(['results' => []]);
        }

        $db = Database::getConnection();
        $param = '%' . $q . '%';
        $results = [];

        // 1. Lecturers (SDM Dosen)
        $stmt = $db->prepare("
            SELECT id, name, nidn 
            FROM lecturers 
            WHERE name LIKE :q1 OR nidn LIKE :q2 
            LIMIT 5
        ");
        $stmt->execute(['q1' => $param, 'q2' => $param]);
        while ($row = $stmt->fetch()) {
            $results[] = [
                'category' => 'Dosen & SDM',
                'title'    => $row['name'],
                'subtitle' => 'NIDN: ' . $row['nidn'],
                'url'      => 'lecturers/detail?id=' . $row['id']
            ];
        }

        // 2. Study Programs (Program Studi)
        $stmt = $db->prepare("
            SELECT id, name, code, degree 
            FROM study_programs 
            WHERE name LIKE :q1 OR code LIKE :q2 
            LIMIT 5
        ");
        $stmt->execute(['q1' => $param, 'q2' => $param]);
        while ($row = $stmt->fetch()) {
            $results[] = [
                'category' => 'Program Studi',
                'title'    => $row['name'] . ' (' . $row['degree'] . ')',
                'subtitle' => 'Kode: ' . $row['code'],
                'url'      => 'master/study-programs'
            ];
        }

        // 3. Students (Mahasiswa)
        $stmt = $db->prepare("
            SELECT id, name, nim 
            FROM students 
            WHERE name LIKE :q1 OR nim LIKE :q2 
            LIMIT 5
        ");
        $stmt->execute(['q1' => $param, 'q2' => $param]);
        while ($row = $stmt->fetch()) {
            $results[] = [
                'category' => 'Mahasiswa',
                'title'    => $row['name'],
                'subtitle' => 'NIM: ' . $row['nim'],
                'url'      => 'students'
            ];
        }

        // 4. Meetings (Rapat Digital)
        $stmt = $db->prepare("
            SELECT id, title, meeting_number 
            FROM meetings 
            WHERE title LIKE :q1 OR meeting_number LIKE :q2 
            LIMIT 5
        ");
        $stmt->execute(['q1' => $param, 'q2' => $param]);
        while ($row = $stmt->fetch()) {
            $results[] = [
                'category' => 'Rapat & Tata Kelola',
                'title'    => $row['title'],
                'subtitle' => $row['meeting_number'],
                'url'      => 'meetings/detail?id=' . $row['id']
            ];
        }

        // 5. Action Items (RTL)
        $stmt = $db->prepare("
            SELECT id, item_code, description, pic_name 
            FROM action_items 
            WHERE item_code LIKE :q1 OR description LIKE :q2 OR pic_name LIKE :q3 
            LIMIT 5
        ");
        $stmt->execute(['q1' => $param, 'q2' => $param, 'q3' => $param]);
        while ($row = $stmt->fetch()) {
            $results[] = [
                'category' => 'Tindak Lanjut (RTL)',
                'title'    => $row['item_code'] . ': ' . substr($row['description'], 0, 50) . '...',
                'subtitle' => 'PIC: ' . $row['pic_name'],
                'url'      => 'meetings/rtl'
            ];
        }

        // 6. Indicators (IKU / Renstra)
        $stmt = $db->prepare("
            SELECT id, code, name, category 
            FROM indicators 
            WHERE code LIKE :q1 OR name LIKE :q2 
            LIMIT 5
        ");
        $stmt->execute(['q1' => $param, 'q2' => $param]);
        while ($row = $stmt->fetch()) {
            $results[] = [
                'category' => 'Indikator Kinerja (' . $row['category'] . ')',
                'title'    => $row['code'] . ' - ' . $row['name'],
                'subtitle' => 'Kategori: ' . $row['category'],
                'url'      => 'strategic'
            ];
        }

        // 7. Cooperations (Kerjasama Mitra)
        $stmt = $db->prepare("
            SELECT id, partner_name, scope, type 
            FROM cooperations 
            WHERE partner_name LIKE :q1 OR scope LIKE :q2 
            LIMIT 5
        ");
        $stmt->execute(['q1' => $param, 'q2' => $param]);
        while ($row = $stmt->fetch()) {
            $results[] = [
                'category' => 'Kerja Sama (' . $row['type'] . ')',
                'title'    => $row['partner_name'],
                'subtitle' => $row['scope'],
                'url'      => 'cooperations'
            ];
        }

        $this->json(['results' => $results]);
    }
}
