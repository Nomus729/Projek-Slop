<?php
require_once __DIR__ . '/config/helper.php';

if (isLoggedIn()) {
    header('Location: dashboard.php');
    exit;
}

$pageTitle = 'Login — Sistem SPK Penilaian Kinerja';
require_once __DIR__ . '/includes/header.php';
$flash = getFlash();
?>

<div class="min-h-screen w-full flex flex-col justify-center items-center py-12 px-4 sm:px-6 lg:px-8 bg-gradient-to-br from-[#0F172A] via-[#1E3A8A] to-[#0D9488]/30 relative overflow-hidden">
    <!-- Decorative Ambient Lights -->
    <div class="absolute -top-32 -left-32 w-96 h-96 bg-blue-600/20 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute -bottom-32 -right-32 w-96 h-96 bg-teal-500/20 rounded-full blur-3xl pointer-events-none"></div>

    <div class="max-w-md w-full space-y-6 relative z-10">
        <!-- Logo & Header -->
        <div class="text-center">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-gradient-to-tr from-[#0D9488] to-teal-400 text-white shadow-xl shadow-teal-900/30 font-bold text-2xl mb-4 border border-teal-300/30">
                <i class="fa-solid fa-brain"></i>
            </div>
            <h1 class="text-2xl font-extrabold text-white tracking-tight sm:text-3xl">
                PT Indobuzz Republik Digital
            </h1>
            <p class="mt-2 text-sm text-blue-200">
                Sistem Evaluasi Kinerja Talenta IT &amp; Rekomendasi Pelatihan
            </p>
            <div class="inline-flex items-center gap-2 mt-3 px-3 py-1 rounded-full bg-blue-900/60 border border-blue-700/50 text-xs text-teal-300">
                <span class="w-2 h-2 rounded-full bg-teal-400 animate-pulse"></span>
                <span>Metode: Simple Additive Weighting (SAW) &amp; Rule-Based</span>
            </div>
        </div>

        <!-- Login Card -->
        <div class="bg-white/95 backdrop-blur-md py-8 px-6 sm:px-10 shadow-2xl rounded-2xl border border-slate-200/80">
            <!-- Alert Notifications -->
            <?php if ($flash): ?>
                <?php
                $bgAlert = 'bg-blue-50 border-blue-200 text-blue-800';
                $icon = 'fa-info-circle text-blue-500';
                if ($flash['type'] === 'danger') {
                    $bgAlert = 'bg-rose-50 border-rose-200 text-rose-700';
                    $icon = 'fa-circle-xmark text-rose-500';
                } elseif ($flash['type'] === 'success') {
                    $bgAlert = 'bg-emerald-50 border-emerald-200 text-emerald-700';
                    $icon = 'fa-circle-check text-emerald-500';
                }
                ?>
                <div class="mb-5 p-3.5 rounded-xl border <?= $bgAlert ?> flex items-center gap-3 text-xs font-medium">
                    <i class="fa-solid <?= $icon ?> text-sm"></i>
                    <span><?= htmlspecialchars($flash['message']) ?></span>
                </div>
            <?php endif; ?>

            <form action="actions/auth_action.php" method="POST" class="space-y-4">
                <input type="hidden" name="action" value="login">

                <div>
                    <label for="username" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">
                        Username
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                            <i class="fa-regular fa-user text-sm"></i>
                        </div>
                        <input id="username" name="username" type="text" required 
                               placeholder="Masukkan username"
                               class="block w-full pl-10 pr-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-slate-900 text-sm placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-[#1E3A8A] focus:border-transparent transition-all">
                    </div>
                </div>

                <div>
                    <label for="password" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">
                        Kata Sandi
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                            <i class="fa-solid fa-lock text-sm"></i>
                        </div>
                        <input id="password" name="password" type="password" required 
                               placeholder="••••••••"
                               class="block w-full pl-10 pr-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-slate-900 text-sm placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-[#1E3A8A] focus:border-transparent transition-all">
                    </div>
                </div>

                <div class="pt-2">
                    <button type="submit" 
                            class="w-full flex justify-center items-center gap-2 py-3 px-4 border border-transparent rounded-xl text-sm font-semibold text-white bg-[#1E3A8A] hover:bg-[#172554] shadow-md shadow-blue-950/20 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#1E3A8A] transition-all">
                        <i class="fa-solid fa-right-to-bracket text-xs"></i>
                        <span>Masuk ke Dashboard</span>
                    </button>
                </div>
            </form>

            <!-- Quick Demo Credentials Selector -->
            <div class="mt-6 pt-5 border-t border-slate-200 text-center">
                <p class="text-xs font-semibold text-slate-500 mb-2">Akun Demo Cepat (Klik untuk Mengisi):</p>
                <div class="flex flex-wrap items-center justify-center gap-2">
                    <button type="button" onclick="fillCreds('admin', 'admin123')" 
                            class="px-3 py-1.5 rounded-lg bg-blue-50 hover:bg-blue-100 border border-blue-200 text-xs font-medium text-[#1E3A8A] transition-colors">
                        <i class="fa-solid fa-shield-halved mr-1 text-teal-600"></i> Admin (admin)
                    </button>
                    <button type="button" onclick="fillCreds('hrd_penilai', 'admin123')" 
                            class="px-3 py-1.5 rounded-lg bg-teal-50 hover:bg-teal-100 border border-teal-200 text-xs font-medium text-[#0D9488] transition-colors">
                        <i class="fa-solid fa-user-tie mr-1 text-teal-600"></i> HRD Specialist
                    </button>
                </div>
            </div>
        </div>

        <!-- Footer Info -->
        <p class="text-center text-xs text-blue-200/80">
            © <?= date('Y') ?> PT Indobuzz Republik Digital • People Operations IT.
        </p>
    </div>
</div>

<script>
function fillCreds(u, p) {
    document.getElementById('username').value = u;
    document.getElementById('password').value = p;
}
</script>

</body>
</html>
