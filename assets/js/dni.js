// --- DNI LOGIC ---
async function handleDni(e) {
    e.preventDefault();
    const dni = document.getElementById('dniInput').value.trim();
    const force = document.getElementById('dniForce').checked;
    const btn = document.getElementById('btnDniSubmit');
    const resultBox = document.getElementById('dniResult');
    const sourceBadge = document.getElementById('dniSourceBadge');

    btn.disabled = true;
    btn.innerHTML = `<div class="w-3.5 h-3.5 border-2 border-white/20 border-t-white rounded-full animate-spin"></div>`;

    try {
        const res = await fetch(`${API_URL}?action=dni&dni=${dni}&force=${force ? '1' : '0'}`);
        const data = await res.json();

        if (!res.ok) {
            showToast(data.error || 'DNI no encontrado o error en el servicio.', 'error');
            resultBox.classList.add('hidden');
            return;
        }

        const person = data.data || {};
        const fullName = `${person.nombres || ''} ${person.apellidoPaterno || ''} ${person.apellidoMaterno || ''}`.trim();
        
        document.getElementById('dniFullName').textContent = fullName || 'Persona encontrada';
        document.getElementById('dniNumber').textContent = person.dni || dni;
        document.getElementById('dniNames').textContent = person.nombres || '-';
        document.getElementById('dniSurnames').textContent = `${person.apellidoPaterno || ''} ${person.apellidoMaterno || ''}`.trim() || '-';

        sourceBadge.className = 'text-[10px] font-semibold px-2.5 py-1 rounded-full border bg-emerald-950/60 border-emerald-800 text-emerald-300 flex items-center gap-1';
        sourceBadge.innerHTML = `<i data-lucide="check-circle" class="w-3 h-3"></i> Verificado`;

        resultBox.classList.remove('hidden');
        showToast('Datos del DNI consultados con éxito', 'success');
        checkStatus();

    } catch (err) {
        showToast('Error de red al consultar DNI.', 'error');
    } finally {
        btn.disabled = false;
        btn.innerHTML = `<i data-lucide="search" class="w-4 h-4"></i> Consultar`;
        lucide.createIcons();
    }
}
