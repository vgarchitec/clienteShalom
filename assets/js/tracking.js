// --- TRACKING LOGIC ---

function forceRefreshCurrentTrack() {
    if (!currentActiveOrder || !currentActiveCode) return;
    document.getElementById('trackOrderNumber').value = currentActiveOrder;
    document.getElementById('trackOrderCode').value = currentActiveCode;
    document.getElementById('trackForce').checked = true;
    handleTrack(new Event('submit'));
}

async function handleTrack(e) {
    if (e) e.preventDefault();
    const orderNumber = document.getElementById('trackOrderNumber').value.trim();
    const orderCode = document.getElementById('trackOrderCode').value.trim().toUpperCase();
    const force = document.getElementById('trackForce').checked;

    const emptyState = document.getElementById('trackEmptyState');
    const loadingState = document.getElementById('trackLoadingState');
    const dataState = document.getElementById('trackDataState');
    const btnSubmit = document.getElementById('btnTrackSubmit');

    emptyState.classList.add('hidden');
    dataState.classList.add('hidden');
    loadingState.classList.remove('hidden');
    btnSubmit.disabled = true;

    try {
        const res = await fetch(`${API_URL}?action=track`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ orderNumber, orderCode, force })
        });

        const text = await res.text();
        let data;
        try {
            data = JSON.parse(text);
        } catch (parseErr) {
            console.error("Respuesta no es JSON:", text);
            showToast('Error del servidor: ' + text.replace(/<[^>]*>?/gm, '').substring(0, 100), 'error');
            emptyState.classList.remove('hidden');
            loadingState.classList.add('hidden');
            return;
        }

        if (!res.ok) {
            showToast(data.error || 'No se pudo consultar el envío.', 'error');
            emptyState.classList.remove('hidden');
            loadingState.classList.add('hidden');
            return;
        }

        if (data.search && data.search.success === false) {
            showToast(data.search.message || 'Guía no encontrada. Revisa los datos.', 'error');
            emptyState.classList.remove('hidden');
            loadingState.classList.add('hidden');
            return;
        }

        currentActiveOrder = orderNumber;
        currentActiveCode = orderCode;

        // Render Result
        renderTrackData(data);
        loadingState.classList.add('hidden');
        dataState.classList.remove('hidden');

        if (data.from_cache) {
            showToast('Información cargada correctamente', 'success');
        } else {
            showToast('Estado actualizado en vivo', 'success');
        }

        // Refresh history & stats safely
        try { loadRecentTracks(); } catch (e) {}
        try { checkStatus(); } catch (e) {}

    } catch (err) {
        showToast(err.message || 'No se pudo conectar con el servicio de rastreo.', 'error');
        emptyState.classList.remove('hidden');
        loadingState.classList.add('hidden');
    } finally {
        btnSubmit.disabled = false;
    }
}

