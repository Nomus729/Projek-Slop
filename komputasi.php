<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/helper.php';
requireAuth();

$pageTitle = 'Komputasi SPK & Rule-Based';
$pageSubtitle = 'Transparansi Algoritma Simple Additive Weighting & Aturan Inferensi';

$periode = $_GET['periode'] ?? ($_SESSION['active_periode'] ?? '2026-Semester-2');
$_SESSION['active_periode'] = $periode;

// Jalankan kalkulasi SAW & Rule-Based untuk mendapatkan data terstruktur
$calcData = executeSpkCalculation($pdo, $periode);

$matrixX = $calcData['matrix_x'] ?? [];
$matrixR = $calcData['matrix_r'] ?? [];
$results = $calcData['results'] ?? [];
$extremes = $calcData['extremes'] ?? [];
$kriteria = $calcData['kriteria'] ?? [];

// List seluruh periode untuk switcher
$pStmt = $pdo->query("SELECT DISTINCT periode FROM penilaian ORDER BY periode DESC");
$listPeriode = $pStmt->fetchAll(PDO::FETCH_COLUMN);
if (!in_array($periode, $listPeriode) && !empty($periode)) {
    array_unshift($listPeriode, $periode);
}

require_once __DIR__ . '/includes/header.php';
?>

<?php require_once __DIR__ . '/includes/sidebar.php'; ?>

