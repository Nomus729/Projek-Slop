<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/helper.php';
requireAuth();

$action = $_POST['action'] ?? $_GET['action'] ?? '';

if ($action === 'create') {
    $nip = trim($_POST['nip'] ?? '');
    $nama = trim($_POST['nama'] ?? '');
    $departemen = trim($_POST['departemen'] ?? '');
    $jabatan = trim($_POST['jabatan'] ?? '');

    if (empty($nip) || empty($nama) || empty($departemen) || empty($jabatan)) {
        setFlash('danger', 'Semua kolom pegawai wajib diisi.');
        header('Location: ../pegawai.php');
        exit;
    }

    // Cek NIP duplikat
    $check = $pdo->prepare("SELECT id FROM pegawai WHERE nip = :nip LIMIT 1");
    $check->execute([':nip' => $nip]);
    if ($check->fetch()) {
        setFlash('danger', "NIP '$nip' sudah terdaftar dalam sistem.");
        header('Location: ../pegawai.php');
        exit;
    }

    try {
        $stmt = $pdo->prepare("INSERT INTO pegawai (nip, nama, departemen, jabatan) VALUES (:nip, :nama, :departemen, :jabatan)");
        $stmt->execute([
            ':nip' => $nip,
            ':nama' => $nama,
            ':departemen' => $departemen,
            ':jabatan' => $jabatan
        ]);
        setFlash('success', "Pegawai '$nama' berhasil ditambahkan.");
    } catch (PDOException $e) {
        setFlash('danger', 'Gagal menambahkan pegawai: ' . $e->getMessage());
    }

    header('Location: ../pegawai.php');
    exit;

} elseif ($action === 'update') {
    $id = (int)($_POST['id'] ?? 0);
    $nip = trim($_POST['nip'] ?? '');
    $nama = trim($_POST['nama'] ?? '');
    $departemen = trim($_POST['departemen'] ?? '');
    $jabatan = trim($_POST['jabatan'] ?? '');

    if ($id <= 0 || empty($nip) || empty($nama) || empty($departemen) || empty($jabatan)) {
        setFlash('danger', 'Data pegawai tidak valid atau kolom kosong.');
        header('Location: ../pegawai.php');
        exit;
    }

    // Cek NIP duplikat pada id lain
    $check = $pdo->prepare("SELECT id FROM pegawai WHERE nip = :nip AND id != :id LIMIT 1");
    $check->execute([':nip' => $nip, ':id' => $id]);
    if ($check->fetch()) {
        setFlash('danger', "NIP '$nip' sudah digunakan oleh pegawai lain.");
        header('Location: ../pegawai.php');
        exit;
    }

    try {
        $stmt = $pdo->prepare("UPDATE pegawai SET nip = :nip, nama = :nama, departemen = :departemen, jabatan = :jabatan WHERE id = :id");
        $stmt->execute([
            ':nip' => $nip,
            ':nama' => $nama,
            ':departemen' => $departemen,
            ':jabatan' => $jabatan,
            ':id' => $id
        ]);
        setFlash('success', "Data pegawai '$nama' berhasil diperbarui.");
    } catch (PDOException $e) {
        setFlash('danger', 'Gagal memperbarui pegawai: ' . $e->getMessage());
    }

    header('Location: ../pegawai.php');
    exit;

} elseif ($action === 'delete') {
    $id = (int)($_GET['id'] ?? 0);
    if ($id <= 0) {
        setFlash('danger', 'ID pegawai tidak valid.');
        header('Location: ../pegawai.php');
        exit;
    }

    try {
        // Ambil nama pegawai
        $stmtInfo = $pdo->prepare("SELECT nama FROM pegawai WHERE id = :id");
        $stmtInfo->execute([':id' => $id]);
        $pegawai = $stmtInfo->fetch();

        $stmt = $pdo->prepare("DELETE FROM pegawai WHERE id = :id");
        $stmt->execute([':id' => $id]);
        
        // Recalculate SPK for all active periods so rankings remain continuous
        $pPeriods = $pdo->query("SELECT DISTINCT periode FROM penilaian")->fetchAll(PDO::FETCH_COLUMN);
        foreach ($pPeriods as $per) {
            executeSpkCalculation($pdo, $per);
        }

        $nama = $pegawai['nama'] ?? 'Pegawai';
        setFlash('success', "Pegawai '$nama' beserta data penilaian terkait berhasil dihapus, perankingan SPK telah disinkronkan.");
    } catch (PDOException $e) {
        setFlash('danger', 'Gagal menghapus pegawai: ' . $e->getMessage());
    }

    header('Location: ../pegawai.php');
    exit;
} else {
    header('Location: ../pegawai.php');
    exit;
}
