<div class="alert alert-info guidance-rule-hint"></div>
<div class="row g-3 mb-3">
    <div class="col-md-3"><label class="form-label">Jenis</label><select name="type" class="form-select guidance-type-select" required><?php foreach (['Skripsi','KKN','PKL'] as $type): ?><option value="<?= $type ?>" <?= $g['type'] === $type ? 'selected' : '' ?>><?= $type ?></option><?php endforeach; ?></select></div>
    <div class="col-md-9"><label class="form-label">Judul / Nama Kegiatan</label><input name="title" class="form-control" value="<?= htmlspecialchars($g['title'], ENT_QUOTES, 'UTF-8') ?>" required></div>
</div>
<div class="row g-3 mb-3">
    <div class="col-md-6"><label class="form-label">Mahasiswa dari OBE</label><div class="border rounded-3 p-2" style="max-height:280px;overflow:auto"><?php $renderParticipantChoices($students, 'student_ids', $g['student_ids'], 'username'); ?></div></div>
    <div class="col-md-6"><label class="form-label">Dosen Pembimbing dari OBE</label><div class="border rounded-3 p-2" style="max-height:280px;overflow:auto"><?php $renderParticipantChoices($lecturers, 'lecturer_ids', $g['lecturer_ids'], 'nidn'); ?></div><small class="text-muted">Urutan pilihan tersimpan sebagai Pembimbing 1 dan 2.</small></div>
</div>
<div class="row g-3 mb-3">
    <div class="col-md-3"><label class="form-label">Progres (%)</label><input type="number" name="progress_percentage" min="0" max="100" class="form-control" value="<?= (float)$g['progress_percentage'] ?>" required></div>
    <div class="col-md-3"><label class="form-label">Status</label><select name="status" class="form-select"><?php foreach (['Aktif','Selesai','Terlambat','Bermasalah'] as $status): ?><option value="<?= $status ?>" <?= $g['status'] === $status ? 'selected' : '' ?>><?= $status ?></option><?php endforeach; ?></select></div>
    <div class="col-md-6"><label class="form-label">Tanggal Bimbingan Terakhir</label><input type="date" name="last_guidance_date" class="form-control" value="<?= htmlspecialchars((string)$g['last_guidance_date']) ?>"></div>
</div>
<div><label class="form-label">Catatan</label><textarea name="notes" class="form-control" rows="3"><?= htmlspecialchars((string)$g['notes'], ENT_QUOTES, 'UTF-8') ?></textarea></div>