<!-- Main Content Area -->
<div class="flex-1 flex flex-col min-w-0 overflow-y-auto">
    <?php require_once __DIR__ . '/includes/topbar.php'; ?>

    <main class="p-6 max-w-7xl w-full mx-auto space-y-6">
        <!-- Control Header & Period Selection -->
        <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-xl font-bold text-slate-900">Transparansi Komputasi SPK</h1>
                <p class="text-xs text-slate-500 mt-0.5">Penjelasan langkah demi langkah algoritma normalisasi SAW dan inferensi Rule-Based</p>
            </div>

            <div class="flex flex-wrap items-center gap-2.5 shrink-0">
                <!-- Form Pilih Periode -->
                <form method="GET" action="komputasi.php" class="inline-flex items-center gap-2 bg-slate-50 border border-slate-200 rounded-xl px-3 py-1.5 text-xs">
                    <i class="fa-regular fa-calendar text-slate-400"></i>
                    <label for="komputasi-periode" class="text-slate-500 font-medium">Periode:</label>
                    <select id="komputasi-periode" name="periode" onchange="this.form.submit()" 
                            class="bg-transparent font-semibold text-slate-800 focus:outline-none cursor-pointer text-xs">
                        <?php foreach ($listPeriode as $per): ?>
                            <option value="<?= htmlspecialchars($per) ?>" <?= $periode === $per ? 'selected' : '' ?>>
                                <?= htmlspecialchars($per) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </form>

                <!-- Tombol Hitung Ulang -->
                <a href="actions/spk_action.php?action=calculate&periode=<?= urlencode($periode) ?>&redirect=komputasi.php" 
                   class="inline-flex items-center gap-2 py-2 px-3.5 rounded-xl border border-slate-200 bg-slate-50 hover:bg-slate-100 text-slate-700 text-xs font-semibold shadow-xs transition-all">
                    <i class="fa-solid fa-rotate text-xs text-teal-600"></i>
                    <span>Hitung Ulang</span>
                </a>

                <!-- Tombol Lihat Lembar Laporan -->
                <a href="laporan.php?periode=<?= urlencode($periode) ?>" 
                   class="inline-flex items-center gap-2 py-2 px-3.5 rounded-xl bg-[#0D9488] hover:bg-teal-700 text-white text-xs font-semibold shadow-sm transition-all">
                    <i class="fa-solid fa-file-invoice text-xs"></i>
                    <span>Lembar Laporan</span>
                </a>
            </div>
        </div>

        <?php if (!$calcData['success'] || empty($matrixX)): ?>
            <!-- Empty State Warning -->
            <div class="bg-amber-50 rounded-2xl p-8 border border-amber-200 text-center max-w-xl mx-auto space-y-3">
                <div class="w-12 h-12 rounded-full bg-amber-100 text-amber-700 flex items-center justify-center text-xl mx-auto">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                </div>
                <h3 class="font-bold text-slate-900 text-base">Belum Ada Data Penilaian</h3>
                <p class="text-xs text-slate-600 leading-relaxed">
                    Tidak ditemukan data penilaian pegawai untuk periode <strong><?= htmlspecialchars($periode) ?></strong>.
                    Silakan input data nilai capaian aktual pegawai terlebih dahulu pada modul Input Penilaian.
                </p>
                <div class="pt-2">
                    <a href="penilaian.php?periode=<?= urlencode($periode) ?>" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-[#1E3A8A] text-white text-xs font-semibold shadow-sm">
                        <i class="fa-solid fa-pen-to-square"></i>
                        <span>Input Penilaian Sekarang</span>
                    </a>
                </div>
            </div>
        <?php else: ?>

            <!-- Navigation Tabs Container -->
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
                <!-- Tab Buttons -->
                <div class="flex border-b border-slate-200 bg-slate-50/70 p-2 gap-2 text-xs font-semibold">
                    <button type="button" onclick="switchTab('tab-x')" id="btn-tab-x" 
                            class="tab-btn active flex items-center gap-2 py-2.5 px-4 rounded-xl transition-all bg-white text-[#1E3A8A] shadow-sm border border-slate-200">
                        <span class="w-5 h-5 rounded-md bg-blue-100 text-[#1E3A8A] flex items-center justify-center text-[10px] font-bold">1</span>
                        <span>Tahap 1: Matriks Nilai Awal</span>
                    </button>

                    <button type="button" onclick="switchTab('tab-r')" id="btn-tab-r" 
                            class="tab-btn flex items-center gap-2 py-2.5 px-4 rounded-xl transition-all text-slate-600 hover:text-slate-900 hover:bg-slate-100">
                        <span class="w-5 h-5 rounded-md bg-slate-200 text-slate-700 flex items-center justify-center text-[10px] font-bold">2</span>
                        <span>Tahap 2: Matriks Normalisasi</span>
                    </button>

                    <button type="button" onclick="switchTab('tab-v')" id="btn-tab-v" 
                            class="tab-btn flex items-center gap-2 py-2.5 px-4 rounded-xl transition-all text-slate-600 hover:text-slate-900 hover:bg-slate-100">
                        <span class="w-5 h-5 rounded-md bg-teal-100 text-[#0D9488] flex items-center justify-center text-[10px] font-bold">3</span>
                        <span>Tahap 3: Peringkat &amp; Rekomendasi Pelatihan</span>
                    </button>
                </div>

                <!-- TAB 1: Matriks Nilai Awal -->
                <div id="tab-x" class="tab-content p-6 space-y-6">
                    <div class="flex flex-col md:flex-row md:items-center justify-between gap-3 pb-4 border-b border-slate-100">
                        <div>
                            <h3 class="font-bold text-base text-slate-900">Matriks Nilai Penilaian Awal</h3>
                            <p class="text-xs text-slate-500 mt-0.5">
                                Kumpulan nilai capaian kinerja aktual setiap talenta IT pada masing-masing kriteria evaluasi.
                            </p>
                        </div>
                        <div class="text-xs font-mono bg-blue-50 text-[#1E3A8A] px-3 py-1.5 rounded-lg border border-blue-200">
                            Total: <?= count($matrixX) ?> Talenta IT &bull; 4 Kriteria Penilaian
                        </div>
                    </div>

                    <!-- Table Matriks X -->
                    <div class="overflow-x-auto border border-slate-200 rounded-xl">
                        <table class="w-full text-left text-xs">
                            <thead class="bg-slate-50 text-slate-700 font-semibold uppercase tracking-wider border-b border-slate-200">
                                <tr>
                                    <th class="py-3 px-4 text-center w-12">No</th>
                                    <th class="py-3 px-4">Talenta IT</th>
                                    <th class="py-3 px-4 text-center">C1: Presensi (20%)</th>
                                    <th class="py-3 px-4 text-center">C2: KPI Proyek (35%)</th>
                                    <th class="py-3 px-4 text-center">C3: Kualitas Kode (25%)</th>
                                    <th class="py-3 px-4 text-center">C4: Tim &amp; Agile (20%)</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <?php foreach ($matrixX as $idx => $row): ?>
                                    <tr class="hover:bg-slate-50/80 transition-colors">
                                        <td class="py-3 px-4 text-center text-slate-400 font-medium"><?= $idx + 1 ?></td>
                                        <td class="py-3 px-4 font-semibold text-slate-900">
                                            <?= htmlspecialchars($row['nama']) ?>
                                            <span class="block text-[11px] font-mono text-slate-400 font-normal"><?= htmlspecialchars($row['nip']) ?></span>
                                        </td>
                                        <td class="py-3 px-4 text-center font-mono <?= $row['c1'] == $extremes['c1']['max'] ? 'text-teal-700 font-bold bg-teal-50/40' : '' ?>">
                                            <?= number_format($row['c1'], 2) ?>
                                        </td>
                                        <td class="py-3 px-4 text-center font-mono <?= $row['c2'] == $extremes['c2']['max'] ? 'text-teal-700 font-bold bg-teal-50/40' : '' ?>">
                                            <?= number_format($row['c2'], 2) ?>
                                        </td>
                                        <td class="py-3 px-4 text-center font-mono <?= $row['c3'] == $extremes['c3']['max'] ? 'text-teal-700 font-bold bg-teal-50/40' : '' ?>">
                                            <?= number_format($row['c3'], 2) ?>
                                        </td>
                                        <td class="py-3 px-4 text-center font-mono <?= $row['c4'] == $extremes['c4']['max'] ? 'text-teal-700 font-bold bg-teal-50/40' : '' ?>">
                                            <?= number_format($row['c4'], 2) ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <!-- Baris Nilai Max dan Min Kolom -->
                            <tfoot class="bg-slate-100/90 font-bold text-slate-800 border-t-2 border-slate-300">
                                <tr>
                                    <td colspan="2" class="py-3 px-4 text-right uppercase tracking-wider text-[11px] text-teal-800">
                                        <i class="fa-solid fa-arrow-up-right-dots mr-1"></i> Nilai Tertinggi Kolom (Maksimum):
                                    </td>
                                    <td class="py-3 px-4 text-center font-mono text-[#0D9488] bg-teal-50/60"><?= number_format($extremes['c1']['max'], 2) ?></td>
                                    <td class="py-3 px-4 text-center font-mono text-[#0D9488] bg-teal-50/60"><?= number_format($extremes['c2']['max'], 2) ?></td>
                                    <td class="py-3 px-4 text-center font-mono text-[#0D9488] bg-teal-50/60"><?= number_format($extremes['c3']['max'], 2) ?></td>
                                    <td class="py-3 px-4 text-center font-mono text-[#0D9488] bg-teal-50/60"><?= number_format($extremes['c4']['max'], 2) ?></td>
                                </tr>
                                <tr class="text-slate-500 font-normal">
                                    <td colspan="2" class="py-2.5 px-4 text-right text-[11px]">
                                        Nilai Terendah Kolom (Minimum):
                                    </td>
                                    <td class="py-2.5 px-4 text-center font-mono"><?= number_format($extremes['c1']['min'], 2) ?></td>
                                    <td class="py-2.5 px-4 text-center font-mono"><?= number_format($extremes['c2']['min'], 2) ?></td>
                                    <td class="py-2.5 px-4 text-center font-mono"><?= number_format($extremes['c3']['min'], 2) ?></td>
                                    <td class="py-2.5 px-4 text-center font-mono"><?= number_format($extremes['c4']['min'], 2) ?></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    <div class="flex justify-end">
                        <button type="button" onclick="switchTab('tab-r')" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-[#1E3A8A] text-white text-xs font-semibold shadow-sm hover:bg-[#172554]">
                            <span>Lanjut ke Tahap 2: Normalisasi Matriks</span>
                            <i class="fa-solid fa-arrow-right text-xs"></i>
                        </button>
                    </div>
                </div>

                <!-- TAB 2: Matriks Normalisasi (R) -->
                <div id="tab-r" class="tab-content p-6 space-y-6 hidden">
                    <div class="flex flex-col md:flex-row md:items-center justify-between gap-3 pb-4 border-b border-slate-100">
                        <div>
                            <h3 class="font-bold text-base text-slate-900">Matriks Normalisasi</h3>
                            <p class="text-xs text-slate-500 mt-0.5">
                                Seluruh kriteria bertipe <strong>Benefit</strong> (semakin tinggi capaian semakin baik). Nilai dinormalisasi dalam rentang 0 hingga 1 dengan membagi capaian aktual terhadap nilai maksimum kriteria.
                            </p>
                        </div>
                    </div>

                    <!-- Table Matriks R -->
                    <div class="overflow-x-auto border border-slate-200 rounded-xl">
                        <table class="w-full text-left text-xs">
                            <thead class="bg-slate-50 text-slate-700 font-semibold uppercase tracking-wider border-b border-slate-200">
                                <tr>
                                    <th class="py-3 px-4 text-center w-12">No</th>
                                    <th class="py-3 px-4">Talenta IT</th>
                                    <th class="py-3 px-4 text-center">
                                        Presensi (C1)
                                        <span class="block text-[10px] text-slate-400 font-normal">Bobot: <?= round($kriteria['c1']['bobot'] * 100) ?>%</span>
                                    </th>
                                    <th class="py-3 px-4 text-center">
                                        KPI Proyek (C2)
                                        <span class="block text-[10px] text-slate-400 font-normal">Bobot: <?= round($kriteria['c2']['bobot'] * 100) ?>%</span>
                                    </th>
                                    <th class="py-3 px-4 text-center">
                                        Kualitas Kode (C3)
                                        <span class="block text-[10px] text-slate-400 font-normal">Bobot: <?= round($kriteria['c3']['bobot'] * 100) ?>%</span>
                                    </th>
                                    <th class="py-3 px-4 text-center">
                                        Tim &amp; Agile (C4)
                                        <span class="block text-[10px] text-slate-400 font-normal">Bobot: <?= round($kriteria['c4']['bobot'] * 100) ?>%</span>
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <?php foreach ($matrixR as $idx => $r): ?>
                                    <tr class="hover:bg-slate-50/80 transition-colors">
                                        <td class="py-3 px-4 text-center text-slate-400 font-medium"><?= $idx + 1 ?></td>
                                        <td class="py-3 px-4 font-semibold text-slate-900">
                                            <?= htmlspecialchars($r['nama']) ?>
                                            <span class="block text-[11px] font-mono text-slate-400 font-normal"><?= htmlspecialchars($r['nip']) ?></span>
                                        </td>
                                        <td class="py-3 px-4 text-center font-mono <?= $r['r1'] == 1.0 ? 'text-teal-700 font-bold bg-teal-50/50' : 'text-slate-700' ?>">
                                            <?= number_format($r['r1'], 4) ?>
                                        </td>
                                        <td class="py-3 px-4 text-center font-mono <?= $r['r2'] == 1.0 ? 'text-teal-700 font-bold bg-teal-50/50' : 'text-slate-700' ?>">
                                            <?= number_format($r['r2'], 4) ?>
                                        </td>
                                        <td class="py-3 px-4 text-center font-mono <?= $r['r3'] == 1.0 ? 'text-teal-700 font-bold bg-teal-50/50' : 'text-slate-700' ?>">
                                            <?= number_format($r['r3'], 4) ?>
                                        </td>
                                        <td class="py-3 px-4 text-center font-mono <?= $r['r4'] == 1.0 ? 'text-teal-700 font-bold bg-teal-50/50' : 'text-slate-700' ?>">
                                            <?= number_format($r['r4'], 4) ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <div class="flex items-center justify-between">
                        <button type="button" onclick="switchTab('tab-x')" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl border border-slate-300 text-slate-700 text-xs font-semibold hover:bg-slate-50">
                            <i class="fa-solid fa-arrow-left text-xs"></i>
                            <span>Kembali ke Tahap 1</span>
                        </button>
                        <button type="button" onclick="switchTab('tab-v')" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-[#0D9488] text-white text-xs font-semibold shadow-sm hover:bg-teal-700">
                            <span>Lanjut ke Tahap 3: Peringkat &amp; Rekomendasi</span>
                            <i class="fa-solid fa-arrow-right text-xs"></i>
                        </button>
                    </div>
                </div>

                <!-- TAB 3: Hasil Akhir Preferensi, Ranking & Rekomendasi Rules -->
                <div id="tab-v" class="tab-content p-6 space-y-6 hidden">
                    <div class="flex flex-col md:flex-row md:items-center justify-between gap-3 pb-4 border-b border-slate-100">
                        <div>
                            <h3 class="font-bold text-base text-slate-900">Peringkat Kinerja &amp; Rekomendasi Program Pelatihan IT</h3>
                            <p class="text-xs text-slate-500 mt-0.5">
                                Skor akhir dihitung dari total penjumlahan perkalian bobot kriteria dengan nilai normalisasi. Rekomendasi program pengembangan ditentukan otomatis oleh <strong>Rule-Based Engine</strong>.
                            </p>
                        </div>
                        <a href="laporan.php?periode=<?= urlencode($periode) ?>" 
                           class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-800 text-xs font-semibold">
                            <i class="fa-solid fa-print"></i>
                            <span>Cetak Format Laporan Resmi</span>
                        </a>
                    </div>

                    <!-- Table of Final Ranked Results -->
                    <div class="overflow-x-auto border border-slate-200 rounded-xl">
                        <table class="w-full text-left text-xs">
                            <thead class="bg-slate-50 text-slate-700 font-semibold uppercase tracking-wider border-b border-slate-200">
                                <tr>
                                    <th class="py-3 px-4 text-center w-14">Rank</th>
                                    <th class="py-3 px-4">Talenta IT</th>
                                    <th class="py-3 px-4">Divisi &amp; Jabatan</th>
                                    <th class="py-3 px-4 text-center">Perhitungan Bobot</th>
                                    <th class="py-3 px-4 text-center">Skor Akhir</th>
                                    <th class="py-3 px-4">Status &amp; Rekomendasi Pelatihan IT</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <?php foreach ($results as $res): ?>
                                    <tr class="hover:bg-slate-50/80 transition-colors <?= $res['ranking'] == 1 ? 'bg-amber-50/30' : '' ?>">
                                        <td class="py-4 px-4 text-center">
                                            <?php if ($res['ranking'] == 1): ?>
                                                <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-amber-100 text-amber-700 font-bold shadow-sm ring-2 ring-amber-300">
                                                    <i class="fa-solid fa-crown text-xs"></i>
                                                </span>
                                            <?php elseif ($res['ranking'] == 2): ?>
                                                <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-slate-200 text-slate-700 font-bold ring-2 ring-slate-300">2</span>
                                            <?php elseif ($res['ranking'] == 3): ?>
                                                <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-amber-800/10 text-amber-800 font-bold ring-2 ring-amber-700/20">3</span>
                                            <?php else: ?>
                                                <span class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-slate-100 text-slate-600 font-semibold"><?= $res['ranking'] ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="py-4 px-4">
                                            <p class="font-bold text-slate-900"><?= htmlspecialchars($res['nama']) ?></p>
                                            <p class="text-[11px] font-mono text-slate-500"><?= htmlspecialchars($res['nip']) ?></p>
                                        </td>
                                        <td class="py-4 px-4 text-slate-600">
                                            <p class="font-medium"><?= htmlspecialchars($res['departemen']) ?></p>
                                            <p class="text-[11px] text-slate-400"><?= htmlspecialchars($res['jabatan']) ?></p>
                                        </td>
                                        <td class="py-4 px-4 text-center font-mono text-[11px] text-slate-500">
                                            <span title="(<?= $kriteria['c1']['bobot'] ?> &times; <?= $res['r1'] ?>) + (<?= $kriteria['c2']['bobot'] ?> &times; <?= $res['r2'] ?>) + (<?= $kriteria['c3']['bobot'] ?> &times; <?= $res['r3'] ?>) + (<?= $kriteria['c4']['bobot'] ?> &times; <?= $res['r4'] ?>)">
                                                (<?= $kriteria['c1']['bobot'] ?>&times;<?= $res['r1'] ?>) + (<?= $kriteria['c2']['bobot'] ?>&times;<?= $res['r2'] ?>) + ...
                                            </span>
                                        </td>
                                        <td class="py-4 px-4 text-center">
                                            <span class="inline-block px-3 py-1.5 rounded-xl font-mono font-bold text-sm bg-blue-50 text-[#1E3A8A] border border-blue-200 shadow-xs">
                                                <?= number_format($res['nilai_akhir_v'], 4) ?>
                                            </span>
                                        </td>
                                        <td class="py-4 px-4">
                                            <?php
                                            $eval = $res['rule_eval'];
                                            $badgeClass = 'bg-blue-50 text-blue-800 border-blue-200';
                                            $icon = 'fa-circle-info';
                                            if ($eval['badge_color'] === 'teal') {
                                                $badgeClass = 'bg-teal-50 text-teal-800 border-teal-200 font-semibold';
                                                $icon = 'fa-medal text-teal-600';
                                            } elseif ($eval['badge_color'] === 'danger') {
                                                $badgeClass = 'bg-rose-50 text-rose-800 border-rose-200 font-semibold';
                                                $icon = 'fa-triangle-exclamation text-rose-600';
                                            } elseif ($eval['badge_color'] === 'warning') {
                                                $badgeClass = 'bg-amber-50 text-amber-800 border-amber-200 font-medium';
                                                $icon = 'fa-user-gear text-amber-600';
                                            }
                                            ?>
                                            <div class="space-y-1">
                                                <div class="inline-flex items-start gap-1.5 px-2.5 py-1 rounded-lg border text-xs leading-snug <?= $badgeClass ?>">
                                                    <i class="fa-solid <?= $icon ?> text-xs mt-0.5 shrink-0"></i>
                                                    <span><?= htmlspecialchars($res['rekomendasi_pelatihan']) ?></span>
                                                </div>
                                                <div class="text-[10px] text-slate-500 italic pl-1">
                                                    Pemicu: <?= htmlspecialchars(implode(', ', $eval['rules'])) ?>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Summary Rule Explanations Card -->
                    <div class="bg-slate-50 rounded-xl p-5 border border-slate-200 text-xs space-y-3">
                        <h4 class="font-bold text-slate-800 flex items-center gap-2">
                            <i class="fa-solid fa-circle-check text-teal-600"></i>
                            <span>Pedoman Logika Aturan Rekomendasi Pelatihan &amp; Pengembangan IT:</span>
                        </h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 text-slate-600">
                            <div class="p-2.5 bg-white rounded-lg border border-slate-200">
                                <span class="font-bold text-slate-800">Rule 1:</span> KPI &amp; Delivery Proyek (C2) &lt; 70 &rarr; <span class="text-amber-800 font-medium">Pelatihan Software Engineering &amp; Delivery Proyek</span>
                            </div>
                            <div class="p-2.5 bg-white rounded-lg border border-slate-200">
                                <span class="font-bold text-slate-800">Rule 2:</span> Kualitas Kode &amp; SOP IT (C3) &lt; 70 &rarr; <span class="text-amber-800 font-medium">Workshop Code Quality, IT Security &amp; DevOps CI/CD</span>
                            </div>
                            <div class="p-2.5 bg-white rounded-lg border border-slate-200">
                                <span class="font-bold text-slate-800">Rule 3:</span> Presensi &amp; Standup Agile (C1) &lt; 70 &rarr; <span class="text-amber-800 font-medium">Workshop Manajemen Waktu &amp; Kedisiplinan Kerja Agile</span>
                            </div>
                            <div class="p-2.5 bg-white rounded-lg border border-slate-200">
                                <span class="font-bold text-slate-800">Rule 4:</span> Kolaborasi &amp; Komunikasi Tim (C4) &lt; 70 &rarr; <span class="text-amber-800 font-medium">Pelatihan Komunikasi Efektif &amp; Kolaborasi Tim Developer</span>
                            </div>
                            <div class="p-2.5 bg-white rounded-lg border border-slate-200 md:col-span-2">
                                <span class="font-bold text-slate-800">Rule 5 (Pendampingan Khusus):</span> Skor Akhir &lt; 0.60 atau lebih dari 2 kriteria &lt; 70 &rarr; <span class="text-rose-700 font-bold">Program Pendampingan Khusus (Performance Improvement Plan / PIP)</span>
                            </div>
                            <div class="p-2.5 bg-white rounded-lg border border-slate-200 md:col-span-2">
                                <span class="font-bold text-slate-800">Rule 6 (Prestasi Unggul):</span> Skor Akhir &ge; 0.85 dan seluruh kriteria &ge; 75 &rarr; <span class="text-teal-800 font-bold">Dipertimbangkan untuk Promosi Jabatan / Jalur Tech Lead &amp; Principal Specialist</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </main>

    <?php require_once __DIR__ . '/includes/footer.php'; ?>
</div>

<script>
function switchTab(tabId) {
    // Hide all tab contents
    document.querySelectorAll('.tab-content').forEach(el => el.classList.add('hidden'));
    
    // Remove active styles from all buttons
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.classList.remove('bg-white', 'text-[#1E3A8A]', 'shadow-sm', 'border', 'border-slate-200');
        btn.classList.add('text-slate-600');
    });

    // Show targeted tab
    const target = document.getElementById(tabId);
    if (target) {
        target.classList.remove('hidden');
    }

    // Set active button
    const btn = document.getElementById('btn-' + tabId);
    if (btn) {
        btn.classList.add('bg-white', 'text-[#1E3A8A]', 'shadow-sm', 'border', 'border-slate-200');
        btn.classList.remove('text-slate-600');
    }
}
</script>
