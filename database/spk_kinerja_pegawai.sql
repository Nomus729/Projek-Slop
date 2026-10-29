-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Win64 (AMD64)
--
-- Host: localhost    Database: spk_kinerja_pegawai
-- ------------------------------------------------------
-- Server version	10.4.32-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Current Database: `spk_kinerja_pegawai`
--

CREATE DATABASE /*!32312 IF NOT EXISTS*/ `spk_kinerja_pegawai` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci */;

USE `spk_kinerja_pegawai`;

--
-- Table structure for table `hasil_spk`
--

DROP TABLE IF EXISTS `hasil_spk`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `hasil_spk` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `penilaian_id` int(11) NOT NULL,
  `nilai_akhir_v` decimal(5,4) NOT NULL,
  `ranking` int(11) NOT NULL,
  `rekomendasi_pelatihan` text NOT NULL,
  `calculated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fk_hasil_penilaian` (`penilaian_id`),
  CONSTRAINT `fk_hasil_penilaian` FOREIGN KEY (`penilaian_id`) REFERENCES `penilaian` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=121 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `hasil_spk`
--

LOCK TABLES `hasil_spk` WRITE;
/*!40000 ALTER TABLE `hasil_spk` DISABLE KEYS */;
INSERT INTO `hasil_spk` VALUES (113,1,0.9891,1,'Dipertimbangkan untuk Tech Lead / Principal / Promosi Jabatan','2026-10-29 02:07:08'),(114,6,0.9610,2,'Dipertimbangkan untuk Tech Lead / Principal / Promosi Jabatan','2026-10-29 02:07:08'),(115,3,0.9156,3,'Dipertimbangkan untuk Tech Lead / Principal / Promosi Jabatan','2026-10-29 02:07:08'),(116,8,0.8772,4,'Pelatihan Komunikasi Efektif & Kolaborasi Tim Developer','2026-10-29 02:07:08'),(117,4,0.8208,5,'Pelatihan Manajemen Waktu & Produktivitas Tim Agile','2026-10-29 02:07:08'),(118,2,0.8204,6,'Pelatihan Software Engineering & Manajemen Delivery Proyek IT','2026-10-29 02:07:08'),(119,7,0.7662,7,'Workshop Code Quality, IT Security & DevOps CI/CD','2026-10-29 02:07:08'),(120,5,0.6557,8,'Program Pembinaan Kinerja Intensif (Performance Improvement Plan / PIP)','2026-10-29 02:07:08');
/*!40000 ALTER TABLE `hasil_spk` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `kriteria`
--

DROP TABLE IF EXISTS `kriteria`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kriteria` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `kode` varchar(10) NOT NULL,
  `nama_kriteria` varchar(100) NOT NULL,
  `bobot` decimal(5,2) NOT NULL,
  `jenis` enum('benefit','cost') NOT NULL DEFAULT 'benefit',
  `keterangan` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `kode` (`kode`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `kriteria`
--

LOCK TABLES `kriteria` WRITE;
/*!40000 ALTER TABLE `kriteria` DISABLE KEYS */;
INSERT INTO `kriteria` VALUES (1,'C1','Presensi & Standup Agile',0.20,'benefit','Tingkat kehadiran, ketepatan waktu daily standup sprint & komitmen jam kerja'),(2,'C2','KPI & Delivery Proyek',0.35,'benefit','Pencapaian sprint backlog, on-time delivery deployment, dan kepuasan klien/PO'),(3,'C3','Kualitas Kode & SOP IT',0.25,'benefit','Clean code standard, code review pass rate, zero bug critical & kepatuhan git workflow'),(4,'C4','Kolaborasi & Komunikasi Tim',0.20,'benefit','Kerjasama lintas divisi tech (frontend, backend, QA, UI/UX), proaktif dan knowledge sharing');
/*!40000 ALTER TABLE `kriteria` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pegawai`
--

DROP TABLE IF EXISTS `pegawai`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pegawai` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nip` varchar(20) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `departemen` varchar(50) NOT NULL,
  `jabatan` varchar(50) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `nip` (`nip`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pegawai`
--

LOCK TABLES `pegawai` WRITE;
/*!40000 ALTER TABLE `pegawai` DISABLE KEYS */;
INSERT INTO `pegawai` VALUES (1,'IB-2024001','Budi Santoso','Software Engineering','Senior Fullstack Developer','2026-10-28 04:22:58'),(2,'IB-2024002','Dewi Lestari','Digital Marketing & SEO','Senior SEO & Growth Specialist','2026-10-28 04:22:58'),(3,'IB-2024003','Ahmad Fauzi','IT Infrastructure & DevOps','DevOps & Cloud Engineer','2026-10-28 04:22:58'),(4,'IB-2024004','Rina Wulandari','Product & UI/UX Design','Lead UI/UX Designer','2026-10-28 04:22:58'),(5,'IB-2024005','Hendro Wicaksono','Software Engineering','Backend Engineer','2026-10-28 04:22:58'),(6,'IB-2024006','Siti Nurhaliza','Quality Assurance & Testing','QA Automation Engineer','2026-10-28 04:22:58'),(7,'IB-2024007','Dimas Pratama','IT Infrastructure & DevOps','Cyber Security Specialist','2026-10-28 04:22:58'),(8,'IB-2024008','Maya Anggraini','Digital Marketing & SEO','Digital Performance Marketer','2026-10-28 04:22:58');
/*!40000 ALTER TABLE `pegawai` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `penilaian`
--

DROP TABLE IF EXISTS `penilaian`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `penilaian` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pegawai_id` int(11) NOT NULL,
  `periode` varchar(20) NOT NULL,
  `c1` decimal(5,2) NOT NULL,
  `c2` decimal(5,2) NOT NULL,
  `c3` decimal(5,2) NOT NULL,
  `c4` decimal(5,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fk_penilaian_pegawai` (`pegawai_id`),
  CONSTRAINT `fk_penilaian_pegawai` FOREIGN KEY (`pegawai_id`) REFERENCES `pegawai` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `penilaian`
--

LOCK TABLES `penilaian` WRITE;
/*!40000 ALTER TABLE `penilaian` DISABLE KEYS */;
INSERT INTO `penilaian` VALUES (1,1,'2026-Semester-2',92.00,95.00,88.00,90.00,'2026-10-28 04:22:58'),(2,2,'2026-Semester-2',85.00,65.00,82.00,78.00,'2026-10-28 04:22:58'),(3,3,'2026-Semester-2',88.00,90.00,75.00,85.00,'2026-10-28 04:22:58'),(4,4,'2026-Semester-2',65.00,82.00,80.00,72.00,'2026-10-28 04:22:58'),(5,5,'2026-Semester-2',62.00,58.00,64.00,60.00,'2026-10-28 04:22:58'),(6,6,'2026-Semester-2',90.00,88.00,92.00,86.00,'2026-10-28 04:22:58'),(7,7,'2026-Semester-2',78.00,74.00,62.00,70.00,'2026-10-28 04:22:58'),(8,8,'2026-Semester-2',82.00,86.00,85.00,68.00,'2026-10-28 04:22:58');
/*!40000 ALTER TABLE `penilaian` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `nama_lengkap` varchar(100) NOT NULL,
  `role` enum('admin','hrd') NOT NULL DEFAULT 'hrd',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'admin','$2y$10$wO39LqLw3M28Z0h5jF23/eqB3sVbK9dOqF8qPq4b8uJ5/1G2jO9O.','Administrator HRD','admin','2026-10-28 04:22:58'),(2,'hrd_penilai','$2y$10$wO39LqLw3M28Z0h5jF23/eqB3sVbK9dOqF8qPq4b8uJ5/1G2jO9O.','Siti Rahmawati, S.Kom (HR & People Ops)','hrd','2026-10-28 04:22:58');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-10-29  9:09:27
