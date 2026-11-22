<!DOCTYPE html>
<html lang="es" class="h-full bg-slate-950 text-slate-100">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shalom API — Portal de Envíos Perú</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- jQuery & Select2 CDN -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <!-- HTML5 QR Code Scanner CDN -->
    <script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">
    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>
    <!-- App Custom Styles -->
    <link rel="stylesheet" href="assets/css/app.css">
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        brand: {
                            50: '#fff1f2',
                            100: '#ffe4e6',
                            200: '#fecdd3',
                            500: '#f43f5e',
                            600: '#e11d48',
                            700: '#be123c',
                            800: '#9f1239',
                            900: '#881337',
                            950: '#4c0519',
                        }
                    },
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'sans-serif'],
                        mono: ['"JetBrains Mono"', 'monospace'],
                    }
                }
            }
        }
    </script>
</head>
<body class="h-full flex flex-col font-sans antialiased selection:bg-brand-600 selection:text-white">

    <!-- Header / Navbar -->
    <header class="border-b border-slate-800 bg-slate-900/90 backdrop-blur sticky top-0 z-40">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-brand-700 to-rose-500 flex items-center justify-center shadow-lg shadow-brand-900/40 border border-brand-500/30">
                    <i data-lucide="package-search" class="w-5 h-5 text-white"></i>
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <span class="font-extrabold text-lg tracking-tight text-white">SHALOM <span class="text-rose-500 font-medium text-sm">API</span></span>
                        <span class="px-2 py-0.5 text-[10px] uppercase font-semibold bg-rose-950 text-rose-300 border border-rose-800/60 rounded-full flex items-center gap-1">
                            <i data-lucide="package" class="w-2.5 h-2.5"></i> Portal de Envíos
                        </span>
                    </div>
                    <p class="text-[11px] text-slate-400">Tracking, Agencias, Cotizador & DNI</p>
                </div>
            </div>

            <!-- API Status & Key Action -->
            <div class="flex items-center gap-3">
                <div id="keyBadge" class="hidden sm:flex items-center gap-2 px-3 py-1.5 rounded-lg text-xs border font-medium transition-all">
                    <span class="w-2 h-2 rounded-full animate-pulse"></span>
                    <span id="keyStatusText">Verificando...</span>
                </div>
                <button onclick="openKeyModal()" class="flex items-center gap-1.5 text-xs bg-slate-800 hover:bg-slate-700 border border-slate-700 text-slate-200 px-3 py-2 rounded-lg transition-all shadow-sm">
                    <i data-lucide="settings-2" class="w-4 h-4 text-rose-400"></i>
                    <span class="hidden md:inline">Configuración</span>
                </button>
            </div>
        </div>
    </header>

    <!-- Main Navigation Bar -->
    <div class="border-b border-slate-800/80 bg-slate-900/50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <nav class="flex gap-2 sm:gap-4 overflow-x-auto py-2.5 custom-scrollbar text-sm" id="tabsNav">
                <button onclick="switchTab('tracking')" id="tab-btn-tracking" class="tab-button active flex items-center gap-2 px-4 py-2 rounded-lg font-medium transition-all text-white bg-brand-700/80 shadow-md shadow-brand-900/20 border border-brand-600/50">
                    <i data-lucide="truck" class="w-4 h-4"></i> Rastrear Guía
                </button>
                <button onclick="switchTab('agencies')" id="tab-btn-agencies" class="tab-button flex items-center gap-2 px-4 py-2 rounded-lg font-medium transition-all text-slate-400 hover:text-white hover:bg-slate-800">
                    <i data-lucide="map-pin" class="w-4 h-4"></i> Agencias & Sedes
                </button>
                <button onclick="switchTab('quote')" id="tab-btn-quote" class="tab-button flex items-center gap-2 px-4 py-2 rounded-lg font-medium transition-all text-slate-400 hover:text-white hover:bg-slate-800">
                    <i data-lucide="calculator" class="w-4 h-4"></i> Cotizador
                </button>
                <button onclick="switchTab('dni')" id="tab-btn-dni" class="tab-button flex items-center gap-2 px-4 py-2 rounded-lg font-medium transition-all text-slate-400 hover:text-white hover:bg-slate-800">
                    <i data-lucide="user-check" class="w-4 h-4"></i> Consultar DNI
                </button>
                <button onclick="switchTab('pro')" id="tab-btn-pro" class="tab-button flex items-center gap-2 px-4 py-2 rounded-lg font-medium transition-all text-slate-400 hover:text-white hover:bg-slate-800">
                    <i data-lucide="shield-alert" class="w-4 h-4"></i> Shalom Pro Info
                </button>
            </nav>
        </div>
    </div>
