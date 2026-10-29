<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/helper.php';
requireAuth();

$pageTitle = 'Input Penilaian Kinerja Pegawai';
$pageSubtitle = 'Evaluasi Capaian Aktual Kinerja Talenta IT';

$periode = $_GET['periode'] ?? ($_SESSION['active_periode'] ?? '2026-Semester-2');
$_SESSION['active_periode'] = $periode;
$filterDept = trim($_GET['departemen'] ?? '');
$search = trim($_GET['search'] ?? '');

// Ambil daftar unik seluruh periode yang pernah diinput
$pStmt = $pdo->query("SELECT DISTINCT periode FROM penilaian ORDER BY periode DESC");
$listPeriode = $pStmt->fetchAll(PDO::FETCH_COLUMN);
if (!in_array($periode, $listPeriode) && !empty($periode)) {
    array_unshift($listPeriode, $periode);
}

// Ambil list departemen
$deptStmt = $pdo->query("SELECT DISTINCT departemen FROM pegawai WHERE departemen IS NOT NULL AND departemen != '' ORDER BY departemen ASC");
$departemenList = $deptStmt->fetchAll(PDO::FETCH_COLUMN);

// Ambil kriteria untuk header kolom
$kriteriaList = $pdo->query("SELECT * FROM kriteria ORDER BY kode ASC")->fetchAll();

// Ambil data penilaian pada periode aktif dengan filter
$sqlPenilaian = "
    SELECT p.*, pg.nip, pg.nama, pg.departemen, pg.jabatan
    FROM penilaian p
    JOIN pegawai pg ON p.pegawai_id = pg.id
    WHERE p.periode = :periode
";
$params = [':periode' => $periode];

if (!empty($filterDept)) {
    $sqlPenilaian .= " AND pg.departemen = :dept";
    $params[':dept'] = $filterDept;
}
if (!empty($search)) {
    $sqlPenilaian .= " AND (pg.nama LIKE :search OR pg.nip LIKE :search)";
    $params[':search'] = "%$search%";
}

$sqlPenilaian .= " ORDER BY pg.nama ASC";
$stmt = $pdo->prepare($sqlPenilaian);
$stmt->execute($params);
$penilaianList = $stmt->fetchAll();

