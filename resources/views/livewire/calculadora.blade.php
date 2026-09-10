<section class="form-register">
        <h4>Calculadora Postal</h4>
        <h3>*Selecciona el servicio postal.</h3>
        <select class="controls" name="servicio" id="servicio" wire:model.live="servicio">
            <option value="">Seleccione un Servicio Postal</option>
            @foreach ($this->catalogoServicios as $codigo => $nombre)
                <option value="{{ $codigo }}">{{ $nombre }}</option>
            @endforeach
        </select>

        @if (count($this->modalidades) > 1)
            <h3>*Selecciona la modalidad.</h3>
            <select class="controls" name="modalidad" id="modalidad" wire:model.live="modalidad">
                <option value="">Seleccione Nacional o Internacional</option>
                @foreach ($this->modalidades as $codigo => $categoria)
                    <option value="{{ $codigo }}">{{ match ($codigo) { 'NAT' => 'Nacional', 'INT' => 'Internacional', 'PAQUE' => 'Paquetería internacional', 'DOC' => 'Documentos internacionales' } }}</option>
                @endforeach
            </select>
        @endif
        <input type="hidden" id="categoria" wire:model="categoria">
        <input type="hidden" id="perfil-categoria" wire:model="perfilCategoria">
        <h3>*Seleciona a donde quieres enviar.</h3>
        <select class="controls" name="destino" id="destino" wire:model="destino" @disabled(!$categoria)>
            <option value="">Seleccione un Destino</option>
            @foreach ($this->destinos as $codigo => $nombre)
                <option value="{{ $codigo }}">{{ $nombre }}</option>
            @endforeach
        </select>
        <h3>*Ingrese el peso del paquete.(Kg.)</h3>
        <input class="controls" type="number" name="peso" id="peso" wire:model="peso"
            placeholder="Ingrese el Peso Aprox. de su Paquete" step="0.001" min="0">

        <h3>Ingrese las dimensiones del paquete.(OPCIONAL /cm.)</h3>
        <div class="dimension-container">
            <div class="dimension-item">
                <input class="controls" type="number" name="largo" id="largo" wire:model="largo"
                    placeholder="Largo (cm)" step="0.1" min="0">
            </div>
            <div class="dimension-item">
                <input class="controls" type="number" name="ancho" id="ancho" wire:model="ancho"
                    placeholder="Ancho (cm)" step="0.1" min="0">
            </div>
            <div class="dimension-item">
                <input class="controls" type="number" name="alto" id="alto" wire:model="alto"
                    placeholder="Alto (cm)" step="0.1" min="0">
            </div>
        </div>

        <h3>Selecciona servicios adicionales:</h3>
        <div class="dimension-container">
            <div class="dimension-item">
                <label for="certificado">
                    <input type="checkbox" name="certificado" id="certificado" wire:model="certificado">
                    Paquete Certificado
                </label>
            </div>
            {{-- <div class="dimension-item">
                <label for="espreso">
                    <input type="checkbox" name="espreso" id="espreso" wire:model="espreso">
                    Servicio Expreso
                </label>
            </div> --}}
            <div class="dimension-item">
                <label for="recibo">
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
                <span wire:loading.remove wire:target="calcular">Calcular</span>
                <span wire:loading wire:target="calcular">Calculando…</span>
            </button>
            <button type="button" class="calculator-clear" wire:click="limpiar" wire:loading.attr="disabled" wire:target="calcular,limpiar">Limpiar datos</button>
        </div>
        <p class="form-note">Cada cálculo se guarda en el historial del sistema. Limpiar datos no elimina consultas anteriores.</p>
        <p class="form-note">
            *El precio mostrado es referencial y puede ajustarse según las tarifas vigentes.*
        </p>
        @if ($resultado !== null)
            <div class="resultado" role="status" aria-live="polite">
                <h5>{{ is_numeric($resultado) ? 'El costo estimado del envío es:' : 'Consulta sin tarifa disponible' }}</h5>
                <p>{{ is_numeric($resultado) ? number_format($resultado, 2).' Bs.' : $resultado }}</p>
                @if ($registroGuardado)<small>Consulta guardada. Puedes limpiar los datos y realizar otro cálculo.</small>@endif
            </div>
        @endif
        <style>
            .calculator-actions { display:flex; align-items:center; gap:12px; flex-wrap:wrap; margin-top:16px; }
            .calculator-actions .botons { flex:1; width:auto; margin:0; }
            .calculator-clear { padding:14px 22px; border:1px solid #0f4aa1; border-radius:12px; color:#082f68; background:#fff; font:inherit; font-weight:700; cursor:pointer; }
            .calculator-clear:hover { background:#edf4ff; }
            .calculator-clear:focus-visible { outline:3px solid #f4c52a; outline-offset:3px; }
            .calculator-actions button:disabled { opacity:.6; cursor:wait; }
            .calculator-feedback { padding:14px; margin:12px 0; border-radius:8px; }
            .calculator-feedback--error { background:#fff1ef; color:#9d2921; border:1px solid #edc6c0; }
        </style>
    </section>
