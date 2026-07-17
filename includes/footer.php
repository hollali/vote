    </main>

    <footer class="border-t border-neutral-800/50 mt-12">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 py-6 text-center">
            <p class="text-xs text-neutral-600">&copy; <?= date('Y') ?> Voting System</p>
        </div>
    </footer>

    <div id="loading-overlay" class="fixed inset-0 bg-[#0a0a0a]/80 backdrop-blur-sm z-[200] flex items-center justify-center hidden">
        <div class="flex flex-col items-center gap-3">
            <div class="w-8 h-8 border-2 border-neutral-600 border-t-neutral-300 rounded-full icon-spin"></div>
            <p class="text-neutral-500 text-xs font-medium">Processing</p>
        </div>
    </div>

    <script>
        document.querySelectorAll('form[data-loading]').forEach(function(form) {
            form.addEventListener('submit', function() {
                document.getElementById('loading-overlay').classList.remove('hidden');
            });
        });
        function showLoading() { document.getElementById('loading-overlay').classList.remove('hidden'); }
        function hideLoading() { document.getElementById('loading-overlay').classList.add('hidden'); }
    </script>
</body>
</html>
