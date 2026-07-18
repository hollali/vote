
            </main>
        </div>
    </div>

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
