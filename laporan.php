<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/helper.php';
requireAuth();

$pageTitle = 'Laporan Rekapitulasi Penilaian Kinerja';
$pageSubtitle = 'Format Resmi Rekapitulasi & Rekomendasi Pelatihan SDM';

$periode = $_GET['periode'] ?? ($_SESSION['active_periode'] ?? '2026-Semester-2');
$filterDept = trim($_GET['departemen'] ?? '');

// Ambil daftar periode
$pStmt = $pdo->query("SELECT DISTINCT periode FROM penilaian ORDER BY periode DESC");
$listPeriode = $pStmt->fetchAll(PDO::FETCH_COLUMN);
if (!in_array($periode, $listPeriode) && !empty($periode)) {
    array_unshift($listPeriode, $periode);
}

// Ambil list departemen untuk filter
$deptStmt = $pdo->query("SELECT DISTINCT departemen FROM pegawai WHERE departemen IS NOT NULL AND departemen != '' ORDER BY departemen ASC");
$departemenList = $deptStmt->fetchAll(PDO::FETCH_COLUMN);

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

require_once __DIR__ . '/includes/header.php';
?>

<?php require_once __DIR__ . '/includes/sidebar.php'; ?>

<!-- Main Content Area -->
<div class="flex-1 flex flex-col min-w-0 overflow-y-auto">
    <div class="no-print">
        <?php require_once __DIR__ . '/includes/topbar.php'; ?>
    </div>

    <main class="p-6 max-w-7xl w-full mx-auto space-y-6">
        <!-- Control Header Card (Hidden on Print) -->
        <div class="no-print bg-white rounded-2xl p-5 border border-slate-200/80 shadow-sm space-y-4">
            <!-- Top Header & Primary Export Actions -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-slate-100">
                <div>
                    <h1 class="text-xl font-bold text-slate-900">Lembar Laporan Resmi</h1>
                    <p class="text-xs text-slate-500 mt-0.5">Rekapitulasi penetapan evaluasi kinerja &amp; rekomendasi pelatihan talenta IT</p>
                </div>

                <!-- Export & Print Action Buttons Group -->
                <div class="flex items-center gap-2.5 shrink-0">
                    <a href="export_excel.php?periode=<?= urlencode($periode) ?><?= $filterDept ? '&departemen=' . urlencode($filterDept) : '' ?>" 
                       class="inline-flex items-center gap-2 py-2 px-3.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold shadow-sm transition-all" title="Unduh spreadsheet resmi">
                        <i class="fa-solid fa-file-excel text-xs"></i>
                        <span>Ekspor Excel (.xls)</span>
                    </a>
                    <a href="cetak.php?periode=<?= urlencode($periode) ?><?= $filterDept ? '&departemen=' . urlencode($filterDept) : '' ?>" target="_blank" 
                       class="inline-flex items-center gap-2 py-2 px-4 rounded-xl bg-[#1E3A8A] hover:bg-[#172554] text-white text-xs font-semibold shadow-sm shadow-blue-900/20 transition-all">
                        <i class="fa-solid fa-print text-xs"></i>
                        <span>Cetak Dokumen</span>
                    </a>
                </div>
            </div>

            <!-- Bottom Toolbar: Filters & SPK Recalculate Sync -->
            <div class="flex flex-wrap items-center justify-between gap-3">
                <form method="GET" action="laporan.php" class="flex flex-wrap items-center gap-3">
                    <!-- Filter Periode -->
                    <div class="inline-flex items-center gap-2 bg-slate-50 border border-slate-200 rounded-xl px-3 py-1.5 text-xs">
                        <i class="fa-regular fa-calendar text-slate-400"></i>
                        <label for="laporan-periode" class="text-slate-500 font-medium">Periode:</label>
                        <select id="laporan-periode" name="periode" onchange="this.form.submit()" 
                                class="bg-transparent font-semibold text-slate-800 focus:outline-none cursor-pointer text-xs">
                            <?php foreach ($listPeriode as $per): ?>
                                <option value="<?= htmlspecialchars($per) ?>" <?= $periode === $per ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($per) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Filter Divisi -->
                    <div class="inline-flex items-center gap-2 bg-slate-50 border border-slate-200 rounded-xl px-3 py-1.5 text-xs">
                        <i class="fa-solid fa-building text-slate-400"></i>
                        <select id="laporan-dept" name="departemen" onchange="this.form.submit()" 
                                class="bg-transparent font-medium text-slate-700 focus:outline-none cursor-pointer text-xs">
                            <option value="">Semua Divisi IT</option>
                            <?php foreach ($departemenList as $d): ?>
                                <option value="<?= htmlspecialchars($d) ?>" <?= $filterDept === $d ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($d) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <?php if (!empty($filterDept)): ?>
                        <a href="laporan.php?periode=<?= urlencode($periode) ?>" class="text-xs py-1.5 px-3 text-slate-600 hover:text-slate-900 bg-slate-100 hover:bg-slate-200 rounded-xl font-medium inline-flex items-center gap-1.5 transition-colors" title="Reset Filter Divisi">
                            <i class="fa-solid fa-rotate-left text-[11px]"></i>
                            <span>Reset</span>
                        </a>
                    <?php endif; ?>
                </form>

                <!-- SPK Re-sync & Status Badge -->
                <div class="flex items-center gap-3">
                    <a href="actions/spk_action.php?action=calculate&periode=<?= urlencode($periode) ?>&redirect=laporan.php<?= $filterDept ? '?periode=' . urlencode($periode) . '&departemen=' . urlencode($filterDept) : '' ?>" 
                       class="inline-flex items-center gap-1.5 py-1.5 px-3 rounded-xl border border-slate-200 bg-slate-50 hover:bg-slate-100 text-slate-700 text-xs font-medium transition-all" title="Sinkronkan perankingan dan rekomendasi">
                        <i class="fa-solid fa-rotate text-xs text-teal-600"></i>
                        <span>Hitung Ulang SPK</span>
                    </a>
                    <span class="hidden sm:inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-slate-50 border border-slate-200 text-xs font-semibold text-slate-600">
                        <i class="fa-solid fa-file-circle-check text-emerald-600"></i>
                        <span><?= count($laporanList) ?> Talenta IT</span>
                    </span>
                </div>
            </div>
        </div>

        <!-- Formal Report Sheet (White Paper Container) -->
        <div class="bg-white rounded-2xl p-8 sm:p-12 border border-slate-200 shadow-md printable-area max-w-5xl mx-auto space-y-6">
            <!-- Kop Surat Resmi Perusahaan -->
            <div class="border-b-4 border-double border-slate-800 pb-5 flex items-center justify-between gap-6">
                <div class="flex items-center gap-4">
                    <div class="w-16 h-16 rounded-xl bg-[#1E3A8A] text-white flex items-center justify-center text-2xl font-black shrink-0 tracking-wider shadow-sm">
                        IB
                    </div>
                    <div>
                        <h2 class="text-xl font-extrabold text-[#1E3A8A] tracking-wider uppercase leading-tight">
                            PT INDOBUZZ REPUBLIK DIGITAL
                        </h2>
                        <h3 class="text-xs font-bold text-slate-700 tracking-wide uppercase mt-0.5">
                            DIVISI HUMAN RESOURCES & PEOPLE OPERATIONS
                        </h3>
                        <p class="text-[11px] text-slate-500 mt-1">
                            Digital Marketing & IT Solutions Agency • Website: indobuzz.co.id • Email: info@indobuzz.co.id • Telp: (0260) 412-890
                        </p>
                    </div>
                </div>
                <div class="text-right text-[11px] text-slate-500 hidden sm:block">
                    <span class="font-bold text-slate-800 block text-xs">SPK-HRD/IB/<?= date('Y/m') ?></span>
                    <span>Dokumen Resmi Internal</span>
                </div>
            </div>

            <!-- Judul Dokumen -->
            <div class="text-center py-2 space-y-1">
                <h4 class="text-base font-extrabold text-slate-900 uppercase tracking-wide">
                    LEMBAR REKAPITULASI EVALUASI KINERJA & REKOMENDASI PELATIHAN<?= !empty($filterDept) ? ' - ' . strtoupper(htmlspecialchars($filterDept)) : '' ?>
                </h4>
                <div class="flex items-center justify-center gap-3 text-xs text-slate-600 font-medium">
                    <span>Periode: <strong class="text-slate-900 font-mono"><?= htmlspecialchars($periode) ?></strong></span>
                    <?php if (!empty($filterDept)): ?>
                        <span>•</span>
                        <span>Divisi: <strong class="text-slate-900"><?= htmlspecialchars($filterDept) ?></strong></span>
                    <?php endif; ?>
                    <span>•</span>
                    <span>Metode Evaluasi: <strong>Simple Additive Weighting (SAW) & Rule-Based</strong></span>
                </div>
            </div>

            <!-- Ringkasan Metadata -->
            <div class="bg-slate-50 rounded-xl p-4 border border-slate-200 text-xs text-slate-700 grid grid-cols-2 sm:grid-cols-4 gap-4">
                <div>
                    <span class="text-slate-400 block text-[10px] uppercase font-semibold">Total Pegawai</span>
                    <strong class="text-sm font-bold text-slate-900"><?= count($laporanList) ?> Orang</strong>
                </div>
                <div>
                    <span class="text-slate-400 block text-[10px] uppercase font-semibold">Peringkat 1 (Terbaik)</span>
                    <strong class="text-sm font-bold text-teal-700"><?= !empty($laporanList) ? htmlspecialchars($laporanList[0]['nama']) : '-' ?></strong>
                </div>
                <div>
                    <span class="text-slate-400 block text-[10px] uppercase font-semibold">Skor Tertinggi</span>
                    <strong class="text-sm font-bold text-[#1E3A8A] font-mono"><?= !empty($laporanList) ? number_format($laporanList[0]['nilai_akhir_v'], 4) : '-' ?></strong>
                </div>
                <div>
                    <span class="text-slate-400 block text-[10px] uppercase font-semibold">Tanggal Dokumen</span>
                    <strong class="text-sm font-bold text-slate-900"><?= date('d F Y') ?></strong>
                </div>
            </div>

            <!-- Tabel Hasil Resmi -->
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs border border-slate-300 border-collapse">
                    <thead class="bg-slate-100 text-slate-800 font-bold uppercase text-[11px] border-b border-slate-300">
                        <tr>
                            <th class="py-2.5 px-3 border border-slate-300 text-center w-12">Peringkat</th>
                            <th class="py-2.5 px-3 border border-slate-300">NIP & Nama Pegawai</th>
                            <th class="py-2.5 px-3 border border-slate-300">Divisi & Jabatan</th>
                            <th class="py-2.5 px-3 border border-slate-300 text-center w-14" title="Presensi & Standup (Bobot 20%)">C1 (20%)</th>
                            <th class="py-2.5 px-3 border border-slate-300 text-center w-14" title="Target KPI & Delivery (Bobot 35%)">C2 (35%)</th>
                            <th class="py-2.5 px-3 border border-slate-300 text-center w-14" title="Kualitas Kode & SOP (Bobot 25%)">C3 (25%)</th>
                            <th class="py-2.5 px-3 border border-slate-300 text-center w-14" title="Kolaborasi Tim (Bobot 20%)">C4 (20%)</th>
                            <th class="py-2.5 px-3 border border-slate-300 text-center w-20">Skor Akhir</th>
                            <th class="py-2.5 px-3 border border-slate-300">Rekomendasi Pelatihan & Tindak Lanjut</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        <?php if (empty($laporanList)): ?>
                            <tr>
                                <td colspan="9" class="py-8 text-center text-slate-400">
                                    <i class="fa-regular fa-folder-open text-2xl mb-1 text-slate-300 block"></i>
                                    Tidak ada data untuk periode / departemen yang dipilih.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($laporanList as $item): ?>
                                <?php
                                $rec = $item['rekomendasi_pelatihan'];
                                $badgeStyle = 'bg-blue-50 text-blue-800 border-blue-200';
                                $icon = 'fa-circle-info';
                                if (str_contains($rec, 'Tech Lead') || str_contains($rec, 'Promosi') || str_contains($rec, 'Principal')) {
                                    $badgeStyle = 'bg-teal-50 text-teal-900 border-teal-300 font-semibold';
                                    $icon = 'fa-medal text-teal-600';
                                } elseif (str_contains($rec, 'PIP') || str_contains($rec, 'Intensif')) {
                                    $badgeStyle = 'bg-rose-50 text-rose-900 border-rose-300 font-semibold';
                                    $icon = 'fa-triangle-exclamation text-rose-600';
                                } elseif (str_contains($rec, 'Pelatihan') || str_contains($rec, 'Workshop')) {
                                    $badgeStyle = 'bg-amber-50 text-amber-900 border-amber-300 font-medium';
                                    $icon = 'fa-user-gear text-amber-600';
                                }
                                ?>
                                <tr class="hover:bg-slate-50 <?= $item['ranking'] == 1 ? 'bg-amber-50/20' : '' ?>">
                                    <td class="py-2.5 px-3 border border-slate-300 text-center font-bold font-mono">
                                        <?php if ($item['ranking'] == 1): ?>
                                            <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-amber-100 text-amber-700 font-bold text-xs ring-1 ring-amber-300">
                                                1
                                            </span>
                                        <?php else: ?>
                                            <?= $item['ranking'] ?>
                                        <?php endif; ?>
                                    </td>
                                    <td class="py-2.5 px-3 border border-slate-300">
                                        <div class="font-bold text-slate-900"><?= htmlspecialchars($item['nama']) ?></div>
                                        <div class="text-[10px] text-slate-500 font-mono"><?= htmlspecialchars($item['nip']) ?></div>
                                    </td>
                                    <td class="py-2.5 px-3 border border-slate-300 text-slate-700">
                                        <div class="font-medium"><?= htmlspecialchars($item['departemen']) ?></div>
                                        <div class="text-[10px] text-slate-400"><?= htmlspecialchars($item['jabatan']) ?></div>
                                    </td>
                                    <td class="py-2.5 px-3 border border-slate-300 text-center font-mono <?= $item['c1'] < 70 ? 'text-rose-600 font-bold' : '' ?>">
                                        <?= number_format($item['c1'], 0) ?>
                                    </td>
                                    <td class="py-2.5 px-3 border border-slate-300 text-center font-mono <?= $item['c2'] < 70 ? 'text-rose-600 font-bold' : '' ?>">
                                        <?= number_format($item['c2'], 0) ?>
                                    </td>
                                    <td class="py-2.5 px-3 border border-slate-300 text-center font-mono <?= $item['c3'] < 70 ? 'text-rose-600 font-bold' : '' ?>">
                                        <?= number_format($item['c3'], 0) ?>
                                    </td>
                                    <td class="py-2.5 px-3 border border-slate-300 text-center font-mono <?= $item['c4'] < 70 ? 'text-rose-600 font-bold' : '' ?>">
                                        <?= number_format($item['c4'], 0) ?>
                                    </td>
                                    <td class="py-2.5 px-3 border border-slate-300 text-center font-mono font-bold text-[#1E3A8A]">
                                        <?= number_format($item['nilai_akhir_v'], 4) ?>
                                    </td>
                                    <td class="py-2.5 px-3 border border-slate-300">
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg border text-[11px] leading-tight <?= $badgeStyle ?>">
                                            <i class="fa-solid <?= $icon ?> text-[10px] shrink-0"></i>
                                            <span><?= htmlspecialchars($item['rekomendasi_pelatihan']) ?></span>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Keterangan Bobot Kriteria IT -->
            <div class="text-[11px] text-slate-500 leading-relaxed bg-slate-50 p-3 rounded-xl border border-slate-200">
                <strong>Catatan Kriteria Penilaian:</strong> C1 = Kedisiplinan & Presensi (20%), C2 = Target KPI & Delivery (35%), C3 = Kualitas Kode & SOP IT (25%), C4 = Kolaborasi Tim (20%).
                Nilai capaian di bawah 70 otomatis direkomendasikan untuk program pelatihan teknis spesifik.
            </div>

            <!-- Tanda Tangan & Pengesahan Dokumen -->
            <div class="pt-8 grid grid-cols-2 text-center text-xs text-slate-800">
                <div class="space-y-16">
                    <p>
                        Diverifikasi oleh,<br>
                        <strong>People Operations Specialist</strong>
                    </p>
                    <div>
                        <p class="font-bold underline text-slate-900"><?= htmlspecialchars($currentUser['nama_lengkap'] ?? 'Siti Rahmawati, S.Kom') ?></p>
                        <p class="text-[11px] text-slate-500">NIP: IB-HRD-002</p>
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
                        <p class="text-[11px] text-slate-500">Direktur Utama</p>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <div class="no-print">
        <?php require_once __DIR__ . '/includes/footer.php'; ?>
    </div>
</div>
