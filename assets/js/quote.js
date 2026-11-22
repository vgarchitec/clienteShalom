// --- QUOTE LOGIC ---
async function handleQuote(e) {
    e.preventDefault();
    const origin = ($('#quoteOrigin').val() || '').trim();
    const destination = ($('#quoteDest').val() || '').trim();

    if (!origin || !destination) {
        showToast('Por favor selecciona la agencia de origen y destino.', 'error');
        return;
    }
    const width = document.getElementById('quoteWidth').value.trim();
    const height = document.getElementById('quoteHeight').value.trim();
    const length = document.getElementById('quoteLength').value.trim();
    const weight = document.getElementById('quoteWeight').value.trim();

    const btn = document.getElementById('btnQuoteSubmit');
    const emptyState = document.getElementById('quoteEmptyState');
    const dataState = document.getElementById('quoteDataState');

    btn.disabled = true;
    btn.innerHTML = `<div class="w-4 h-4 border-2 border-white/20 border-t-white rounded-full animate-spin"></div> Calculando...`;

    try {
        const payload = { origin, destination };
        if (width && height && length && weight) {
            payload.width = width;
            payload.height = height;
            payload.length = length;
            payload.weight = weight;
        }

        const res = await fetch(`${API_URL}?action=quote`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        });
        const data = await res.json();

        if (!res.ok) {
            showToast(data.error || 'No se pudo cotizar.', 'error');
            return;
        }

        // Render Tariffs
        renderQuoteData(data);
        emptyState.classList.add('hidden');
        dataState.classList.remove('hidden');
        showToast('Tarifa calculada con éxito', 'success');

    } catch (err) {
        showToast('Error al conectar con el cotizador.', 'error');
    } finally {
        btn.disabled = false;
        btn.innerHTML = `<i data-lucide="calculator" class="w-4 h-4"></i> Calcular Tarifa`;
        lucide.createIcons();
    }
}

function renderQuoteData(res) {
    const data = res.data || {};
    const tariff = data.tariff || {};

    document.getElementById('quoteModeBadge').textContent = res.modo || 'estándar';

    const volCard = document.getElementById('quoteVolumeCard');
    if (data.price) {
        volCard.classList.remove('hidden');
        document.getElementById('quoteVolumePrice').textContent = `S/ ${data.price.toFixed(2)}`;
    } else {
        volCard.classList.add('hidden');
    }

    const cardsContainer = document.getElementById('tariffCards');
    cardsContainer.innerHTML = '';

    const packageNames = {
        'sobre': 'Sobre',
        'cajapaquetexxs': 'Paquete XXS',
        'cajapaquetexs': 'Paquete XS',
        'cajapaquetes': 'Paquete S',
        'cajapaquetem': 'Paquete M',
        'cajapaquetel': 'Paquete L'
    };

    for (const [key, price] of Object.entries(tariff)) {
        const card = document.createElement('div');
        card.className = 'p-3 rounded-xl bg-slate-950 border border-slate-800 flex items-center justify-between';
        card.innerHTML = `
            <span class="text-xs text-slate-300 font-medium">${packageNames[key] || key}</span>
            <span class="text-sm font-bold text-rose-400">S/ ${price}</span>
        `;
        cardsContainer.appendChild(card);
    }
}

// Initialize Select2 Agency Pickers
$(document).ready(function() {
    $('.select2-agency').select2({
        width: '100%',
        allowClear: true,
        language: {
            noResults: function() {
                return "No se encontraron agencias";
            },
            searching: function() {
                return "Buscando...";
            }
        }
    });
});
