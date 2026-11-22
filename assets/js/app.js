// Base API URL helper (handles both /nova/shalom and /nova/shalom/ seamlessly)
const API_URL = (function() {
    let p = window.location.pathname;
    if (!p.endsWith('/') && !p.endsWith('.php')) {
        p += '/';
    }
    return p.endsWith('.php') ? p : p + 'index.php';
})();

let currentActiveOrder = null;
let currentActiveCode = null;

// Notification Helper
function showToast(message, type = 'info') {
    const toast = document.getElementById('toast');
    const toastMsg = document.getElementById('toastMessage');
    const toastIcon = document.getElementById('toastIcon');

    toastMsg.textContent = message;
    if (type === 'success') {
        toastIcon.setAttribute('data-lucide', 'check-circle');
        toastIcon.className = 'w-5 h-5 text-emerald-400 shrink-0';
    } else if (type === 'error') {
        toastIcon.setAttribute('data-lucide', 'alert-circle');
        toastIcon.className = 'w-5 h-5 text-rose-400 shrink-0';
    } else {
        toastIcon.setAttribute('data-lucide', 'info');
        toastIcon.className = 'w-5 h-5 text-sky-400 shrink-0';
    }
    lucide.createIcons();

    toast.classList.remove('translate-y-24', 'opacity-0');
    toast.classList.add('translate-y-0', 'opacity-100');

    setTimeout(() => {
        toast.classList.remove('translate-y-0', 'opacity-100');
        toast.classList.add('translate-y-24', 'opacity-0');
    }, 4000);
}

// Tab Navigation
function switchTab(tabId) {
    document.querySelectorAll('.tab-content').forEach(el => el.classList.add('hidden'));
    document.querySelectorAll('.tab-button').forEach(btn => {
        btn.classList.remove('text-white', 'bg-brand-700/80', 'border-brand-600/50', 'shadow-md');
        btn.classList.add('text-slate-400');
    });

    const activeContent = document.getElementById('tab-' + tabId);
    const activeBtn = document.getElementById('tab-btn-' + tabId);
    if (activeContent) activeContent.classList.remove('hidden');
    if (activeBtn) {
        activeBtn.classList.remove('text-slate-400');
        activeBtn.classList.add('text-white', 'bg-brand-700/80', 'border-brand-600/50', 'shadow-md');
    }

    if (tabId === 'quote') {
        setTimeout(() => {
            $('#quoteOrigin, #quoteDest').select2({ width: '100%' });
        }, 10);
    }
    if (tabId === 'agencies' && (!window.agenciesLoaded)) {
        loadAgencies();
    }
    if (tabId === 'tracking') {
        loadRecentTracks();
    }
}

// Modal: Settings
function openKeyModal() {
    document.getElementById('keyModal').classList.remove('hidden');
    document.getElementById('keyTestResult').classList.add('hidden');
    fetchSettings();
}

function closeKeyModal() {
    document.getElementById('keyModal').classList.add('hidden');
}

function toggleKeyVisibility() {
    const input = document.getElementById('apiKeyInput');
    const icon = document.getElementById('toggleKeyIcon');
    if (input.type === 'password') {
        input.type = 'text';
        icon.setAttribute('data-lucide', 'eye-off');
    } else {
        input.type = 'password';
        icon.setAttribute('data-lucide', 'eye');
    }
    lucide.createIcons();
}

async function fetchSettings() {
    try {
        const res = await fetch(`${API_URL}?action=get_settings`);
        const data = await res.json();
        if (data.settings) {
            if (data.settings.SHALOM_API_KEY) {
                document.getElementById('apiKeyInput').value = data.settings.SHALOM_API_KEY;
            }
            if (data.settings.SHALOM_INSTANCE_ID) {
                document.getElementById('instanceIdInput').value = data.settings.SHALOM_INSTANCE_ID;
            }
            if (data.settings.DEFAULT_ORIGIN) {
                document.getElementById('defaultOriginInput').value = data.settings.DEFAULT_ORIGIN;
            }
        }
    } catch (err) {
        console.error('Error al cargar configuración:', err);
    }
}

async function clearDbHistory() {
    if (!confirm('¿Deseas vaciar todo el historial de consultas recientes?')) return;
    try {
        const res = await fetch(`${API_URL}?action=clear_history`);
        const data = await res.json();
        if (data.success) {
            showToast('Historial de consultas vaciado', 'info');
            loadRecentTracks();
            checkStatus();
        }
    } catch (err) {
        showToast('Error al vaciar historial', 'error');
    }
}

async function checkStatus() {
    try {
        const res = await fetch(`${API_URL}?action=status`);
        const data = await res.json();
        const badge = document.getElementById('keyBadge');
        const text = document.getElementById('keyStatusText');

        if (data.configured && data.valid) {
            badge.className = 'flex items-center gap-2 px-3 py-1.5 rounded-lg text-xs border font-medium bg-emerald-950/60 border-emerald-800 text-emerald-300';
            const remaining = data.data.remaining !== null ? ` (Cuota: ${data.data.remaining})` : '';
            text.textContent = `API Key Activa${remaining}`;
        } else if (data.configured && !data.valid) {
            badge.className = 'flex items-center gap-2 px-3 py-1.5 rounded-lg text-xs border font-medium bg-rose-950/60 border-rose-800 text-rose-300';
            text.textContent = 'API Key Inválida';
        } else {
            badge.className = 'flex items-center gap-2 px-3 py-1.5 rounded-lg text-xs border font-medium bg-amber-950/50 border-amber-800 text-amber-300';
            text.textContent = 'Modo Público (Sin Key)';
        }
    } catch (err) {
        console.error('Error al comprobar estado:', err);
    }
}

async function handleSaveSettings(e) {
    e.preventDefault();
    const btn = document.getElementById('btnSaveKey');
    const apiKey = document.getElementById('apiKeyInput').value.trim();
    const instanceId = document.getElementById('instanceIdInput').value.trim();
    const defaultOrigin = document.getElementById('defaultOriginInput').value.trim();
    const resultBox = document.getElementById('keyTestResult');

    btn.disabled = true;
    btn.innerHTML = `<div class="w-4 h-4 border-2 border-white/20 border-t-white rounded-full animate-spin"></div> Guardando...`;

    try {
        const res = await fetch(`${API_URL}?action=save_settings`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ apiKey, instanceId, defaultOrigin })
        });
        const data = await res.json();

        if (res.ok && data.success) {
            resultBox.className = 'text-xs p-3 rounded-xl border bg-emerald-950/50 border-emerald-800 text-emerald-200 block';
            resultBox.textContent = '¡Configuración guardada correctamente!';
            showToast('Configuración guardada correctamente', 'success');
            setTimeout(() => {
                closeKeyModal();
                checkStatus();
            }, 1000);
        } else {
            resultBox.className = 'text-xs p-3 rounded-xl border bg-rose-950/50 border-rose-800 text-rose-200 block';
            resultBox.textContent = data.error || 'Error al guardar la configuración.';
        }
    } catch (err) {
        resultBox.className = 'text-xs p-3 rounded-xl border bg-rose-950/50 border-rose-800 text-rose-200 block';
        resultBox.textContent = 'Error de conexión con el servidor.';
    } finally {
        btn.disabled = false;
        btn.innerHTML = `<i data-lucide="save" class="w-4 h-4"></i> Guardar Configuración`;
        lucide.createIcons();
    }
}

// App Initialization
document.addEventListener('DOMContentLoaded', () => {
    lucide.createIcons();
    checkStatus();
    loadRecentTracks();
});
