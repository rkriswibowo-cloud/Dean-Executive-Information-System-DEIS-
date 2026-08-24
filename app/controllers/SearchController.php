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

        // 1. Lecturers
        $stmt = $db->prepare("SELECT id, name, nidn, 'Dosen' as type, 'lecturers/detail?id=' as url_prefix FROM lecturers WHERE name LIKE :q OR nidn LIKE :q LIMIT 5");
        $stmt->execute(['q' => $param]);
        while ($row = $stmt->fetch()) {
            $results[] = [
                'category' => 'Dosen & SDM',
                'title'    => $row['name'],
                'subtitle' => 'NIDN: ' . $row['nidn'],
                'url'      => $row['url_prefix'] . $row['id']
            ];
        }

        // 2. Students
        $stmt = $db->prepare("SELECT id, name, nim, 'Mahasiswa' as type FROM students WHERE name LIKE :q OR nim LIKE :q LIMIT 5");
        $stmt->execute(['q' => $param]);
        while ($row = $stmt->fetch()) {
            $results[] = [
                'category' => 'Mahasiswa',
                'title'    => $row['name'],
                'subtitle' => 'NIM: ' . $row['nim'],
                'url'      => 'students'
            ];
        }

        // 3. Meetings
        $stmt = $db->prepare("SELECT id, title, meeting_number FROM meetings WHERE title LIKE :q OR meeting_number LIKE :q OR agenda LIKE :q LIMIT 5");
        $stmt->execute(['q' => $param]);
        while ($row = $stmt->fetch()) {
            $results[] = [
                'category' => 'Rapat & Tata Kelola',
                'title'    => $row['title'],
                'subtitle' => $row['meeting_number'],
                'url'      => 'meetings/detail?id=' . $row['id']
            ];
        }

        // 4. Action Items (RTL)
        $stmt = $db->prepare("SELECT id, item_code, description, pic_name FROM action_items WHERE item_code LIKE :q OR description LIKE :q OR pic_name LIKE :q LIMIT 5");
        $stmt->execute(['q' => $param]);
        while ($row = $stmt->fetch()) {
            $results[] = [
                'category' => 'Tindak Lanjut (RTL)',
                'title'    => $row['item_code'] . ': ' . substr($row['description'], 0, 50) . '...',
                'subtitle' => 'PIC: ' . $row['pic_name'],
                'url'      => 'meetings/rtl'
            ];
        }

        // 5. Indicators
        $stmt = $db->prepare("SELECT id, code, name, category FROM indicators WHERE code LIKE :q OR name LIKE :q LIMIT 5");
        $stmt->execute(['q' => $param]);
        while ($row = $stmt->fetch()) {
            $results[] = [
                'category' => 'Indikator Kinerja (' . $row['category'] . ')',
                'title'    => $row['code'] . ' - ' . $row['name'],
                'subtitle' => 'Kategori: ' . $row['category'],
                'url'      => 'strategic'
            ];
        }

        // 6. Cooperations
        $stmt = $db->prepare("SELECT id, partner_name, scope, type FROM cooperations WHERE partner_name LIKE :q OR scope LIKE :q LIMIT 5");
        $stmt->execute(['q' => $param]);
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
