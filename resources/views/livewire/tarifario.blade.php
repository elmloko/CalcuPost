<div class="container-fluid">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Tarifario AGBC</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="#">Inicio</a></li>
                        <li class="breadcrumb-item active">Tarifario</li>
                    </ol>
                </div>
            </div>
            <div class="d-flex flex-wrap gap-2 mb-3">
                <button type="button" class="btn btn-primary mr-2" wire:click="prepararCreacion">
                    Crear nueva tarifa
                </button>
                @if (auth()->user()?->isAdministrator())
                    <button type="button" class="btn btn-outline-primary" wire:click="prepararNuevoServicio">
                        <i class="fas fa-plus-circle mr-1"></i> Crear servicio postal
                    </button>
                @endif
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="tariff-service-picker mb-4">
                <div class="tariff-service-picker__head">
                    <div>
                        <span>CATÁLOGO TARIFARIO</span>
                        <strong>Selecciona servicio y modalidad</strong>
                    </div>
                    <b>
                        {{ $servicioActivo && $modalidadActiva
                            ? $this->catalogoServicios[$servicioActivo]['label'].' · '.$this->catalogoServicios[$servicioActivo]['modalidades'][$modalidadActiva]['label']
                            : 'Elige modalidad' }}
                    </b>
                </div>
                <div class="tariff-selection-step">
                    <span class="tariff-selection-step__number">01</span>
                    <div>
                        <strong>Servicio postal</strong>
                        <small>Selecciona la familia del envío</small>
                    </div>
                </div>
                <div class="tariff-service-picker__tabs" role="tablist" aria-label="Servicios postales">
                    @foreach ($this->catalogoServicios as $codigo => $servicio)
                        <button type="button" class="tariff-service-tab {{ $servicioActivo === $codigo ? 'is-active' : '' }}"
                            wire:click="selectService('{{ $codigo }}')" role="tab" aria-selected="{{ $servicioActivo === $codigo ? 'true' : 'false' }}">
                            <span>{{ $servicio['label'] }}</span>
                            <small>{{ count($servicio['modalidades']) > 1 ? 'Elegir modalidad' : collect($servicio['modalidades'])->first()['label'] }}</small>
                        </button>
                    @endforeach
                </div>
                @if ($servicioActivo)
                    <div class="tariff-modality-picker">
                        <div class="tariff-selection-step">
                            <span class="tariff-selection-step__number">02</span>
                            <div>
                                <strong>Modalidad</strong>
                                <small>{{ count($this->catalogoServicios[$servicioActivo]['modalidades']) > 1 ? 'Elige Nacional o Internacional' : 'Modalidad disponible' }}</small>
                            </div>
                        </div>
                        <div class="tariff-modality-picker__options" role="tablist" aria-label="Modalidades del servicio">
                            @foreach ($this->catalogoServicios[$servicioActivo]['modalidades'] as $codigo => $modalidad)
                                <button type="button" class="tariff-modality-button {{ $modalidadActiva === $codigo ? 'is-active' : '' }}"
                                    wire:click="selectModality('{{ $codigo }}')" role="tab" aria-selected="{{ $modalidadActiva === $codigo ? 'true' : 'false' }}">
                                    {{ $modalidad['label'] }}
                                </button>
                            @endforeach
                        </div>
                        @if (auth()->user()?->isAdministrator())
                            <div class="tariff-custom-service-actions">
                                <button type="button" class="btn btn-sm btn-outline-primary"
                                    wire:click="prepararEdicionServicio('{{ $servicioActivo }}')">
                                    <i class="fas fa-edit mr-1"></i> Editar servicio
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-danger"
                                    wire:click="eliminarServicioPostal('{{ $servicioActivo }}')"
                                    wire:confirm="Se eliminará el servicio y todas sus tarifas. Esta acción no se puede deshacer. ¿Desea continuar?">
                                    <i class="fas fa-trash mr-1"></i> Eliminar servicio
                                </button>
                            </div>
                        @endif
                    </div>
                @endif
            </div>
            @if (session()->has('message'))
                <div class="alert alert-success">
                    {{ session('message') }}
                </div>
            @endif

            @if (session()->has('error'))
                <div class="alert alert-danger">
                    {{ session('error') }}
                </div>
            @endif
            <div wire:loading.delay wire:target="selectService,selectModality" role="status" class="alert alert-info">Cargando tarifario...</div>
            @if (! $activeTab)
                <div class="alert alert-info">Selecciona primero un servicio postal y después su modalidad.</div>
            @elseif ($tarifarios->total() === 0)
                <div class="alert alert-info" role="status">
                    No hay tarifas registradas para
                    {{ $this->catalogoServicios[$servicioActivo]['label'] }} ·
                    {{ $this->catalogoServicios[$servicioActivo]['modalidades'][$modalidadActiva]['label'] }}.
                    @if (app()->environment('testing'))
                        Este es el entorno de pruebas; sus tarifas son independientes del sistema principal.
                    @endif
                </div>
            @else
            <section class="tariff-table-card" aria-labelledby="tariff-table-title">
                <header class="tariff-table-card__header">
                    <div>
                        <span class="panel-kicker">TARIFAS VIGENTES</span>
                        <h2 id="tariff-table-title">{{ $this->catalogoServicios[$servicioActivo]['label'] }} · {{ $this->catalogoServicios[$servicioActivo]['modalidades'][$modalidadActiva]['label'] }}</h2>
                    </div>
                    <span class="tariff-table-card__count">{{ $tarifarios->total() }} rangos de peso</span>
                </header>
                <div class="table-responsive tariff-table-wrap">
                    <table class="table tariff-table">
                        <thead>
                            <tr>
                                <th scope="col">Rango de peso</th>
                                @foreach ($this->columnasTarifa as $campo => $etiqueta)
                                    <th scope="col">{{ $etiqueta }}</th>
                                @endforeach
                                <th scope="col" class="tariff-actions-column">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($tarifarios as $tarifario)
                                <tr wire:key="tarifa-row-{{ $tarifario->id }}" @class(['is-editing' => $editingId === $tarifario->id])>
                                    <td class="tariff-weight-cell">
                                        @if ($editingId === $tarifario->id)
                                            <div class="tariff-range-inputs">
                                                <input type="number" min="0" step="0.001" wire:model="peso_min_kg"
                                                    aria-label="Peso mínimo" class="form-control @error('peso_min_kg') is-invalid @enderror">
                                                <span>—</span>
                                                <input type="number" min="0" step="0.001" wire:model="peso_max_kg"
                                                    aria-label="Peso máximo" class="form-control @error('peso_max_kg') is-invalid @enderror">
                                            </div>
                                            @error('peso_min_kg')<small class="text-danger d-block">{{ $message }}</small>@enderror
                                            @error('peso_max_kg')<small class="text-danger d-block">{{ $message }}</small>@enderror
                                        @else
                                            <span class="tariff-weight">
                                                {{ number_format((float) $tarifario->peso_min_kg, 3, ',', '.') }}
                                                <i aria-hidden="true">→</i>
                                                {{ number_format((float) $tarifario->peso_max_kg, 3, ',', '.') }} kg
                                            </span>
                                        @endif
                                    </td>
                                    @foreach ($this->columnasTarifa as $campo => $etiqueta)
                                        <td>
                                            @if ($editingId === $tarifario->id)
                                                <input type="number" min="0" step="0.01" wire:model="{{ $campo }}"
                                                    aria-label="{{ $etiqueta }}" class="form-control tariff-inline-input @error($campo) is-invalid @enderror">
                                                @error($campo)<small class="text-danger d-block">{{ $message }}</small>@enderror
                                            @else
                                                <span class="tariff-amount">
                                                    @if ($tarifario->{$campo} === null)
                                                        —
                                                    @else
                                                        {{ number_format((float) $tarifario->{$campo}, 2, ',', '.') }} <small>Bs.</small>
                                                    @endif
                                                </span>
                                            @endif
                                        </td>
                                    @endforeach
                                    <td class="tariff-actions-column">
                                        @if ($editingId === $tarifario->id)
                                            <div class="tariff-row-actions">
                                                <button type="button" class="btn btn-primary btn-sm" wire:click="updateTarifa"
                                                    wire:loading.attr="disabled" wire:target="updateTarifa">
                                                    <i class="fas fa-check mr-1" aria-hidden="true"></i> Guardar
                                                </button>
                                                <button type="button" class="btn btn-light btn-sm" wire:click="cancelarEdicionTarifa">
                                                    Cancelar
                                                </button>
                                            </div>
                                        @else
                                            <div class="tariff-row-actions">
                                                <button type="button" class="btn btn-outline-primary btn-sm"
                                                    wire:click="editTarifa({{ $tarifario->id }})">
                                                    <i class="fas fa-pen mr-1" aria-hidden="true"></i> Editar
                                                </button>
                                                <button type="button" class="btn btn-outline-danger btn-sm"
                                                    wire:click="solicitarEliminacionTarifa({{ $tarifario->id }})">
                                                    <i class="fas fa-trash-alt" aria-hidden="true"></i>
                                                    <span class="sr-only">Eliminar tarifa</span>
                                                </button>
                                            </div>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="{{ count($this->columnasTarifa) + 2 }}" class="tariff-empty">No se encontraron tarifas.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>

            <div class="mt-4">
                {{ $tarifarios->links() }}
            </div>
            @endif
        </div>

        @if ($this->tarifaAEliminar)
            <div class="tariff-confirm-backdrop" role="presentation" wire:click.self="cancelarEliminacionTarifa"
                wire:keydown.escape.window="cancelarEliminacionTarifa">
                <section class="tariff-confirm" role="alertdialog" aria-modal="true"
                    aria-labelledby="delete-tariff-title" aria-describedby="delete-tariff-description">
                    <div class="tariff-confirm__icon" aria-hidden="true"><i class="fas fa-trash-alt"></i></div>
                    <span class="panel-kicker">CONFIRMAR ELIMINACIÓN</span>
                    <h2 id="delete-tariff-title">¿Eliminar esta tarifa?</h2>
                    <p id="delete-tariff-description">
                        Se eliminará el rango de
                        <strong>{{ number_format((float) $this->tarifaAEliminar->peso_min_kg, 3, ',', '.') }} a
                        {{ number_format((float) $this->tarifaAEliminar->peso_max_kg, 3, ',', '.') }} kg</strong>.
                        Esta acción no se puede deshacer.
                    </p>
                    <div class="tariff-confirm__actions">
                        <button type="button" class="btn btn-light" wire:click="cancelarEliminacionTarifa" autofocus>
                            Conservar tarifa
                        </button>
                        <button type="button" class="btn btn-danger" wire:click="deleteTarifa"
                            wire:loading.attr="disabled" wire:target="deleteTarifa">
                            Sí, eliminar
                        </button>
                    </div>
                </section>
            </div>
        @endif

        <div class="modal fade" id="createPostalServiceModal" tabindex="-1" role="dialog" wire:ignore.self aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <div>
                            <span class="panel-kicker">{{ $editandoServicioCodigo ? 'EDITAR CATÁLOGO' : 'NUEVO CATÁLOGO' }}</span>
                            <h5 class="modal-title">{{ $editandoServicioCodigo ? 'Editar servicio postal' : 'Crear servicio postal' }}</h5>
                        </div>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar"><span aria-hidden="true">&times;</span></button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="postal-service-name">Nombre del servicio</label>
                            <input id="postal-service-name" type="text" wire:model="nombreNuevoServicio"
                                class="form-control @error('nombreNuevoServicio') is-invalid @enderror"
                                placeholder="Ej.: Correo prioritario">
                            @error('nombreNuevoServicio')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <fieldset class="postal-modality-fieldset">
                            <legend>Modalidades disponibles</legend>
                            <label><input type="checkbox" value="NAT" wire:model="modalidadesNuevoServicio"> Nacional</label>
                            <label><input type="checkbox" value="INT" wire:model="modalidadesNuevoServicio"> Internacional</label>
                        </fieldset>
                        @error('modalidadesNuevoServicio')<div class="text-danger small mt-2">{{ $message }}</div>@enderror
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                        <button type="button" class="btn btn-primary"
                            wire:click="{{ $editandoServicioCodigo ? 'actualizarServicioPostal' : 'crearServicioPostal' }}">
                            {{ $editandoServicioCodigo ? 'Guardar cambios' : 'Crear servicio' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="createTarifaModal" tabindex="-1" role="dialog" aria-labelledby="modalTitle" wire:ignore.self
            aria-hidden="true">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Crear tarifa</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form wire:submit.prevent="createTarifa">
                            @if ($creando)
                                <div class="tarifa-create-selector">
                                    <div class="form-group">
                                        <label>Servicio postal</label>
                                        <select wire:model.live="nuevoServicio" class="form-control">
                                            <option value="">Seleccione servicio</option>
                                            @foreach ($this->catalogoServicios as $codigo => $servicio)
                                                <option value="{{ $codigo }}">{{ $servicio['label'] }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    @if (count($this->modalidadesCreacion) > 1)
                                        <div class="form-group">
                                            <label>Modalidad</label>
                                            <select wire:model.live="nuevaModalidad" class="form-control">
                                                <option value="">Seleccione modalidad</option>
                                                @foreach ($this->modalidadesCreacion as $codigo => $categoria)
                                                    <option value="{{ $codigo }}">{{ match ($codigo) { 'NAT' => 'Nacional', 'INT' => 'Internacional', 'PAQUE' => 'Paquetería internacional', 'DOC' => 'Documentos internacionales' } }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    @endif
                                    <small class="text-muted d-block">Borrador guardado automáticamente mientras completas tarifa.</small>
                                    @if (session()->has('tarifario.borrador'))
                                        <button type="button" class="btn btn-link btn-sm px-0" wire:click="descartarBorrador">Descartar borrador</button>
                                    @endif
                                </div>
                            @endif

                            @if ($creando && !$this->categoriaNueva)
                                <div class="alert alert-info mb-0">Selecciona servicio y modalidad para mostrar campos de tarifa.</div>
                            @else
                            @if ($this->categoriaVisual === 'EMS INT')
                                <div class="form-group">
                                    <label>Peso mínimo (kg)</label>
                                    <input type="number" step="0.001" wire:model.live.debounce.500ms="peso_min_kg" class="form-control">
                                </div>
                                <div class="form-group">
                                    <label>Peso máximo (kg)</label>
                                    <input type="number" step="0.001" wire:model.live.debounce.500ms="peso_max_kg" class="form-control">
                                </div>
                                <div class="form-group">
                                    <label>Destino A</label>
                                    <input type="number" wire:model.live.debounce.500ms="dest_a" class="form-control">
                                </div>
                                <div class="form-group">
                                    <label>Destino B</label>
                                    <input type="number" wire:model.live.debounce.500ms="dest_b" class="form-control">
                                </div>
                                <div class="form-group">
                                    <label>Destino C</label>
                                    <input type="number" wire:model.live.debounce.500ms="dest_c" class="form-control">
                                </div>
                                <div class="form-group">
                                    <label>Destino D</label>
                                    <input type="number" wire:model.live.debounce.500ms="dest_d" class="form-control">
                                </div>
                                <div class="form-group">
                                    <label>Destino E</label>
                                    <input type="number" wire:model.live.debounce.500ms="dest_e" class="form-control">
                                </div>
                            @elseif ($this->categoriaVisual === 'EMS NAT')
                                <div class="form-group">
                                    <label>Peso mínimo (kg)</label>
                                    <input type="number" step="0.001" wire:model.live.debounce.500ms="peso_min_kg"
                                        class="form-control">
                                </div>
                                <div class="form-group">
                                    <label>Peso máximo (kg)</label>
                                    <input type="number" step="0.001" wire:model.live.debounce.500ms="peso_max_kg"
                                        class="form-control">
                                </div>
                                <div class="form-group">
                                    <label>EMS Local 1</label>
                                    <input type="number" wire:model.live.debounce.500ms="local_1" class="form-control">
                                </div>
                                <div class="form-group">
                                    <label>EMS Local 2</label>
                                    <input type="number" wire:model.live.debounce.500ms="local_2" class="form-control">
                                </div>
                                <div class="form-group">
                                    <label>EMS Local 3</label>
                                    <input type="number" wire:model.live.debounce.500ms="local_3" class="form-control">
                                </div>
                                <div class="form-group">
                                    <label>EMS Local 4</label>
                                    <input type="number" wire:model.live.debounce.500ms="local_4" class="form-control">
                                </div>
                                <div class="form-group">
                                    <label>EMS Nacional</label>
                                    <input type="number" wire:model.live.debounce.500ms="nacional" class="form-control">
                                </div>
                                <div class="form-group">
                                    <label>Cuidades Intermedias</label>
                                    <input type="number" wire:model.live.debounce.500ms="cui_int" class="form-control">
                                </div>
                                <div class="form-group">
                                    <label>Provincia</label>
                                    <input type="number" wire:model.live.debounce.500ms="pro_dentro" class="form-control">
                                </div>
                                <div class="form-group">
                                    <label>Trinidad / Cobija</label>
                                    <input type="number" wire:model.live.debounce.500ms="cui1" class="form-control">
                                </div>
                                <div class="form-group">
                                    <label>Riberalta / Guayaramerin</label>
                                    <input type="number" wire:model.live.debounce.500ms="cui2" class="form-control">
                                </div>
                            @elseif ($this->categoriaVisual === 'MI ENCOMIENDA')
                                <div class="form-group">
                                    <label>Peso mínimo (kg)</label>
                                    <input type="number" step="0.001" wire:model.live.debounce.500ms="peso_min_kg"
                                        class="form-control">
                                </div>
                                <div class="form-group">
                                    <label>Peso máximo (kg)</label>
                                    <input type="number" step="0.001" wire:model.live.debounce.500ms="peso_max_kg"
                                        class="form-control">
                                </div>
                                <div class="form-group">
                                    <label>Cuidades Capitales</label>
                                    <input type="number" wire:model.live.debounce.500ms="cui_cap" class="form-control">
                                </div>
                                <div class="form-group">
                                    <label>Trinidad / Cobija(Destinos Especiales)</label>
                                    <input type="number" wire:model.live.debounce.500ms="cui1" class="form-control">
                                </div>
                                <div class="form-group">
                                    <label>Prov. Detro Depto.</label>
                                    <input type="number" wire:model.live.debounce.500ms="pro_dentro" class="form-control">
                                </div>
                                <div class="form-group">
                                    <label>Prov. en Otro Depto</label>
                                    <input type="number" wire:model.live.debounce.500ms="pro_otro" class="form-control">
                                </div>
                            @elseif ($this->categoriaVisual === 'ENCOMIENDA')
                                <div class="form-group">
                                    <label>Peso mínimo (kg)</label>
                                    <input type="number" step="0.001" wire:model.live.debounce.500ms="peso_min_kg"
                                        class="form-control">
                                </div>
                                <div class="form-group">
                                    <label>Peso máximo (kg)</label>
                                    <input type="number" step="0.001" wire:model.live.debounce.500ms="peso_max_kg"
                                        class="form-control">
                                </div>
                                <div class="form-group">
                                    <label>Destino A</label>
                                    <input type="number" wire:model.live.debounce.500ms="dest_a" class="form-control">
                                </div>
                                <div class="form-group">
                                    <label>Destino B</label>
                                    <input type="number" wire:model.live.debounce.500ms="dest_b" class="form-control">
                                </div>
                                <div class="form-group">
                                    <label>Destino C</label>
                                    <input type="number" wire:model.live.debounce.500ms="dest_c" class="form-control">
                                </div>
                                <div class="form-group">
                                    <label>Destino D</label>
                                    <input type="number" wire:model.live.debounce.500ms="dest_d" class="form-control">
                                </div>
                                <div class="form-group">
                                    <label>Destino E</label>
                                    <input type="number" wire:model.live.debounce.500ms="dest_e" class="form-control">
                                </div>
                            @elseif ($this->categoriaVisual === 'LC/AO INT')
                                <div class="form-group">
                                    <label>Peso mínimo (kg)</label>
                                    <input type="number" step="0.001" wire:model.live.debounce.500ms="peso_min_kg"
                                        class="form-control">
                                </div>
                                <div class="form-group">
                                    <label>Peso máximo (kg)</label>
                                    <input type="number" step="0.001" wire:model.live.debounce.500ms="peso_max_kg"
                                        class="form-control">
                                </div>
                                <div class="form-group">
                                    <label>Destino A</label>
                                    <input type="number" wire:model.live.debounce.500ms="dest_a" class="form-control">
                                </div>
                                <div class="form-group">
                                    <label>Destino B</label>
                                    <input type="number" wire:model.live.debounce.500ms="dest_b" class="form-control">
                                </div>
                                <div class="form-group">
                                    <label>Destino C</label>
                                    <input type="number" wire:model.live.debounce.500ms="dest_c" class="form-control">
                                </div>
                                <div class="form-group">
                                    <label>Destino D</label>
                                    <input type="number" wire:model.live.debounce.500ms="dest_d" class="form-control">
                                </div>
                                <div class="form-group">
                                    <label>Destino E</label>
                                    <input type="number" wire:model.live.debounce.500ms="dest_e" class="form-control">
                                </div>
                            @elseif ($this->categoriaVisual === 'LC/AO NAT')
                                <div class="form-group">
                                    <label>Peso mínimo (kg)</label>
                                    <input type="number" step="0.001" wire:model.live.debounce.500ms="peso_min_kg"
                                        class="form-control">
                                </div>
                                <div class="form-group">
                                    <label>Peso máximo (kg)</label>
                                    <input type="number" step="0.001" wire:model.live.debounce.500ms="peso_max_kg"
                                        class="form-control">
                                </div>
                                <div class="form-group">
                                    <label>Servicio Local</label>
                                    <input type="number" wire:model.live.debounce.500ms="local_1" class="form-control">
                                </div>
                                <div class="form-group">
                                    <label>Servicio Nacional</label>
                                    <input type="number" wire:model.live.debounce.500ms="nacional" class="form-control">
                                </div>
                                <div class="form-group">
                                    <label>Prov. Detro Depto.</label>
                                    <input type="number" wire:model.live.debounce.500ms="pro_dentro" class="form-control">
                                </div>
                                <div class="form-group">
                                    <label>Prov. en Otro Depto</label>
                                    <input type="number" wire:model.live.debounce.500ms="pro_otro" class="form-control">
                                </div>
                                <div class="form-group">
                                    <label>Trinidad / Cobija</label>
                                    <input type="number" wire:model.live.debounce.500ms="cui1" class="form-control">
                                </div>
                                <div class="form-group">
                                    <label>Riberalta / Guayaremarin</label>
                                    <input type="number" wire:model.live.debounce.500ms="cui2" class="form-control">
                                </div>
                            @elseif ($this->categoriaVisual === 'ECA INT')
                                <div class="form-group">
                                    <label>Peso mínimo (kg)</label>
                                    <input type="number" step="0.001" wire:model.live.debounce.500ms="peso_min_kg"
                                        class="form-control">
                                </div>
                                <div class="form-group">
                                    <label>Peso máximo (kg)</label>
                                    <input type="number" step="0.001" wire:model.live.debounce.500ms="peso_max_kg"
                                        class="form-control">
                                </div>
                                <div class="form-group">
                                    <label>Destino A</label>
                                    <input type="number" wire:model.live.debounce.500ms="dest_a" class="form-control">
                                </div>
                                <div class="form-group">
                                    <label>Destino B</label>
                                    <input type="number" wire:model.live.debounce.500ms="dest_b" class="form-control">
                                </div>
                                <div class="form-group">
                                    <label>Destino C</label>
                                    <input type="number" wire:model.live.debounce.500ms="dest_c" class="form-control">
                                </div>
                                <div class="form-group">
                                    <label>Destino D</label>
                                    <input type="number" wire:model.live.debounce.500ms="dest_d" class="form-control">
                                </div>
                                <div class="form-group">
                                    <label>Destino E</label>
                                    <input type="number" wire:model.live.debounce.500ms="dest_e" class="form-control">
                                </div>
                            @elseif ($this->categoriaVisual === 'ECA NAT')
                                <div class="form-group">
                                    <label>Peso mínimo (kg)</label>
                                    <input type="number" step="0.001" wire:model.live.debounce.500ms="peso_min_kg"
                                        class="form-control">
                                </div>
                                <div class="form-group">
                                    <label>Peso máximo (kg)</label>
                                    <input type="number" step="0.001" wire:model.live.debounce.500ms="peso_max_kg"
                                        class="form-control">
                                </div>
                                <div class="form-group">
                                    <label>Local</label>
                                    <input type="number" wire:model.live.debounce.500ms="local_1" class="form-control">
                                </div>
                                <div class="form-group">
                                    <label>Nacional</label>
                                    <input type="number" wire:model.live.debounce.500ms="nacional" class="form-control">
                                </div>
                                <div class="form-group">
                                    <label>Prov. Detro Depto.</label>
                                    <input type="number" wire:model.live.debounce.500ms="pro_dentro" class="form-control">
                                </div>
                                <div class="form-group">
                                    <label>Prov. en Otro Depto</label>
                                    <input type="number" wire:model.live.debounce.500ms="pro_otro" class="form-control">
                                </div>
                                <div class="form-group">
                                    <label>Trinidad / Cobija</label>
                                    <input type="number" wire:model.live.debounce.500ms="cui1" class="form-control">
                                </div>
                                <div class="form-group">
                                    <label>Riberalta / Guayaremarin</label>
                                    <input type="number" wire:model.live.debounce.500ms="cui2" class="form-control">
                                </div>
                            @elseif ($this->categoriaVisual === 'PLIEGOS INT')
                                <div class="form-group">
                                    <label>Peso mínimo (kg)</label>
                                    <input type="number" step="0.001" wire:model.live.debounce.500ms="peso_min_kg"
                                        class="form-control">
                                </div>
                                <div class="form-group">
                                    <label>Peso máximo (kg)</label>
                                    <input type="number" step="0.001" wire:model.live.debounce.500ms="peso_max_kg"
                                        class="form-control">
                                </div>
                                <div class="form-group">
                                    <label>Destino A</label>
                                    <input type="number" wire:model.live.debounce.500ms="dest_a" class="form-control">
                                </div>
                                <div class="form-group">
                                    <label>Destino B</label>
                                    <input type="number" wire:model.live.debounce.500ms="dest_b" class="form-control">
                                </div>
                                <div class="form-group">
                                    <label>Destino C</label>
                                    <input type="number" wire:model.live.debounce.500ms="dest_c" class="form-control">
                                </div>
                                <div class="form-group">
                                    <label>Destino D</label>
                                    <input type="number" wire:model.live.debounce.500ms="dest_d" class="form-control">
                                </div>
                                <div class="form-group">
                                    <label>Destino E</label>
                                    <input type="number" wire:model.live.debounce.500ms="dest_e" class="form-control">
                                </div>
                            @elseif ($this->categoriaVisual === 'PLIEGOS NAT')
                                <div class="form-group">
                                    <label>Peso mínimo (kg)</label>
                                    <input type="number" step="0.001" wire:model.live.debounce.500ms="peso_min_kg"
                                        class="form-control">
                                </div>
                                <div class="form-group">
                                    <label>Peso máximo (kg)</label>
                                    <input type="number" step="0.001" wire:model.live.debounce.500ms="peso_max_kg"
                                        class="form-control">
                                </div>
                                <div class="form-group">
                                    <label>Local</label>
                                    <input type="number" wire:model.live.debounce.500ms="local_1" class="form-control">
                                </div>
                                <div class="form-group">
                                    <label>Nacional</label>
                                    <input type="number" wire:model.live.debounce.500ms="nacional" class="form-control">
                                </div>
                                <div class="form-group">
                                    <label>Prov. Detro Depto.</label>
                                    <input type="number" wire:model.live.debounce.500ms="pro_dentro" class="form-control">
                                </div>
                                <div class="form-group">
                                    <label>Prov. en Otro Depto</label>
                                    <input type="number" wire:model.live.debounce.500ms="pro_otro" class="form-control">
                                </div>
                            @elseif ($this->categoriaVisual === 'SACAS M INT')
                                <div class="form-group">
                                    <label>Peso mínimo (kg)</label>
                                    <input type="number" step="0.001" wire:model.live.debounce.500ms="peso_min_kg"
                                        class="form-control">
                                </div>
                                <div class="form-group">
                                    <label>Peso máximo (kg)</label>
                                    <input type="number" step="0.001" wire:model.live.debounce.500ms="peso_max_kg"
                                        class="form-control">
                                </div>
                                <div class="form-group">
                                    <label>Destino A</label>
                                    <input type="number" wire:model.live.debounce.500ms="dest_a" class="form-control">
                                </div>
                                <div class="form-group">
                                    <label>Destino B</label>
                                    <input type="number" wire:model.live.debounce.500ms="dest_b" class="form-control">
                                </div>
                                <div class="form-group">
                                    <label>Destino C</label>
                                    <input type="number" wire:model.live.debounce.500ms="dest_c" class="form-control">
                                </div>
                                <div class="form-group">
                                    <label>Destino D</label>
                                    <input type="number" wire:model.live.debounce.500ms="dest_d" class="form-control">
                                </div>
                                <div class="form-group">
                                    <label>Destino E</label>
                                    <input type="number" wire:model.live.debounce.500ms="dest_e" class="form-control">
                                </div>
                            @elseif ($this->categoriaVisual === 'SACAS M NAT')
                                <div class="form-group">
                                    <label>Peso mínimo (kg)</label>
                                    <input type="number" step="0.001" wire:model.live.debounce.500ms="peso_min_kg"
                                        class="form-control">
                                </div>
                                <div class="form-group">
                                    <label>Peso máximo (kg)</label>
                                    <input type="number" step="0.001" wire:model.live.debounce.500ms="peso_max_kg"
                                        class="form-control">
                                </div>
                                <div class="form-group">
                                    <label>Nacional</label>
                                    <input type="number" wire:model.live.debounce.500ms="nacional" class="form-control">
                                </div>
                                <div class="form-group">
                                    <label>Provincial</label>
                                    <input type="number" wire:model.live.debounce.500ms="pro_dentro" class="form-control">
                                </div>
                            @elseif ($this->categoriaVisual === 'SUPER NAT')
                                <div class="form-group">
                                    <label>Peso mínimo (kg)</label>
                                    <input type="number" step="0.001" wire:model.live.debounce.500ms="peso_min_kg"
                                        class="form-control">
                                </div>
                                <div class="form-group">
                                    <label>Peso máximo (kg)</label>
                                    <input type="number" step="0.001" wire:model.live.debounce.500ms="peso_max_kg"
                                        class="form-control">
                                </div>
                                <div class="form-group">
                                    <label>Nacional</label>
                                    <input type="number" wire:model.live.debounce.500ms="nacional" class="form-control">
                                </div>
                            @elseif ($this->categoriaVisual === 'SUPER PAQUE NAT')
                                <div class="form-group">
                                    <label>Peso mínimo (kg)</label>
                                    <input type="number" step="0.001" wire:model.live.debounce.500ms="peso_min_kg"
                                        class="form-control">
                                </div>
                                <div class="form-group">
                                    <label>Peso máximo (kg)</label>
                                    <input type="number" step="0.001" wire:model.live.debounce.500ms="peso_max_kg"
                                        class="form-control">
                                </div>
                                <div class="form-group">
                                    <label>Destino A</label>
                                    <input type="number" wire:model.live.debounce.500ms="dest_a" class="form-control">
                                </div>
                                <div class="form-group">
                                    <label>Destino B</label>
                                    <input type="number" wire:model.live.debounce.500ms="dest_b" class="form-control">
                                </div>
                                <div class="form-group">
                                    <label>Destino C</label>
                                    <input type="number" wire:model.live.debounce.500ms="dest_c" class="form-control">
                                </div>
                                <div class="form-group">
                                    <label>Destino D</label>
                                    <input type="number" wire:model.live.debounce.500ms="dest_d" class="form-control">
                                </div>
                                <div class="form-group">
                                    <label>Destino E</label>
                                    <input type="number" wire:model.live.debounce.500ms="dest_e" class="form-control">
                                </div>
                                <div class="form-group">
                                    <label>Destino F</label>
                                    <input type="number" wire:model.live.debounce.500ms="dest_f" class="form-control">
                                </div>
                                <div class="form-group">
                                    <label>Destino G</label>
                                    <input type="number" wire:model.live.debounce.500ms="dest_g" class="form-control">
                                </div>
                            @elseif ($this->categoriaVisual === 'SUPER DOC NAT')
                                <div class="form-group">
                                    <label>Peso mínimo (kg)</label>
                                    <input type="number" step="0.001" wire:model.live.debounce.500ms="peso_min_kg"
                                        class="form-control">
                                </div>
                                <div class="form-group">
                                    <label>Peso máximo (kg)</label>
                                    <input type="number" step="0.001" wire:model.live.debounce.500ms="peso_max_kg"
                                        class="form-control">
                                </div>
                                <div class="form-group">
                                    <label>Destino A</label>
                                    <input type="number" wire:model.live.debounce.500ms="dest_a" class="form-control">
                                </div>
                                <div class="form-group">
                                    <label>Destino B</label>
                                    <input type="number" wire:model.live.debounce.500ms="dest_b" class="form-control">
                                </div>
                                <div class="form-group">
                                    <label>Destino C</label>
                                    <input type="number" wire:model.live.debounce.500ms="dest_c" class="form-control">
                                </div>
                                <div class="form-group">
                                    <label>Destino D</label>
                                    <input type="number" wire:model.live.debounce.500ms="dest_d" class="form-control">
                                </div>
                                <div class="form-group">
                                    <label>Destino E</label>
                                    <input type="number" wire:model.live.debounce.500ms="dest_e" class="form-control">
                                </div>
                                <div class="form-group">
                                    <label>Destino F</label>
                                    <input type="number" wire:model.live.debounce.500ms="dest_f" class="form-control">
                                </div>
                                <div class="form-group">
                                    <label>Destino G</label>
                                    <input type="number" wire:model.live.debounce.500ms="dest_g" class="form-control">
                                </div>
                            @elseif ($this->esCategoriaPersonalizada)
                                <div class="form-row">
                                    <div class="form-group col-md-6"><label>Peso mínimo (kg)</label><input type="number" step="0.001" wire:model="peso_min_kg" class="form-control"></div>
                                    <div class="form-group col-md-6"><label>Peso máximo (kg)</label><input type="number" step="0.001" wire:model="peso_max_kg" class="form-control"></div>
                                </div>
                                @if ($this->modalidadCategoriaActiva === 'INT')
                                    <div class="form-row">
                                        @foreach (['dest_a' => 'Destino A', 'dest_b' => 'Destino B', 'dest_c' => 'Destino C', 'dest_d' => 'Destino D', 'dest_e' => 'Destino E'] as $campo => $etiqueta)
                                            <div class="form-group col-md-4"><label>{{ $etiqueta }}</label><input type="number" wire:model="{{ $campo }}" class="form-control"></div>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="form-row">
                                        <div class="form-group col-md-6"><label>Local</label><input type="number" wire:model="local_1" class="form-control"></div>
                                        <div class="form-group col-md-6"><label>Nacional</label><input type="number" wire:model="nacional" class="form-control"></div>
                                        <div class="form-group col-md-6"><label>Provincia dentro del departamento</label><input type="number" wire:model="pro_dentro" class="form-control"></div>
                                        <div class="form-group col-md-6"><label>Provincia en otro departamento</label><input type="number" wire:model="pro_otro" class="form-control"></div>
                                    </div>
                                @endif
                            @endif
                            @endif
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                        <button type="button" class="btn btn-primary" wire:click="createTarifa" @disabled($creando && !$this->categoriaNueva)>
                            Guardar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        window.addEventListener('closeModal', () => {
            $('#createTarifaModal').modal('hide');
        });
        window.addEventListener('openModal', () => {
            $('#createTarifaModal').modal('show');
        });
        window.addEventListener('openServiceModal', () => {
            $('#createPostalServiceModal').modal('show');
        });
        window.addEventListener('closeServiceModal', () => {
            $('#createPostalServiceModal').modal('hide');
        });
    });
</script>
