-- Skema Database SPK Penilaian Kinerja Pegawai & Rekomendasi Pelatihan
CREATE DATABASE IF NOT EXISTS `spk_kinerja_pegawai` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `spk_kinerja_pegawai`;

-- 1. Tabel users
CREATE TABLE IF NOT EXISTS `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `username` VARCHAR(50) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `nama_lengkap` VARCHAR(100) NOT NULL,
  `role` ENUM('admin', 'hrd') NOT NULL DEFAULT 'hrd',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2. Tabel pegawai
CREATE TABLE IF NOT EXISTS `pegawai` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `nip` VARCHAR(20) NOT NULL UNIQUE,
  `nama` VARCHAR(100) NOT NULL,
  `departemen` VARCHAR(50) NOT NULL,
  `jabatan` VARCHAR(50) NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3. Tabel kriteria
CREATE TABLE IF NOT EXISTS `kriteria` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `kode` VARCHAR(10) NOT NULL UNIQUE,
  `nama_kriteria` VARCHAR(100) NOT NULL,
  `bobot` DECIMAL(5,2) NOT NULL,
  `jenis` ENUM('benefit', 'cost') NOT NULL DEFAULT 'benefit',
  `keterangan` VARCHAR(255) NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 4. Tabel penilaian
CREATE TABLE IF NOT EXISTS `penilaian` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `pegawai_id` INT NOT NULL,
  `periode` VARCHAR(20) NOT NULL,
  `c1` DECIMAL(5,2) NOT NULL,
  `c2` DECIMAL(5,2) NOT NULL,
  `c3` DECIMAL(5,2) NOT NULL,
  `c4` DECIMAL(5,2) NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT `fk_penilaian_pegawai` FOREIGN KEY (`pegawai_id`) REFERENCES `pegawai` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 5. Tabel hasil_spk
CREATE TABLE IF NOT EXISTS `hasil_spk` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `penilaian_id` INT NOT NULL,
  `nilai_akhir_v` DECIMAL(5,4) NOT NULL,
  `ranking` INT NOT NULL,
  `rekomendasi_pelatihan` TEXT NOT NULL,
  `calculated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT `fk_hasil_penilaian` FOREIGN KEY (`penilaian_id`) REFERENCES `penilaian` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Seed Data Users (password default: 'admin123' & 'hrd123')
-- Seed Data Users (password default: 'admin123' & 'hrd123')
INSERT INTO `users` (`id`, `username`, `password`, `nama_lengkap`, `role`) VALUES
(1, 'admin', '$2y$10$wO39LqLw3M28Z0h5jF23/eqB3sVbK9dOqF8qPq4b8uJ5/1G2jO9O.', 'Administrator HRD', 'admin'),
(2, 'hrd_penilai', '$2y$10$wO39LqLw3M28Z0h5jF23/eqB3sVbK9dOqF8qPq4b8uJ5/1G2jO9O.', 'Siti Rahmawati, S.Kom (HR & People Ops)', 'hrd')
ON DUPLICATE KEY UPDATE `username`=`username`;

-- Seed Kriteria Default IT (C1: 0.20, C2: 0.35, C3: 0.25, C4: 0.20)
INSERT INTO `kriteria` (`id`, `kode`, `nama_kriteria`, `bobot`, `jenis`, `keterangan`) VALUES
(1, 'C1', 'Kedisiplinan & Presensi Kerja', 0.20, 'benefit', 'Ketepatan waktu kerja & kehadiran agile standup harian'),
(2, 'C2', 'Pencapaian Target KPI & Delivery', 0.35, 'benefit', 'Penyelesaian sprint task & delivery fitur aplikasi'),
(3, 'C3', 'Kualitas Kode & Standar SOP IT', 0.25, 'benefit', 'Clean code, QA testing pass-rate, & SOP deployment'),
(4, 'C4', 'Kolaborasi Tim & Komunikasi', 0.20, 'benefit', 'Kerjasama tim agile, code review, & komunikasi aktif')
ON DUPLICATE KEY UPDATE `kode`=`kode`;

-- Seed Master Pegawai IT (PT Indobuzz Republik Digital)
INSERT INTO `pegawai` (`id`, `nip`, `nama`, `departemen`, `jabatan`) VALUES
(1, 'IB-2024001', 'Budi Santoso', 'Software Engineering', 'Senior Fullstack Developer'),
(2, 'IB-2024002', 'Dewi Lestari', 'Digital Marketing & SEO', 'Senior SEO & Growth Specialist'),
(3, 'IB-2024003', 'Ahmad Fauzi', 'IT Infrastructure & DevOps', 'DevOps & Cloud Engineer'),
(4, 'IB-2024004', 'Rina Wulandari', 'Product & UI/UX Design', 'Lead UI/UX Designer'),
(5, 'IB-2024005', 'Hendro Wicaksono', 'Software Engineering', 'Backend Engineer'),
(6, 'IB-2024006', 'Siti Nurhaliza', 'Quality Assurance & Testing', 'QA Automation Engineer'),
(7, 'IB-2024007', 'Dimas Pratama', 'IT Infrastructure & DevOps', 'Cyber Security Specialist'),
(8, 'IB-2024008', 'Maya Anggraini', 'Digital Marketing & SEO', 'Digital Performance Marketer')
ON DUPLICATE KEY UPDATE `nip`=`nip`;

-- Seed Penilaian Awal (Periode 2026-Semester-2)
INSERT INTO `penilaian` (`id`, `pegawai_id`, `periode`, `c1`, `c2`, `c3`, `c4`) VALUES
(1, 1, '2026-Semester-2', 92.00, 95.00, 88.00, 90.00),
(2, 2, '2026-Semester-2', 85.00, 65.00, 82.00, 78.00),
(3, 3, '2026-Semester-2', 88.00, 90.00, 75.00, 85.00),
(4, 4, '2026-Semester-2', 65.00, 82.00, 80.00, 72.00),
(5, 5, '2026-Semester-2', 62.00, 58.00, 64.00, 60.00),
(6, 6, '2026-Semester-2', 90.00, 88.00, 92.00, 86.00),
(7, 7, '2026-Semester-2', 78.00, 74.00, 62.00, 70.00),
(8, 8, '2026-Semester-2', 82.00, 86.00, 85.00, 68.00)
ON DUPLICATE KEY UPDATE `id`=`id`;
