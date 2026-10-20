<?php
require __DIR__ . '/bootstrap.php';

use App\Models\Guidance;
use App\Services\Obe\LecturerRepository;
use App\Services\Obe\ObeReadConnection;
use App\Services\Obe\StudentRepository;

$failures=[]; $checks=0;
$assert=static function(bool $ok,string $message) use (&$failures,&$checks):void{$checks++;if(!$ok)$failures[]=$message;};
$obe=new ObeReadConnection();
$before=$obe->fetchOne('SELECT COUNT(*) total,COALESCE(SUM(id),0) id_sum FROM users');
$students=(new StudentRepository($obe))->all();
$lecturers=(new LecturerRepository($obe))->all();
$model=new Guidance();
$caseId=$model->createObeCase(['type'=>'KKN','title'=>'TEST TRACKING SESSION','progress'=>0,'status'=>'Aktif','last_date'=>null,'notes'=>null],[(int)$students[0]['obe_id'],(int)$students[1]['obe_id']],[(int)$lecturers[0]['obe_id']]);
try {
    $first=$model->createSession($caseId,['guidance_date'=>'2026-10-19','topic'=>'Pertemuan pertama','notes'=>null,'progress_after'=>10],[(int)$students[0]['obe_id'],(int)$students[1]['obe_id']]);
    $second=$model->createSession($caseId,['guidance_date'=>'2026-10-20','topic'=>'Pertemuan kedua','notes'=>null,'progress_after'=>25],[(int)$students[0]['obe_id']]);
    $sessions=$model->sessions($caseId);
    $counts=[(int)$students[0]['obe_id']=>0,(int)$students[1]['obe_id']=>0];
    foreach($sessions as $session) foreach($session['student_ids'] as $studentId) $counts[$studentId]++;
    $assert(count($sessions)===2,'Dua sesi bimbingan tidak tersimpan.');
    $assert($counts[(int)$students[0]['obe_id']]===2,'Jumlah bimbingan mahasiswa pertama harus dua.');
    $assert($counts[(int)$students[1]['obe_id']]===1,'Jumlah bimbingan mahasiswa kedua harus satu.');
    $assert((float)$model->findObeCase($caseId)['progress_percentage']===25.0,'Progres aktivitas belum mengikuti sesi terbaru.');
    $model->deleteSession($second,$caseId);
    $assert(count($model->sessions($caseId))===1,'Penghapusan sesi gagal.');
} finally {
    $model->deleteObeCase($caseId);
}
$after=$obe->fetchOne('SELECT COUNT(*) total,COALESCE(SUM(id),0) id_sum FROM users');
$assert($before===$after,'Tracking sesi mengubah data OBE.');
$routes=file_get_contents(dirname(__DIR__).'/public/index.php');
$view=file_get_contents(dirname(__DIR__).'/app/views/academic/guidance_detail.php');
$assert(str_contains($routes,"'/academic/guidance/detail'"),'Route detail belum tersedia.');
$assert(str_contains($view,'Jumlah Bimbingan per Mahasiswa'),'Rekap mahasiswa belum tersedia.');
if($failures){fwrite(STDERR,"FAILED ({$checks} checks)\n- ".implode("\n- ",$failures)."\n");exit(1);}
echo "OK ({$checks} checks) - guidance sessions and per-student attendance are tracked.\n";