function renderTrackData(res) {
    const info = res.search?.data || {};
    const statuses = res.statuses || {};

    const sourceBadge = document.getElementById('trackSourceBadge');
    sourceBadge.className = 'px-2 py-0.5 rounded text-[10px] font-semibold border bg-slate-800/80 border-slate-700 text-slate-300 flex items-center gap-1';
    sourceBadge.innerHTML = `<i data-lucide="clock" class="w-3 h-3 text-rose-400"></i> ${res.cached_at || 'Actualizado'}`;

    document.getElementById('trackStatusBadge').textContent = statuses.message || (info.entregado ? 'Entregado' : 'En proceso');
    document.getElementById('trackOrderBadge').textContent = `#${info.numero_orden || ''} • ${info.codigo_orden || ''}`;
    document.getElementById('trackContent').textContent = info.contenido || 'Paquete Shalom';

    document.getElementById('trackOrigin').textContent = info.origen?.nombre || info.origen || 'No especificado';
    document.getElementById('trackDestination').textContent = info.destino?.nombre || info.destino || 'No especificado';
    document.getElementById('trackSender').textContent = info.remitente?.nombre || info.remitente || '-';
    document.getElementById('trackReceiver').textContent = info.destinatario?.nombre || info.destinatario || '-';
    document.getElementById('trackAmount').textContent = info.monto ? `S/ ${info.monto}` : (info.pago || 'Pagado');
    
    // Safe Invoice rendering
    let invoiceText = 'Sin comprobante';
    if (info.comprobante && typeof info.comprobante === 'object') {
        const comp = info.comprobante;
        const num = (comp.serie && comp.numero) ? `${comp.serie}-${comp.numero}` : (comp.serie || comp.numero || comp.tipo || '');
        if (num) invoiceText = num;
    } else if (info.comprobante && typeof info.comprobante === 'string') {
        invoiceText = info.comprobante;
    } else if (info.ticket) {
        invoiceText = typeof info.ticket === 'string' ? info.ticket : 'Ticket emitido';
    }
    document.getElementById('trackInvoice').textContent = invoiceText;
    document.getElementById('trackSavedAt').textContent = res.cached_at || 'Reciente';

    // Build Timeline
    const timelineContainer = document.getElementById('timelineCards');
    timelineContainer.innerHTML = '';

    const stages = [
        { key: 'registrado', label: 'Registrado' },
        { key: 'origen', label: 'En Origen' },
        { key: 'transito', label: 'En Tránsito' },
        { key: 'destino', label: 'En Destino' },
        { key: 'entregado', label: 'Entregado' }
    ];

    const stData = statuses.data || {};

    stages.forEach(stage => {
        const item = stData[stage.key];
        const isPassed = item && item.fecha;

        const card = document.createElement('div');
        card.className = `p-3 rounded-xl border text-center transition-all ${
            isPassed 
                ? 'bg-rose-950/40 border-rose-800 text-rose-200' 
                : 'bg-slate-950 border-slate-800 text-slate-500'
        }`;
        card.innerHTML = `
            <div class="w-6 h-6 rounded-full mx-auto mb-2 flex items-center justify-center text-xs font-bold ${
                isPassed ? 'bg-rose-600 text-white' : 'bg-slate-800 text-slate-500'
            }">
                ${isPassed ? '✓' : '•'}
            </div>
            <span class="block text-xs font-semibold ${isPassed ? 'text-white' : 'text-slate-400'}">${stage.label}</span>
            <span class="block text-[10px] mt-1 font-mono text-slate-400">${isPassed ? item.fecha.split(' ')[0] : 'Pendiente'}</span>
            <span class="block text-[9px] text-slate-500">${isPassed ? (item.fecha.split(' ')[1] || '') : ''}</span>
        `;
        timelineContainer.appendChild(card);
    });

    // Raw JSON
    document.getElementById('trackRawJson').textContent = JSON.stringify(res, null, 2);
    lucide.createIcons();
}

// --- STATUS COLOR BADGE HELPER ---
function getStatusBadge(statusText) {
    const s = (statusText || '').toLowerCase().trim();

    // 1. En destino -> VERDE
    if (s.includes('destino')) {
        return `<span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-950/80 text-emerald-300 border border-emerald-700/60 shadow-sm">
            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span> En destino
        </span>`;
    }
    // 2. En tránsito -> NARANJA
    if (s.includes('transito') || s.includes('tránsito') || s.includes('camino')) {
        return `<span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-950/80 text-amber-300 border border-amber-700/60 shadow-sm">
            <span class="w-1.5 h-1.5 rounded-full bg-amber-400"></span> En tránsito
        </span>`;
    }
    // 3. Entregado -> AZUL
    if (s.includes('entregado') || s.includes('recibido')) {
        return `<span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-sky-950/80 text-sky-300 border border-sky-700/60 shadow-sm">
            <span class="w-1.5 h-1.5 rounded-full bg-sky-400"></span> Entregado
        </span>`;
    }
    // 4. Registrado / En origen -> ROJO
    if (s.includes('registrado') || s.includes('origen') || s.includes('emision') || s.includes('emisión')) {
        return `<span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-rose-950/80 text-rose-300 border border-rose-700/60 shadow-sm">
            <span class="w-1.5 h-1.5 rounded-full bg-rose-400"></span> Registrado
        </span>`;
    }
    // 5. Demora de envíos -> Naranja/Ámbar con icono
    if (s.includes('demora') || s.includes('retraso')) {
        return `<span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-950/90 text-amber-200 border border-amber-600/70 shadow-sm">
            <i data-lucide="alert-circle" class="w-3 h-3 text-amber-400"></i> Demora de envíos
        </span>`;
    }

    // Default
    return `<span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-800 text-slate-300 border border-slate-700">
        ${escapeHtml(statusText || 'En proceso')}
    </span>`;
}

function escapeHtml(str) {
    if (!str) return '';
    return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
}

