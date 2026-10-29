    <!-- Main Content Footer -->
    <footer class="mt-auto py-4 px-6 border-t border-slate-200/80 bg-white/70 text-center text-xs text-slate-500 flex flex-col sm:flex-row items-center justify-between gap-2">
        <p>© <?= date('Y') ?> PT Indobuzz Republik Digital — SPK Evaluasi Kinerja Talenta IT.</p>
        <p class="text-[11px] text-slate-400">Metode: Simple Additive Weighting (SAW) &amp; Rule-Based Engine</p>
    </footer>

    <!-- Universal Modal Controller Script -->
    <script>
        function openModal(id) {
            const modal = document.getElementById(id);
            if (modal) {
                modal.classList.remove('hidden');
                modal.classList.add('flex');
                document.body.style.overflow = 'hidden';
            }
        }

        function closeModal(id) {
            const modal = document.getElementById(id);
            if (modal) {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
                document.body.style.overflow = 'auto';
            }
        }

        // Close on Escape key
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                const openModals = document.querySelectorAll('[role="dialog"]:not(.hidden)');
                openModals.forEach(m => closeModal(m.id));
            }
        });
    </script>
</body>
</html>
