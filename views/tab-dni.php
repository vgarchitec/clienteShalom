        <!-- ================= TAB: DNI ================= -->
        <section id="tab-dni" class="tab-content hidden space-y-6">
            <div class="max-w-2xl mx-auto bg-slate-900 border border-slate-800 rounded-2xl p-6 sm:p-8 shadow-xl">
                <div class="flex items-center gap-2 text-rose-400 text-sm font-semibold mb-2">
                    <i data-lucide="id-card" class="w-4 h-4"></i>
                    <span>Verificación de Identidad</span>
                </div>
                <h2 class="text-xl font-bold text-white mb-2">Consulta de DNI</h2>
                <p class="text-slate-400 text-xs leading-relaxed mb-6">
                    Consulta y valida el nombre oficial registrado antes de emitir o recepcionar tu envío.
                </p>

                <form id="dniForm" onsubmit="handleDni(event)" class="space-y-3 mb-6">
                    <div class="flex gap-2">
                        <input type="text" id="dniInput" required placeholder="Ingresa 8 dígitos de DNI" maxlength="8" pattern="\d{8}"
                            class="flex-1 bg-slate-950 border border-slate-700 rounded-xl px-4 py-2.5 text-sm font-mono text-white placeholder-slate-500 focus:border-rose-500 focus:outline-none">
                        <button type="submit" id="btnDniSubmit"
                            class="bg-rose-600 hover:bg-rose-500 text-white font-medium text-xs px-5 py-2.5 rounded-xl shadow-lg transition-all flex items-center gap-2">
                            <i data-lucide="search" class="w-4 h-4"></i> Consultar
                        </button>
                    </div>
                    <div class="flex items-center gap-2">
                        <input type="checkbox" id="dniForce" class="rounded text-rose-600 focus:ring-rose-500 bg-slate-950 border-slate-700">
                        <label for="dniForce" class="text-xs text-slate-400 cursor-pointer">Actualizar datos en línea</label>
                    </div>
                </form>

                <!-- DNI Result -->
                <div id="dniResult" class="hidden p-6 rounded-xl bg-slate-950 border border-slate-800 space-y-4">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-12 h-12 rounded-full bg-slate-800 flex items-center justify-center text-rose-400">
                                <i data-lucide="user" class="w-6 h-6"></i>
                            </div>
                            <div>
                                <span class="text-[10px] uppercase font-bold text-slate-400">Nombre Completo</span>
                                <h3 id="dniFullName" class="text-base font-bold text-white"></h3>
                            </div>
                        </div>
                        <span id="dniSourceBadge" class="text-[10px] font-semibold px-2.5 py-1 rounded-full border"></span>
                    </div>
                    <div class="grid grid-cols-3 gap-3 pt-3 border-t border-slate-800 text-xs">
                        <div>
                            <span class="text-slate-500 block text-[10px]">DNI</span>
                            <span id="dniNumber" class="font-mono font-semibold text-slate-200"></span>
                        </div>
                        <div>
                            <span class="text-slate-500 block text-[10px]">Nombres</span>
                            <span id="dniNames" class="font-semibold text-slate-200"></span>
                        </div>
                        <div>
                            <span class="text-slate-500 block text-[10px]">Apellidos</span>
                            <span id="dniSurnames" class="font-semibold text-slate-200"></span>
                        </div>
                    </div>
                </div>
            </div>
        </section>