// --- RECENT TRACKS ---
async function loadRecentTracks() {
    try {
        const res = await fetch(`${API_URL}?action=recent_tracks`);
        const data = await res.json();
        const tbody = document.getElementById('historyTableBody');
        const countBadge = document.getElementById('historyCount');

        if (!res.ok || !data.data || data.data.length === 0) {
            tbody.innerHTML = `<tr><td colspan="7" class="py-6 text-center text-slate-500">No hay consultas recientes aún.</td></tr>`;
            countBadge.textContent = '0';
            return;
        }

        countBadge.textContent = data.data.length;
        tbody.innerHTML = '';

        data.data.forEach(item => {
            const tr = document.createElement('tr');
            tr.className = 'border-b border-slate-800/60 hover:bg-slate-800/40 transition-colors';

            // 1. Orden / Código
            const colOrder = `<td class="py-2.5 px-3 font-mono text-xs whitespace-nowrap">
                <span class="font-bold text-white">${item.order_number}</span>
                <span class="text-slate-600 mx-1">/</span>
                <span class="font-semibold text-rose-400">${item.order_code}</span>
            </td>`;

            // 2. Fecha del estado actual
            const colDate = `<td class="py-2.5 px-3 font-mono text-[11px] text-slate-300 whitespace-nowrap">
                <div class="flex items-center gap-1.5">
                    <i data-lucide="clock" class="w-3 h-3 text-slate-500"></i>
                    <span>${item.status_date || item.updated_at || '-'}</span>
                </div>
            </td>`;

            // 3. Estado actual con color referencial
            const colStatus = `<td class="py-2.5 px-3 whitespace-nowrap">
                ${getStatusBadge(item.status_message)}
            </td>`;

            // 4. Nombre del que recepciona
            const colReceiver = `<td class="py-2.5 px-3 text-slate-200 font-medium">
                <div class="flex items-center gap-1.5 max-w-[200px]" title="${escapeHtml(item.receiver_name)}">
                    <i data-lucide="user" class="w-3 h-3 text-slate-500 shrink-0"></i>
                    <span class="truncate text-xs">${escapeHtml(item.receiver_name)}</span>
                </div>
            </td>`;

            // 5. Contenido
            const colContent = `<td class="py-2.5 px-3 text-slate-400 text-xs max-w-[160px] truncate" title="${escapeHtml(item.content)}">
                ${escapeHtml(item.content)}
            </td>`;

            // 6. Monto
            const amt = parseFloat(item.amount);
            const amountFormatted = (!isNaN(amt) && amt > 0) ? `S/ ${amt.toFixed(2)}` : (item.amount ? `S/ ${item.amount}` : '-');
            const colAmount = `<td class="py-2.5 px-3 font-mono font-semibold text-xs text-emerald-400 whitespace-nowrap">
                ${amountFormatted}
            </td>`;

            // 7. Acciones
            const colActions = `<td class="py-2.5 px-3 text-right whitespace-nowrap">
                <div class="flex items-center justify-end gap-1.5">
                    <button onclick="quickViewTrack('${item.order_number}', '${item.order_code}')" 
                        class="px-2.5 py-1 bg-rose-600 hover:bg-rose-500 text-white rounded-lg text-xs font-semibold transition-all flex items-center gap-1 shadow-sm">
                        <i data-lucide="eye" class="w-3 h-3"></i> Ver
                    </button>
                    <button onclick="deleteTrackItem(${item.id})" class="p-1 text-slate-500 hover:text-rose-400 rounded transition-colors" title="Eliminar del historial">
                        <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                    </button>
                </div>
            </td>`;

            tr.innerHTML = colOrder + colDate + colStatus + colReceiver + colContent + colAmount + colActions;
            tbody.appendChild(tr);
        });
        lucide.createIcons();
    } catch (err) {
        console.error('Error al cargar historial:', err);
    }
}

function quickViewTrack(order, code) {
    switchTab('tracking');
    document.getElementById('trackOrderNumber').value = order;
    document.getElementById('trackOrderCode').value = code;
    document.getElementById('trackForce').checked = false;
    handleTrack(new Event('submit'));
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

async function deleteTrackItem(id) {
    if (!confirm('¿Deseas eliminar este registro del historial?')) return;
    try {
        await fetch(`${API_URL}?action=delete_track&id=${id}`);
        showToast('Registro eliminado del historial', 'info');
        loadRecentTracks();
        checkStatus();
    } catch (err) {
        showToast('Error al eliminar', 'error');
    }
}
