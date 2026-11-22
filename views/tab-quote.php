        <!-- ================= TAB: QUOTE ================= -->
        <section id="tab-quote" class="tab-content hidden space-y-6">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Quote Form -->
                <div class="lg:col-span-1 bg-slate-900 border border-slate-800 rounded-2xl p-6 shadow-xl">
                    <div class="flex items-center gap-2 text-rose-400 text-sm font-semibold mb-2">
                        <i data-lucide="scale" class="w-4 h-4"></i>
                        <span>Cotización de Flete</span>
                    </div>
                    <h2 class="text-xl font-bold text-white mb-2">Cotizar Envío</h2>
                    <p class="text-slate-400 text-xs leading-relaxed mb-6">
                        Calcula el costo del envío entre dos sedes de Shalom. Opcionalmente añade medidas para cotizar cajas especiales por volumen.
                    </p>

                    <form id="quoteForm" onsubmit="handleQuote(event)" class="space-y-4">
                        <div>
                            <label class="block text-xs font-medium text-slate-300 mb-1">Agencia Origen</label>
                            <select id="quoteOrigin" class="select2-agency w-full" data-placeholder="Buscar o seleccionar agencia de origen...">
                                <option value=""></option>
                                <?php if (!empty($agencies_by_dept)): ?>
                                    <?php foreach ($agencies_by_dept as $dept => $list): ?>
                                        <optgroup label="<?= htmlspecialchars($dept) ?>">
                                            <?php foreach ($list as $ag): ?>
                                                <option value="<?= htmlspecialchars($ag['ter_id']) ?>">
                                                    <?= htmlspecialchars($ag['nombre']) ?> — ID: <?= $ag['ter_id'] ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </optgroup>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                            <span class="text-[10px] text-slate-500">Escribe el nombre, distrito o ID para filtrar</span>
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-slate-300 mb-1">Agencia Destino</label>
                            <select id="quoteDest" class="select2-agency w-full" data-placeholder="Buscar o seleccionar agencia de destino...">
                                <option value=""></option>
                                <?php if (!empty($agencies_by_dept)): ?>
                                    <?php foreach ($agencies_by_dept as $dept => $list): ?>
                                        <optgroup label="<?= htmlspecialchars($dept) ?>">
                                            <?php foreach ($list as $ag): ?>
                                                <option value="<?= htmlspecialchars($ag['ter_id']) ?>">
                                                    <?= htmlspecialchars($ag['nombre']) ?> — ID: <?= $ag['ter_id'] ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </optgroup>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                            <span class="text-[10px] text-slate-500">Escribe el nombre, distrito o ID para filtrar</span>
                        </div>

                        <div class="pt-2 border-t border-slate-800">
                            <span class="block text-xs font-semibold text-slate-300 mb-2">Medidas Volumétricas (Opcional - "Otra Medida")</span>
                            <div class="grid grid-cols-2 gap-2 text-xs">
                                <div>
                                    <label class="text-[10px] text-slate-400">Ancho (m, máx 1.5)</label>
                                    <input type="number" step="0.01" id="quoteWidth" placeholder="Ej: 0.30" class="w-full bg-slate-950 border border-slate-700 rounded-lg p-2 text-white">
                                </div>
                                <div>
                                    <label class="text-[10px] text-slate-400">Alto (m, máx 1.5)</label>
                                    <input type="number" step="0.01" id="quoteHeight" placeholder="Ej: 0.20" class="w-full bg-slate-950 border border-slate-700 rounded-lg p-2 text-white">
                                </div>
                                <div>
                                    <label class="text-[10px] text-slate-400">Largo (m, máx 1.5)</label>
                                    <input type="number" step="0.01" id="quoteLength" placeholder="Ej: 0.40" class="w-full bg-slate-950 border border-slate-700 rounded-lg p-2 text-white">
                                </div>
                                <div>
                                    <label class="text-[10px] text-slate-400">Peso (kg, máx 500)</label>
                                    <input type="number" step="0.1" id="quoteWeight" placeholder="Ej: 3.5" class="w-full bg-slate-950 border border-slate-700 rounded-lg p-2 text-white">
                                </div>
                            </div>
                        </div>

                        <button type="submit" id="btnQuoteSubmit"
                            class="w-full mt-3 bg-gradient-to-r from-brand-700 to-rose-600 hover:from-brand-600 hover:to-rose-500 text-white font-medium text-xs py-2.5 px-4 rounded-xl shadow-lg transition-all flex items-center justify-center gap-2">
                            <i data-lucide="calculator" class="w-4 h-4"></i> Calcular Tarifa
                        </button>
                    </form>
                </div>

                <!-- Quote Results -->
                <div class="lg:col-span-2 bg-slate-900 border border-slate-800 rounded-2xl p-6 shadow-xl flex flex-col justify-center" id="quoteResultContainer">
                    <div id="quoteEmptyState" class="text-center py-16 text-slate-500 text-xs">
                        <i data-lucide="coins" class="w-10 h-10 mx-auto mb-2 text-slate-600"></i>
                        Ingresa origen y destino para calcular las tarifas.
                    </div>

                    <div id="quoteDataState" class="hidden space-y-6">
                        <div class="p-4 rounded-xl bg-slate-950 border border-slate-800 flex items-center justify-between">
                            <div>
                                <span class="text-[10px] uppercase font-bold text-slate-400">Modo de Cotización</span>
                                <p id="quoteModeBadge" class="text-sm font-semibold text-rose-400 capitalize">Estándar</p>
                            </div>
                            <div id="quoteVolumeCard" class="hidden text-right">
                                <span class="text-[10px] uppercase font-bold text-slate-400">Precio Otra Medida</span>
                                <p id="quoteVolumePrice" class="text-xl font-bold text-emerald-400">S/ 0.00</p>
                            </div>
                        </div>

                        <div>
                            <h4 class="text-xs font-semibold uppercase tracking-wider text-slate-400 mb-3">Tarifas por Tipo de Paquete Fijo</h4>
                            <div class="grid grid-cols-2 sm:grid-cols-3 gap-3" id="tariffCards">
                                <!-- Generated by JS -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
