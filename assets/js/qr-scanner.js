// --- QR SCANNER LOGIC ---
let html5QrScanner = null;
let qrCurrentMode = 'camera';

function openQrModal() {
    document.getElementById('qrModal').classList.remove('hidden');
    switchQrMode('camera');
    lucide.createIcons();
}

function closeQrModal() {
    stopQrCamera();
    document.getElementById('qrModal').classList.add('hidden');
}

function switchQrMode(mode) {
    qrCurrentMode = mode;
    const btnCam = document.getElementById('qrTabCamera');
    const btnFile = document.getElementById('qrTabFile');
    const secCam = document.getElementById('qrCameraSection');
    const secFile = document.getElementById('qrFileSection');

    if (mode === 'camera') {
        btnCam.className = 'flex-1 py-1.5 rounded-lg font-medium text-center transition-all bg-rose-600 text-white';
        btnFile.className = 'flex-1 py-1.5 rounded-lg font-medium text-center transition-all text-slate-400 hover:text-white';
        secCam.classList.remove('hidden');
        secFile.classList.add('hidden');
        startQrCamera();
    } else {
        btnFile.className = 'flex-1 py-1.5 rounded-lg font-medium text-center transition-all bg-rose-600 text-white';
        btnCam.className = 'flex-1 py-1.5 rounded-lg font-medium text-center transition-all text-slate-400 hover:text-white';
        secFile.classList.remove('hidden');
        secCam.classList.add('hidden');
        stopQrCamera();
    }
    lucide.createIcons();
}

function startQrCamera() {
    const qrBox = document.getElementById('qrReader');
    if (!qrBox) return;

    if (html5QrScanner) {
        stopQrCamera();
    }

    try {
        html5QrScanner = new Html5Qrcode("qrReader");
        const config = {
            fps: 10,
            qrbox: function(viewfinderWidth, viewfinderHeight) {
                const edgeSize = Math.floor(Math.min(viewfinderWidth, viewfinderHeight) * 0.75);
                return { width: edgeSize, height: edgeSize };
            }
        };

        html5QrScanner.start(
            { facingMode: "environment" },
            config,
            (decodedText) => {
                stopQrCamera();
                processScannedQr(decodedText);
            },
            () => {
                // ignore frame tick
            }
        ).catch(err => {
            console.warn("No se pudo iniciar la cámara:", err);
            const placeholder = document.getElementById('qrCameraPlaceholder');
            if (placeholder) {
                placeholder.innerHTML = `
                    <i data-lucide="camera-off" class="w-8 h-8 mx-auto mb-2 text-rose-500"></i>
                    <span class="text-rose-300 font-semibold block mb-1">Cámara no disponible</span>
                    <span class="text-slate-400 text-[11px] block">Puedes usar la pestaña <strong>Subir Imagen</strong> para leer una foto del ticket.</span>
                `;
                lucide.createIcons();
            }
        });
    } catch (e) {
        console.error("Html5Qrcode error:", e);
    }
}

function stopQrCamera() {
    if (html5QrScanner) {
        try {
            html5QrScanner.stop().catch(() => {}).finally(() => {
                html5QrScanner = null;
            });
        } catch (e) {
            html5QrScanner = null;
        }
    }
}

async function handleQrFileSelected(event) {
    const file = event.target.files[0];
    if (!file) return;

    const statusBox = document.getElementById('qrFileStatus');
    statusBox.className = 'text-xs text-center text-slate-300 block';
    statusBox.innerHTML = '<div class="w-4 h-4 border-2 border-rose-500/30 border-t-rose-500 rounded-full animate-spin inline-block mr-1"></div> Analizando imagen...';

    try {
        const scanner = new Html5Qrcode("qrReader");
        const decodedText = await scanner.scanFile(file, true);
        statusBox.classList.add('hidden');
        processScannedQr(decodedText);
    } catch (err) {
        statusBox.className = 'text-xs text-center text-rose-400 block';
        statusBox.textContent = 'No se detectó un código QR válido en la imagen. Intenta con una foto más clara.';
        showToast('No se encontró un código QR en la imagen.', 'error');
    } finally {
        event.target.value = '';
    }
}

function processScannedQr(decodedText) {
    decodedText = (decodedText || '').trim();
    console.log("QR decodificado:", decodedText);

    // 1. Direct Pattern: "104425275/H733" or "104425275 - H733"
    const directMatch = decodedText.match(/^(\d{8,12})\s*[\/\-]\s*([a-zA-Z0-9]{4})$/);
    if (directMatch) {
        document.getElementById('trackOrderNumber').value = directMatch[1];
        document.getElementById('trackOrderCode').value = directMatch[2].toUpperCase();
        closeQrModal();
        showToast(`QR detectado: Guía #${directMatch[1]}`, 'success');
        handleTrack(new Event('submit'));
        return;
    }

    // 2. Shalom Standard QR: "107869605/document/1/" or "107869605/document" or "107869605"
    const oseMatch = decodedText.match(/^(\d{7,15})(?:\/document(?:\/\d*)?)?/i);
    if (oseMatch) {
        const oseId = oseMatch[1];
        resolveOseAndTrack(oseId);
        return;
    }

    // 3. Fallback: Any 7-15 digit number in string
    const digitsMatch = decodedText.match(/\b\d{7,15}\b/);
    if (digitsMatch) {
        resolveOseAndTrack(digitsMatch[0]);
        return;
    }

    showToast('Formato QR no reconocido: ' + decodedText.substring(0, 30), 'error');
}

async function resolveOseAndTrack(oseId) {
    showToast(`Resolviendo código QR (${oseId})...`, 'info');
    closeQrModal();

    try {
        const res = await fetch(`${API_URL}?action=track_by_ose&ose_id=${oseId}`);
        const data = await res.json();

        if (!res.ok || !data.success) {
            showToast(data.error || 'No se pudo resolver el envío con el código QR.', 'error');
            return;
        }

        document.getElementById('trackOrderNumber').value = data.orderNumber;
        document.getElementById('trackOrderCode').value = data.orderCode;
        showToast(`QR resuelto: Orden #${data.orderNumber} • ${data.orderCode}`, 'success');

        // Execute track immediately
        handleTrack(new Event('submit'));

    } catch (err) {
        console.error("Error al resolver OSE:", err);
        showToast('Error de conexión al resolver el código QR.', 'error');
    }
}
