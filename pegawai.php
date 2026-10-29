<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/helper.php';
requireAuth();

$pageTitle = 'Master Data Pegawai';
$pageSubtitle = 'Pengelolaan Daftar Pegawai & Struktur Divisi';

// Ambil list unik departemen untuk filter dropdown
$deptStmt = $pdo->query("SELECT DISTINCT departemen FROM pegawai WHERE departemen IS NOT NULL AND departemen != '' ORDER BY departemen ASC");
$departemenList = $deptStmt->fetchAll(PDO::FETCH_COLUMN);

// Filter & Search Handling
$search = trim($_GET['search'] ?? '');
$filterDept = trim($_GET['departemen'] ?? '');

$sql = "SELECT * FROM pegawai WHERE 1=1";
$params = [];

if (!empty($search)) {
    $sql .= " AND (nip LIKE :search OR nama LIKE :search OR jabatan LIKE :search)";
    $params[':search'] = "%$search%";
}

if (!empty($filterDept)) {
    $sql .= " AND departemen = :dept";
    $params[':dept'] = $filterDept;
}

$sql .= " ORDER BY nama ASC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$pegawaiList = $stmt->fetchAll();

require_once __DIR__ . '/includes/header.php';
?>

<?php require_once __DIR__ . '/includes/sidebar.php'; ?>

