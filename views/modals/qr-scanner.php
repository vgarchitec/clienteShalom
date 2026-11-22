    <!-- Modal: QR Code Scanner -->
    <div id="qrModal" class="fixed inset-0 bg-slate-950/85 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
        <div class="bg-slate-900 border border-slate-800 rounded-2xl max-w-md w-full p-6 shadow-2xl space-y-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-lg bg-rose-600/20 text-rose-400 flex items-center justify-center border border-rose-500/30">
                        <i data-lucide="qr-code" class="w-4 h-4"></i>
                    </div>
                    <div>
                        <h3 class="font-bold text-white text-base">Lector de Código QR</h3>
                        <p class="text-[11px] text-slate-400">Ticket y rótulos de Shalom</p>
                    </div>
                </div>
                <button type="button" onclick="closeQrModal()" class="text-slate-400 hover:text-white p-1 rounded-lg">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <!-- Tab Buttons for Camera / File Upload -->
            <div class="flex bg-slate-950 p-1 rounded-xl border border-slate-800 text-xs">
                <button type="button" id="qrTabCamera" onclick="switchQrMode('camera')"
                    class="flex-1 py-1.5 rounded-lg font-medium text-center transition-all bg-rose-600 text-white">
                    <i data-lucide="camera" class="w-3.5 h-3.5 inline mr-1"></i> Cámara en Vivo
                </button>
                <button type="button" id="qrTabFile" onclick="switchQrMode('file')"
                    class="flex-1 py-1.5 rounded-lg font-medium text-center transition-all text-slate-400 hover:text-white">
                    <i data-lucide="upload" class="w-3.5 h-3.5 inline mr-1"></i> Subir Imagen / Foto
                </button>
            </div>

            <!-- Camera View Area -->
            <div id="qrCameraSection" class="space-y-2">
                <div id="qrReader" class="overflow-hidden rounded-xl border border-slate-700 bg-black min-h-[260px] flex items-center justify-center relative">
                    <div id="qrCameraPlaceholder" class="text-center p-6 text-slate-500 text-xs">
                        <i data-lucide="camera" class="w-8 h-8 mx-auto mb-2 text-slate-600"></i>
                        Iniciando cámara...
                    </div>
                </div>
                <p class="text-[11px] text-center text-slate-400">
                    Apunta la cámara al código QR impreso en tu ticket o paquete.
                </p>
            </div>

            <!-- File Upload Area -->
            <div id="qrFileSection" class="hidden space-y-3">
                <div onclick="document.getElementById('qrFileInput').click()"
                    class="border-2 border-dashed border-slate-700 hover:border-rose-500 rounded-xl p-8 text-center cursor-pointer bg-slate-950/50 hover:bg-slate-950 transition-all group">
                    <i data-lucide="image" class="w-10 h-10 text-slate-500 group-hover:text-rose-400 mx-auto mb-2 transition-colors"></i>
                    <span class="block text-xs font-semibold text-slate-200">Haz clic para seleccionar foto o captura del QR</span>
                    <span class="block text-[11px] text-slate-500 mt-1">Formatos JPG, PNG, WEBP</span>
                </div>
                <input type="file" id="qrFileInput" accept="image/*" class="hidden" onchange="handleQrFileSelected(event)">
                <div id="qrFileStatus" class="hidden text-xs text-center text-slate-300"></div>
            </div>

            <!-- Hint / Format info -->
            <div class="p-3 bg-slate-950 border border-slate-800 rounded-xl text-[11px] text-slate-400 flex items-start gap-2">
                <i data-lucide="sparkles" class="w-4 h-4 text-rose-400 shrink-0 mt-0.5"></i>
                <div>
                    <strong>Detección inteligente:</strong> Lee códigos QR Shalom como <code class="text-rose-300 font-mono text-[10px]">107869605/document/1/</code> y resuelve automáticamente el número de orden y código de seguridad.
                </div>
            </div>
        </div>
    </div>
