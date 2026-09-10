UJIAN ONLINE SMP NEGERI 1 SUNGAI LALA - PHP + MySQL
====================================================

Fitur utama:
- Login siswa dengan NISN + password + token
- Soal tersimpan di MySQL, bukan localStorage
- Hasil ujian tersimpan terpusat
- Login admin dengan password_hash()
- Tambah/import siswa
- Unlock/hapus siswa
- Import soal format Word/text
- Aktif/nonaktif token
- Rekap nilai dan download CSV
- Timer ujian 45 menit (bisa diubah pada tabel token_ujian)

CARA INSTAL DI CPANEL
1. Buat database MySQL, user database, dan berikan ALL PRIVILEGES.
2. Import schema.sql melalui phpMyAdmin.
3. Edit api.php:
   host, db, user, pass
4. Buat password admin yang aman.
   Jalankan file sementara setup_admin.php (lihat catatan di bawah), atau
   generate hash menggunakan PHP password_hash().
5. Upload index.html, api.php, .htaccess dan file logo ke public_html.
6. Pastikan nama file logo persis:
   LOGO SMPN 1 SUNGAI LALA.png
7. Buka domain Anda.

PENTING:
- Jangan menggunakan password admin default di produksi.
- Jangan menghapus validasi server.
- Aktifkan HTTPS/SSL pada domain.
- Backup database secara berkala.

SETUP ADMIN PALING MUDAH
Buat file sementara setup_admin.php dengan:
<?php
echo password_hash('PASSWORD_ADMIN_ANDA', PASSWORD_DEFAULT);
?>
Buka sekali di browser, salin hash, lalu masukkan ke schema.sql pada INSERT admin.
Setelah selesai, HAPUS setup_admin.php dari hosting.

FORMAT IMPORT SISWA
001, Ahmad Dahlan, 123
002, Siti Nurhaliza, 123

FORMAT IMPORT SOAL
1. Ibu kota Indonesia adalah...
A. Bandung
B. Jakarta
C. Surabaya
D. Medan
KUNCI: B

CATATAN PERBEDAAN DENGAN SCRIPT LAMA
Script lama menggunakan localStorage sehingga data admin hanya berada di browser/perangkat tersebut.
Versi ini memindahkan data siswa, soal, token, dan hasil ujian ke MySQL sehingga dapat dipakai bersama dari banyak HP/komputer.