<!-- Main Content Area -->
<div class="flex-1 flex flex-col min-w-0 overflow-y-auto">
    <?php require_once __DIR__ . '/includes/topbar.php'; ?>

    <main class="p-6 max-w-7xl w-full mx-auto space-y-6">
        <!-- Page Header & Action Button Card -->
        <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-xl font-bold text-slate-900">Daftar Talenta IT</h1>
                <p class="text-xs text-slate-500 mt-0.5">Pengelolaan master data talenta IT, unit kerja, dan jabatan</p>
            </div>
            
            <!-- Primary Action Button -->
            <button type="button" onclick="openModal('modal-tambah-pegawai')" 
                    class="inline-flex items-center gap-2 py-2 px-4 rounded-xl bg-[#1E3A8A] hover:bg-[#172554] text-white text-xs font-semibold shadow-sm shadow-blue-900/20 transition-all shrink-0">
                <i class="fa-solid fa-user-plus text-xs"></i>
                <span>Tambah Pegawai Baru</span>
            </button>
        </div>

        <!-- Filter & Search Toolbar Card -->
        <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-sm">
            <form method="GET" action="pegawai.php" class="flex flex-wrap items-center justify-between gap-3">
                <div class="flex flex-wrap items-center gap-3">
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
                        <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Cari NIP / Nama / Jabatan..." 
                               class="text-xs py-1.5 pl-8 pr-3 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#1E3A8A] w-48 sm:w-64 text-slate-800">
                        <i class="fa-solid fa-magnifying-glass absolute left-2.5 top-2 text-slate-400 text-xs"></i>
                    </div>

                    <?php if (!empty($search) || !empty($filterDept)): ?>
                        <a href="pegawai.php" class="text-xs py-1.5 px-3 text-slate-600 hover:text-slate-900 bg-slate-100 hover:bg-slate-200 rounded-xl font-medium inline-flex items-center gap-1.5 transition-colors" title="Reset Filter">
                            <i class="fa-solid fa-rotate-left text-[11px]"></i>
                            <span>Reset</span>
                        </a>
                    <?php endif; ?>
                </div>

                <!-- Total Count Badge -->
                <div class="hidden sm:inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-slate-50 border border-slate-200 text-xs font-semibold text-slate-600">
                    <i class="fa-solid fa-id-badge text-teal-600"></i>
                    <span>Total: <?= count($pegawaiList) ?> Talenta IT</span>
                </div>
            </form>
        </div>

        <!-- Table Card -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50/80 text-slate-600 font-semibold uppercase tracking-wider border-b border-slate-200">
                        <tr>
                            <th class="py-3.5 px-4 text-center w-12">No</th>
                            <th class="py-3.5 px-4">NIP</th>
                            <th class="py-3.5 px-4">Nama Lengkap</th>
                            <th class="py-3.5 px-4">Divisi / Departemen</th>
                            <th class="py-3.5 px-4">Jabatan</th>
                            <th class="py-3.5 px-4 text-center w-28">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php if (empty($pegawaiList)): ?>
                            <tr>
                                <td colspan="6" class="py-10 text-center text-slate-400">
                                    <i class="fa-solid fa-user-slash text-3xl mb-2 text-slate-300"></i>
                                    <p class="font-medium">Data pegawai tidak ditemukan.</p>
                                    <p class="text-[11px] mt-1 text-slate-400">Silakan ubah filter pencarian atau tambahkan pegawai baru.</p>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($pegawaiList as $idx => $p): ?>
                                <tr class="hover:bg-slate-50/80 transition-colors">
                                    <td class="py-3 px-4 text-center text-slate-400 font-medium">
                                        <?= $idx + 1 ?>
                                    </td>
                                    <td class="py-3 px-4 font-mono font-semibold text-slate-700">
                                        <?= htmlspecialchars($p['nip']) ?>
                                    </td>
                                    <td class="py-3 px-4">
                                        <div class="flex items-center gap-2.5">
                                            <div class="w-7 h-7 rounded-full bg-blue-100 text-[#1E3A8A] flex items-center justify-center font-bold text-xs">
                                                <?= strtoupper(substr($p['nama'], 0, 1)) ?>
                                            </div>
                                            <span class="font-semibold text-slate-900"><?= htmlspecialchars($p['nama']) ?></span>
                                        </div>
                                    </td>
                                    <td class="py-3 px-4">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-medium bg-slate-100 text-slate-700 border border-slate-200">
                                            <?= htmlspecialchars($p['departemen']) ?>
                                        </span>
                                    </td>
                                    <td class="py-3 px-4 text-slate-600">
                                        <?= htmlspecialchars($p['jabatan']) ?>
                                    </td>
                                    <td class="py-3 px-4 text-center">
                                        <div class="flex items-center justify-center gap-1.5">
                                            <!-- Tombol Edit -->
                                            <button type="button" 
                                                    onclick="editPegawai(<?= htmlspecialchars(json_encode($p)) ?>)" 
                                                    title="Ubah Data"
                                                    class="p-1.5 rounded-lg text-blue-600 hover:bg-blue-50 border border-transparent hover:border-blue-200 transition-colors">
                                                <i class="fa-solid fa-pen-to-square text-xs"></i>
                                            </button>
                                            
                                            <!-- Tombol Hapus -->
                                            <a href="actions/pegawai_action.php?action=delete&id=<?= $p['id'] ?>" 
                                               onclick="return confirm('Apakah Anda yakin ingin menghapus data pegawai <?= htmlspecialchars(addslashes($p['nama'])) ?>? Seluruh riwayat penilaian terkait juga akan terhapus.');" 
                                               title="Hapus Pegawai"
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

            <div class="py-3 px-5 border-t border-slate-100 bg-slate-50/50 flex items-center justify-between text-xs text-slate-500">
                <span>Total Pegawai: <strong class="text-slate-800"><?= count($pegawaiList) ?></strong> orang</span>
                <span>SPK Penilaian Kinerja</span>
            </div>
        </div>
    </main>

    <?php require_once __DIR__ . '/includes/footer.php'; ?>
</div>

