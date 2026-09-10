CREATE DATABASE IF NOT EXISTS ujian_smp1 CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE ujian_smp1;

CREATE TABLE admin (
 id INT AUTO_INCREMENT PRIMARY KEY,
 username VARCHAR(50) NOT NULL UNIQUE,
 password_hash VARCHAR(255) NOT NULL
);

CREATE TABLE siswa (
 id INT AUTO_INCREMENT PRIMARY KEY,
 nisn VARCHAR(30) NOT NULL UNIQUE,
 nama VARCHAR(150) NOT NULL,
 kelas VARCHAR(30) NOT NULL,
 password_hash VARCHAR(255) NOT NULL,
 pelanggaran INT NOT NULL DEFAULT 0,
 terkunci TINYINT(1) NOT NULL DEFAULT 0,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE token_ujian (
 token VARCHAR(50) PRIMARY KEY,
 tingkat VARCHAR(10) NOT NULL,
 aktif TINYINT(1) NOT NULL DEFAULT 1,
 durasi_menit INT NOT NULL DEFAULT 45
);

CREATE TABLE soal (
 id INT AUTO_INCREMENT PRIMARY KEY,
 token VARCHAR(50) NOT NULL,
 tingkat VARCHAR(10) NOT NULL,
 pertanyaan TEXT NOT NULL,
 pilihan_json JSON NOT NULL,
 kunci TINYINT NOT NULL,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 INDEX(token),
 CONSTRAINT fk_soal_token FOREIGN KEY(token) REFERENCES token_ujian(token) ON DELETE CASCADE
);

CREATE TABLE hasil_ujian (
 id BIGINT AUTO_INCREMENT PRIMARY KEY,
 siswa_id INT NOT NULL,
 token VARCHAR(50) NOT NULL,
 benar INT NOT NULL,
 total_soal INT NOT NULL,
 nilai DECIMAL(5,2) NOT NULL,
 waktu DATETIME NOT NULL,
 INDEX(siswa_id),
 INDEX(token),
 CONSTRAINT fk_hasil_siswa FOREIGN KEY(siswa_id) REFERENCES siswa(id) ON DELETE CASCADE
);

-- Ganti hash berikut dengan hasil password_hash() milik admin Anda.
-- Contoh: buat sementara melalui PHP: echo password_hash('PasswordBaruAnda', PASSWORD_DEFAULT);
INSERT INTO admin(username,password_hash)
VALUES ('admin','admin152');
