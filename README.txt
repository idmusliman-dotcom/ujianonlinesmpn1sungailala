# Aplikasi Ujian Online SMP 1 (Static JSON Version)

Aplikasi ini adalah versi statis dari skrip Ujian Online SMP 1. Seluruh fungsi backend PHP dan MySQL telah digantikan oleh skrip JavaScript dan file `data.json`, sehingga **dapat dipublikasikan secara langsung melalui GitHub Pages**.

## Format File
1. `index.html` - Tampilan UI aplikasi (Form login, lembar ujian, navigasi soal, dan perhitungan nilai).
2. `data.json` - Basis data user (login) dan bank soal ujian.

## Akun Login bawaan (Sesuai `data.json`)
* **Admin / Pengawas**: Username `admin` | Password `password123`
* **Siswa 1**: Username `siswa1` | Password `123`
* **Siswa 2**: Username `siswa2` | Password `123`

## Cara Dipublikasikan di GitHub Pages:
1. Buat Repository baru di GitHub.
2. Upload file `index.html` dan `data.json` ke repository tersebut.
3. Buka menu **Settings** > **Pages** pada Repository Anda.
4. Pada bagian **Branch**, pilih `main` (atau `master`) dan folder `/root`, lalu klik **Save**.
5. Tunggu 1-2 menit, link situs publikasi Ujian Online Anda akan siap digunakan!
