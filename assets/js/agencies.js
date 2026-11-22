// --- AGENCIES LOGIC ---
async function loadAgencies(params = {}) {
    const grid = document.getElementById('agenciesGrid');
    const countText = document.getElementById('agenciesCount');
    const syncText = document.getElementById('agenciesLastSynced');

    grid.innerHTML = `<div class="col-span-full text-center py-12 text-slate-400 text-xs flex items-center justify-center gap-2">
        <div class="w-4 h-4 border-2 border-rose-500/30 border-t-rose-500 rounded-full animate-spin"></div>
        Cargando agencias...
    </div>`;

    try {
        const qs = new URLSearchParams(params).toString();
        const res = await fetch(`${API_URL}?action=agencies&${qs}`);
        const data = await res.json();

        if (!res.ok || !data.data) {
            grid.innerHTML = `<div class="col-span-full text-center py-8 text-rose-400 text-xs">${data.error || 'No se pudieron cargar agencias'}</div>`;
            return;
        }

        const agencies = data.data;
        window.agenciesLoaded = true;
        countText.textContent = `Mostrando ${agencies.length} agencias`;
        if (data.last_synced) {
            syncText.textContent = `Última sincro API: ${data.last_synced}`;
        }

        if (agencies.length === 0) {
            grid.innerHTML = `<div class="col-span-full text-center py-8 text-slate-400 text-xs">No se encontraron agencias con ese criterio.</div>`;
            return;
        }

        grid.innerHTML = '';
        agencies.forEach(agency => {
            const card = document.createElement('div');
            card.className = 'bg-slate-950 border border-slate-800 hover:border-slate-700 rounded-xl p-4 transition-all flex flex-col justify-between group';
            
            const isAereo = agency.ter_aereo == 1;
            const mapsUrl = `https://www.google.com/maps/search/?api=1&query=${agency.latitud},${agency.longitud}`;
            const distBadge = agency.distancia_km !== null && agency.distancia_km !== undefined 
                ? `<span class="px-2 py-0.5 rounded text-[10px] bg-rose-950 text-rose-300 font-mono font-bold">${parseFloat(agency.distancia_km).toFixed(1)} km</span>` 
                : '';

            card.innerHTML = `
                <div>
                    <div class="flex items-start justify-between gap-2 mb-2">
                        <span class="font-mono text-xs text-rose-400 font-bold">ID: ${agency.ter_id}</span>
                        <div class="flex items-center gap-1.5">
                            ${distBadge}
                            ${isAereo ? '<span class="px-1.5 py-0.5 text-[9px] uppercase font-bold bg-sky-950 text-sky-300 border border-sky-800 rounded">Aéreo</span>' : ''}
                        </div>
                    </div>
                    <h4 class="text-xs font-bold text-white leading-snug mb-1 group-hover:text-rose-400 transition-colors">${agency.nombre || agency.lugar_over || 'Agencia Shalom'}</h4>
                    <p class="text-[11px] text-slate-400 mb-2">${agency.direccion || 'Sin dirección'}</p>
                    
                    <div class="space-y-1 text-[10px] text-slate-500 mb-3">
                        <div><strong class="text-slate-400">Horario:</strong> ${agency.hora_atencion || 'No especificado'}</div>
                        <div><strong class="text-slate-400">Tel:</strong> ${agency.telefono || '(01) 500 7878'}</div>
                    </div>
                </div>

                <div class="pt-3 border-t border-slate-900 flex items-center justify-between gap-2 text-xs">
                    <a href="${mapsUrl}" target="_blank" class="text-slate-400 hover:text-white flex items-center gap-1 text-[11px]">
                        <i data-lucide="map" class="w-3.5 h-3.5 text-rose-400"></i> Mapa
                    </a>
                    <div class="flex items-center gap-1">
                        <button onclick="selectForQuote('origin', ${agency.ter_id}, '${escapeQuoteName(agency.nombre)}')" class="px-2 py-1 rounded bg-slate-800 hover:bg-slate-700 text-[10px] text-slate-200">
                            Origen
                        </button>
                        <button onclick="selectForQuote('destination', ${agency.ter_id}, '${escapeQuoteName(agency.nombre)}')" class="px-2 py-1 rounded bg-slate-800 hover:bg-slate-700 text-[10px] text-slate-200">
                            Destino
                        </button>
                    </div>
                </div>
            `;
            grid.appendChild(card);
        });
        lucide.createIcons();
    } catch (err) {
        grid.innerHTML = `<div class="col-span-full text-center py-8 text-rose-400 text-xs">Error al cargar agencias.</div>`;
    }
}

async function syncAgencies(forced = false) {
    const btn = document.getElementById('btnSyncAgencies');
    btn.disabled = true;
    btn.innerHTML = `<div class="w-3.5 h-3.5 border-2 border-white/20 border-t-white rounded-full animate-spin"></div> Sincronizando...`;

    try {
        const res = await fetch(`${API_URL}?action=sync_agencies`);
        const data = await res.json();
        if (res.ok && data.success) {
            showToast('Catálogo de agencias actualizado', 'success');
            loadAgencies();
            checkStatus();
        } else {
            showToast(data.error || 'Error al sincronizar catálogo', 'error');
        }
    } catch (err) {
        showToast('Error de conexión al sincronizar', 'error');
    } finally {
        btn.disabled = false;
        btn.innerHTML = `<i data-lucide="refresh-cw" class="w-4 h-4"></i> Actualizar Catálogo`;
        lucide.createIcons();
    }
}

function escapeQuoteName(name) {
    return (name || '').replace(/'/g, "\\'").replace(/"/g, "&quot;");
}

function handleAgenciesSearch(e) {
    e.preventDefault();
    const q = document.getElementById('agencyQ').value.trim();
    const departamento = document.getElementById('agencyDept').value.trim();
    const aereo = document.getElementById('agencyAereo').checked;

    const params = {};
    if (q) params.q = q;
    if (departamento) params.departamento = departamento;
    if (aereo) params.aereo = 'true';

    loadAgencies(params);
}

function getUserLocation() {
    if (!navigator.geolocation) {
        showToast('Tu navegador no soporta geolocalización.', 'error');
        return;
    }
    const btn = document.getElementById('btnNearby');
    btn.innerHTML = `<div class="w-3.5 h-3.5 border-2 border-slate-400 border-t-white rounded-full animate-spin"></div> Calculando distancia...`;

    navigator.geolocation.getCurrentPosition(
        pos => {
            const lat = pos.coords.latitude.toFixed(6);
            const lng = pos.coords.longitude.toFixed(6);
            btn.innerHTML = `<i data-lucide="crosshair" class="w-4 h-4 text-rose-400"></i> Cerca de Mí (GPS)`;
            lucide.createIcons();
            showToast('Agencias ordenadas por cercanía a tu ubicación', 'success');
            loadAgencies({ near: `${lat},${lng}`, per_page: 25 });
        },
        err => {
            btn.innerHTML = `<i data-lucide="crosshair" class="w-4 h-4 text-rose-400"></i> Cerca de Mí (GPS)`;
            lucide.createIcons();
            showToast('Permiso de ubicación denegado.', 'error');
        }
    );
}

function selectForQuote(target, id, name) {
    if (target === 'origin') {
        $('#quoteOrigin').val(id).trigger('change');
        showToast(`Agencia seleccionada como Origen`, 'info');
    } else {
        $('#quoteDest').val(id).trigger('change');
        showToast(`Agencia seleccionada como Destino`, 'info');
    }
    switchTab('quote');
}
