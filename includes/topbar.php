<?php
$user = getCurrentUser();
$flash = getFlash();
$activePeriod = $_SESSION['active_periode'] ?? '2026-Semester-2';
?>
<!-- Top Header Bar -->
<header class="h-16 bg-white border-b border-slate-200 px-6 flex items-center justify-between sticky top-0 z-30 shrink-0">
    <div class="flex items-center gap-3">
        <!-- Mobile Toggle Button -->
        <button onclick="toggleSidebar()" class="lg:hidden p-2 rounded-lg text-slate-500 hover:text-slate-700 hover:bg-slate-100 focus:outline-none" aria-label="Toggle Menu">
            <i class="fa-solid fa-bars text-lg"></i>
        </button>

        <!-- Clean Page Title (Breadcrumb Removed) -->
        <h1 class="text-base sm:text-lg font-bold text-slate-900 tracking-tight leading-none"><?= htmlspecialchars($pageTitle ?? 'Dashboard') ?></h1>
    </div>

    <div class="flex items-center gap-3">
        <!-- Active Period Pill Badge -->
        <div class="hidden sm:inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-slate-50 border border-slate-200/80 text-xs font-medium text-slate-600">
            <i class="fa-regular fa-calendar text-slate-400"></i>
            <span class="text-slate-400">Periode:</span>
            <span class="font-semibold text-slate-800"><?= htmlspecialchars($activePeriod) ?></span>
        </div>

        <!-- User Profile Pill & Logout -->
        <div class="flex items-center gap-2 pl-2 border-l border-slate-200">
            <div class="w-8 h-8 rounded-full bg-[#1E3A8A] text-white flex items-center justify-center font-bold text-xs shadow-sm">
                <?= strtoupper(substr($user['nama_lengkap'] ?? 'A', 0, 1)) ?>
            </div>
            <div class="hidden md:block text-left text-xs">
                <p class="font-semibold text-slate-800 leading-none"><?= htmlspecialchars($user['nama_lengkap'] ?? 'HR IT') ?></p>
                <span class="text-[10px] text-teal-700 font-medium"><?= htmlspecialchars($user['role'] === 'admin' ? 'Administrator' : 'HR Specialist') ?></span>
            </div>
            <a href="actions/auth_action.php?action=logout" title="Keluar / Logout" class="ml-1 text-slate-400 hover:text-rose-600 p-1.5 rounded-lg hover:bg-rose-50 transition-colors">
                <i class="fa-solid fa-right-from-bracket text-xs"></i>
            </a>
        </div>
    </div>
</header>

<!-- Flash Notification Banner -->
<?php if ($flash): ?>
    <?php
    $bgClass = 'bg-blue-50 border-blue-200 text-blue-800';
    $icon = 'fa-info-circle text-blue-500';
    if ($flash['type'] === 'success') {
        $bgClass = 'bg-emerald-50 border-emerald-200 text-emerald-800';
        $icon = 'fa-circle-check text-emerald-600';
    } elseif ($flash['type'] === 'warning') {
        $bgClass = 'bg-amber-50 border-amber-200 text-amber-800';
        $icon = 'fa-triangle-exclamation text-amber-600';
    } elseif ($flash['type'] === 'danger') {
        $bgClass = 'bg-rose-50 border-rose-200 text-rose-800';
        $icon = 'fa-circle-xmark text-rose-600';
    }
    ?>
    <div class="mx-6 mt-4 p-4 rounded-xl border <?= $bgClass ?> flex items-start gap-3 shadow-sm transition-all duration-300">
        <i class="fa-solid <?= $icon ?> text-base mt-0.5"></i>
        <div class="flex-1 text-sm font-medium">
            <?= htmlspecialchars($flash['message']) ?>
        </div>
        <button onclick="this.parentElement.remove()" class="text-slate-400 hover:text-slate-600 text-sm">
            <i class="fa-solid fa-xmark"></i>
        </button>
    </div>
<?php endif; ?>