<!-- Modal Tambah Pegawai -->
<div id="modal-tambah-pegawai" role="dialog" class="fixed inset-0 z-50 bg-slate-900/50 backdrop-blur-xs items-center justify-center p-4 hidden">
    <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full border border-slate-200 overflow-hidden transform transition-all">
        <div class="px-6 py-4 bg-[#1E3A8A] text-white flex items-center justify-between">
            <h3 class="font-bold text-sm flex items-center gap-2">
                <i class="fa-solid fa-user-plus text-teal-300"></i>
                <span>Tambah Pegawai Baru</span>
            </h3>
            <button type="button" onclick="closeModal('modal-tambah-pegawai')" class="text-blue-200 hover:text-white">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <form action="actions/pegawai_action.php" method="POST" class="p-6 space-y-4">
            <input type="hidden" name="action" value="create">

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Nomor Induk Pegawai (NIP)</label>
                <input type="text" name="nip" required placeholder="Contoh: IB-2024009" 
                       class="w-full text-xs py-2.5 px-3 bg-slate-50 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#1E3A8A]">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Nama Lengkap</label>
                <input type="text" name="nama" required placeholder="Contoh: Ahmad Hidayat" 
                       class="w-full text-xs py-2.5 px-3 bg-slate-50 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#1E3A8A]">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Divisi / Departemen</label>
                <input type="text" name="departemen" required list="list-dept" placeholder="Pilih atau ketik departemen..." 
                       class="w-full text-xs py-2.5 px-3 bg-slate-50 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#1E3A8A]">
                <datalist id="list-dept">
                    <?php foreach ($departemenList as $d): ?>
                        <option value="<?= htmlspecialchars($d) ?>">
                    <?php endforeach; ?>
                </datalist>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Jabatan</label>
                <input type="text" name="jabatan" required placeholder="Contoh: Frontend Developer" 
                       class="w-full text-xs py-2.5 px-3 bg-slate-50 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#1E3A8A]">
            </div>

            <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2">
                <button type="button" onclick="closeModal('modal-tambah-pegawai')" 
                        class="px-4 py-2 rounded-xl border border-slate-300 text-xs font-medium text-slate-700 hover:bg-slate-50">
                    Batal
                </button>
                <button type="submit" 
                        class="px-4 py-2 rounded-xl bg-[#1E3A8A] hover:bg-[#172554] text-xs font-semibold text-white shadow-sm">
                    Simpan Pegawai
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Edit Pegawai -->
<div id="modal-edit-pegawai" role="dialog" class="fixed inset-0 z-50 bg-slate-900/50 backdrop-blur-xs items-center justify-center p-4 hidden">
    <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full border border-slate-200 overflow-hidden transform transition-all">
        <div class="px-6 py-4 bg-[#1E3A8A] text-white flex items-center justify-between">
            <h3 class="font-bold text-sm flex items-center gap-2">
                <i class="fa-solid fa-user-pen text-teal-300"></i>
                <span>Ubah Data Pegawai</span>
            </h3>
            <button type="button" onclick="closeModal('modal-edit-pegawai')" class="text-blue-200 hover:text-white">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <form action="actions/pegawai_action.php" method="POST" class="p-6 space-y-4">
            <input type="hidden" name="action" value="update">
            <input type="hidden" name="id" id="edit-id">

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Nomor Induk Pegawai (NIP)</label>
                <input type="text" name="nip" id="edit-nip" required 
                       class="w-full text-xs py-2.5 px-3 bg-slate-50 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#1E3A8A]">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Nama Lengkap</label>
                <input type="text" name="nama" id="edit-nama" required 
                       class="w-full text-xs py-2.5 px-3 bg-slate-50 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#1E3A8A]">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Divisi / Departemen</label>
                <input type="text" name="departemen" id="edit-departemen" required list="list-dept" 
                       class="w-full text-xs py-2.5 px-3 bg-slate-50 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#1E3A8A]">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Jabatan</label>
                <input type="text" name="jabatan" id="edit-jabatan" required 
                       class="w-full text-xs py-2.5 px-3 bg-slate-50 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#1E3A8A]">
            </div>

            <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2">
                <button type="button" onclick="closeModal('modal-edit-pegawai')" 
                        class="px-4 py-2 rounded-xl border border-slate-300 text-xs font-medium text-slate-700 hover:bg-slate-50">
                    Batal
                </button>
                <button type="submit" 
                        class="px-4 py-2 rounded-xl bg-[#0D9488] hover:bg-teal-700 text-xs font-semibold text-white shadow-sm">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function editPegawai(pegawai) {
    document.getElementById('edit-id').value = pegawai.id;
    document.getElementById('edit-nip').value = pegawai.nip;
    document.getElementById('edit-nama').value = pegawai.nama;
    document.getElementById('edit-departemen').value = pegawai.departemen;
    document.getElementById('edit-jabatan').value = pegawai.jabatan;
    openModal('modal-edit-pegawai');
}
</script>
