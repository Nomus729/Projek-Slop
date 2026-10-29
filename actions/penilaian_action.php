<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/helper.php';
requireAuth();

$action = $_POST['action'] ?? $_GET['action'] ?? '';

if ($action === 'create') {
    $pegawai_id = (int)($_POST['pegawai_id'] ?? 0);
    $periode = trim($_POST['periode'] ?? '2026-Semester-2');
    $c1 = (float)($_POST['c1'] ?? 0);
    $c2 = (float)($_POST['c2'] ?? 0);
    $c3 = (float)($_POST['c3'] ?? 0);
    $c4 = (float)($_POST['c4'] ?? 0);

    if ($pegawai_id <= 0 || empty($periode)) {
        setFlash('danger', 'Pegawai dan periode penilaian wajib dipilih.');
        header('Location: ../penilaian.php');
        exit;
    }

    // Validasi rentang angka 1 - 100
    if ($c1 < 1 || $c1 > 100 || $c2 < 1 || $c2 > 100 || $c3 < 1 || $c3 > 100 || $c4 < 1 || $c4 > 100) {
        setFlash('danger', 'Nilai seluruh kriteria (C1 - C4) harus berada dalam rentang angka 1 hingga 100.');
        header('Location: ../penilaian.php');
        exit;
    }

    // Cek apakah pegawai sudah dinilai pada periode yang sama
    $check = $pdo->prepare("SELECT id FROM penilaian WHERE pegawai_id = :pid AND periode = :periode LIMIT 1");
    $check->execute([':pid' => $pegawai_id, ':periode' => $periode]);
    if ($check->fetch()) {
        setFlash('warning', "Pegawai ini sudah memiliki penilaian pada periode '$periode'. Silakan gunakan tombol edit untuk mengubah nilai.");
        header('Location: ../penilaian.php');
        exit;
    }

    try {
        $stmt = $pdo->prepare("
            INSERT INTO penilaian (pegawai_id, periode, c1, c2, c3, c4) 
            VALUES (:pid, :periode, :c1, :c2, :c3, :c4)
        ");
        $stmt->execute([
            ':pid' => $pegawai_id,
            ':periode' => $periode,
            ':c1' => $c1,
            ':c2' => $c2,
            ':c3' => $c3,
            ':c4' => $c4
        ]);

        // Hitung ulang SPK otomatis untuk periode ini
        executeSpkCalculation($pdo, $periode);

        setFlash('success', 'Penilaian pegawai berhasil disimpan dan komputasi SPK diperbarui otomatis.');
    } catch (PDOException $e) {
        setFlash('danger', 'Gagal menyimpan penilaian: ' . $e->getMessage());
    }

    header('Location: ../penilaian.php');
    exit;

} elseif ($action === 'update') {
    $id = (int)($_POST['id'] ?? 0);
    $periode = trim($_POST['periode'] ?? '2026-Semester-2');
    $c1 = (float)($_POST['c1'] ?? 0);
    $c2 = (float)($_POST['c2'] ?? 0);
    $c3 = (float)($_POST['c3'] ?? 0);
    $c4 = (float)($_POST['c4'] ?? 0);

    if ($id <= 0) {
        setFlash('danger', 'ID Penilaian tidak valid.');
        header('Location: ../penilaian.php');
        exit;
    }

    // Validasi rentang angka 1 - 100
    if ($c1 < 1 || $c1 > 100 || $c2 < 1 || $c2 > 100 || $c3 < 1 || $c3 > 100 || $c4 < 1 || $c4 > 100) {
        setFlash('danger', 'Nilai seluruh kriteria (C1 - C4) harus berada dalam rentang angka 1 hingga 100.');
        header('Location: ../penilaian.php');
        exit;
    }

    try {
        $stmt = $pdo->prepare("
            UPDATE penilaian 
            SET c1 = :c1, c2 = :c2, c3 = :c3, c4 = :c4 
            WHERE id = :id
        ");
        $stmt->execute([
            ':c1' => $c1,
            ':c2' => $c2,
            ':c3' => $c3,
            ':c4' => $c4,
            ':id' => $id
        ]);

        // Jalankan kalkulasi SPK ulang
        executeSpkCalculation($pdo, $periode);

        setFlash('success', 'Nilai evaluasi kinerja berhasil diperbarui dan komputasi SPK telah dihitung ulang.');
    } catch (PDOException $e) {
        setFlash('danger', 'Gagal memperbarui penilaian: ' . $e->getMessage());
    }

    header('Location: ../penilaian.php');
    exit;

} elseif ($action === 'delete') {
    $id = (int)($_GET['id'] ?? 0);
    $periode = trim($_GET['periode'] ?? '2026-Semester-2');

    if ($id <= 0) {
        setFlash('danger', 'ID Penilaian tidak valid.');
        header('Location: ../penilaian.php');
        exit;
    }

    try {
        $stmt = $pdo->prepare("DELETE FROM penilaian WHERE id = :id");
        $stmt->execute([':id' => $id]);

        // Jalankan kalkulasi SPK ulang jika masih ada data penilaian
        executeSpkCalculation($pdo, $periode);

        setFlash('success', 'Data penilaian berhasil dihapus.');
    } catch (PDOException $e) {
        setFlash('danger', 'Gagal menghapus penilaian: ' . $e->getMessage());
    }

    header('Location: ../penilaian.php');
    exit;
} else {
    header('Location: ../penilaian.php');
    exit;
}
