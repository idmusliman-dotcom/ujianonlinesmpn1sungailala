<?php
declare(strict_types=1);
session_start();
header('Content-Type: application/json; charset=utf-8');

$config = [
 'host'=>'localhost',
 'db'=>'ujian_smp1',
 'user'=>'ujian_user',
 'pass'=>'GANTI_PASSWORD_DATABASE'
];
try{
 $pdo=new PDO("mysql:host={$config['host']};dbname={$config['db']};charset=utf8mb4",$config['user'],$config['pass'],[
  PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,
  PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC
 ]);
}catch(Throwable $e){http_response_code(500);echo json_encode(['ok'=>false,'message'=>'Koneksi database gagal. Periksa config api.php']);exit;}

function out($a){echo json_encode($a,JSON_UNESCAPED_UNICODE);exit;}
function needAdmin(){if(empty($_SESSION['admin']))out(['ok'=>false,'message'=>'Sesi admin tidak valid. Silakan login lagi.']);}
function body(){return json_decode(file_get_contents('php://input'),true) ?: [];}
$d=body();$action=$d['action']??'';

try{
 switch($action){
 case 'student_login':
   $nisn=trim((string)($d['nisn']??''));$pass=(string)($d['password']??'');$token=strtoupper(trim((string)($d['token']??'')));
   $s=$pdo->prepare("SELECT id,nisn,nama,kelas,password_hash,terkunci FROM siswa WHERE nisn=? LIMIT 1");$s->execute([$nisn]);$student=$s->fetch();
   if(!$student || !password_verify($pass,$student['password_hash'])) out(['ok'=>false,'message'=>'NISN atau Password salah!']);
   if((int)$student['terkunci']) out(['ok'=>false,'message'=>'Akun terkunci. Hubungi Admin/Pengawas.']);
   $t=$pdo->prepare("SELECT token,tingkat,aktif,durasi_menit FROM token_ujian WHERE token=? LIMIT 1");$t->execute([$token]);$tok=$t->fetch();
   if(!$tok || !(int)$tok['aktif']) out(['ok'=>false,'message'=>'Token tidak valid atau belum diaktifkan.']);
   $q=$pdo->prepare("SELECT id,pertanyaan,pilihan_json FROM soal WHERE token=? ORDER BY id");$q->execute([$token]);$qs=[];
   foreach($q as $r){$qs[]=['id'=>(int)$r['id'],'pertanyaan'=>$r['pertanyaan'],'pilihan'=>json_decode($r['pilihan_json'],true)];}
   if(!$qs)out(['ok'=>false,'message'=>'Soal untuk token ini belum tersedia.']);
   $_SESSION['exam']=['siswa_id'=>(int)$student['id'],'token'=>$token,'started'=>time()];
   out(['ok'=>true,'student'=>['id'=>(int)$student['id'],'nisn'=>$student['nisn'],'nama'=>$student['nama'],'kelas'=>$student['kelas']],'token'=>$token,'questions'=>$qs,'duration'=>((int)$tok['durasi_menit']*60)]);
 case 'submit_exam':
   if(empty($_SESSION['exam']))out(['ok'=>false,'message'=>'Sesi ujian tidak ditemukan. Login kembali.']);
   $ex=$_SESSION['exam'];if($ex['token']!==strtoupper((string)($d['token']??'')))out(['ok'=>false,'message'=>'Token sesi tidak cocok.']);
   $answers=$d['answers']??[];$q=$pdo->prepare("SELECT id,kunci FROM soal WHERE token=? ORDER BY id");$q->execute([$ex['token']]);$rows=$q->fetchAll();
   $correct=0;$total=count($rows);foreach($rows as $i=>$r){if(isset($answers[(string)$i]) && (int)$answers[(string)$i]===(int)$r['kunci'])$correct++;}
   $nilai=$total?round($correct/$total*100):0;
   $ins=$pdo->prepare("INSERT INTO hasil_ujian(siswa_id,token,benar,total_soal,nilai,waktu) VALUES(?,?,?,?,?,NOW())");
   $ins->execute([$ex['siswa_id'],$ex['token'],$correct,$total,$nilai]);unset($_SESSION['exam']);out(['ok'=>true,'nilai'=>$nilai,'benar'=>$correct,'total'=>$total]);
 case 'admin_login':
   $u=(string)($d['username']??'');$p=(string)($d['password']??'');$q=$pdo->prepare("SELECT id,password_hash FROM admin WHERE username=? LIMIT 1");$q->execute([$u]);$a=$q->fetch();
   if(!$a||!password_verify($p,$a['password_hash']))out(['ok'=>false,'message'=>'Username atau password admin salah!']);$_SESSION['admin']=(int)$a['id'];out(['ok'=>true]);
 case 'results':
   needAdmin();$q=$pdo->query("SELECT s.nisn,s.nama,s.kelas,h.token,h.nilai,h.benar,h.total_soal,DATE_FORMAT(h.waktu,'%d-%m-%Y %H:%i') waktu FROM hasil_ujian h JOIN siswa s ON s.id=h.siswa_id ORDER BY h.waktu DESC");out(['ok'=>true,'rows'=>$q->fetchAll()]);
 case 'students':
   needAdmin();$q=$pdo->query("SELECT nisn,nama,kelas,terkunci FROM siswa ORDER BY kelas,nama");out(['ok'=>true,'rows'=>$q->fetchAll()]);
 case 'add_student':
   needAdmin();$nisn=trim((string)($d['nisn']??''));$nama=trim((string)($d['nama']??''));$kelas=strtoupper(trim((string)($d['kelas']??'')));$pass=(string)($d['password']??'');
   if(!$nisn||!$nama||!$kelas||!$pass)out(['ok'=>false,'message'=>'Semua data siswa wajib diisi.']);
   $st=$pdo->prepare("INSERT INTO siswa(nisn,nama,kelas,password_hash) VALUES(?,?,?,?)");$st->execute([$nisn,$nama,$kelas,password_hash($pass,PASSWORD_DEFAULT)]);out(['ok'=>true]);
 case 'import_students':
   needAdmin();$kelas=strtoupper(trim((string)($d['kelas']??'')));$text=trim((string)($d['text']??''));$count=0;
   foreach(preg_split('/\R/',$text) as $line){$c=array_map('trim',explode(',',$line));if(count($c)>=3&&!$pdo->query("SELECT 1")->fetchColumn()){ } if(count($c)>=3&&$c[0]&&$c[1]&&$c[2]){try{$st=$pdo->prepare("INSERT INTO siswa(nisn,nama,kelas,password_hash) VALUES(?,?,?,?)");$st->execute([$c[0],$c[1],$kelas,password_hash($c[2],PASSWORD_DEFAULT)]);$count++;}catch(Throwable $e){}}}
   out(['ok'=>true,'count'=>$count]);
 case 'unlock_student':
   needAdmin();$q=$pdo->prepare("UPDATE siswa SET terkunci=0,pelanggaran=0 WHERE nisn=?");$q->execute([trim((string)$d['nisn'])]);out(['ok'=>true]);
 case 'delete_student':
   needAdmin();$q=$pdo->prepare("DELETE FROM siswa WHERE nisn=?");$q->execute([trim((string)$d['nisn'])]);out(['ok'=>true]);
 case 'import_questions':
   needAdmin();$token=strtoupper(trim((string)($d['token']??'')));$tingkat=strtoupper(trim((string)($d['tingkat']??'IX')));$text=trim((string)($d['text']??''));if(!$token||!$text)out(['ok'=>false,'message'=>'Token dan teks soal wajib diisi.']);
   $pdo->prepare("INSERT INTO token_ujian(token,tingkat,aktif,durasi_menit) VALUES(?,?,1,45) ON DUPLICATE KEY UPDATE tingkat=VALUES(tingkat)")->execute([$token,$tingkat]);
   $lines=preg_split('/\R/',$text);$cur=null;$tmp=[];$map=['A'=>0,'B'=>1,'C'=>2,'D'=>3];
   foreach($lines as $line){$l=trim($line);if($l==='')continue;
    if(preg_match('/^(\d+)[\.\)]\s*(.+)$/',$l,$m)){if($cur&&count($cur['pilihan'])>=2)$tmp[]=$cur;$cur=['pertanyaan'=>$m[2],'pilihan'=>[],'kunci'=>0];continue;}
    if(preg_match('/^([A-D])[\.\)]\s*(.+)$/i',$l,$m)&&$cur){$cur['pilihan'][]=$m[2];continue;}
    if(preg_match('/^KUNCI:\s*([A-D])/i',$l,$m)&&$cur){$cur['kunci']=$map[strtoupper($m[1])];continue;}
    if($cur&&count($cur['pilihan'])===0)$cur['pertanyaan'].='<br>'.htmlspecialchars($l,ENT_QUOTES,'UTF-8');
   }
   if($cur&&count($cur['pilihan'])>=2)$tmp[]=$cur;$ins=$pdo->prepare("INSERT INTO soal(token,tingkat,pertanyaan,pilihan_json,kunci) VALUES(?,?,?,?,?)");foreach($tmp as $x)$ins->execute([$token,$tingkat,$x['pertanyaan'],json_encode($x['pilihan'],JSON_UNESCAPED_UNICODE),$x['kunci']]);out(['ok'=>true,'count'=>count($tmp)]);
 case 'tokens':
   needAdmin();$q=$pdo->query("SELECT t.token,t.aktif,COUNT(s.id) jumlah FROM token_ujian t LEFT JOIN soal s ON s.token=t.token GROUP BY t.token,t.aktif ORDER BY t.token");out(['ok'=>true,'rows'=>$q->fetchAll()]);
 case 'toggle_token':
   needAdmin();$q=$pdo->prepare("UPDATE token_ujian SET aktif=NOT aktif WHERE token=?");$q->execute([strtoupper(trim((string)$d['token']))]);out(['ok'=>true]);
 default:out(['ok'=>false,'message'=>'Aksi tidak dikenal.']);
 }
}catch(Throwable $e){http_response_code(400);out(['ok'=>false,'message'=>'Operasi gagal: '.$e->getMessage()]);}
?>