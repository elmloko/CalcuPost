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
            <button class="btn btn-primary mb-3" data-toggle="modal" data-target="#createTarifaModal">
                Crear nueva tarifa
            </button>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="row mb-4">
                <div class="col-12">
                    <ul class="nav nav-tabs">
                        @foreach (['EMS INT', 'EMS NAT', 'MI ENCOMIENDA', 'ENCOMIENDA', 'LC/AO INT', 'LC/AO NAT', 'ECA INT', 'ECA NAT', 'PLIEGOS INT', 'PLIEGOS NAT', 'SACAS M INT', 'SACAS M NAT', 'SUPER NAT', 'SUPER PAQUE NAT', 'SUPER DOC NAT'] as $tab)
                            <li class="nav-item">
                                <button class="nav-link {{ $activeTab === $tab ? 'active' : '' }}"
                                    wire:click="selectTab('{{ $tab }}')">
                                    {{ $tab }}
                                </button>
                            </li>
                        @endforeach
                    </ul>
                </div>
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
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>

                            @if ($activeTab === 'EMS INT')
                                <th>Peso min / Peso max</th>
                                <th>Destino A</th>
                                <th>Destino B</th>
                                <th>Destino C</th>
                                <th>Destino D</th>
                                <th>Destino E</th>
                            @endif

                            @if ($activeTab === 'EMS NAT')
                                <th>Peso min / Peso max</th>
                                <th>EMS Local 1</th>
                                <th>EMS Local 2</th>
                                <th>EMS Local 3</th>
                                <th>EMS Local 4</th>
                                <th>EMS Nacional</th>
                                <th>Cuidades Intermedias</th>
                                <th>Trinidad / Cobija</th>
                                <th>Riberalta / Guayaramarin</th>
                            @endif

                            @if ($activeTab === 'ENCOMIENDA')
                                <th>Peso min / Peso max</th>
                                <th>Destino A</th>
                                <th>Destino B</th>
                                <th>Destino C</th>
                                <th>Destino D</th>
                                <th>Destino E</th>
                            @endif

                            @if ($activeTab === 'MI ENCOMIENDA')
                                <th>Peso min / Peso max</th>
                                <th>Cuidades Capitales</th>
                                <th>Trinidad / Cobija(Destinos Especiales)</th>
                                <th>Prov. Detro Depto.</th>
                                <th>Prov. en Otro Depto</th>
                            @endif

                            @if ($activeTab === 'LC/AO INT')
                                <th>Peso min / Peso max</th>
                                <th>Destino A</th>
                                <th>Destino B</th>
                                <th>Destino C</th>
                                <th>Destino D</th>
                                <th>Destino E</th>
                            @endif

                            @if ($activeTab === 'LC/AO NAT')
                                <th>Peso min / Peso max</th>
                                <th>Servicio Local</th>
                                <th>Servicio Nacional</th>
                                <th>Prov. Detro Depto.</th>
                                <th>Prov. en Otro Depto</th>
                                <th>Trinidad / Cobija</th>
                                <th>Riberalta / Guayaramarin</th>
                            @endif

                            @if ($activeTab === 'ECA INT')
                                <th>Peso min / Peso max</th>
                                <th>Destino A</th>
                                <th>Destino B</th>
                                <th>Destino C</th>
                                <th>Destino D</th>
                                <th>Destino E</th>
                            @endif

                            @if ($activeTab === 'ECA NAT')
                                <th>Peso min / Peso max</th>
                                <th>Local</th>
                                <th>Nacional</th>
                                <th>Prov. Detro Depto.</th>
                                <th>Prov. en Otro Depto</th>
                                <th>Trinidad / Cobija</th>
                                <th>Riberalta / Guayaramarin</th>
                            @endif

                            @if ($activeTab === 'PLIEGOS INT')
                                <th>Peso min / Peso max</th>
                                <th>Destino A</th>
                                <th>Destino B</th>
                                <th>Destino C</th>
                                <th>Destino D</th>
                                <th>Destino E</th>
                            @endif

                            @if ($activeTab === 'PLIEGOS NAT')
                                <th>Peso min / Peso max</th>
                                <th>Local</th>
                                <th>Nacional</th>
                                <th>Prov. Detro Depto.</th>
                                <th>Prov. en Otro Depto</th>
                            @endif

                            @if ($activeTab === 'SACAS M INT')
                                <th>Peso min / Peso max</th>
                                <th>Destino A</th>
                                <th>Destino B</th>
                                <th>Destino C</th>
                                <th>Destino D</th>
                                <th>Destino E</th>
                            @endif

                            @if ($activeTab === 'SACAS M NAT')
                                <th>Peso min / Peso max</th>
                                <th>Nacional</th>
                                <th>Provincial</th>
                            @endif

                            @if ($activeTab === 'SUPER NAT')
                                <th>Peso min / Peso max</th>
                                <th>Nacional</th>
                            @endif

                            @if ($activeTab === 'SUPER PAQUE NAT')
                                <th>Peso min / Peso max</th>
                                <th>Tarifa 1</th>
                                <th>Tarifa 2</th>
                                <th>Tarifa 3</th>
                                <th>Tarifa 4</th>
                                <th>Tarifa 5</th>
                                <th>Tarifa 6</th>
                                <th>Tarifa 7</th>
                            @endif

                            @if ($activeTab === 'SUPER DOC NAT')
                                <th>Peso min / Peso max</th>
                                <th>Tarifa 1</th>
                                <th>Tarifa 2</th>
                                <th>Tarifa 3</th>
                                <th>Tarifa 4</th>
                                <th>Tarifa 5</th>
                                <th>Tarifa 6</th>
                                <th>Tarifa 7</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($tarifarios as $tarifario)
                            <tr>

                                @if ($activeTab === 'EMS INT')
                                    <td>{{ $tarifario->peso_min_kg }}/{{ $tarifario->peso_max_kg }}</td>
                                    <td>{{ $tarifario->dest_a }}</td>
                                    <td>{{ $tarifario->dest_b }}</td>
                                    <td>{{ $tarifario->dest_c }}</td>
                                    <td>{{ $tarifario->dest_d }}</td>
                                    <td>{{ $tarifario->dest_e }}</td>
                                @endif

                                @if ($activeTab === 'EMS NAT')
                                    <td>{{ $tarifario->peso_min_kg }}/{{ $tarifario->peso_max_kg }}</td>
                                    <td>{{ $tarifario->local_1 }}</td>
                                    <td>{{ $tarifario->local_2 }}</td>
                                    <td>{{ $tarifario->local_3 }}</td>
                                    <td>{{ $tarifario->local_4 }}</td>
                                    <td>{{ $tarifario->nacional }}</td>
                                    <td>{{ $tarifario->cui_int }}</td>
                                    <td>{{ $tarifario->cui1 }}</td>
                                    <td>{{ $tarifario->cui2 }}</td>
                                @endif

                                @if ($activeTab === 'ENCOMIENDA')
                                    <td>{{ $tarifario->peso_min_kg }}/{{ $tarifario->peso_max_kg }}</td>
                                    <td>{{ $tarifario->dest_a }}</td>
                                    <td>{{ $tarifario->dest_b }}</td>
                                    <td>{{ $tarifario->dest_c }}</td>
                                    <td>{{ $tarifario->dest_d }}</td>
                                    <td>{{ $tarifario->dest_e }}</td>
                                @endif

                                @if ($activeTab === 'MI ENCOMIENDA')
                                    <td>{{ $tarifario->peso_min_kg }}/{{ $tarifario->peso_max_kg }}</td>
                                    <td>{{ $tarifario->cui_cap }}</td>
                                    <td>{{ $tarifario->cui1 }}</td>
                                    <td>{{ $tarifario->pro_dentro }}</td>
                                    <td>{{ $tarifario->pro_otro }}</td>
                                @endif

                                @if ($activeTab === 'LC/AO INT')
                                    <td>{{ $tarifario->peso_min_kg }}/{{ $tarifario->peso_max_kg }}</td>
                                    <td>{{ $tarifario->dest_a }}</td>
                                    <td>{{ $tarifario->dest_b }}</td>
                                    <td>{{ $tarifario->dest_c }}</td>
                                    <td>{{ $tarifario->dest_d }}</td>
                                    <td>{{ $tarifario->dest_e }}</td>
                                @endif

                                @if ($activeTab === 'LC/AO NAT')
                                    <td>{{ $tarifario->peso_min_kg }}/{{ $tarifario->peso_max_kg }}</td>
                                    <td>{{ $tarifario->local_1 }}</td>
                                    <td>{{ $tarifario->nacional }}</td>
                                    <td>{{ $tarifario->pro_dentro }}</td>
                                    <td>{{ $tarifario->pro_otro }}</td>
                                    <td>{{ $tarifario->cui1 }}</td>
                                    <td>{{ $tarifario->cui2 }}</td>
                                @endif

                                @if ($activeTab === 'ECA INT')
                                    <td>{{ $tarifario->peso_min_kg }}/{{ $tarifario->peso_max_kg }}</td>
                                    <td>{{ $tarifario->dest_a }}</td>
                                    <td>{{ $tarifario->dest_b }}</td>
                                    <td>{{ $tarifario->dest_c }}</td>
                                    <td>{{ $tarifario->dest_d }}</td>
                                    <td>{{ $tarifario->dest_e }}</td>
                                @endif

                                @if ($activeTab === 'ECA NAT')
                                    <td>{{ $tarifario->peso_min_kg }}/{{ $tarifario->peso_max_kg }}</td>
                                    <td>{{ $tarifario->local_1 }}</td>
                                    <td>{{ $tarifario->nacional }}</td>
                                    <td>{{ $tarifario->pro_dentro }}</td>
                                    <td>{{ $tarifario->pro_otro }}</td>
                                    <td>{{ $tarifario->cui1 }}</td>
                                    <td>{{ $tarifario->cui2 }}</td>
                                @endif

                                @if ($activeTab === 'PLIEGOS INT')
                                    <td>{{ $tarifario->peso_min_kg }}/{{ $tarifario->peso_max_kg }}</td>
                                    <td>{{ $tarifario->dest_a }}</td>
                                    <td>{{ $tarifario->dest_b }}</td>
                                    <td>{{ $tarifario->dest_c }}</td>
                                    <td>{{ $tarifario->dest_d }}</td>
                                    <td>{{ $tarifario->dest_e }}</td>
                                @endif

                                @if ($activeTab === 'PLIEGOS NAT')
                                    <td>{{ $tarifario->peso_min_kg }}/{{ $tarifario->peso_max_kg }}</td>
                                    <td>{{ $tarifario->local_1 }}</td>
                                    <td>{{ $tarifario->nacional }}</td>
                                    <td>{{ $tarifario->pro_dentro }}</td>
                                    <td>{{ $tarifario->pro_otro }}</td>
                                @endif

                                @if ($activeTab === 'SACAS M INT')
                                    <td>{{ $tarifario->peso_min_kg }}/{{ $tarifario->peso_max_kg }}</td>
                                    <td>{{ $tarifario->dest_a }}</td>
                                    <td>{{ $tarifario->dest_b }}</td>
                                    <td>{{ $tarifario->dest_c }}</td>
                                    <td>{{ $tarifario->dest_d }}</td>
                                    <td>{{ $tarifario->dest_e }}</td>
                                @endif

                                @if ($activeTab === 'SACAS M NAT')
                                    <td>{{ $tarifario->peso_min_kg }}/{{ $tarifario->peso_max_kg }}</td>
                                    <td>{{ $tarifario->nacional }}</td>
                                    <td>{{ $tarifario->pro_dentro }}</td>
                                @endif

                                @if ($activeTab === 'SUPER NAT')
                                    <td>{{ $tarifario->peso_min_kg }}/{{ $tarifario->peso_max_kg }}</td>
                                    <td>{{ $tarifario->nacional }}</td>
                                @endif

                                @if ($activeTab === 'SUPER PAQUE NAT')
                                    <td>{{ $tarifario->peso_min_kg }}/{{ $tarifario->peso_max_kg }}</td>
                                    <td>{{ $tarifario->dest_a }}</td>
                                    <td>{{ $tarifario->dest_b }}</td>
                                    <td>{{ $tarifario->dest_c }}</td>
                                    <td>{{ $tarifario->dest_d }}</td>
                                    <td>{{ $tarifario->dest_e }}</td>
                                    <td>{{ $tarifario->dest_f }}</td>
                                    <td>{{ $tarifario->dest_g }}</td>
                                @endif

                                @if ($activeTab === 'SUPER DOC NAT')
                                    <td>{{ $tarifario->peso_min_kg }}/{{ $tarifario->peso_max_kg }}</td>
                                    <td>{{ $tarifario->dest_a }}</td>
                                    <td>{{ $tarifario->dest_b }}</td>
                                    <td>{{ $tarifario->dest_c }}</td>
                                    <td>{{ $tarifario->dest_d }}</td>
                                    <td>{{ $tarifario->dest_e }}</td>
                                    <td>{{ $tarifario->dest_f }}</td>
                                    <td>{{ $tarifario->dest_g }}</td>
                                @endif
                            </tr>
                        @empty
                            <tr>
                                <td colspan="18" class="text-center">No se encontraron resultados.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $tarifarios->links() }}
            </div>
        </div>

        <div class="modal fade" id="createTarifaModal" tabindex="-1" role="dialog" aria-labelledby="modalTitle"
            aria-hidden="true">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Crear Tarifa - {{ $activeTab }}</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form wire:submit.prevent="createTarifa">
                            @if ($activeTab === 'EMS INT')
                                <div class="form-group">
                                    <label>Peso mínimo (kg)</label>
                                    <input type="number" step="0.01" wire:model="peso_min_kg" class="form-control">
                                </div>
                                <div class="form-group">
                                    <label>Peso máximo (kg)</label>
                                    <input type="number" step="0.01" wire:model="peso_max_kg" class="form-control">
                                </div>
                                <div class="form-group">
                                    <label>Destino A</label>
                                    <input type="text" wire:model="dest_a" class="form-control">
                                </div>
                                <div class="form-group">
                                    <label>Destino B</label>
                                    <input type="text" wire:model="dest_b" class="form-control">
                                </div>
                                <div class="form-group">
                                    <label>Destino C</label>
                                    <input type="text" wire:model="dest_c" class="form-control">
                                </div>
                                <div class="form-group">
                                    <label>Destino D</label>
                                    <input type="text" wire:model="dest_d" class="form-control">
                                </div>
                                <div class="form-group">
                                    <label>Destino E</label>
                                    <input type="text" wire:model="dest_e" class="form-control">
                                </div>
                                <!-- Otros campos específicos de EMS INT -->
                            @elseif ($activeTab === 'EMS NAT')
                                <div class="form-group">
                                    <label>Peso mínimo (kg)</label>
                                    <input type="number" step="0.01" wire:model="peso_min_kg" class="form-control">
                                </div>
                                <div class="form-group">
                                    <label>EMS Local 1</label>
                                    <input type="text" wire:model="local_1" class="form-control">
                                </div>
                                <div class="form-group">
                                    <label>EMS Local 2</label>
                                    <input type="text" wire:model="local_2" class="form-control">
                                </div>
                                <!-- Otros campos específicos de EMS NAT -->
                            @endif
                            <!-- Agregar más condicionales para otras pestañas -->
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                        <button type="button" class="btn btn-primary" wire:click="createTarifa">
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
    });
</script>