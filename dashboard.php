<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/helper.php';
requireAuth();

$pageTitle = 'Executive Dashboard';
$pageSubtitle = 'Ringkasan Eksekutif & Evaluasi Kinerja Pegawai';
$periode = $_GET['periode'] ?? ($_SESSION['active_periode'] ?? '2026-Semester-2');
$_SESSION['active_periode'] = $periode;

// Ambil list periode
$pStmt = $pdo->query("SELECT DISTINCT periode FROM penilaian ORDER BY periode DESC");
$listPeriode = $pStmt->fetchAll(PDO::FETCH_COLUMN);
if (!in_array($periode, $listPeriode) && !empty($periode)) {
    array_unshift($listPeriode, $periode);
}

// 1. Hitung total pegawai
$totalPegawai = (int)$pdo->query("SELECT COUNT(*) FROM pegawai")->fetchColumn();

// 2. Hitung total dinilai pada periode aktif
$stmtCount = $pdo->prepare("SELECT COUNT(*) FROM penilaian WHERE periode = :periode");
$stmtCount->execute([':periode' => $periode]);
$totalDinilai = (int)$stmtCount->fetchColumn();

// 3. Ambil data kriteria
$kriteriaList = $pdo->query("SELECT * FROM kriteria ORDER BY kode ASC")->fetchAll();

