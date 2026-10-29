<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/helper.php';
requireAuth();

$action = $_GET['action'] ?? $_POST['action'] ?? '';
$periode = $_POST['periode'] ?? $_GET['periode'] ?? ($_SESSION['active_periode'] ?? '2026-Semester-2');
$redirect = $_GET['redirect'] ?? $_POST['redirect'] ?? 'komputasi.php';

if ($action === 'calculate') {
    $result = executeSpkCalculation($pdo, $periode);
    if ($result['success']) {
        setFlash('success', "Engine SPK (Metode SAW & Rule-Based) berhasil dieksekusi untuk periode {$periode}. Ranking dan rekomendasi pelatihan telah diperbarui.");
    } else {
        setFlash('warning', $result['message']);
    }
    header("Location: ../{$redirect}");
    exit;
} elseif ($action === 'set_periode') {
    $_SESSION['active_periode'] = $periode;
    setFlash('info', "Periode evaluasi aktif telah diubah ke '{$periode}'.");
    header("Location: ../{$redirect}");
    exit;
} else {
    header('Location: ../dashboard.php');
    exit;
}
