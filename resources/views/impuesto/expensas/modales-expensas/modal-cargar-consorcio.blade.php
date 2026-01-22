<div class="modal fade" id="CargarConsorcio" tabindex="-1" aria-labelledby="CargarConsorcioLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="CargarConsorcioLabel">Cargar Consorcio</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="{{ route('exp_edificios.cargar') }}" method="POST" autocomplete="off">
                    @csrf
                    <div class="mb-3">
                        <label for="search" class="form-label">Nombre Edificio</label>
                        <input type="text" class="form-control" id="search" name="nombre">
                    </div>
                    <div class="row">
                        <div class="col-md-9 ">
                            <label for="search-calle" style="margin-bottom: 9px;">Calle</label>
                            <input type="text" id="search-calle"
                                class="form-control @error('calle') is-invalid @enderror" placeholder="Buscar calle..."
                                value="{{ optional(App\Models\At_cl\Calle::find(old('calle')))->name }}">
                            <input type="hidden" id="calle_id" name="calle" value="{{ old('calle') }}">
                            <div id="search-results" class="list-group mt-2" style="position: absolute; z-index: 1000;">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label for="search" class="form-label">Altura</label>
                            <input type="number" class="form-control" id="search" name="altura" >
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="search" class="form-label">Nombre Administrador</label>
                        <select name="administrador" id="administrador" class="form-control">
                            @foreach ($administradores as $administrador)
                                <option value="{{ $administrador->id }}">{{ $administrador->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary">Guardar</button>
                </form>
            </div>
        </div>
    </div>
</div>