// 4. Ambil Top 5 Ranking dari tabel hasil_spk
$stmtTop = $pdo->prepare("
    SELECT h.*, p.periode, pg.nip, pg.nama, pg.departemen, pg.jabatan,
           p.c1, p.c2, p.c3, p.c4
    FROM hasil_spk h
    JOIN penilaian p ON h.penilaian_id = p.id
    JOIN pegawai pg ON p.pegawai_id = pg.id
    WHERE p.periode = :periode
    ORDER BY h.ranking ASC
    LIMIT 5
");
$stmtTop->execute([':periode' => $periode]);
$topEmployees = $stmtTop->fetchAll();

// Jika hasil_spk belum ada untuk periode ini namun ada data penilaian, hitung otomatis
if (empty($topEmployees) && $totalDinilai > 0) {
    executeSpkCalculation($pdo, $periode);
    $stmtTop->execute([':periode' => $periode]);
    $topEmployees = $stmtTop->fetchAll();
}

// 5. Statistik Nilai Rata-rata V
$stmtAvg = $pdo->prepare("
    SELECT AVG(h.nilai_akhir_v) as avg_v, MAX(h.nilai_akhir_v) as max_v, MIN(h.nilai_akhir_v) as min_v
    FROM hasil_spk h
    JOIN penilaian p ON h.penilaian_id = p.id
    WHERE p.periode = :periode
");
$stmtAvg->execute([':periode' => $periode]);
$stats = $stmtAvg->fetch();
$avgV = $stats['avg_v'] ? round((float)$stats['avg_v'], 4) : 0;
$topEmployee = !empty($topEmployees) ? $topEmployees[0] : null;

// 6. Distribusi Rekomendasi
$stmtDist = $pdo->prepare("
    SELECT h.rekomendasi_pelatihan, COUNT(*) as jumlah
    FROM hasil_spk h
    JOIN penilaian p ON h.penilaian_id = p.id
    WHERE p.periode = :periode
    GROUP BY h.rekomendasi_pelatihan
");
$stmtDist->execute([':periode' => $periode]);
$rekomendasiDist = $stmtDist->fetchAll();

require_once __DIR__ . '/includes/header.php';
?>

<?php require_once __DIR__ . '/includes/sidebar.php'; ?>

<!-- Main Content Area -->
<div class="flex-1 flex flex-col min-w-0 overflow-y-auto">
    <?php require_once __DIR__ . '/includes/topbar.php'; ?>

    <main class="p-6 max-w-7xl w-full mx-auto space-y-6">
        <!-- Welcome Hero Banner -->
        <div class="bg-gradient-to-r from-[#1E3A8A] via-[#172554] to-[#0D9488] rounded-2xl p-6 sm:p-8 text-white shadow-xl relative overflow-hidden">
            <div class="absolute -right-10 -bottom-10 w-64 h-64 bg-teal-400/10 rounded-full blur-2xl"></div>
            <div class="relative z-10 max-w-3xl">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/10 backdrop-blur-md border border-white/20 text-xs font-medium text-teal-300 mb-3">
                    <i class="fa-solid fa-laptop-code"></i>
                    <span>Sistem Penunjang Keputusan Talenta IT • PT Indobuzz Republik Digital</span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight">
                    Evaluasi Kinerja Talenta IT &amp; Rekomendasi Pelatihan
                </h1>
                <p class="mt-2 text-sm text-blue-100/90 leading-relaxed">
                    Pengambilan keputusan objektif menggunakan kombinasi algoritma 
                    <span class="font-semibold text-teal-300">Simple Additive Weighting (SAW)</span> untuk perankingan kinerja dan 
                    <span class="font-semibold text-teal-300">Rule-Based Inference Engine</span> untuk pemetaan program peningkatan keahlian teknologi secara tepat sasaran.
                </p>
                <div class="mt-6 pt-5 border-t border-white/10 flex flex-wrap items-center justify-between gap-4">
                    <!-- Period Filter Pill -->
                    <form method="GET" action="dashboard.php" class="inline-flex items-center gap-2 bg-blue-950/60 backdrop-blur-md px-3.5 py-2 rounded-xl border border-white/15 text-xs">
                        <i class="fa-regular fa-calendar text-teal-300"></i>
                        <label for="dash-periode" class="text-blue-200 font-medium">Periode Aktif:</label>
                        <select id="dash-periode" name="periode" onchange="this.form.submit()" 
                                class="bg-blue-900/80 text-white font-semibold py-1 px-3 rounded-lg border border-blue-400/30 focus:outline-none focus:ring-2 focus:ring-teal-400 text-xs cursor-pointer">
                            <?php foreach ($listPeriode as $per): ?>
                                <option value="<?= htmlspecialchars($per) ?>" <?= $periode === $per ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($per) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </form>

                    <!-- Aligned Action Buttons Group -->
                    <div class="flex items-center gap-2.5">
                        <a href="komputasi.php?periode=<?= urlencode($periode) ?>" 
                           class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-white/10 hover:bg-white/20 border border-white/20 text-white text-xs font-semibold backdrop-blur-sm transition-all shadow-sm">
                            <i class="fa-solid fa-calculator text-teal-300"></i>
                            <span>Tahapan Perhitungan</span>
                        </a>
                        <a href="laporan.php?periode=<?= urlencode($periode) ?>" 
                           class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-[#0D9488] hover:bg-teal-600 text-white text-xs font-semibold shadow-md shadow-teal-950/25 transition-all">
                            <i class="fa-solid fa-file-invoice"></i>
                            <span>Lembar Laporan Resmi</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- 4 Stat Metric Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
            <!-- Card 1: Total Pegawai -->
            <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-sm hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Total Talenta IT</p>
                        <h3 class="text-2xl font-bold text-slate-900 mt-1"><?= $totalPegawai ?></h3>
                        <p class="text-[11px] text-teal-600 font-medium mt-1">
                            <i class="fa-solid fa-id-badge mr-1"></i> Data Pegawai Aktif
                        </p>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-blue-50 text-[#1E3A8A] flex items-center justify-center text-xl">
                        <i class="fa-solid fa-users"></i>
                    </div>
                </div>
            </div>

            <!-- Card 2: Terpenilai Periode Ini -->
            <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-sm hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Telah Dinilai</p>
                        <h3 class="text-2xl font-bold text-slate-900 mt-1"><?= $totalDinilai ?> <span class="text-xs text-slate-400 font-normal">/ <?= $totalPegawai ?></span></h3>
                        <p class="text-[11px] text-emerald-600 font-medium mt-1">
                            <i class="fa-solid fa-circle-check mr-1"></i> Periode <?= htmlspecialchars($periode) ?>
                        </p>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl">
                        <i class="fa-solid fa-clipboard-check"></i>
                    </div>
                </div>
            </div>

            <!-- Card 3: Rata-rata Skor Kinerja -->
            <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-sm hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Rata-rata Skor</p>
                        <h3 class="text-2xl font-bold text-slate-900 mt-1"><?= number_format($avgV, 4) ?></h3>
                        <p class="text-[11px] text-blue-600 font-medium mt-1">
                            <i class="fa-solid fa-chart-simple mr-1"></i> Nilai Preferensi SAW
                        </p>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-teal-50 text-[#0D9488] flex items-center justify-center text-xl">
                        <i class="fa-solid fa-scale-balanced"></i>
                    </div>
                </div>
            </div>

            <!-- Card 4: Top Performer Pegawai -->
            <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-sm hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between">
                    <div class="min-w-0 pr-2">
                        <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Peringkat Teratas (#1)</p>
                        <h3 class="text-base font-bold text-slate-900 mt-1 truncate" title="<?= htmlspecialchars($topEmployee['nama'] ?? 'Belum ada') ?>">
                            <?= htmlspecialchars($topEmployee['nama'] ?? 'Belum ada data') ?>
                        </h3>
                        <p class="text-[11px] text-amber-600 font-semibold mt-1">
                            <i class="fa-solid fa-trophy mr-1 text-amber-500"></i> Skor: <?= $topEmployee ? number_format($topEmployee['nilai_akhir_v'], 4) : '-' ?>
                        </p>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-xl shrink-0">
                        <i class="fa-solid fa-award"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Two Columns Section: Top Rank Table & Criteria Weights -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Left 2 Cols: Top 5 Ranking Table -->
            <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden flex flex-col">
                <div class="p-5 border-b border-slate-100 flex items-center justify-between">
                    <div>
                        <h2 class="font-bold text-base text-slate-900">5 Peringkat Kinerja Teratas</h2>
                        <p class="text-xs text-slate-500 mt-0.5">Talenta IT dengan nilai preferensi kinerja tertinggi pada periode ini</p>
                    </div>
                    <a href="komputasi.php" class="text-xs font-semibold text-teal-700 hover:text-teal-800 flex items-center gap-1">
                        <span>Lihat Semua</span>
                        <i class="fa-solid fa-arrow-right text-[10px]"></i>
                    </a>
                </div>

                <div class="overflow-x-auto flex-1">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-50/80 text-slate-600 font-semibold uppercase tracking-wider border-b border-slate-200">
                            <tr>
                                <th class="py-3 px-4 text-center w-14">Rank</th>
                                <th class="py-3 px-4">Pegawai</th>
                                <th class="py-3 px-4 text-center">Skor Akhir</th>
                                <th class="py-3 px-4">Rekomendasi Pelatihan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <?php if (empty($topEmployees)): ?>
                                <tr>
                                    <td colspan="4" class="py-8 text-center text-slate-400">
                                        <i class="fa-solid fa-inbox text-3xl mb-2"></i>
                                        <p>Belum ada data penilaian pada periode ini.</p>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($topEmployees as $emp): ?>
                                    <tr class="hover:bg-slate-50/80 transition-colors">
                                        <td class="py-3.5 px-4 text-center">
                                            <?php if ($emp['ranking'] == 1): ?>
                                                <span class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-amber-100 text-amber-700 font-bold shadow-sm">
                                                    <i class="fa-solid fa-crown text-xs"></i>
                                                </span>
                                            <?php elseif ($emp['ranking'] == 2): ?>
                                                <span class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-slate-200 text-slate-700 font-bold">2</span>
                                            <?php elseif ($emp['ranking'] == 3): ?>
                                                <span class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-amber-800/10 text-amber-800 font-bold">3</span>
                                            <?php else: ?>
                                                <span class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-slate-100 text-slate-600 font-semibold"><?= $emp['ranking'] ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="py-3.5 px-4">
                                            <p class="font-semibold text-slate-900"><?= htmlspecialchars($emp['nama']) ?></p>
                                            <p class="text-[11px] text-slate-500 font-mono"><?= htmlspecialchars($emp['nip']) ?> • <?= htmlspecialchars($emp['departemen']) ?></p>
                                        </td>
                                        <td class="py-3.5 px-4 text-center">
                                            <span class="font-bold text-sm text-[#1E3A8A] font-mono">
                                                <?= number_format($emp['nilai_akhir_v'], 4) ?>
                                            </span>
                                        </td>
                                        <td class="py-3.5 px-4">
                                            <?php
                                             $rec = $emp['rekomendasi_pelatihan'];
                                             $badgeStyle = 'bg-blue-50 text-blue-700 border-blue-200';
                                             if (str_contains($rec, 'Talenta Pemimpin') || str_contains($rec, 'Promosi') || str_contains($rec, 'Tech Lead')) {
                                                 $badgeStyle = 'bg-teal-50 text-teal-800 border-teal-200 font-semibold';
                                             } elseif (str_contains($rec, 'PIP') || str_contains($rec, 'Intensif')) {
                                                 $badgeStyle = 'bg-rose-50 text-rose-800 border-rose-200 font-semibold';
                                             } elseif (str_contains($rec, 'Pelatihan') || str_contains($rec, 'Sosialisasi') || str_contains($rec, 'Workshop')) {
                                                 $badgeStyle = 'bg-amber-50 text-amber-800 border-amber-200 font-medium';
                                             }
                                             ?>
                                            <span class="inline-block px-2.5 py-1 rounded-md text-[11px] border leading-tight <?= $badgeStyle ?>">
                                                <?= htmlspecialchars($rec) ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Right Col: Criteria Weights & Rule-Based Info -->
            <div class="space-y-6">
                <!-- Bobot Kriteria Card -->
                <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-sm">
                    <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                        <div>
                            <h3 class="font-bold text-sm text-slate-900">Kriteria Penilaian &amp; Bobot</h3>
                            <p class="text-[11px] text-slate-500">Bobot preferensi SAW (Total: 100%)</p>
                        </div>
                        <a href="kriteria.php" class="text-xs text-teal-700 hover:text-teal-800 font-medium">Ubah</a>
                    </div>
                    <div class="mt-4 space-y-3.5">
                        <?php foreach ($kriteriaList as $k): ?>
                            <div>
                                <div class="flex justify-between text-xs mb-1">
                                    <span class="font-medium text-slate-700">
                                        <strong class="text-teal-700"><?= $k['kode'] ?>:</strong> <?= htmlspecialchars($k['nama_kriteria']) ?>
                                    </span>
                                    <span class="font-semibold text-slate-900"><?= round($k['bobot'] * 100) ?>%</span>
                                </div>
                                <div class="w-full bg-slate-100 rounded-full h-2">
                                    <div class="bg-[#1E3A8A] h-2 rounded-full" style="width: <?= round($k['bobot'] * 100) ?>%"></div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Distribusi Rekomendasi Pelatihan Card -->
                <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-sm">
                    <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                        <div>
                            <h3 class="font-bold text-sm text-slate-900">Distribusi Rekomendasi Pelatihan</h3>
                            <p class="text-[11px] text-slate-500">Hasil diagnosis inferensi periode <?= htmlspecialchars($periode) ?></p>
                        </div>
                        <span class="text-xs font-semibold px-2 py-0.5 rounded-full bg-slate-100 text-slate-700">
                            <?= count($rekomendasiDist) ?> Kategori
                        </span>
                    </div>

                    <div class="mt-4 space-y-3">
                        <?php if (empty($rekomendasiDist)): ?>
                            <p class="text-xs text-slate-400 py-3 text-center">Belum ada hasil komputasi pada periode ini.</p>
                        <?php else: ?>
                            <?php foreach ($rekomendasiDist as $dist): ?>
                                <?php
                                $cnt = (int)$dist['jumlah'];
                                $pct = $totalDinilai > 0 ? round(($cnt / $totalDinilai) * 100) : 0;
                                $recText = $dist['rekomendasi_pelatihan'];
                                $barColor = 'bg-blue-600';
                                $badgeColor = 'bg-blue-50 text-blue-700 border-blue-200';
                                if (str_contains($recText, 'Talenta Pemimpin') || str_contains($recText, 'Promosi') || str_contains($recText, 'Tech Lead')) {
                                    $barColor = 'bg-[#0D9488]';
                                    $badgeColor = 'bg-teal-50 text-teal-800 border-teal-200';
                                } elseif (str_contains($recText, 'PIP') || str_contains($recText, 'Intensif')) {
                                    $barColor = 'bg-rose-500';
                                    $badgeColor = 'bg-rose-50 text-rose-800 border-rose-200';
                                } elseif (str_contains($recText, 'Pelatihan') || str_contains($recText, 'Sosialisasi') || str_contains($recText, 'Workshop')) {
                                    $barColor = 'bg-amber-500';
                                    $badgeColor = 'bg-amber-50 text-amber-800 border-amber-200';
                                }
                                ?>
                                <div class="space-y-1">
                                    <div class="flex items-start justify-between text-xs gap-2">
                                        <span class="text-[11px] font-medium text-slate-700 leading-tight">
                                            <?= htmlspecialchars($recText) ?>
                                        </span>
                                        <span class="inline-flex items-center gap-1 font-mono font-bold text-[11px] px-1.5 py-0.5 rounded border shrink-0 <?= $badgeColor ?>">
                                            <?= $cnt ?> Org (<?= $pct ?>%)
                                        </span>
                                    </div>
                                    <div class="w-full bg-slate-100 rounded-full h-1.5 overflow-hidden">
                                        <div class="<?= $barColor ?> h-1.5 rounded-full" style="width: <?= $pct ?>%"></div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Aturan Inferensi Rule-Based Summary Card -->
                <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-sm">
                    <h3 class="font-bold text-sm text-slate-900 mb-2">
                        <i class="fa-solid fa-code-branch text-teal-600 mr-1.5"></i> Logika Aturan Rekomendasi
                    </h3>
                    <p class="text-xs text-slate-500 mb-3">Aturan inferensi pemetaan program pengembangan talenta IT:</p>
                    <ul class="text-[11px] space-y-2 text-slate-600">
                        <li class="p-2 rounded-lg bg-teal-50 border border-teal-100 text-teal-900">
                            <strong>Rule 6 (Prestasi Tinggi):</strong> Skor &ge; 0.85 &amp; semua kriteria &ge; 75 &rarr; <em>Promosi Jabatan / Tech Lead</em>
                        </li>
                        <li class="p-2 rounded-lg bg-amber-50 border border-amber-100 text-amber-900">
                            <strong>Rule 1–4 (Fokus Keahlian):</strong> Jika ada kriteria &lt; 70 &rarr; <em>Pelatihan IT spesifik sesuai bidang</em>
                        </li>
                        <li class="p-2 rounded-lg bg-rose-50 border border-rose-100 text-rose-900">
                            <strong>Rule 5 (Pendampingan):</strong> Skor &lt; 0.60 atau lebih dari 2 kriteria &lt; 70 &rarr; <em>Performance Improvement Plan (PIP)</em>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </main>

    <?php require_once __DIR__ . '/includes/footer.php'; ?>
</div>
