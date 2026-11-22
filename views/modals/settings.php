    <!-- Modal: Settings -->
    <div id="keyModal" class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
        <div class="bg-slate-900 border border-slate-800 rounded-2xl max-w-lg w-full p-6 shadow-2xl space-y-5">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-lg bg-rose-600/20 text-rose-400 flex items-center justify-center border border-rose-500/30">
                        <i data-lucide="settings-2" class="w-4 h-4"></i>
                    </div>
                    <div>
                        <h3 class="font-bold text-white text-base">Configuración de Shalom API</h3>
                        <p class="text-[11px] text-slate-400">Credenciales y preferencias de tu cuenta</p>
                    </div>
                </div>
                <button onclick="closeKeyModal()" class="text-slate-400 hover:text-white p-1 rounded-lg">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <form id="settingsForm" onsubmit="handleSaveSettings(event)" class="space-y-4">
                <!-- API Key -->
                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <label class="block text-xs font-medium text-slate-300">Shalom API Key</label>
                        <span id="keyValidateStatus" class="text-[10px] text-slate-500 font-mono">sk_...</span>
                    </div>
                    <div class="relative">
                        <input type="password" id="apiKeyInput" placeholder="sk_live_..."
                            class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3.5 py-2.5 text-xs font-mono text-white placeholder-slate-600 focus:border-rose-500 focus:outline-none">
                        <button type="button" onclick="toggleKeyVisibility()" class="absolute right-3 top-2.5 text-slate-500 hover:text-slate-300">
                            <i data-lucide="eye" id="toggleKeyIcon" class="w-4 h-4"></i>
                        </button>
                    </div>
                    <p class="text-[10px] text-slate-500 mt-1">Obtenida desde el panel de <a href="https://shalom-api.lat" target="_blank" class="text-rose-400 underline">shalom-api.lat</a>.</p>
                </div>

                <!-- Instance ID -->
                <div>
                    <label class="block text-xs font-medium text-slate-300 mb-1.5">ID de Instancia Shalom Pro <span class="text-slate-500 font-normal">(Opcional)</span></label>
                    <input type="text" id="instanceIdInput" placeholder="Ej: 2e656a02-7e37-4573-9d68-e76740d337dc"
                        class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3.5 py-2 text-xs font-mono text-white placeholder-slate-600 focus:border-rose-500 focus:outline-none">
                    <p class="text-[10px] text-slate-500 mt-1">Aplica tus tarifas corporativas al cotizar y permite descargar rótulos/tickets.</p>
                </div>

                <!-- Default Origin -->
                <div>
                    <label class="block text-xs font-medium text-slate-300 mb-1.5">Agencia de Origen Predeterminada <span class="text-slate-500 font-normal">(Opcional)</span></label>
                    <input type="text" id="defaultOriginInput" placeholder="Ej: 7 o LIMA"
                        class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3.5 py-2 text-xs font-mono text-white placeholder-slate-600 focus:border-rose-500 focus:outline-none">
                    <p class="text-[10px] text-slate-500 mt-1">Pre-cargada automáticamente en el cotizador.</p>
                </div>

                <div class="p-3 rounded-xl bg-slate-950 border border-slate-800 flex items-center justify-between text-xs text-slate-400">
                    <span>Historial de consultas:</span>
                    <button type="button" onclick="clearDbHistory()" class="text-rose-400 hover:text-rose-300 text-xs underline">
                        Vaciar historial
                    </button>
                </div>

                <div id="keyTestResult" class="hidden text-xs p-3 rounded-xl border"></div>

                <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-800">
                    <button type="button" onclick="closeKeyModal()" class="px-4 py-2 text-xs font-medium text-slate-400 hover:text-white">
                        Cancelar
                    </button>
                    <button type="submit" id="btnSaveKey"
                        class="px-5 py-2.5 text-xs font-semibold bg-rose-600 hover:bg-rose-500 text-white rounded-xl shadow-lg transition-all flex items-center gap-2">
                        <i data-lucide="save" class="w-4 h-4"></i> Guardar Configuración
                    </button>
                </div>
            </form>
        </div>
    </div>
