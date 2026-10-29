<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/helper.php';
requireAuth();

$periode = $_GET['periode'] ?? ($_SESSION['active_periode'] ?? '2026-Semester-2');
$filterDept = trim($_GET['departemen'] ?? '');

// Pastikan hasil SPK terhitung otomatis jika belum ada
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
$laporanList = $stmt->fetchAll();

$currentUser = getCurrentUser();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Laporan Rekapitulasi SPK - Periode <?= htmlspecialchars($periode) ?></title>
    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            font-family: 'Inter', sans-serif;
            color: #0F172A;
            background: #FFFFFF;
        }
        @media print {
            .no-print {
                display: none !important;
            }
            @page {
                size: A4 landscape;
                margin: 1.5cm;
            }
        }
    </style>
</head>
<body class="p-8 max-w-6xl mx-auto space-y-6">

    <!-- Print Action Bar -->
    <div class="no-print bg-slate-100 p-4 rounded-xl border border-slate-300 flex items-center justify-between">
        <div class="text-xs text-slate-700">
            <strong>Mode Cetak Laporan Resmi:</strong> Pastikan opsi margin dan background graphics aktif saat mencetak.
        </div>
        <div class="flex items-center gap-2">
            <button onclick="window.print()" class="px-4 py-2 bg-[#1E3A8A] text-white text-xs font-semibold rounded-lg shadow hover:bg-blue-900 transition-colors">
                Cetak Halaman Ini (Print / PDF)
            </button>
            <button onclick="window.close()" class="px-4 py-2 bg-slate-200 text-slate-700 text-xs font-semibold rounded-lg hover:bg-slate-300 transition-colors">
                Tutup
            </button>
        </div>
    </div>

    <!-- Kop Surat Perusahaan -->
    <div class="border-b-4 border-double border-slate-900 pb-4 flex items-center justify-between gap-6">
        <div class="flex items-center gap-4">
            <div class="w-14 h-14 rounded-xl bg-[#1E3A8A] text-white flex items-center justify-center text-xl font-black shrink-0 tracking-wider">
                IB
            </div>
            <div>
                <h1 class="text-base font-extrabold text-[#1E3A8A] uppercase tracking-wider leading-none">
                    PT INDOBUZZ REPUBLIK DIGITAL
                </h1>
                <h2 class="text-xs font-bold text-slate-700 uppercase mt-1">
                    DIVISI HUMAN RESOURCES & PEOPLE OPERATIONS
                </h2>
                <p class="text-[10px] text-slate-500 mt-0.5">
                    Digital Marketing & IT Solutions Agency • Website: indobuzz.co.id • Email: info@indobuzz.co.id • Telp: (0260) 412-890
                </p>
            </div>
        </div>
        <div class="text-right text-[10px] text-slate-500">
            <div>Dokumen Resmi Internal</div>
            <div class="font-bold text-slate-800">SPK-HRD/IB/<?= date('Y/m') ?></div>
        </div>
    </div>

    <!-- Judul -->
    <div class="text-center py-2 space-y-1">
        <h3 class="text-sm font-extrabold text-slate-900 uppercase tracking-wide">
            LEMBAR REKAPITULASI PENILAIAN KINERJA & REKOMENDASI PELATIHAN<?= !empty($filterDept) ? ' - ' . strtoupper(htmlspecialchars($filterDept)) : '' ?>
        </h3>
        <p class="text-xs text-slate-600">
            Periode: <strong><?= htmlspecialchars($periode) ?></strong> &nbsp;|&nbsp; 
            <?php if (!empty($filterDept)): ?>
                Divisi: <strong><?= htmlspecialchars($filterDept) ?></strong> &nbsp;|&nbsp;
            <?php endif; ?>
            Metode Evaluasi: <strong>Simple Additive Weighting (SAW) & Rule-Based</strong>
        </p>
    </div>

    <!-- Tabel -->
    <table class="w-full text-left text-xs border border-slate-400 border-collapse">
        <thead class="bg-slate-100 text-slate-800 font-bold uppercase text-[10px]">
            <tr>
                <th class="py-2 px-2.5 border border-slate-300 text-center w-10">Peringkat</th>
                <th class="py-2 px-2.5 border border-slate-300">NIP & Nama Pegawai</th>
                <th class="py-2 px-2.5 border border-slate-300">Divisi & Jabatan</th>
                <th class="py-2 px-2 border border-slate-300 text-center w-14">C1 (20%)</th>
                <th class="py-2 px-2 border border-slate-300 text-center w-14">C2 (35%)</th>
                <th class="py-2 px-2 border border-slate-300 text-center w-14">C3 (25%)</th>
                <th class="py-2 px-2 border border-slate-300 text-center w-14">C4 (20%)</th>
                <th class="py-2 px-2.5 border border-slate-300 text-center w-16">Skor Akhir</th>
                <th class="py-2 px-2.5 border border-slate-300">Rekomendasi Pelatihan Resmi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-200">
            <?php if (empty($laporanList)): ?>
                <tr>
                    <td colspan="9" class="py-8 text-center text-slate-400">
                        Tidak ada data penilaian untuk periode/filter departemen ini.
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($laporanList as $item): ?>
                    <tr>
                        <td class="py-2 px-2.5 border border-slate-300 text-center font-bold font-mono">
                            <?= $item['ranking'] ?>
                        </td>
                        <td class="py-2 px-2.5 border border-slate-300">
                            <span class="font-bold text-slate-900"><?= htmlspecialchars($item['nama']) ?></span>
                            <span class="block text-[10px] text-slate-500 font-mono"><?= htmlspecialchars($item['nip']) ?></span>
                        </td>
                        <td class="py-2 px-2.5 border border-slate-300 text-slate-700">
                            <span class="font-medium"><?= htmlspecialchars($item['departemen']) ?></span>
                            <span class="block text-[10px] text-slate-500"><?= htmlspecialchars($item['jabatan']) ?></span>
                        </td>
                        <td class="py-2 px-2 border border-slate-300 text-center font-mono <?= $item['c1'] < 70 ? 'text-rose-600 font-bold' : '' ?>">
                            <?= number_format($item['c1'], 0) ?>
                        </td>
                        <td class="py-2 px-2 border border-slate-300 text-center font-mono <?= $item['c2'] < 70 ? 'text-rose-600 font-bold' : '' ?>">
                            <?= number_format($item['c2'], 0) ?>
                        </td>
                        <td class="py-2 px-2 border border-slate-300 text-center font-mono <?= $item['c3'] < 70 ? 'text-rose-600 font-bold' : '' ?>">
                            <?= number_format($item['c3'], 0) ?>
                        </td>
                        <td class="py-2 px-2 border border-slate-300 text-center font-mono <?= $item['c4'] < 70 ? 'text-rose-600 font-bold' : '' ?>">
                            <?= number_format($item['c4'], 0) ?>
                        </td>
                        <td class="py-2 px-2.5 border border-slate-300 text-center font-mono font-bold text-[#1E3A8A]">
                            <?= number_format($item['nilai_akhir_v'], 4) ?>
                        </td>
                        <td class="py-2 px-2.5 border border-slate-300 text-slate-800 text-[11px] font-medium leading-tight">
                            <?= htmlspecialchars($item['rekomendasi_pelatihan']) ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

    <!-- Tanda Tangan -->
    <div class="pt-10 grid grid-cols-2 text-center text-xs text-slate-800">
        <div class="space-y-16">
            <p>
                Diverifikasi oleh,<br>
                <strong>People Operations Specialist</strong>
            </p>
            <div>
                <p class="font-bold underline text-slate-900"><?= htmlspecialchars($currentUser['nama_lengkap'] ?? 'Siti Rahmawati, S.Kom') ?></p>
                <p class="text-[10px] text-slate-500">NIP: IB-HRD-002</p>
            </div>
        </div>

        <div class="space-y-16">
            <p>
                Subang, <?= date('d F Y') ?><br>
                Menyetujui,<br>
                <strong>Director & CEO, PT Indobuzz Republik Digital</strong>
            </p>
            <div>
                <p class="font-bold underline text-slate-900">Jeni Lutfi, S.T.</p>
                <p class="text-[10px] text-slate-500">Direktur Utama</p>
            </div>
        </div>
    </div>

</body>
</html>
