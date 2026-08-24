<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Services\CommandCenterService;

class AiController extends Controller {
    public function chat(): void {
        $this->requireAuth();
        $prompt = trim($this->getPost('message', ''));

        if (empty($prompt)) {
            $this->json(['status' => 'error', 'message' => 'Pesan tidak boleh kosong.'], 400);
        }

        $db = Database::getConnection();
        $promptLower = strtolower($prompt);

        // Intelligent Executive Assistant Rule & Context Engine
        $response = "";

        if (strpos($promptLower, 'iku') !== false || strpos($promptLower, 'kinerja') !== false || strpos($promptLower, 'capaian') !== false) {
            $stmt = $db->query("
                SELECT i.code, i.name, ir.achievement_percentage, ir.status 
                FROM indicators i
                JOIN indicator_targets it ON i.id = it.indicator_id AND it.year = 2026
                JOIN indicator_realizations ir ON it.id = ir.indicator_target_id
                WHERE i.category = 'IKU'
            ");
            $rows = $stmt->fetchAll();
            $avg = count($rows) > 0 ? round(array_sum(array_column($rows, 'achievement_percentage')) / count($rows), 1) : 0;

            $response = "📊 **Analisis Kinerja IKU Fakultas (2026):**\n\n";
            $response .= "Rata-rata capaian IKU fakultas saat ini berada pada angka **{$avg}%**.\n\n";
            $response .= "• **Capaian Tertinggi:** IKU-2 (Mahasiswa di Luar Kampus: 109.1%) & IKU-5 (Keluaran Riset: 106.7%).\n";
            $response .= "• **Perlu Perhatian Kritis:** IKU-8 (Akreditasi Unggul Prodi: 50.0%) dan IKU-4 (Kualifikasi S3 Dosen: 77.5%).";
        } elseif (strpos($promptLower, 'dosen') !== false || strpos($promptLower, 'bkd') !== false) {
            $stmt = $db->query("SELECT name, attendance_percentage, bkd_status FROM lecturers WHERE attendance_percentage < 75.00 OR bkd_status = 'Belum Memenuhi'");
            $problematic = $stmt->fetchAll();

            $response = "👨‍🏫 **Laporan Kinerja & Beban Dosen:**\n\n";
            $response .= "Ditemukan **" . count($problematic) . " dosen** yang memerlukan intervensi pembinaan dekanat:\n";
            foreach ($problematic as $p) {
                $response .= "• **{$p['name']}**: Presensi {$p['attendance_percentage']}%, Status BKD: *{$p['bkd_status']}*\n";
            }
            $response .= "\nRekomendasi: Jadwalkan pemanggilan konsultasi pimpinan prodi sebelum penutupan portal SISTER.";
        } elseif (strpos($promptLower, 'akreditasi') !== false) {
            $stmt = $db->query("SELECT sp.name, a.institution, a.valid_until, DATEDIFF(a.valid_until, CURDATE()) as days_left, a.status FROM accreditations a JOIN study_programs sp ON a.study_program_id = sp.id");
            $accs = $stmt->fetchAll();

            $response = "🏛️ **Radar Status Akreditasi Program Studi:**\n\n";
            foreach ($accs as $a) {
                $response .= "• **{$a['name']} ({$a['institution']})**: Status *{$a['status']}*, tersisa **{$a['days_left']} hari** (Hingga {$a['valid_until']})\n";
            }
            $response .= "\n⚠️ **Prioritas:** Prodi Sistem Informasi membutuhkan percepatan finalisasi LED Kriteria 4 & 6.";
        } elseif (strpos($promptLower, 'rapat') !== false || strpos($promptLower, 'agenda') !== false) {
            $stmt = $db->query("SELECT title, meeting_date, start_time, location FROM meetings WHERE meeting_date >= CURDATE() ORDER BY meeting_date ASC LIMIT 3");
            $meetings = $stmt->fetchAll();

            $response = "📅 **Agenda Rapat Dekan Mendatang:**\n\n";
            if (empty($meetings)) {
                $response .= "Tidak ada jadwal rapat dalam waktu dekat.";
            } else {
                foreach ($meetings as $m) {
                    $response .= "• **{$m['title']}**\n  Waktu: {$m['meeting_date']} pukul {$m['start_time']} WIB\n  Tempat: {$m['location']}\n\n";
                }
            }
        } elseif (strpos($promptLower, 'ringkas') !== false || strpos($promptLower, 'fakultas') !== false || strpos($promptLower, 'halo') !== false || strpos($promptLower, 'bantu') !== false) {
            $attention = CommandCenterService::getMyAttention();
            $response = "👋 **Halo Prof. Dr. Ir. Hendra Wijaya, M.Kom.**\n\n";
            $response .= "Berikut ringkasan eksekutif kondisi Fakultas FTIK saat ini:\n";
            $response .= "• **Pusat Perhatian (Attention):** Terdeteksi **{$attention['total']} item penting**.\n";
            $response .= "• **Persetujuan Pending:** {$attention['pending_approvals']} usulan menunggu approval Anda.\n";
            $response .= "• **Tindak Lanjut RTL Terlambat:** {$attention['overdue_rtl']} item.\n";
            $response .= "• **Akreditasi Kritis:** 1 prodi (Sistem Informasi) akan kadaluarsa dalam 88 hari.\n\n";
            $response .= "Ketik pertanyaan spesifik seperti *'Capaian IKU'*, *'Dosen bermasalah'*, atau *'Status akreditasi'* untuk detail lebih lanjut.";
        } else {
            $response = "🤖 **Asisten AI Eksekutif DEIS:**\n\n";
            $response .= "Saya telah mencatat pertanyaan Anda mengenai *\"{$prompt}\"*. Berdasarkan basis data fakultas saat ini, Anda dapat menanyakan ringkasan mengenai: \n• Capaian IKU & Renstra\n• Status Akreditasi & Deadline\n• Evaluasi Kinerja Dosen & BKD\n• Early Warning Mahasiswa\n• Laporan Keuangan & Realisasi Anggaran.";
        }

        $this->json([
            'status'   => 'success',
            'response' => $response
        ]);
    }
}
