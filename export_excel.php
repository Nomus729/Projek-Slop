<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/helper.php';
requireAuth();

$periode = $_GET['periode'] ?? ($_SESSION['active_periode'] ?? '2026-Semester-2');
$filterDept = trim($_GET['departemen'] ?? '');

// Pastikan hasil SPK terhitung
$stmtCheck = $pdo->prepare("
    SELECT COUNT(*) 
    FROM hasil_spk h 
    JOIN penilaian p ON h.penilaian_id = p.id 
    WHERE p.periode = :periode
");
$stmtCheck->execute([':periode' => $periode]);
if ((int)$stmtCheck->fetchColumn() === 0) {
    executeSpkCalculation($pdo, $periode);
}

// Ambil data hasil perankingan
$sql = "
    SELECT h.*, p.c1, p.c2, p.c3, p.c4, pg.nip, pg.nama, pg.departemen, pg.jabatan
    FROM hasil_spk h
    JOIN penilaian p ON h.penilaian_id = p.id
    JOIN pegawai pg ON p.pegawai_id = pg.id
    WHERE p.periode = :periode
";
$params = [':periode' => $periode];

if (!empty($filterDept)) {
    $sql .= " AND pg.departemen = :dept";
    $params[':dept'] = $filterDept;
}

$sql .= " ORDER BY h.ranking ASC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$data = $stmt->fetchAll();

$deptLabel = !empty($filterDept) ? " - Divisi $filterDept" : "";
$filename = "Laporan_SPK_Kinerja_" . preg_replace('/[^a-zA-Z0-9_-]/', '_', $periode) . ($filterDept ? '_' . preg_replace('/[^a-zA-Z0-9_-]/', '_', $filterDept) : '') . "_" . date('Ymd_His') . ".xls";

header("Content-Type: application/vnd.ms-excel; charset=utf-8");
header("Content-Disposition: attachment; filename=\"$filename\"");
header("Pragma: no-cache");
header("Expires: 0");

$currentUser = getCurrentUser();
?>
<!DOCTYPE html>
<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <style>
        body { font-family: Arial, sans-serif; font-size: 11pt; color: #0F172A; }
        .kop-title { font-size: 14pt; font-weight: bold; color: #1E3A8A; text-align: center; }
        .kop-sub { font-size: 10pt; font-weight: bold; text-align: center; color: #475569; }
        .kop-desc { font-size: 9pt; text-align: center; color: #64748B; }
        .title-doc { font-size: 12pt; font-weight: bold; text-align: center; padding: 10px 0; }
        table { border-collapse: collapse; width: 100%; }
        th { background-color: #1E3A8A; color: #FFFFFF; font-weight: bold; text-align: center; border: 1px solid #94A3B8; padding: 8px; font-size: 10pt; }
        td { border: 1px solid #CBD5E1; padding: 6px 8px; vertical-align: middle; font-size: 10pt; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-mono { font-family: 'Courier New', monospace; }
        .bold { font-weight: bold; }
        .highlight-top { background-color: #FEF3C7; font-weight: bold; }
        .gap-skill { color: #DC2626; font-weight: bold; }
        .meta-table td { border: none; padding: 4px; font-size: 10pt; }
    </style>
</head>
<body>
    <div class="kop-title">PT INDOBUZZ REPUBLIK DIGITAL</div>
    <div class="kop-sub">DIGITAL MARKETING AGENCY &amp; IT SOFTWARE SOLUTIONS</div>
    <div class="kop-desc">Jl. Otto Iskandardinata No. 16, Subang, Jawa Barat 41211 • Email: info@indobuzz.co.id • Web: indobuzz.co.id</div>
    <hr style="border: 2px solid #0F172A; margin: 10px 0;" />

    <div class="title-doc">
        REKAPITULASI HASIL EVALUASI KINERJA TALENTA IT &amp; REKOMENDASI PELATIHAN<?= strtoupper($deptLabel) ?>
    </div>

    <table class="meta-table" style="margin-bottom: 15px;">
        <tr>
            <td style="width: 180px;"><strong>Periode Evaluasi:</strong></td>
            <td><?= htmlspecialchars($periode) ?></td>
            <td style="width: 180px;"><strong>Metode SPK:</strong></td>
            <td>Simple Additive Weighting (SAW) + Rule-Based Engine</td>
        </tr>
        <tr>
            <td><strong>Total Talenta IT:</strong></td>
            <td><?= count($data) ?> Orang</td>
            <td><strong>Tanggal Ekspor:</strong></td>
            <td><?= date('d F Y, H:i') ?> WIB</td>
        </tr>
        <?php if (!empty($filterDept)): ?>
        <tr>
            <td><strong>Filter Divisi:</strong></td>
            <td colspan="3"><?= htmlspecialchars($filterDept) ?></td>
        </tr>
        <?php endif; ?>
    </table>

    <table border="1">
        <thead>
            <tr>
                <th style="width: 50px;">Rank</th>
                <th style="width: 120px;">NIP</th>
                <th style="width: 180px;">Nama Pegawai</th>
                <th style="width: 160px;">Divisi / Unit</th>
                <th style="width: 150px;">Jabatan IT</th>
                <th style="width: 85px;">C1 - Presensi (20%)</th>
                <th style="width: 85px;">C2 - KPI Proyek (35%)</th>
                <th style="width: 85px;">C3 - Kualitas Kode (25%)</th>
                <th style="width: 85px;">C4 - Tim &amp; Agile (20%)</th>
                <th style="width: 90px;">Skor Akhir (SAW)</th>
                <th style="width: 280px;">Rekomendasi Pelatihan &amp; Pengembangan IT</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($data)): ?>
                <tr>
                    <td colspan="11" class="text-center" style="padding: 20px; color: #64748B;">
                        Tidak ada data penilaian untuk kriteria pencarian ini.
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($data as $row): ?>
                    <tr <?= $row['ranking'] == 1 ? 'class="highlight-top"' : '' ?>>
                        <td class="text-center bold font-mono"><?= $row['ranking'] ?></td>
                        <td class="font-mono"><?= htmlspecialchars($row['nip']) ?></td>
                        <td class="bold"><?= htmlspecialchars($row['nama']) ?></td>
                        <td><?= htmlspecialchars($row['departemen']) ?></td>
                        <td><?= htmlspecialchars($row['jabatan']) ?></td>
                        <td class="text-center font-mono <?= $row['c1'] < 70 ? 'gap-skill' : '' ?>"><?= number_format($row['c1'], 1) ?></td>
                        <td class="text-center font-mono <?= $row['c2'] < 70 ? 'gap-skill' : '' ?>"><?= number_format($row['c2'], 1) ?></td>
                        <td class="text-center font-mono <?= $row['c3'] < 70 ? 'gap-skill' : '' ?>"><?= number_format($row['c3'], 1) ?></td>
                        <td class="text-center font-mono <?= $row['c4'] < 70 ? 'gap-skill' : '' ?>"><?= number_format($row['c4'], 1) ?></td>
                        <td class="text-center bold font-mono" style="color: #1E3A8A;"><?= number_format($row['nilai_akhir_v'], 4) ?></td>
                        <td><?= htmlspecialchars($row['rekomendasi_pelatihan']) ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

    <br /><br />
    <table class="meta-table" style="width: 100%;">
        <tr>
            <td style="width: 50%; text-align: center;">
                Diverifikasi oleh,<br />
                <strong>People Operations / HR IT</strong><br /><br /><br /><br />
                <u><strong><?= htmlspecialchars($currentUser['nama_lengkap'] ?? 'Siti Rahmawati, S.Kom') ?></strong></u><br />
                NIP: IB-HRD-001
            </td>
            <td style="width: 50%; text-align: center;">
                Subang, <?= date('d F Y') ?><br />
                Menyetujui,<br />
                <strong>Chief Executive Officer (CEO)</strong><br /><br /><br /><br />
                <u><strong>Jeni Lutfi, S.T.</strong></u><br />
                PT Indobuzz Republik Digital
            </td>
        </tr>
    </table>
</body>
</html>
