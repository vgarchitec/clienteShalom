        <!-- ================= TAB: SHALOM PRO INFO ================= -->
        <section id="tab-pro" class="tab-content hidden space-y-6">
            <div class="max-w-3xl mx-auto bg-slate-900 border border-slate-800 rounded-2xl p-6 sm:p-8 shadow-xl space-y-6">
                <div>
                    <span class="text-rose-400 text-xs font-semibold uppercase tracking-wider">Integración Avanzada</span>
                    <h2 class="text-xl font-bold text-white mt-1">Shalom Pro & Creación de Guías</h2>
                    <p class="text-slate-400 text-xs mt-1">
                        Para crear guías reales, anular pendientes o descargar rótulos se requiere una cuenta de Shalom Pro conectada mediante una instancia.
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="p-4 rounded-xl bg-slate-950 border border-slate-800">
                        <h3 class="text-sm font-semibold text-white flex items-center gap-2 mb-2">
                            <i data-lucide="server" class="w-4 h-4 text-rose-400"></i> ¿Cómo funciona la Instancia?
                        </h3>
                        <p class="text-xs text-slate-400 leading-relaxed">
                            Creas una instancia con <code class="text-rose-300 font-mono text-[11px]">POST /instances</code> e inicias sesión una sola vez con <code class="text-rose-300 font-mono text-[11px]">POST /instances/login</code>. La API mantiene y reconecta la sesión automáticamente.
                        </p>
                    </div>

                    <div class="p-4 rounded-xl bg-slate-950 border border-slate-800">
                        <h3 class="text-sm font-semibold text-white flex items-center gap-2 mb-2">
                            <i data-lucide="file-text" class="w-4 h-4 text-emerald-400"></i> Rótulo y Comprobantes
                        </h3>
                        <p class="text-xs text-slate-400 leading-relaxed">
                            Con tu <code class="text-emerald-300 font-mono text-[11px]">instanceId</code> puedes descargar los rótulos oficiales en PDF (<code class="text-emerald-300 font-mono text-[11px]">GET /track/label</code>) y los tickets (<code class="text-emerald-300 font-mono text-[11px]">GET /track/voucher</code>).
                        </p>
                    </div>
                </div>

                <div class="p-4 rounded-xl bg-brand-950/40 border border-brand-800/50 text-xs text-rose-200 space-y-2">
                    <div class="font-semibold flex items-center gap-1.5 text-rose-300">
                        <i data-lucide="alert-triangle" class="w-4 h-4"></i> Reglas Críticas para Desarrolladores:
                    </div>
                    <ul class="list-disc list-inside space-y-1 text-slate-300">
                        <li><strong>No uses polling:</strong> Usa Webhooks (<code class="font-mono text-rose-300">PUT /webhooks</code>) con verificación HMAC-SHA256 en vez de loops periódicos.</li>
                        <li><strong>Plan gratuito:</strong> Permite tracking y catálogo de agencias (5 ops/mes), pero no creación de guías.</li>
                        <li><strong>Catálogo optimizado:</strong> El catálogo de agencias se sincroniza periódicamente para permitir búsquedas y cálculo de cercanía por GPS al instante.</li>
                    </ul>
                </div>
            </div>
        </section>
