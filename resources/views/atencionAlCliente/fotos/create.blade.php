<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Subir Foto</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-5">
        <h1>Subir Foto</h1>

        @if(session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        <form action="{{ route('fotos.store') }}" method="POST" enctype="multipart/form-data" autocomplete="off">
            @csrf
            <div class="mb-3">
                <label for="propiedad_id" class="form-label">Propiedad</label>
                <select name="propiedad_id" id="propiedad_id" class="form-select" required>
                    <option value="">Seleccione una propiedad</option>
                    @foreach($propiedades as $propiedad)
                        <option value="{{ $propiedad->id }}">{{ $propiedad->id }}</option>
                    @endforeach
                </select>
            </div>

            <div class="mb-3">
                <label for="foto" class="form-label">Foto</label>
                <input type="file" name="foto" id="foto" class="form-control" required>
            </div>

            <div class="mb-3">
                <label for="notes" class="form-label">Notas</label>
                <input type="text" name="notes" id="notes" class="form-control" maxlength="255">
            </div>

            <button type="submit" class="btn btn-primary">Subir Foto</button>
        </form>
    </div>
</body>
</html>
