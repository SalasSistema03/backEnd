@extends('layout.nav')

@section('title', 'Editar Documentacion')

@section('content')
    
    <div class = "container datosPropiedad g-1">

        <form action="{{ route('documentacion.store') }}" method="POST" enctype="multipart/form-data" autocomplete="off"
            novalidate class="row g-2 d-flex justify-content-start align-items-center">
            @csrf
            <div class = "col-md-1">
                <a href="{{ route('propiedad.edit', session('propiedad_id')) }}" class="btn">
                    <i class="btnIcons fa-solid fa-circle-left "></i>
                </a>
            </div>
            <!-- Campo para seleccionar múltiples fotos -->
            <div class="col-md-2">
                <label for="fotos" class="form-label ">Seleccionar Fotos</label>
            </div>
            <div class="col-md-3">
                <input type="file" class="form-control form-select " id="fotos" name="fotos[]"
                    accept="image/*,application/pdf" multiple>
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-primary w-100">Subir Documentacion</button>
            </div>
        </form>

        <hr>

        {{-- Galería de fotos existentes --}}
        {{-- Galería de documentos existentes --}}
        <div class="row  g-1 overflow-y-auto" style="max-height: 65vh">
            @forelse ($documentos as $documento)
                <div class="col-md-4">
                    <div class="card">
                        {{-- Previsualización del PDF --}}
                        <iframe src="{{ $documento->url }}"  height="200px" style="border:none;"></iframe>
                        <div class="card-body">
                             {{-- Formulario para editar o eliminar nota --}}
                             <form action="{{ route('documentacion.update', $documento->id) }}" method="POST"
                                class="row g-1 d-flex justify-content-between align-items-center" autocomplete="off">
                                @csrf
                                @method('PUT')
                                <div class="col-md-9">
                                    <textarea name="notes" class="form-control text-center" rows="3" placeholder="Escribe una nota...">{{ $documento->notes }}</textarea>
                                </div>
                                <div class="col-md-3">
                                    <button type="submit" class="btn btn-primary btn-sm w-100">Guardar</button>
                                </div>
                                
                            </form>
                            <hr>
                            {{-- Formulario para reemplazar un PDF --}}
                            <form action="{{ route('documentacion.update', $documento->id) }}" method="POST"
                                enctype="multipart/form-data" class="row g-1" autocomplete="off">
                                @csrf
                                @method('PUT')
                                <div class="col-md-9">
                                    <input type="file" name="nueva_foto" class="form-control mb-2" accept="application/pdf">
                                </div>
                                <div class="col-md-2">
                                    <button type="submit" class="btn btn-primary btn-sm">Actualizar</button>
                                </div> 
                            </form>
                            
                            {{-- Formulario para eliminar un PDF --}}
                            <form action="{{ route('documentacion.destroy', $documento->id) }}" method="POST"
                                onsubmit="return confirm('¿Estás seguro de eliminar el documento?')"
                                class="g-3 d-flex justify-content-end mt-3" autocomplete="off">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm w-100">Eliminar</button>
                            </form>
                        </div>
                    </div>
                </div>
            @empty
                <div class="text-center">No hay documentos disponibles</div>
            @endforelse
        </div>

    </div>



    <script>
        document.getElementById('fotos').addEventListener('change', function(event) {
            let fotosDetallesDiv = document.getElementById('fotosDetalles');
            fotosDetallesDiv.innerHTML = ''; // Limpiar las diapositivas anteriores

            const archivos = event.target.files;

            for (let i = 0; i < archivos.length; i++) {
                const index = i + 1;
                const file = archivos[i];
                const reader = new FileReader();

                // Crear contenedor para la diapositiva
                const slideContainer = document.createElement('div');
                slideContainer.classList.add('carousel-item');

                reader.onload = function(e) {
                    let contenido = '';

                    if (file.type.startsWith('image/')) {
                        // Imagen con el mismo tamaño que antes
                        contenido = `<img src="${e.target.result}" class="d-block w-100 img-thumbnail" 
                    style="height: 250px; object-fit: cover;">`;
                    } else if (file.type === "application/pdf") {
                        // PDF con el mismo tamaño que las imágenes
                        contenido = `
                    <iframe src="${e.target.result}" width="100%" height="250px" 
                        style="border:none; object-fit: cover;"></iframe>
                `;
                    }

                    // Agregar la imagen o PDF dentro del slideContainer
                    slideContainer.innerHTML = contenido;

                    // Agregar la descripción debajo del archivo
                    const detallesHtml = `
                <div class="carousel-caption d-none d-md-block">
                    <label for="descripcion${index}" class="form-label mt-2">Descripción del archivo ${index}</label>
                    <textarea class="form-control" id="descripcion${index}" name="notes[${i}][descripcion]"
                        rows="2" placeholder="Descripción"></textarea>
                </div>
            `;

                    // Agregar descripción debajo de la imagen o PDF
                    slideContainer.innerHTML += detallesHtml;
                };

                // Si es la primera diapositiva, agregar la clase "active"
                if (i === 0) {
                    slideContainer.classList.add('active');
                }

                fotosDetallesDiv.appendChild(slideContainer);

                // Leer el archivo como URL
                reader.readAsDataURL(file);
            }
        });
    </script>
@endsection
