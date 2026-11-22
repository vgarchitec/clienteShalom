<!-- ================= TAB: TRACKING ================= -->
<section id="tab-tracking" class="tab-content space-y-6">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Search Box -->
        <div class="lg:col-span-1 bg-slate-900 border border-slate-800 rounded-2xl p-6 shadow-xl flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between mb-2">
                    <div class="flex items-center gap-2 text-rose-400 text-sm font-semibold">
                        <i data-lucide="search" class="w-4 h-4"></i>
                        <span>Búsqueda de Envíos</span>
                    </div>
                    <button type="button" onclick="openQrModal()"
                        class="inline-flex items-center gap-1.5 px-3 py-1 bg-rose-950/80 hover:bg-rose-900 border border-rose-700/60 text-rose-300 hover:text-white rounded-lg text-xs font-semibold transition-all shadow-sm">
                        <i data-lucide="qr-code" class="w-3.5 h-3.5"></i> Escanear QR
                    </button>
                </div>
                <h2 class="text-xl font-bold text-white mb-2">Rastreo de Guía</h2>
                <p class="text-slate-400 text-xs leading-relaxed mb-6">
                    Ingresa el número de orden (8-12 dígitos) y el código de seguridad (4 caracteres) del ticket de tu envío.
                </p>

                <form id="trackingForm" onsubmit="handleTrack(event)" class="space-y-4">
                    <div>
                        <label class="block text-xs font-medium text-slate-300 mb-1.5">Número de Orden</label>
                        <div class="relative">
                            <input type="text" id="trackOrderNumber" required placeholder="Ej: 66479331" maxlength="12" pattern="\d+"
                                class="w-full bg-slate-950 border border-slate-700 focus:border-rose-500 focus:ring-1 focus:ring-rose-500 rounded-xl px-3.5 py-2.5 text-sm font-mono text-white placeholder-slate-500 transition-all">
                            <span class="absolute right-3 top-2.5 text-xs text-slate-500">8-12 dig</span>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-slate-300 mb-1.5">Código de Seguridad</label>
                        <div class="relative">
                            <input type="text" id="trackOrderCode" required placeholder="Ej: 3KTH" maxlength="4"
                                class="w-full bg-slate-950 border border-slate-700 focus:border-rose-500 focus:ring-1 focus:ring-rose-500 rounded-xl px-3.5 py-2.5 text-sm font-mono uppercase text-white placeholder-slate-500 transition-all">
                            <span class="absolute right-3 top-2.5 text-xs text-slate-500">4 car</span>
                        </div>
                    </div>

                    <div class="flex items-center gap-2 pt-1">
                        <input type="checkbox" id="trackForce" class="rounded text-rose-600 focus:ring-rose-500 bg-slate-950 border-slate-700">
                        <label for="trackForce" class="text-xs text-slate-300 select-none cursor-pointer">
                            Actualizar estado en vivo
                        </label>
                    </div>

                    <div class="grid grid-cols-2 gap-2 mt-2">
                        <button type="submit" id="btnTrackSubmit"
                            class="bg-gradient-to-r from-brand-700 to-rose-600 hover:from-brand-600 hover:to-rose-500 text-white font-medium text-xs py-2.5 px-3 rounded-xl shadow-lg shadow-brand-900/30 flex items-center justify-center gap-1.5 transition-all">
                            <i data-lucide="search" class="w-4 h-4"></i> Consultar
                        </button>
                        <button type="button" onclick="openQrModal()"
                            class="bg-slate-800 hover:bg-slate-700 border border-slate-700 text-slate-200 hover:text-white font-medium text-xs py-2.5 px-3 rounded-xl flex items-center justify-center gap-1.5 transition-all">
                            <i data-lucide="qr-code" class="w-4 h-4 text-rose-400"></i> Escanear QR
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Tracking Display Area -->
        <div class="lg:col-span-2 bg-slate-900 border border-slate-800 rounded-2xl p-6 shadow-xl min-h-[420px] flex flex-col justify-center" id="trackResultContainer">
            <div id="trackEmptyState" class="text-center py-16">
                <div class="w-16 h-16 rounded-2xl bg-slate-800/60 border border-slate-700 flex items-center justify-center mx-auto mb-4 text-slate-500">
                    <i data-lucide="package" class="w-8 h-8"></i>
                </div>
                <h3 class="text-base font-semibold text-slate-300">Sin consulta activa</h3>
                <p class="text-xs text-slate-500 max-w-sm mx-auto mt-1">
                    Ingresa un número y código para consultar el estado y la línea de tiempo del envío.
                </p>
            </div>

            <div id="trackLoadingState" class="hidden text-center py-16">
                <div class="w-12 h-12 border-4 border-rose-500/20 border-t-rose-500 rounded-full animate-spin mx-auto mb-4"></div>
                <p class="text-sm font-medium text-slate-300">Consultando envío...</p>
                <p class="text-xs text-slate-500 mt-1">Obteniendo estado oficial de Shalom</p>
            </div>

            <div id="trackDataState" class="hidden space-y-6">
                <!-- Top status card -->
                <div class="flex flex-wrap items-center justify-between gap-4 p-4 rounded-xl bg-slate-950 border border-slate-800">
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="text-[10px] uppercase font-bold tracking-wider text-slate-400">Estado del Envío</span>
                            <span id="trackSourceBadge" class="px-2 py-0.5 rounded text-[10px] font-semibold border flex items-center gap-1"></span>
                        </div>
                        <div class="flex items-center gap-2.5 mt-1">
                            <span id="trackStatusBadge" class="px-3 py-1 text-xs font-bold rounded-full bg-blue-900/60 text-blue-300 border border-blue-700/50">
                                En tránsito
                            </span>
                            <span id="trackOrderBadge" class="text-sm font-mono text-slate-300"></span>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <button onclick="forceRefreshCurrentTrack()" id="btnForceCurrent"
                            title="Forzar actualización en vivo desde Shalom API"
                            class="flex items-center gap-1.5 px-3 py-1.5 bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs rounded-lg border border-slate-700 transition-all">
                            <i data-lucide="refresh-cw" class="w-3.5 h-3.5 text-rose-400"></i> Actualizar
                        </button>
                    </div>
                </div>

                <!-- Timeline -->
                <div>
                    <h4 class="text-xs font-semibold uppercase tracking-wider text-slate-400 mb-4">Línea de Tiempo del Courier</h4>
                    <div class="grid grid-cols-2 sm:grid-cols-5 gap-3" id="timelineCards">
                        <!-- Generated via JS -->
                    </div>
                </div>

                <!-- Details Grid -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="p-4 rounded-xl bg-slate-950/60 border border-slate-800 space-y-2 text-xs">
                        <div class="flex items-center justify-between text-slate-400">
                            <span>Origen:</span>
                            <span id="trackOrigin" class="font-semibold text-white"></span>
                        </div>
                        <div class="flex items-center justify-between text-slate-400">
                            <span>Destino:</span>
                            <span id="trackDestination" class="font-semibold text-white"></span>
                        </div>
                        <div class="flex items-center justify-between text-slate-400">
                            <span>Contenido:</span>
                            <span id="trackContent" class="font-semibold text-white"></span>
                        </div>
                        <div class="flex items-center justify-between text-slate-400">
                            <span>Remitente:</span>
                            <span id="trackSender" class="font-semibold text-slate-200"></span>
                        </div>
                    </div>
                    <div class="p-4 rounded-xl bg-slate-950/60 border border-slate-800 space-y-2 text-xs">
                        <div class="flex items-center justify-between text-slate-400">
                            <span>Destinatario:</span>
                            <span id="trackReceiver" class="font-semibold text-slate-200"></span>
                        </div>
                        <div class="flex items-center justify-between text-slate-400">
                            <span>Monto / Cobro:</span>
                            <span id="trackAmount" class="font-semibold text-emerald-400"></span>
                        </div>
                        <div class="flex items-center justify-between text-slate-400">
                            <span>Comprobante:</span>
                            <span id="trackInvoice" class="font-mono text-slate-300"></span>
                        </div>
                        <div class="flex items-center justify-between text-slate-400">
                            <span>Fecha de Consulta:</span>
                            <span id="trackSavedAt" class="font-mono text-slate-400"></span>
                        </div>
                    </div>
                </div>

                <!-- Raw JSON toggle -->
                <details class="text-xs text-slate-400 bg-slate-950/40 border border-slate-800/80 rounded-xl p-3">
                    <summary class="cursor-pointer hover:text-white font-medium select-none">Ver Respuesta JSON Completa</summary>
                    <pre id="trackRawJson" class="mt-3 p-3 bg-slate-950 rounded-lg font-mono text-[11px] text-slate-300 overflow-x-auto max-h-56 custom-scrollbar"></pre>
                </details>
            </div>
        </div>
    </div>

    <!-- Recent Tracking History -->
    <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 shadow-xl">
        <div class="flex items-center justify-between mb-4">
            <div class="flex items-center gap-2">
                <i data-lucide="history" class="w-4 h-4 text-rose-400"></i>
                <h3 class="text-sm font-bold text-white">Consultas Recientes (<span id="historyCount">0</span>)</h3>
            </div>
            <button onclick="loadRecentTracks()" class="text-xs text-slate-400 hover:text-white flex items-center gap-1">
                <i data-lucide="refresh-cw" class="w-3 h-3"></i> Refrescar lista
            </button>
        </div>

        <div id="historyTableContainer" class="overflow-x-auto custom-scrollbar">
            <table class="w-full text-left text-xs text-slate-300 border-collapse">
                <thead>
                    <tr class="border-b border-slate-800 text-[11px] text-slate-500 uppercase tracking-wider">
                        <th class="py-2.5 px-3">Orden / Código</th>
                        <th class="py-2.5 px-3">Fecha Estado</th>
                        <th class="py-2.5 px-3">Estado Actual</th>
                        <th class="py-2.5 px-3">Recepciona</th>
                        <th class="py-2.5 px-3">Contenido</th>
                        <th class="py-2.5 px-3">Monto</th>
                        <th class="py-2.5 px-3 text-right">Acción</th>
                    </tr>
                </thead>
                <tbody id="historyTableBody">
                    <tr>
                        <td colspan="7" class="py-6 text-center text-slate-500">No hay consultas recientes aún.</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</section>