// Ambil pegawai yang BELUM dinilai pada periode ini (untuk pilihan di modal tambah)
$stmtBelum = $pdo->prepare("
    SELECT * FROM pegawai 
    WHERE id NOT IN (SELECT pegawai_id FROM penilaian WHERE periode = :periode)
    ORDER BY nama ASC
");
$stmtBelum->execute([':periode' => $periode]);
$pegawaiBelumDinilai = $stmtBelum->fetchAll();

require_once __DIR__ . '/includes/header.php';
?>

<?php require_once __DIR__ . '/includes/sidebar.php'; ?>

<!-- Main Content Area -->
<div class="flex-1 flex flex-col min-w-0 overflow-y-auto">
    <?php require_once __DIR__ . '/includes/topbar.php'; ?>

    <main class="p-6 max-w-7xl w-full mx-auto space-y-6">
        <!-- Page Header & Action Buttons Card -->
        <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-xl font-bold text-slate-900">Penilaian Kinerja Talenta IT</h1>
                <p class="text-xs text-slate-500 mt-0.5">Input capaian kinerja aktual per kriteria dengan skala nilai 1 s.d. 100</p>
            </div>

            <!-- Primary Action Buttons Group -->
            <div class="flex items-center gap-2.5 shrink-0">
                <a href="actions/spk_action.php?action=calculate&periode=<?= urlencode($periode) ?>&redirect=komputasi.php" 
                   class="inline-flex items-center gap-2 py-2 px-3.5 rounded-xl border border-teal-200 bg-teal-50/50 hover:bg-teal-100/60 text-[#0D9488] text-xs font-semibold shadow-xs transition-all">
                    <i class="fa-solid fa-calculator text-xs"></i>
                    <span>Hitung SPK</span>
                </a>
                <button type="button" onclick="openModal('modal-tambah-penilaian')" 
                        class="inline-flex items-center gap-2 py-2 px-4 rounded-xl bg-[#1E3A8A] hover:bg-[#172554] text-white text-xs font-semibold shadow-sm shadow-blue-900/20 transition-all">
                    <i class="fa-solid fa-plus text-xs"></i>
                    <span>Input Penilaian</span>
                </button>
            </div>
        </div>

        <!-- Filter & Search Toolbar Card -->
        <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-sm">
            <form method="GET" action="penilaian.php" class="flex flex-wrap items-center justify-between gap-3">
                <div class="flex flex-wrap items-center gap-3">
                    <!-- Filter Periode -->
                    <div class="inline-flex items-center gap-2 bg-slate-50 border border-slate-200 rounded-xl px-3 py-1.5 text-xs">
                        <i class="fa-regular fa-calendar text-slate-400"></i>
                        <label for="periode-select" class="text-slate-500 font-medium">Periode:</label>
                        <select id="periode-select" name="periode" onchange="this.form.submit()" 
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
                        <select name="departemen" onchange="this.form.submit()" class="bg-transparent font-medium text-slate-700 focus:outline-none cursor-pointer text-xs">
                            <option value="">Semua Divisi IT</option>
                            <?php foreach ($departemenList as $d): ?>
                                <option value="<?= htmlspecialchars($d) ?>" <?= $filterDept === $d ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($d) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Search Input -->
                    <div class="relative">
                        <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Cari NIP / Nama..." 
                               class="text-xs py-1.5 pl-8 pr-3 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#1E3A8A] w-44 sm:w-56 text-slate-800">
                        <i class="fa-solid fa-magnifying-glass absolute left-2.5 top-2 text-slate-400 text-xs"></i>
                    </div>

                    <?php if (!empty($filterDept) || !empty($search)): ?>
                        <a href="penilaian.php?periode=<?= urlencode($periode) ?>" class="text-xs py-1.5 px-3 text-slate-600 hover:text-slate-900 bg-slate-100 hover:bg-slate-200 rounded-xl font-medium inline-flex items-center gap-1.5 transition-colors" title="Reset Filter">
                            <i class="fa-solid fa-rotate-left text-[11px]"></i>
                            <span>Reset</span>
                        </a>
                    <?php endif; ?>
                </div>

                <!-- Total Count Badge -->
                <div class="hidden sm:inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-slate-50 border border-slate-200 text-xs font-semibold text-slate-600">
                    <i class="fa-solid fa-users text-teal-600"></i>
                    <span>Terdata: <?= count($penilaianList) ?> Talenta IT</span>
                </div>
            </form>
        </div>

        <!-- Table of Penilaian -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
            <div class="p-4 sm:p-5 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                <div>
                    <h2 class="font-bold text-sm text-slate-900">Daftar Capaian Kinerja — Periode <?= htmlspecialchars($periode) ?></h2>
                    <p class="text-xs text-slate-500 mt-0.5">Nilai capaian aktual kriteria evaluasi talenta IT sebelum proses komputasi</p>
                </div>
                <div class="sm:hidden text-xs font-medium text-slate-500">
                    Total: <?= count($penilaianList) ?>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 text-slate-600 font-semibold uppercase tracking-wider border-b border-slate-200">
                        <tr>
                            <th class="py-3 px-4 text-center w-12">No</th>
                            <th class="py-3 px-4">Pegawai</th>
                            <th class="py-3 px-4">Divisi</th>
                            <?php foreach ($kriteriaList as $k): ?>
                                <th class="py-3 px-4 text-center" title="<?= htmlspecialchars($k['nama_kriteria']) ?>">
                                    <span class="font-bold text-[#1E3A8A] font-mono"><?= $k['kode'] ?></span>
                                    <span class="block text-[10px] text-slate-400 font-normal lowercase">(<?= round($k['bobot']*100) ?>%)</span>
                                </th>
                            <?php endforeach; ?>
                            <th class="py-3 px-4 text-center w-24">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php if (empty($penilaianList)): ?>
                            <tr>
                                <td colspan="<?= 4 + count($kriteriaList) ?>" class="py-12 text-center text-slate-400">
                                    <i class="fa-regular fa-folder-open text-3xl mb-2 text-slate-300"></i>
                                    <p class="font-medium">Belum ada penilaian untuk periode <?= htmlspecialchars($periode) ?>.</p>
                                    <p class="text-[11px] text-slate-400 mt-1">Klik tombol <strong>Input Penilaian Pegawai</strong> di atas untuk menambahkan data.</p>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($penilaianList as $idx => $p): ?>
                                <tr class="hover:bg-slate-50/80 transition-colors">
                                    <td class="py-3.5 px-4 text-center text-slate-400 font-medium">
                                        <?= $idx + 1 ?>
                                    </td>
                                    <td class="py-3.5 px-4">
                                        <p class="font-semibold text-slate-900"><?= htmlspecialchars($p['nama']) ?></p>
                                        <p class="text-[11px] text-slate-500 font-mono"><?= htmlspecialchars($p['nip']) ?></p>
                                    </td>
                                    <td class="py-3.5 px-4 text-slate-600">
                                        <?= htmlspecialchars($p['departemen']) ?>
                                    </td>

                                    <!-- C1 -->
                                    <td class="py-3.5 px-4 text-center">
                                        <span class="inline-block px-2.5 py-1 rounded-lg font-mono font-bold text-xs <?= $p['c1'] < 70 ? 'bg-rose-50 text-rose-700 border border-rose-200' : 'bg-slate-100 text-slate-800' ?>">
                                            <?= number_format($p['c1'], 1) ?>
                                        </span>
                                    </td>

                                    <!-- C2 -->
                                    <td class="py-3.5 px-4 text-center">
                                        <span class="inline-block px-2.5 py-1 rounded-lg font-mono font-bold text-xs <?= $p['c2'] < 70 ? 'bg-rose-50 text-rose-700 border border-rose-200' : 'bg-slate-100 text-slate-800' ?>">
                                            <?= number_format($p['c2'], 1) ?>
                                        </span>
                                    </td>

                                    <!-- C3 -->
                                    <td class="py-3.5 px-4 text-center">
                                        <span class="inline-block px-2.5 py-1 rounded-lg font-mono font-bold text-xs <?= $p['c3'] < 70 ? 'bg-rose-50 text-rose-700 border border-rose-200' : 'bg-slate-100 text-slate-800' ?>">
                                            <?= number_format($p['c3'], 1) ?>
                                        </span>
                                    </td>

                                    <!-- C4 -->
                                    <td class="py-3.5 px-4 text-center">
                                        <span class="inline-block px-2.5 py-1 rounded-lg font-mono font-bold text-xs <?= $p['c4'] < 70 ? 'bg-rose-50 text-rose-700 border border-rose-200' : 'bg-slate-100 text-slate-800' ?>">
                                            <?= number_format($p['c4'], 1) ?>
                                        </span>
                                    </td>

                                    <td class="py-3.5 px-4 text-center">
                                        <div class="flex items-center justify-center gap-1.5">
                                            <button type="button" 
                                                    onclick="editPenilaian(<?= htmlspecialchars(json_encode($p)) ?>)" 
                                                    title="Ubah Nilai"
                                                    class="p-1.5 rounded-lg text-blue-600 hover:bg-blue-50 border border-transparent hover:border-blue-200 transition-colors">
                                                <i class="fa-solid fa-pen-to-square text-xs"></i>
                                            </button>
                                            
                                            <a href="actions/penilaian_action.php?action=delete&id=<?= $p['id'] ?>&periode=<?= urlencode($periode) ?>" 
                                               onclick="return confirm('Hapus penilaian untuk pegawai <?= htmlspecialchars(addslashes($p['nama'])) ?>?');" 
                                               title="Hapus Penilaian"
                                               class="p-1.5 rounded-lg text-rose-600 hover:bg-rose-50 border border-transparent hover:border-rose-200 transition-colors">
                                                <i class="fa-solid fa-trash-can text-xs"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Table Footer Legend -->
            <div class="py-3 px-5 border-t border-slate-100 bg-slate-50/50 flex flex-wrap items-center justify-between gap-3 text-xs text-slate-500">
                <div class="flex items-center gap-3">
                    <span class="flex items-center gap-1.5">
                        <span class="w-2.5 h-2.5 rounded bg-rose-100 border border-rose-300"></span>
                        <span class="text-rose-700 font-medium">Merah: Nilai &lt; 70 (Memicu Aturan Rekomendasi Pelatihan)</span>
                    </span>
                    <span class="flex items-center gap-1.5">
                        <span class="w-2.5 h-2.5 rounded bg-slate-200"></span>
                        <span>Normal: Nilai &ge; 70</span>
                    </span>
                </div>
                <span>Skala Pengukuran: <strong>1 - 100</strong></span>
            </div>
        </div>
    </main>

    <?php require_once __DIR__ . '/includes/footer.php'; ?>
</div>

<!-- Modal Tambah Penilaian -->
<div id="modal-tambah-penilaian" role="dialog" class="fixed inset-0 z-50 bg-slate-900/50 backdrop-blur-xs items-center justify-center p-4 hidden">
    <div class="bg-white rounded-2xl shadow-2xl max-w-lg w-full border border-slate-200 overflow-hidden transform transition-all">
        <div class="px-6 py-4 bg-[#1E3A8A] text-white flex items-center justify-between">
            <h3 class="font-bold text-sm flex items-center gap-2">
                <i class="fa-solid fa-pen-to-square text-teal-300"></i>
                <span>Input Penilaian Kinerja Pegawai</span>
            </h3>
            <button type="button" onclick="closeModal('modal-tambah-penilaian')" class="text-blue-200 hover:text-white">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <form action="actions/penilaian_action.php" method="POST" class="p-6 space-y-4">
            <input type="hidden" name="action" value="create">

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Periode Evaluasi</label>
                <input type="text" name="periode" value="<?= htmlspecialchars($periode) ?>" list="modal-periodes" required 
                       class="w-full text-xs py-2 px-3 bg-slate-50 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#1E3A8A] font-medium font-mono">
                <datalist id="modal-periodes">
                    <?php foreach ($listPeriode as $lp): ?>
                        <option value="<?= htmlspecialchars($lp) ?>">
                    <?php endforeach; ?>
                    <option value="2027-Semester-1">
                    <option value="2027-Semester-2">
                </datalist>
                <p class="text-[10px] text-slate-400 mt-0.5">Pilih periode atau ketik nama periode baru</p>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Pilih Pegawai (Kandidat)</label>
                <?php if (empty($pegawaiBelumDinilai)): ?>
                    <div class="p-3 bg-amber-50 rounded-xl border border-amber-200 text-xs text-amber-800">
                        Seluruh pegawai yang terdaftar sudah memiliki nilai pada periode <strong><?= htmlspecialchars($periode) ?></strong>. 
                        Anda dapat mengedit nilai yang ada pada tabel utama.
                    </div>
                <?php else: ?>
                    <select name="pegawai_id" required class="w-full text-xs py-2.5 px-3 bg-slate-50 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#1E3A8A]">
                        <option value="">-- Pilih Pegawai --</option>
                        <?php foreach ($pegawaiBelumDinilai as $pb): ?>
                            <option value="<?= $pb['id'] ?>">
                                <?= htmlspecialchars($pb['nama']) ?> (<?= htmlspecialchars($pb['nip']) ?>) — <?= htmlspecialchars($pb['departemen']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                <?php endif; ?>
            </div>

            <div class="grid grid-cols-2 gap-3 pt-2 border-t border-slate-100">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">
                        C1: Kedisiplinan & Presensi (1-100)
                    </label>
                    <input type="number" step="0.5" min="1" max="100" name="c1" required placeholder="1 - 100"
                           class="w-full text-xs py-2.5 px-3 bg-slate-50 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#1E3A8A] font-mono">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">
                        C2: Target KPI (1-100)
                    </label>
                    <input type="number" step="0.5" min="1" max="100" name="c2" required placeholder="1 - 100"
                           class="w-full text-xs py-2.5 px-3 bg-slate-50 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#1E3A8A] font-mono">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">
                        C3: K3 & Prosedur SOP (1-100)
                    </label>
                    <input type="number" step="0.5" min="1" max="100" name="c3" required placeholder="1 - 100"
                           class="w-full text-xs py-2.5 px-3 bg-slate-50 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#1E3A8A] font-mono">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">
                        C4: Kepemimpinan & Kerjasama (1-100)
                    </label>
                    <input type="number" step="0.5" min="1" max="100" name="c4" required placeholder="1 - 100"
                           class="w-full text-xs py-2.5 px-3 bg-slate-50 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#1E3A8A] font-mono">
                </div>
            </div>

            <p class="text-[11px] text-slate-500 italic">
                *Catatan: Nilai kriteria di bawah 70 akan otomatis didiagnosis oleh Rule-Based Engine untuk penentuan program pelatihan spesifik.
            </p>

            <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2">
                <button type="button" onclick="closeModal('modal-tambah-penilaian')" 
                        class="px-4 py-2 rounded-xl border border-slate-300 text-xs font-medium text-slate-700 hover:bg-slate-50">
                    Batal
                </button>
                <button type="submit" <?= empty($pegawaiBelumDinilai) ? 'disabled' : '' ?>
                        class="px-4 py-2 rounded-xl bg-[#1E3A8A] hover:bg-[#172554] text-xs font-semibold text-white shadow-sm disabled:opacity-50">
                    Simpan Penilaian
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Edit Penilaian -->
<div id="modal-edit-penilaian" role="dialog" class="fixed inset-0 z-50 bg-slate-900/50 backdrop-blur-xs items-center justify-center p-4 hidden">
    <div class="bg-white rounded-2xl shadow-2xl max-w-lg w-full border border-slate-200 overflow-hidden transform transition-all">
        <div class="px-6 py-4 bg-[#1E3A8A] text-white flex items-center justify-between">
            <h3 class="font-bold text-sm flex items-center gap-2">
                <i class="fa-solid fa-pen-to-square text-teal-300"></i>
                <span>Ubah Nilai Kinerja: <span id="edit-penilaian-nama" class="text-teal-300 font-semibold"></span></span>
            </h3>
            <button type="button" onclick="closeModal('modal-edit-penilaian')" class="text-blue-200 hover:text-white">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <form action="actions/penilaian_action.php" method="POST" class="p-6 space-y-4">
            <input type="hidden" name="action" value="update">
            <input type="hidden" name="id" id="edit-penilaian-id">
            <input type="hidden" name="periode" value="<?= htmlspecialchars($periode) ?>">

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">C1: Kedisiplinan & Presensi</label>
                    <input type="number" step="0.5" min="1" max="100" name="c1" id="edit-c1" required 
                           class="w-full text-xs py-2.5 px-3 bg-slate-50 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#1E3A8A] font-mono">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">C2: Target Kerja (KPI)</label>
                    <input type="number" step="0.5" min="1" max="100" name="c2" id="edit-c2" required 
                           class="w-full text-xs py-2.5 px-3 bg-slate-50 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#1E3A8A] font-mono">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">C3: Kedisiplinan K3 & SOP</label>
                    <input type="number" step="0.5" min="1" max="100" name="c3" id="edit-c3" required 
                           class="w-full text-xs py-2.5 px-3 bg-slate-50 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#1E3A8A] font-mono">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">C4: Kepemimpinan & Kerjasama</label>
                    <input type="number" step="0.5" min="1" max="100" name="c4" id="edit-c4" required 
                           class="w-full text-xs py-2.5 px-3 bg-slate-50 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#1E3A8A] font-mono">
                </div>
            </div>

            <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2">
                <button type="button" onclick="closeModal('modal-edit-penilaian')" 
                        class="px-4 py-2 rounded-xl border border-slate-300 text-xs font-medium text-slate-700 hover:bg-slate-50">
                    Batal
                </button>
                <button type="submit" 
                        class="px-4 py-2 rounded-xl bg-[#0D9488] hover:bg-teal-700 text-xs font-semibold text-white shadow-sm">
                    Simpan Perubahan Nilai
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function editPenilaian(p) {
    document.getElementById('edit-penilaian-id').value = p.id;
    document.getElementById('edit-penilaian-nama').innerText = p.nama;
    document.getElementById('edit-c1').value = p.c1;
    document.getElementById('edit-c2').value = p.c2;
    document.getElementById('edit-c3').value = p.c3;
    document.getElementById('edit-c4').value = p.c4;
    openModal('modal-edit-penilaian');
}
</script>
