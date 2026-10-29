<?php
/**
 * Konfigurasi Koneksi Database MySQL (PDO)
 * Sistem Pendukung Keputusan Penilaian Kinerja Pegawai
 */

$host = '127.0.0.1';
$dbname = 'spk_kinerja_pegawai';
$user = 'admin';
$pass = '123456';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$dbname;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    // Jika database belum dibuat, coba koneksi ke MySQL server dan buat database
    try {
        $tempPdo = new PDO("mysql:host=$host;charset=$charset", $user, $pass, $options);
        $tempPdo->exec("CREATE DATABASE IF NOT EXISTS `$dbname` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        $pdo = new PDO($dsn, $user, $pass, $options);
    } catch (PDOException $ex) {
        die("Koneksi Database Gagal: " . $ex->getMessage());
    }
}
