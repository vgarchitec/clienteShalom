    <!-- Toast Notification container -->
    <div id="toast" class="fixed bottom-5 right-5 z-50 transform translate-y-24 opacity-0 transition-all duration-300 max-w-sm w-full bg-slate-900 border border-slate-700 text-white px-4 py-3 rounded-xl shadow-2xl flex items-center gap-3">
        <i id="toastIcon" data-lucide="info" class="w-5 h-5 text-rose-400 shrink-0"></i>
        <div class="text-xs flex-1" id="toastMessage"></div>
    </div>

    <!-- Footer -->
    <footer class="border-t border-slate-900 bg-slate-950 py-4 text-center text-xs text-slate-500">
        <div class="max-w-7xl mx-auto px-4 flex flex-col sm:flex-row items-center justify-between gap-2">
            <span>Shalom API • Portal de Servicios y Envíos Perú</span>
            <div class="flex items-center gap-4 text-[11px]">
                <a href="https://docs.shalom-api.lat" target="_blank" class="hover:text-rose-400 transition-colors">Documentación Oficial</a>
                <span>•</span>
                <a href="https://shalom-api.lat" target="_blank" class="hover:text-rose-400 transition-colors">Panel Shalom API</a>
            </div>
        </div>
    </footer>

    <!-- App Scripts Modularized -->
    <script src="assets/js/app.js"></script>
    <script src="assets/js/tracking.js"></script>
    <script src="assets/js/qr-scanner.js"></script>
    <script src="assets/js/agencies.js"></script>
    <script src="assets/js/quote.js"></script>
    <script src="assets/js/dni.js"></script>
</body>
</html>
