<?php
use App\Helpers\CsrfHelper;
use App\Helpers\FormatHelper;

$renderParticipantChoices = static function (array $items, string $name, array $selected, string $identityKey): void {
    foreach ($items as $item):
        $id = (int)$item['obe_id']; ?>
        <label class="d-flex gap-2 align-items-start border rounded-3 p-2 mb-2 participant-choice">
            <input class="form-check-input mt-1" type="checkbox" name="<?= $name ?>[]" value="<?= $id ?>" <?= in_array($id, $selected, true) ? 'checked' : '' ?>>
            <span><strong><?= htmlspecialchars($item['name'], ENT_QUOTES, 'UTF-8') ?></strong><br><small class="text-muted"><?= htmlspecialchars((string)($item[$identityKey] ?: $item['username']), ENT_QUOTES, 'UTF-8') ?> · <?= htmlspecialchars((string)($item['study_program_name'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></small></span>
        </label>
    <?php endforeach;
};
?>

<div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
    <div>
        <h3 class="fw-bold mb-1"><i class="fa-solid fa-users-rectangle text-primary me-2"></i>Bimbingan Skripsi, KKN & PKL</h3>
        <div class="text-muted small">Mahasiswa dan dosen dibaca langsung dari OBE. Data kegiatan dan progres disimpan di DEIS.</div>
    </div>
    <button type="button" class="btn btn-primary guidance-add-btn" data-bs-toggle="modal" data-bs-target="#modalCreateGuidance"><i class="fa-solid fa-plus me-1"></i>Tambah Bimbingan</button>
</div>

<?php if ($obeError): ?><div class="alert alert-danger">Data OBE tidak dapat dibaca: <?= htmlspecialchars($obeError, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>

<div class="card card-lg shadow-sm rounded-4">
    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
        <h5 class="card-title mb-0">Daftar Bimbingan</h5>
        <select class="form-select form-select-sm" style="width:auto" onchange="location.href='<?= $baseUrl ?>/academic/guidance?type='+this.value">
            <option value="">Semua Jenis</option>
            <?php foreach (['Skripsi','KKN','PKL'] as $type): ?><option value="<?= $type ?>" <?= $filterType === $type ? 'selected' : '' ?>><?= $type ?></option><?php endforeach; ?>
        </select>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead><tr><th class="ps-4">Jenis & Topik</th><th>Mahasiswa OBE</th><th>Dosen Pembimbing OBE</th><th>Progres</th><th>Status</th><th class="text-end pe-4">Aksi</th></tr></thead>
                <tbody>
                <?php if (!$guidances): ?><tr><td colspan="6" class="text-center text-muted py-5">Belum ada data bimbingan.</td></tr><?php endif; ?>
                <?php foreach ($guidances as $g): ?>
                    <tr class="<?= in_array($g['status'], ['Terlambat','Bermasalah'], true) ? 'table-danger' : '' ?>">
                        <td class="ps-4"><span class="badge bg-primary-subtle text-primary mb-1"><?= $g['type'] ?></span><div class="fw-semibold"><?= htmlspecialchars($g['title'], ENT_QUOTES, 'UTF-8') ?></div><small class="text-muted"><?= $g['last_guidance_date'] ?: 'Belum ada tanggal bimbingan' ?></small></td>
                        <td><?php foreach ($g['students'] as $student): ?><div class="mb-1"><strong><?= htmlspecialchars($student['name'], ENT_QUOTES, 'UTF-8') ?></strong><br><small class="text-muted"><?= htmlspecialchars($student['username'], ENT_QUOTES, 'UTF-8') ?> · <?= htmlspecialchars((string)$student['study_program_name'], ENT_QUOTES, 'UTF-8') ?></small></div><?php endforeach; ?></td>
                        <td><?php foreach ($g['lecturers'] as $i => $lecturer): ?><div class="mb-1"><span class="badge bg-light text-dark border me-1"><?= $i + 1 ?></span><?= htmlspecialchars($lecturer['name'], ENT_QUOTES, 'UTF-8') ?></div><?php endforeach; ?></td>
                        <td><strong><?= number_format((float)$g['progress_percentage'], 0) ?>%</strong><div class="progress mt-1" style="height:5px"><div class="progress-bar" style="width:<?= min(100, (float)$g['progress_percentage']) ?>%"></div></div></td>
                        <td><?= FormatHelper::statusBadge($g['status']) ?></td>
                        <td class="text-end pe-4 text-nowrap">
                            <a href="<?= $baseUrl ?>/academic/guidance/detail?id=<?= $g['id'] ?>" class="btn btn-sm btn-outline-info" title="Detail dan riwayat bimbingan"><i class="fa-solid fa-eye"></i> Detail</a>
                            <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modalEditGuidance<?= $g['id'] ?>"><i class="fa-solid fa-pen"></i></button>
                            <form action="<?= $baseUrl ?>/academic/guidance/delete" method="POST" class="d-inline" onsubmit="return confirm('Hapus data bimbingan ini?')"><?= CsrfHelper::tokenField() ?><input type="hidden" name="guidance_id" value="<?= $g['id'] ?>"><button class="btn btn-sm btn-outline-danger"><i class="fa-solid fa-trash"></i></button></form>
                        </td>
                    </tr>

                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php foreach ($guidances as $g): ?>
<div class="modal fade" id="modalEditGuidance<?= $g['id'] ?>" tabindex="-1"><div class="modal-dialog modal-xl modal-dialog-scrollable"><div class="modal-content"><form action="<?= $baseUrl ?>/academic/guidance/update" method="POST">
    <?= CsrfHelper::tokenField() ?><input type="hidden" name="guidance_id" value="<?= $g['id'] ?>">
    <div class="modal-header"><h5 class="modal-title">Edit Bimbingan</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <div class="modal-body"><?php include __DIR__ . '/guidance_form_fields.php'; ?></div>
    <div class="modal-footer"><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button><button type="submit" class="btn btn-primary">Simpan Perubahan</button></div>
</form></div></div></div>
<?php endforeach; ?>

<div class="modal fade" id="modalCreateGuidance" tabindex="-1"><div class="modal-dialog modal-xl modal-dialog-scrollable"><div class="modal-content"><form action="<?= $baseUrl ?>/academic/guidance/create" method="POST">
    <?= CsrfHelper::tokenField() ?>
    <div class="modal-header"><h5 class="modal-title">Tambah Bimbingan dari Data OBE</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <div class="modal-body">
        <?php $g = ['type'=>'Skripsi','title'=>'','progress_percentage'=>0,'status'=>'Aktif','last_guidance_date'=>'','notes'=>'','student_ids'=>[],'lecturer_ids'=>[]]; include __DIR__ . '/guidance_form_fields.php'; ?>
        <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top guidance-create-actions">
            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
            <button type="submit" class="btn btn-primary guidance-save-btn"><i class="fa-solid fa-floppy-disk me-1"></i>Simpan</button>
        </div>
    </div>
</form></div></div></div>

<script>
document.querySelectorAll('.guidance-type-select').forEach(select => {
    const updateHint = () => {
        const form = select.closest('form');
        const hint = form.querySelector('.guidance-rule-hint');
        hint.textContent = select.value === 'Skripsi'
            ? 'Skripsi: pilih tepat 1 mahasiswa dan tepat 2 dosen pembimbing.'
            : `${select.value}: pilih minimal 1 mahasiswa (boleh kelompok) dan 1–2 dosen pembimbing.`;
    };
    select.addEventListener('change', updateHint);
    updateHint();
});
</script>

<style>
.guidance-add-btn {
    display: inline-flex !important;
    align-items: center;
    justify-content: center;
    gap: 4px;
    background: var(--primary) !important;
    border-color: var(--primary) !important;
    color: #fff !important;
    opacity: 1 !important;
    visibility: visible !important;
}

.guidance-save-btn {
    display: inline-flex !important;
    align-items: center;
    background: var(--primary) !important;
    border-color: var(--primary) !important;
    color: #fff !important;
    visibility: visible !important;
}
</style>
