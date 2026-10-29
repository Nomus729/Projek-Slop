<?php
$currentPage = basename($_SERVER['PHP_SELF']);
$user = getCurrentUser();
?>
<!-- Sidebar Backdrop for Mobile -->
<div id="sidebar-backdrop" onclick="toggleSidebar()" class="fixed inset-0 bg-slate-900/50 z-40 hidden lg:hidden"></div>

<!-- Sidebar Navigation (256px / w-64) -->
<aside id="main-sidebar" class="fixed inset-y-0 left-0 z-50 w-64 bg-[#1E3A8A] text-white flex flex-col transition-transform duration-300 transform -translate-x-full lg:translate-x-0 lg:static lg:inset-auto shrink-0 shadow-xl">
    <!-- Brand Header -->
    <div class="h-16 flex items-center px-6 border-b border-blue-900/60 bg-[#172554]/50">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-[#0D9488] to-teal-400 flex items-center justify-center text-white shadow-md shadow-teal-900/30 font-bold text-lg">
                <i class="fa-solid fa-brain"></i>
            </div>
            <div>
                <h1 class="font-bold text-base tracking-tight leading-none text-white">INDOBUZZ</h1>
                <p class="text-[11px] font-medium text-teal-300 mt-1 uppercase tracking-wider">IT &amp; Digital Agency</p>
            </div>
        </div>
        <button onclick="toggleSidebar()" class="lg:hidden ml-auto text-blue-200 hover:text-white p-1">
            <i class="fa-solid fa-xmark text-lg"></i>
        </button>
    </div>

    <!-- System Badge / Case Study -->
    <div class="px-5 py-3 border-b border-blue-900/40">
        <div class="bg-blue-950/60 rounded-lg p-2.5 border border-blue-800/40 text-xs">
            <div class="flex items-center justify-between text-slate-300 mb-1">
                <span class="font-semibold text-teal-300 text-[10px] uppercase tracking-wider">Sistem SPK</span>
                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-medium bg-teal-500/20 text-teal-300 border border-teal-500/30">SAW + Rule</span>
            </div>
            <p class="text-blue-100/90 text-[11px] leading-relaxed">
                Evaluasi Kinerja Talenta IT &amp; Rekomendasi Pelatihan
            </p>
        </div>
    </div>

    <!-- Navigation Menu -->
    <nav class="flex-1 overflow-y-auto px-3 py-4 space-y-1">
        <div class="px-3 pb-2 text-[10px] font-bold uppercase tracking-wider text-blue-300/70">Menu Utama</div>

        <!-- Dashboard -->
        <a href="dashboard.php" class="flex items-center gap-3 px-3.5 py-2.5 rounded-lg text-sm font-medium transition-colors <?= $currentPage === 'dashboard.php' ? 'bg-[#0D9488] text-white shadow-md shadow-teal-900/20 font-semibold' : 'text-blue-100 hover:bg-blue-900/60 hover:text-white' ?>">
            <i class="fa-solid fa-chart-pie w-5 text-center text-sm <?= $currentPage === 'dashboard.php' ? 'text-white' : 'text-teal-300' ?>"></i>
            <span>Dashboard</span>
            <?php if ($currentPage === 'dashboard.php'): ?>
                <span class="ml-auto w-1.5 h-1.5 rounded-full bg-white"></span>
            <?php endif; ?>
        </a>

        <div class="pt-4 px-3 pb-2 text-[10px] font-bold uppercase tracking-wider text-blue-300/70">Data Master</div>

        <!-- Master Pegawai -->
        <a href="pegawai.php" class="flex items-center gap-3 px-3.5 py-2.5 rounded-lg text-sm font-medium transition-colors <?= $currentPage === 'pegawai.php' ? 'bg-[#0D9488] text-white shadow-md shadow-teal-900/20 font-semibold' : 'text-blue-100 hover:bg-blue-900/60 hover:text-white' ?>">
            <i class="fa-solid fa-users w-5 text-center text-sm <?= $currentPage === 'pegawai.php' ? 'text-white' : 'text-teal-300' ?>"></i>
            <span>Data Pegawai</span>
            <?php if ($currentPage === 'pegawai.php'): ?>
                <span class="ml-auto w-1.5 h-1.5 rounded-full bg-white"></span>
            <?php endif; ?>
        </a>

        <!-- Master Kriteria & Bobot -->
        <a href="kriteria.php" class="flex items-center gap-3 px-3.5 py-2.5 rounded-lg text-sm font-medium transition-colors <?= $currentPage === 'kriteria.php' ? 'bg-[#0D9488] text-white shadow-md shadow-teal-900/20 font-semibold' : 'text-blue-100 hover:bg-blue-900/60 hover:text-white' ?>">
            <i class="fa-solid fa-sliders w-5 text-center text-sm <?= $currentPage === 'kriteria.php' ? 'text-white' : 'text-teal-300' ?>"></i>
            <span>Kriteria & Bobot</span>
            <?php if ($currentPage === 'kriteria.php'): ?>
                <span class="ml-auto w-1.5 h-1.5 rounded-full bg-white"></span>
            <?php endif; ?>
        </a>

        <div class="pt-4 px-3 pb-2 text-[10px] font-bold uppercase tracking-wider text-blue-300/70">Proses SPK</div>

        <!-- Input Penilaian -->
        <a href="penilaian.php" class="flex items-center gap-3 px-3.5 py-2.5 rounded-lg text-sm font-medium transition-colors <?= $currentPage === 'penilaian.php' ? 'bg-[#0D9488] text-white shadow-md shadow-teal-900/20 font-semibold' : 'text-blue-100 hover:bg-blue-900/60 hover:text-white' ?>">
            <i class="fa-solid fa-pen-to-square w-5 text-center text-sm <?= $currentPage === 'penilaian.php' ? 'text-white' : 'text-teal-300' ?>"></i>
            <span>Input Penilaian</span>
            <?php if ($currentPage === 'penilaian.php'): ?>
                <span class="ml-auto w-1.5 h-1.5 rounded-full bg-white"></span>
            <?php endif; ?>
        </a>

        <!-- Komputasi SAW & Rules -->
        <a href="komputasi.php" class="flex items-center gap-3 px-3.5 py-2.5 rounded-lg text-sm font-medium transition-colors <?= $currentPage === 'komputasi.php' ? 'bg-[#0D9488] text-white shadow-md shadow-teal-900/20 font-semibold' : 'text-blue-100 hover:bg-blue-900/60 hover:text-white' ?>">
            <i class="fa-solid fa-calculator w-5 text-center text-sm <?= $currentPage === 'komputasi.php' ? 'text-white' : 'text-teal-300' ?>"></i>
            <span>Komputasi SPK & Rules</span>
            <?php if ($currentPage === 'komputasi.php'): ?>
                <span class="ml-auto w-1.5 h-1.5 rounded-full bg-white"></span>
            <?php endif; ?>
        </a>

        <div class="pt-4 px-3 pb-2 text-[10px] font-bold uppercase tracking-wider text-blue-300/70">Output & Hasil</div>

        <!-- Laporan & Ekspor -->
        <a href="laporan.php" class="flex items-center gap-3 px-3.5 py-2.5 rounded-lg text-sm font-medium transition-colors <?= $currentPage === 'laporan.php' ? 'bg-[#0D9488] text-white shadow-md shadow-teal-900/20 font-semibold' : 'text-blue-100 hover:bg-blue-900/60 hover:text-white' ?>">
            <i class="fa-solid fa-file-invoice w-5 text-center text-sm <?= $currentPage === 'laporan.php' ? 'text-white' : 'text-teal-300' ?>"></i>
            <span>Laporan Rekapitulasi</span>
            <?php if ($currentPage === 'laporan.php'): ?>
                <span class="ml-auto w-1.5 h-1.5 rounded-full bg-white"></span>
            <?php endif; ?>
        </a>
    </nav>

    <!-- User Profile & Logout Bottom Bar -->
    <div class="p-4 border-t border-blue-900/60 bg-[#172554]/80">
        <div class="flex items-center gap-3">
            <div class="w-9 h-9 rounded-full bg-blue-800 border border-teal-400/40 flex items-center justify-center font-bold text-teal-300 text-sm">
                <?= strtoupper(substr($user['nama_lengkap'] ?? 'A', 0, 1)) ?>
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-xs font-semibold text-white truncate"><?= htmlspecialchars($user['nama_lengkap'] ?? 'HRD Admin') ?></p>
                <div class="flex items-center gap-1.5 mt-0.5">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                    <span class="text-[10px] font-medium text-teal-300 uppercase tracking-wider"><?= htmlspecialchars($user['role'] ?? 'HRD') ?></span>
                </div>
            </div>
            <a href="actions/auth_action.php?action=logout" title="Keluar / Logout" class="text-blue-300 hover:text-rose-400 p-1.5 rounded-lg hover:bg-blue-900/50 transition-colors">
                <i class="fa-solid fa-right-from-bracket text-sm"></i>
            </a>
        </div>
    </div>
</aside>

<script>
function toggleSidebar() {
    const sidebar = document.getElementById('main-sidebar');
    const backdrop = document.getElementById('sidebar-backdrop');
    if (sidebar.classList.contains('-translate-x-full')) {
        sidebar.classList.remove('-translate-x-full');
        backdrop.classList.remove('hidden');
    } else {
        sidebar.classList.add('-translate-x-full');
        backdrop.classList.add('hidden');
    }
}
</script>
