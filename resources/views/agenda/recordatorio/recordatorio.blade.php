@extends('layout.nav')
@section('title', 'Recordatorio ' . session('usuario')->username)
@section('content')
    <div class="p-2">
        <div class="row g-3">
            <!------------------------------------------------ Formulario para agendar ----------------------------------------------------------->
            <div class="col-md-3 ">
                <div class="card shadow-sm h-100 card-salas-recordatorio-carga">
                    <div class="card-header card-header-recordatorio">
                        Agendar Recordatorio
                    </div>
                    <div class="card-body p-2 card-salas-recordatorio-carga text-center">
                        <form action="{{ route('recordatorio.store') }}" method="post" autocomplete="off">
                            @csrf

                            <div class="row">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold" for="fecha">Fecha</label>
                                    <input type="date" id="fecha" class="form-control" name="fecha_inicio">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold" for="hora">Hora</label>
                                    <input type="time" id="hora" class="form-control" name="hora">
                                </div>
                                <div class="col-md-4 ">
                                    <label class="form-label fw-semibold" for="titulo">Frecuencia</label>
                                    <select name="intervalo" id="intervalo" class="form-control">
                                        <option value="">Seleccione</option>
                                        <option value="Diario">Diario</option>
                                        <option value="Mensual">Mensual</option>
                                    </select>
                                </div>
                                <div class="col-md-4 ">
                                    <label class="form-label fw-semibold" for="titulo">Cantidad</label>
                                    <input type="number" id="cantidad" class="form-control" name="cantidad" value="1"
                                        min="1">
                                </div>
                                <div class="col-md-4 ">
                                    <label class="form-label fw-semibold" for="titulo">Repetir</label>
                                    <input type="number" id="repetir" class="form-control" name="repetir" value="1"
                                        min="1">
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 ">
                                    <label class="form-label fw-semibold" for="intervalo-modal">Proximo</label>
                                    <input type="date" id="proximo-carga" class="form-control" name="cantidad" disabled>
                                </div>
                                <div class="col-md-6 ">
                                    <label class="form-label fw-semibold" for="repetir-modal">Finaliza</label>
                                    <input type="date" id="finaliza-carga" class="form-control" name="repetir" disabled>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label fw-semibold" for="titulo">Agendar</label>
                                <select name="agenda_id" id="agenda_id" class="form-control">
                                    <option value="">Seleccione</option>
                                    <option value="">Personal</option>
                                    @foreach ($agenda as $item)
                                        <option value="{{ $item->id }}">{{ $item->sector->nombre }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label fw-semibold" for="descripcion">Descripción</label>
                                <textarea id="descripcion" rows="3" class="form-control" name="descripcion"
                                    placeholder="Detalles del recordatorio"></textarea>
                            </div>
                            <input type="hidden" name="usuario_id" value="{{ session('usuario_id') }}">
                            <button type="submit" class="btn btnSalas w-100 mt-2">
                                Guardar
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            <!-- ------------------------------------------Lista de recordatorios agendados ----------------------------------------------------------->
            <div class="col-md-5 ">
                <div class="card shadow-sm h-100 ">
                    <div class="card-header card-header-recordatorio">
                        Recordatorios Pendientes
                    </div>
                    <div class="card-body p-1 card-salas-recordatorio ">
                        @foreach ($recordatorioHoy as $item)
                            <div class="list-group list-group-flush">
                                <div class="list-group-item list-group-item-action">
                                    <div class="d-flex justify-content-between align-items-start gap-3">
                                        <!-- Contenido principal con flex-grow para ocupar el espacio disponible -->
                                        <div class="flex-grow-1 min-width-0">
                                            <div>
                                                <p class="mb-1 text-break">{{ $item->descripcion }}</p>
                                            </div>
                                            @if ($item->agenda_id)
                                                <div class="row">
                                                    <div class="col-md-8 row">
                                                        <small class="text-muted col-md-6">
                                                            <label for="">Fecha: </label>
                                                            <small>{{ \Carbon\Carbon::parse($item->fecha_inicio)->format('d/m/Y') }}</small>
                                                        </small>
                                                        <small class="text-muted col-md-6">
                                                            <label for="">Hora:</label>
                                                            <small>
                                                                {{ \Carbon\Carbon::parse($item->hora)->format('H:i') }}</small>
                                                        </small>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <small class="text-muted">
                                                            <label for="">Sector:</label>
                                                            <small>{{ $item->agenda->sector->nombre }}</small>
                                                        </small>
                                                    </div>
                                                </div>
                                            @else
                                                <div class="row">
                                                    <div class="col-md-12 row">
                                                        <small class="text-muted col-md-6">
                                                            <label for="">Fecha: </label>
                                                            <small>{{ \Carbon\Carbon::parse($item->fecha_inicio)->format('d/m/Y') }}</small>
                                                        </small>
                                                        <small class="text-muted col-md-6">
                                                            <label for="">Hora:</label>
                                                            <small>{{ \Carbon\Carbon::parse($item->hora)->format('H:i') }}</small>
                                                        </small>
                                                    </div>
                                                </div>
                                            @endif
                                        </div>

                                        <!-- Botones con flex-shrink-0 para mantener su tamaño -->
                                        <div class="flex-shrink-0 d-flex gap-2">
                                            <form action="{{ route('recordatorio.update', $item->id) }}" method="POST" autocomplete="off">
                                                @csrf
                                                @method('PUT')
                                                <input type="hidden" name="finalizado" value="1">
                                                <input type="hidden" name="id" value="{{ $item->id }}">
                                                <button type="submit" class="btn btn-sm btn-success">
                                                    <i class="bi bi-check-lg"></i>
                                                </button>
                                            </form>

                                            <button id="editarRecordatorio" type="button" class="btn btn-sm btn-primary"
                                                data-bs-toggle="modal" data-bs-target="#editarRecordatorioModal"
                                                data-id="{{ $item->id }}"
                                                data-descripcion="{{ $item->descripcion }}"
                                                data-fecha_inicio="{{ $item->fecha_inicio }}"
                                                data-hora="{{ $item->hora }}" data-intervalo="{{ $item->intervalo }}"
                                                data-cantidad="{{ $item->cantidad }}"
                                                data-agenda_id="{{ $item->agenda_id }}"
                                                data-repetir="{{ $item->repetir }}"
                                                data-agenda_nombre="{{ $item->agenda->sector->nombre ?? 'Personal' }}">
                                                <i class="bi bi-pencil me-1"></i>
                                            </button>

                                            <form action="{{ route('recordatorio.destroy', $item->id) }}" method="POST" autocomplete="off">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger" title="Eliminar">
                                                    <i class="bi bi-trash3"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <hr>
                        @endforeach
                    </div>
                </div>
            </div>
            <!------------------------------------------------ Lista de recordatorios PROXIMOS ----------------------------------------------------------->
            <div class="col-md-4 ">
                <div class="card shadow-sm h-100 ">
                    <div class="card-header card-header-recordatorio">
                        Proximos Recordatorios
                    </div>
                    <div class="card-body p-1 card-salas-recordatorio-futuros">
                        @foreach ($recordatorio as $item)
                            {{-- @dump($item) --}}
                            <div class="list-group list-group-flush">
                                <div class="list-group-item list-group-item-action">
                                    <div class="d-flex justify-content-between align-items-start ">
                                        <!-- Contenido principal con flex-grow para ocupar el espacio disponible -->
                                        <div class="flex-grow-1 min-width-0">
                                            <div>
                                                <p class="mb-1 text-break">{{ $item->descripcion }}</p>
                                            </div>
                                            <div id="fechas2">
                                                @if ($item->agenda_id)
                                                    <div class="row">
                                                        <div class="col-md-4">
                                                            <small class="text-muted">
                                                                <label for="">Fecha: </label>
                                                                <small>{{ \Carbon\Carbon::parse($item->fecha_inicio)->format('d/m/Y') }}</small>
                                                            </small>
                                                        </div>
                                                        <div class="col-md-3">
                                                            <small class="text-muted">
                                                                <label for="">Hora:</label>
                                                                <small>{{ \Carbon\Carbon::parse($item->hora)->format('H:i') }}</small>
                                                            </small>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <small class="text-muted">
                                                                <label for="">Sector:</label>
                                                                <small>{{ $item->agenda->sector->nombre }}</small>
                                                            </small>
                                                        </div>
                                                    </div>
                                                @else
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <small class="text-muted">
                                                                <label for="">Fecha: </label>
                                                                <small>{{ \Carbon\Carbon::parse($item->fecha_inicio)->format('d/m/Y') }}</small>
                                                            </small>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <small class="text-muted">
                                                                <label for="">Hora:</label>
                                                                <small>{{ \Carbon\Carbon::parse($item->hora)->format('H:i') }}</small>
                                                            </small>
                                                        </div>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>

                                        <!-- Botones con flex-shrink-0 para mantener su tamaño -->
                                        <div class="flex-shrink-0 d-flex gap-1">
                                            <form action="{{ route('recordatorio.update', $item->id) }}" method="POST" autocomplete="off">
                                                @csrf
                                                @method('PUT')
                                                <input type="hidden" name="finalizado" value="1">
                                                <input type="hidden" name="id" value="{{ $item->id }}">
                                                <button type="submit" class="btn btn-sm btn-success">
                                                    <i class="bi bi-check-lg"></i>
                                                </button>
                                            </form>

                                            <button type="button" class="btn btn-sm btnSalasAzul" data-bs-toggle="modal"
                                                data-bs-target="#editarRecordatorioModal" data-id="{{ $item->id }}"
                                                data-descripcion="{{ $item->descripcion }}"
                                                data-fecha_inicio="{{ $item->fecha_inicio }}"
                                                data-hora="{{ $item->hora }}" data-intervalo="{{ $item->intervalo }}"
                                                data-cantidad="{{ $item->cantidad }}"
                                                data-agenda_id="{{ $item->agenda_id }}"
                                                data-repetir="{{ $item->repetir }}"
                                                data-agenda_nombre="{{ $item->agenda->sector->nombre ?? 'Personal' }}">
                                                <i class="bi bi-pencil me-1"></i>
                                            </button>

                                            <form action="{{ route('recordatorio.destroy', $item->id) }}" method="POST" autocomplete="off">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger" title="Eliminar">
                                                    <i class="bi bi-trash3"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <hr>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
        @include('agenda.recordatorio.modal-editar-recordatorio')
    </div>

@endsection
@section('scripts')
    <script src="{{ asset('js/agenda/recordatorio/pasar-datos-modal.js') }}"></script>
    <script src="{{ asset('js/agenda/recordatorio/calcular-fechas.js') }}"></script>
@endsection
