<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/helper.php';
requireAuth();

$pageTitle = 'Master Kriteria & Pembobotan';
$pageSubtitle = 'Pengaturan Parameter Kriteria SAW & Bobot Preferensi';

$kriteriaList = $pdo->query("SELECT * FROM kriteria ORDER BY kode ASC")->fetchAll();

$totalBobot = 0;
foreach ($kriteriaList as $k) {
    $totalBobot += (float)$k['bobot'];
}
$persenTotal = round($totalBobot * 100, 1);
$isBalanced = abs($totalBobot - 1.0) < 0.001;

require_once __DIR__ . '/includes/header.php';
?>

<?php require_once __DIR__ . '/includes/sidebar.php'; ?>

<!-- Main Content Area -->
<div class="flex-1 flex flex-col min-w-0 overflow-y-auto">
    <?php require_once __DIR__ . '/includes/topbar.php'; ?>

    <main class="p-6 max-w-7xl w-full mx-auto space-y-6">
        <!-- Header Banner & Bobot Summary -->
        <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-5">
            <div>
                <h1 class="text-xl font-bold text-slate-900">Kriteria Penilaian &amp; Pembobotan</h1>
                <p class="text-xs text-slate-500 mt-1 max-w-2xl leading-relaxed">
                    Setiap kriteria memiliki bobot preferensi yang menentukan pengaruhnya terhadap skor evaluasi kinerja akhir talenta IT.
                    Total seluruh bobot kriteria harus berjumlah <strong>100% (1.00)</strong>.
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-3">
                <!-- Status Total Bobot Badge -->
                <div class="px-4 py-2 rounded-xl border <?= $isBalanced ? 'bg-emerald-50 border-emerald-200 text-emerald-800' : 'bg-rose-50 border-rose-200 text-rose-800' ?> flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg <?= $isBalanced ? 'bg-emerald-500 text-white' : 'bg-rose-500 text-white' ?> flex items-center justify-center text-sm font-bold">
                        <i class="fa-solid <?= $isBalanced ? 'fa-check' : 'fa-triangle-exclamation' ?>"></i>
                    </div>
                    <div>
                        <div class="text-[10px] font-semibold uppercase tracking-wider text-slate-500">Total Akumulasi Bobot</div>
                        <div class="text-sm font-bold font-mono">
                            <?= $persenTotal ?>% <span class="text-xs font-normal text-slate-500">(<?= number_format($totalBobot, 2) ?>)</span>
                        </div>
                    </div>
                </div>

                <!-- Tombol Reset Default -->
                <a href="actions/kriteria_action.php?action=reset_default" 
                   onclick="return confirm('Kembalikan seluruh kriteria ke bobot standar spesifikasi (C1: 20%, C2: 35%, C3: 25%, C4: 20%)?');"
                   class="px-3.5 py-2 rounded-xl border border-slate-200 text-slate-700 hover:bg-slate-50 text-xs font-semibold flex items-center gap-2 transition-all">
                    <i class="fa-solid fa-rotate-left text-slate-400"></i>
                    <span>Reset Default</span>
                </a>
            </div>
        </div>

        <!-- Table Card -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50/80 text-slate-600 font-semibold uppercase tracking-wider border-b border-slate-200">
                        <tr>
                            <th class="py-3.5 px-4 text-center w-16">Kode</th>
                            <th class="py-3.5 px-4">Nama Kriteria</th>
                            <th class="py-3.5 px-4 text-center">Tipe / Sifat</th>
                            <th class="py-3.5 px-4 text-center">Bobot Nilai</th>
                            <th class="py-3.5 px-4 text-center">Persentase</th>
                            <th class="py-3.5 px-4">Keterangan Indikator</th>
                            <th class="py-3.5 px-4 text-center w-24">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php foreach ($kriteriaList as $k): ?>
                            <tr class="hover:bg-slate-50/80 transition-colors">
                                <td class="py-4 px-4 text-center">
                                    <span class="inline-flex items-center justify-center px-2.5 py-1 rounded-md text-xs font-bold font-mono bg-blue-50 text-[#1E3A8A] border border-blue-200">
                                        <?= htmlspecialchars($k['kode']) ?>
                                    </span>
                                </td>
                                <td class="py-4 px-4 font-semibold text-slate-900">
                                    <?= htmlspecialchars($k['nama_kriteria']) ?>
                                </td>
                                <td class="py-4 px-4 text-center">
                                    <?php if (strtolower($k['jenis']) === 'benefit'): ?>
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            <i class="fa-solid fa-arrow-trend-up text-[10px]"></i> Benefit
                                        </span>
                                    <?php else: ?>
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-rose-50 text-rose-700 border border-rose-200">
                                            <i class="fa-solid fa-arrow-trend-down text-[10px]"></i> Cost
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="py-4 px-4 text-center font-mono font-bold text-slate-800">
                                    <?= number_format((float)$k['bobot'], 2) ?>
                                </td>
                                <td class="py-4 px-4 text-center">
                                    <div class="inline-flex items-center gap-2">
                                        <div class="w-16 bg-slate-100 rounded-full h-2">
                                            <div class="bg-[#0D9488] h-2 rounded-full" style="width: <?= round($k['bobot'] * 100) ?>%"></div>
                                        </div>
                                        <span class="font-bold font-mono text-teal-700"><?= round($k['bobot'] * 100) ?>%</span>
                                    </div>
                                </td>
                                <td class="py-4 px-4 text-slate-500">
                                    <?= htmlspecialchars($k['keterangan'] ?? '-') ?>
                                </td>
                                <td class="py-4 px-4 text-center">
                                    <button type="button" 
                                            onclick="editKriteria(<?= htmlspecialchars(json_encode($k)) ?>)" 
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold text-[#1E3A8A] bg-blue-50 hover:bg-blue-100 border border-blue-200 transition-colors">
                                        <i class="fa-solid fa-pen-to-square text-[11px]"></i>
                                        <span>Ubah</span>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Ketentuan Pembobotan Helper Card -->
            <div class="p-5 bg-slate-50/70 border-t border-slate-200 flex flex-col md:flex-row items-start md:items-center justify-between gap-4 text-xs text-slate-600">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-blue-100 text-[#1E3A8A] flex items-center justify-center font-bold">
                        <i class="fa-solid fa-calculator"></i>
                    </div>
                    <div>
                        <span class="font-semibold text-slate-900">Ketentuan Tipe Kriteria SPK:</span>
                        <p class="text-slate-500 mt-0.5">
                            Semua kriteria bertipe <strong>Benefit</strong>: semakin tinggi nilai capaian kinerja, semakin tinggi kontribusinya terhadap peringkat akhir.
                        </p>
                    </div>
                </div>
                <div class="text-[11px] font-mono text-slate-700 bg-white px-3 py-1.5 rounded-lg border border-slate-200">
                    Total Bobot Ideal: 100% (1.00)
                </div>
            </div>
        </div>
    </main>

    <?php require_once __DIR__ . '/includes/footer.php'; ?>
</div>

<!-- Modal Edit Kriteria -->
<div id="modal-edit-kriteria" role="dialog" class="fixed inset-0 z-50 bg-slate-900/50 backdrop-blur-xs items-center justify-center p-4 hidden">
    <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full border border-slate-200 overflow-hidden transform transition-all">
        <div class="px-6 py-4 bg-[#1E3A8A] text-white flex items-center justify-between">
            <h3 class="font-bold text-sm flex items-center gap-2">
                <i class="fa-solid fa-sliders text-teal-300"></i>
                <span>Ubah Parameter Kriteria <span id="modal-kriteria-kode" class="font-mono text-teal-300"></span></span>
            </h3>
            <button type="button" onclick="closeModal('modal-edit-kriteria')" class="text-blue-200 hover:text-white">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <form action="actions/kriteria_action.php" method="POST" class="p-6 space-y-4">
            <input type="hidden" name="action" value="update">
            <input type="hidden" name="id" id="edit-id">

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Nama Kriteria</label>
                <input type="text" name="nama_kriteria" id="edit-nama" required 
                       class="w-full text-xs py-2.5 px-3 bg-slate-50 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#1E3A8A]">
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Bobot Preferensi (0.01 - 1.0)</label>
                    <input type="number" step="0.01" min="0.01" max="1" name="bobot" id="edit-bobot" required 
                           class="w-full text-xs py-2.5 px-3 bg-slate-50 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#1E3A8A] font-mono">
                    <p class="text-[10px] text-slate-400 mt-1">Misal 0.20 untuk 20%</p>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Tipe Kriteria</label>
                    <select name="jenis" id="edit-jenis" required 
                            class="w-full text-xs py-2.5 px-3 bg-slate-50 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#1E3A8A]">
                        <option value="benefit">Benefit (Maksimal)</option>
                        <option value="cost">Cost (Minimal)</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Keterangan / Definisi Operasional</label>
                <textarea name="keterangan" id="edit-keterangan" rows="2" 
                          class="w-full text-xs py-2 px-3 bg-slate-50 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#1E3A8A]"></textarea>
            </div>

            <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2">
                <button type="button" onclick="closeModal('modal-edit-kriteria')" 
                        class="px-4 py-2 rounded-xl border border-slate-300 text-xs font-medium text-slate-700 hover:bg-slate-50">
                    Batal
                </button>
                <button type="submit" 
                        class="px-4 py-2 rounded-xl bg-[#0D9488] hover:bg-teal-700 text-xs font-semibold text-white shadow-sm">
                    Simpan Parameter
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function editKriteria(k) {
    document.getElementById('edit-id').value = k.id;
    document.getElementById('modal-kriteria-kode').innerText = '(' + k.kode + ')';
    document.getElementById('edit-nama').value = k.nama_kriteria;
    document.getElementById('edit-bobot').value = k.bobot;
    document.getElementById('edit-jenis').value = k.jenis;
    document.getElementById('edit-keterangan').value = k.keterangan || '';
    openModal('modal-edit-kriteria');
}
</script>
