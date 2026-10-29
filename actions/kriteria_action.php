<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/helper.php';
requireAuth();

$action = $_POST['action'] ?? $_GET['action'] ?? '';

if ($action === 'update') {
    $id = (int)($_POST['id'] ?? 0);
    $nama_kriteria = trim($_POST['nama_kriteria'] ?? '');
    $bobot = (float)($_POST['bobot'] ?? 0);
    $jenis = strtolower(trim($_POST['jenis'] ?? 'benefit'));
    $keterangan = trim($_POST['keterangan'] ?? '');

    if ($id <= 0 || empty($nama_kriteria) || $bobot <= 0) {
        setFlash('danger', 'Nama kriteria dan nilai bobot harus diisi dengan benar.');
        header('Location: ../kriteria.php');
        exit;
    }

    if (!in_array($jenis, ['benefit', 'cost'])) {
        $jenis = 'benefit';
    }

    try {
        $stmt = $pdo->prepare("UPDATE kriteria SET nama_kriteria = :nama, bobot = :bobot, jenis = :jenis, keterangan = :ket WHERE id = :id");
        $stmt->execute([
            ':nama' => $nama_kriteria,
            ':bobot' => $bobot,
            ':jenis' => $jenis,
            ':ket' => $keterangan,
            ':id' => $id
        ]);

        // Cek total bobot
        $sumStmt = $pdo->query("SELECT SUM(bobot) as total_bobot FROM kriteria");
        $totalBobot = (float)$sumStmt->fetch()['total_bobot'];

        // Recalculate SPK for all active periods in penilaian
        $pPeriods = $pdo->query("SELECT DISTINCT periode FROM penilaian")->fetchAll(PDO::FETCH_COLUMN);
        foreach ($pPeriods as $per) {
            executeSpkCalculation($pdo, $per);
        }

        if (abs($totalBobot - 1.0) > 0.001) {
            $persen = round($totalBobot * 100, 1);
            setFlash('warning', "Kriteria berhasil diperbarui (komputasi SPK dihitung ulang), namun TOTAL BOBOT saat ini adalah {$persen}%. Idealnya total bobot seluruh kriteria berjumlah 100% (1.0).");
        } else {
            setFlash('success', "Kriteria '$nama_kriteria' berhasil diperbarui dan seluruh perankingan SPK telah dihitung ulang secara otomatis (Total bobot: 100%).");
        }
    } catch (PDOException $e) {
        setFlash('danger', 'Gagal memperbarui kriteria: ' . $e->getMessage());
    }

    header('Location: ../kriteria.php');
    exit;
} elseif ($action === 'reset_default') {
    try {
        $pdo->exec("
            UPDATE kriteria SET nama_kriteria = 'Presensi & Standup Agile', bobot = 0.20, jenis = 'benefit', keterangan = 'Tingkat kehadiran, ketepatan waktu daily standup sprint & komitmen jam kerja' WHERE kode = 'C1';
            UPDATE kriteria SET nama_kriteria = 'KPI & Delivery Proyek', bobot = 0.35, jenis = 'benefit', keterangan = 'Pencapaian sprint backlog, on-time delivery deployment, dan kepuasan klien/PO' WHERE kode = 'C2';
            UPDATE kriteria SET nama_kriteria = 'Kualitas Kode & SOP IT', bobot = 0.25, jenis = 'benefit', keterangan = 'Clean code standard, code review pass rate, zero bug critical & kepatuhan git workflow' WHERE kode = 'C3';
            UPDATE kriteria SET nama_kriteria = 'Kolaborasi & Komunikasi Tim', bobot = 0.20, jenis = 'benefit', keterangan = 'Kerjasama lintas divisi tech (frontend, backend, QA, UI/UX), proaktif dan knowledge sharing' WHERE kode = 'C4';
        ");

        // Recalculate SPK for all periods
        $pPeriods = $pdo->query("SELECT DISTINCT periode FROM penilaian")->fetchAll(PDO::FETCH_COLUMN);
        foreach ($pPeriods as $per) {
            executeSpkCalculation($pdo, $per);
        }

        setFlash('success', 'Bobot dan tipe kriteria berhasil dikembalikan ke standar spesifikasi (C1: 20%, C2: 35%, C3: 25%, C4: 20%) dan seluruh komputasi SPK telah diperbarui.');
    } catch (PDOException $e) {
        setFlash('danger', 'Gagal mereset kriteria: ' . $e->getMessage());
    }
    header('Location: ../kriteria.php');
    exit;
} else {
    header('Location: ../kriteria.php');
    exit;
}
