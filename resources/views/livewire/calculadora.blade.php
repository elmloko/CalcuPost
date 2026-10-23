<section class="form-register">
        <header class="calculator-heading">
            <h2>Calculadora postal</h2>
            <p>Elige tu servicio, destino y peso para consultar una tarifa. Los campos con * son obligatorios.</p>
        </header>
        <label class="field-label" id="servicio-label" for="servicio">Servicio postal *</label>
        <select class="controls" name="servicio" id="servicio" aria-labelledby="servicio-label" wire:model.live="servicio">
            <option value="">Seleccione un Servicio Postal</option>
            @foreach ($this->catalogoServicios as $codigo => $nombre)
                <option value="{{ $codigo }}">{{ $nombre }}</option>
            @endforeach
        </select>

        @if (count($this->modalidades) > 1)
            <label class="field-label" id="modalidad-label" for="modalidad">Modalidad del envío *</label>
            <select class="controls" name="modalidad" id="modalidad" aria-labelledby="modalidad-label" wire:model.live="modalidad">
                <option value="">Seleccione Nacional o Internacional</option>
                @foreach ($this->modalidades as $codigo => $categoria)
                    <option value="{{ $codigo }}">{{ match ($codigo) { 'NAT' => 'Nacional', 'INT' => 'Internacional', 'PAQUE' => 'Paquetería internacional', 'DOC' => 'Documentos internacionales' } }}</option>
                @endforeach
            </select>
        @endif
        <input type="hidden" id="categoria" wire:model="categoria">
        <input type="hidden" id="perfil-categoria" wire:model="perfilCategoria">
        <label class="field-label" id="destino-label" for="destino">Destino del envío *</label>
        <div class="destination-search" x-data="{
            get options() { return $wire.opcionesDestino; }, query: $wire.entangle('busquedaDestino'), open: false, active: -1,
            normalize(value) { return value.normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLocaleLowerCase('es'); },
            get matches() { return Object.entries(this.options).filter(([, name]) => this.normalize(name).includes(this.normalize(this.query.trim()))); },
            choose(option) { this.query = option[1]; $wire.set('destino', option[0], false); this.open = false; this.active = -1; },
            move(step) { this.open = true; this.active = Math.max(0, Math.min(this.matches.length - 1, this.active + step)); this.$nextTick(() => this.$refs.list.querySelector('[aria-selected=true]')?.scrollIntoView({ block: 'nearest' })); }
        }" @click.outside="open = false; active = -1">
            <input class="controls" type="text" name="destino" id="destino" role="combobox"
                aria-labelledby="destino-label" aria-describedby="destino-ayuda" autocomplete="off"
                aria-autocomplete="list" aria-controls="destino-opciones" :aria-expanded="open"
                :aria-activedescendant="open && active >= 0 ? 'destino-opcion-' + active : null"
                placeholder="Escribe o selecciona un destino" x-model="query" @disabled(!$categoria)
                @focus="open = true" @click="open = true" @input="open = true; active = -1; $wire.set('destino', '', false); $wire.set('resultado', null, false)"
                @keydown.down.prevent="move(1)" @keydown.up.prevent="move(-1)"
                @keydown.enter.prevent="if (open && matches.length) choose(matches[active >= 0 ? active : 0])"
                @keydown.escape.prevent="open = false; active = -1" @keydown.tab="open = false; active = -1">
            <ul id="destino-opciones" class="destination-search__options" role="listbox" aria-labelledby="destino-label" wire:ignore
                x-ref="list" x-show="open" x-cloak>
                <template x-for="(option, index) in matches" :key="option[0]">
                    <li role="option" :id="'destino-opcion-' + index" :aria-selected="active === index">
                        <button type="button" tabindex="-1" @mousedown.prevent @click="choose(option)" x-text="option[1]"></button>
                    </li>
                </template>
                <li class="destination-search__empty" x-show="matches.length === 0" role="presentation">No se encontraron coincidencias.</li>
            </ul>
        </div>
        <p class="form-note" id="destino-ayuda">Escribe para buscar y selecciona un destino de las coincidencias.</p>
        <label class="field-label" id="peso-label" for="peso">Peso del paquete (kg) *</label>
        <input class="controls" type="number" name="peso" id="peso" aria-labelledby="peso-label" wire:model="peso"
            placeholder="Ej.: 2.5" step="0.001" min="0">

        <p class="section-label">Dimensiones en cm <span>· Opcional</span></p>
        <div class="dimension-container">
            <div class="dimension-item">
                <label for="largo">Largo (cm)</label>
                <input class="controls" type="number" name="largo" id="largo" aria-label="Largo en centímetros" wire:model="largo"
                    placeholder="Largo (cm)" step="0.1" min="0">
            </div>
            <div class="dimension-item">
                <label for="ancho">Ancho (cm)</label>
                <input class="controls" type="number" name="ancho" id="ancho" aria-label="Ancho en centímetros" wire:model="ancho"
                    placeholder="Ancho (cm)" step="0.1" min="0">
            </div>
            <div class="dimension-item">
                <label for="alto">Alto (cm)</label>
                <input class="controls" type="number" name="alto" id="alto" aria-label="Alto en centímetros" wire:model="alto"
                    placeholder="Alto (cm)" step="0.1" min="0">
            </div>
        </div>

        <p class="section-label">Servicios adicionales <span>· Opcional</span></p>
        <div class="dimension-container additional-services">
            <div class="dimension-item">
                <label class="additional-option" for="certificado">
                    <input type="checkbox" name="certificado" id="certificado" wire:model="certificado">
                    Paquete Certificado
                </label>
            </div>
            <div class="dimension-item">
                <label class="additional-option" for="recibo">
                    <input type="checkbox" name="recibo" id="recibo" wire:model="recibo">
                    Aviso de Recibo
                </label>
            </div>
        </div>
        @if ($errors->any())
            <div class="calculator-feedback calculator-feedback--error" role="alert">
                @foreach ($errors->all() as $error)<div>{{ $error }}</div>@endforeach
            </div>
        @endif
        <div class="calculator-actions">
            <button type="button" class="botons" wire:click="calcular" wire:loading.attr="disabled" wire:target="calcular,limpiar">
                <span wire:loading.remove wire:target="calcular">Calcular tarifa</span>
                <span wire:loading wire:target="calcular">Calculando…</span>
            </button>
            <button type="button" class="calculator-clear" wire:click="limpiar" wire:loading.attr="disabled" wire:target="calcular,limpiar">Limpiar datos</button>
        </div>
        <p class="form-note">Cada cálculo se guarda en el historial del sistema. Limpiar datos no elimina consultas anteriores.</p>
        <p class="form-note">
            El precio es referencial y puede ajustarse según las tarifas vigentes.
        </p>
        @if ($resultado !== null)
            <div class="resultado" role="status" aria-live="polite" wire:dirty.remove
                wire:target="servicio,modalidad,destino,busquedaDestino,peso,largo,ancho,alto,certificado,recibo">
                <h3>{{ is_numeric($resultado) ? 'El costo estimado del envío es:' : 'Consulta sin tarifa disponible' }}</h3>
                <p>{{ is_numeric($resultado) ? number_format($resultado, 2).' Bs.' : $resultado }}</p>
                @if ($registroGuardado)<small>Consulta guardada. Puedes limpiar los datos y realizar otro cálculo.</small>@endif
            </div>
        @endif
        <style>
            [x-cloak] { display:none !important; }
            .destination-search { position:relative; }
            .destination-search .controls { margin-bottom:0; }
            .destination-search__options { position:absolute; z-index:20; top:100%; left:0; right:0; max-height:240px; overflow-y:auto; margin:4px 0 0; padding:4px; list-style:none; background:#fff; color:#153257; border:1px solid #dde5f0; border-radius:12px; box-shadow:0 8px 20px rgba(15,74,161,.12); }
            .destination-search__options button { display:block; width:100%; padding:10px 14px; border:0; border-radius:8px; text-align:left; background:transparent; color:inherit; font:inherit; cursor:pointer; }
            .destination-search__options button:hover, .destination-search__options [aria-selected="true"] button { background:var(--postal-yellow-soft); color:#082f68; }
            .destination-search__empty { padding:10px 14px; }
            .calculator-actions { display:flex; align-items:center; gap:12px; flex-wrap:wrap; margin-top:16px; }
            .calculator-actions .botons { flex:1; width:auto; margin:0; }
            .calculator-clear { min-height:48px; padding:12px 18px; border:1px solid #bbc8d7; border-radius:8px; color:#082f68; background:#fff; font:inherit; font-weight:700; cursor:pointer; }
            .calculator-clear:hover { background:#edf4ff; }
            .calculator-clear:focus-visible { outline:3px solid #f4c52a; outline-offset:3px; }
            .calculator-actions button:disabled { opacity:.6; cursor:wait; }
            .calculator-feedback { padding:14px; margin:12px 0; border-radius:8px; }
            .calculator-feedback--error { background:#fff1ef; color:#9d2921; border:1px solid #edc6c0; }
        </style>
    </section>
