<!-- ================= TAB: AGENCIES ================= -->
<section id="tab-agencies" class="tab-content hidden space-y-6">
    <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 shadow-xl">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
            <div>
                <h2 class="text-xl font-bold text-white flex items-center gap-2">
                    <i data-lucide="building-2" class="w-5 h-5 text-rose-500"></i> Catálogo de Agencias Shalom
                </h2>
                <p class="text-xs text-slate-400 mt-1">
                    Consulta direcciones, teléfonos, horarios de atención y cobertura de agencias a nivel nacional.
                </p>
            </div>

            <div class="flex items-center gap-2 flex-wrap">
                <button onclick="syncAgencies(true)" id="btnSyncAgencies"
                    class="inline-flex items-center gap-2 bg-rose-700/80 hover:bg-rose-600 text-white px-3.5 py-2 rounded-xl text-xs font-semibold border border-rose-600 transition-all">
                    <i data-lucide="refresh-cw" class="w-4 h-4"></i> Actualizar Catálogo
                </button>
                <button onclick="getUserLocation()" id="btnNearby"
                    class="inline-flex items-center gap-2 bg-slate-800 hover:bg-slate-700 text-slate-200 hover:text-white px-3.5 py-2 rounded-xl text-xs font-semibold border border-slate-700 transition-all">
                    <i data-lucide="crosshair" class="w-4 h-4 text-rose-400"></i> Cerca de Mí (GPS)
                </button>
            </div>
        </div>

        <!-- Filters Form -->
        <form id="agenciesForm" onsubmit="handleAgenciesSearch(event)" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 mb-6">
            <div class="relative">
                <input type="text" id="agencyQ" placeholder="Ciudad, distrito o sede (ej: Miraflores)"
                    class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3.5 py-2 text-xs text-white placeholder-slate-500 focus:border-rose-500 focus:outline-none">
            </div>
            <div>
                <input type="text" id="agencyDept" placeholder="Departamento (ej: Lima, Piura, Cusco)"
                    class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3.5 py-2 text-xs text-white placeholder-slate-500 focus:border-rose-500 focus:outline-none">
            </div>
            <div class="flex items-center gap-2 bg-slate-950 border border-slate-700 rounded-xl px-3.5 py-2">
                <input type="checkbox" id="agencyAereo" class="rounded text-rose-600 focus:ring-rose-500 bg-slate-900 border-slate-700">
                <label for="agencyAereo" class="text-xs text-slate-300 select-none">Solo Servicio Aéreo</label>
            </div>
            <div>
                <button type="submit" class="w-full bg-rose-600 hover:bg-rose-500 text-white font-medium text-xs py-2 px-4 rounded-xl flex items-center justify-center gap-2 transition-all">
                    <i data-lucide="filter" class="w-3.5 h-3.5"></i> Filtrar Agencias
                </button>
            </div>
        </form>

        <!-- Status indicator -->
        <div class="flex items-center justify-between text-xs text-slate-400 mb-4 pb-2 border-b border-slate-800">
            <span id="agenciesCount">Cargando catálogo de agencias...</span>
            <span id="agenciesLastSynced" class="text-[11px] text-slate-500"></span>
        </div>

        <!-- Agency Cards Grid -->
        <div id="agenciesGrid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <div class="col-span-full text-center py-12 text-slate-500 text-xs">Cargando agencias...</div>
        </div>
    </div>
</section>
