@extends('layout.nav')

@section('title', 'Editar Videos')

@section('content')

    <div class="container datosPropiedad g-1">

        <form action="{{ route('video.store') }}" method="POST" 
            enctype="multipart/form-data" 
            autocomplete="off" 
            novalidate
            class="row g-2 d-flex justify-content-start align-items-center">
            @csrf

            {{-- Agregar una nueva video --}}
            <div class = "col-md-1">
                <a href="{{ route('propiedad.edit', session('propiedad_id')) }}" class="btn">
                    <i class="btnIcons fa-solid fa-circle-left "></i>
                </a>
            </div>
            <div class="col-md-2 ">
                <!-- Formulario de carga de varias videos con detalles y previsualización dentro de un carrusel -->
                <label for="videos" class="form-label ">Seleccionar Videos</label>
            </div>
            <div class = "col-md-3">
                <input type="file" class="form-control form-select " id="videos" name="videos[]"
                    accept="video/*" multiple>
            </div>
            <div class= "col-md-3">
                <button type="submit" class="btn btn-primary">Subir Video</button>
            </div>
        </form>
        <hr>
        {{-- Galería de videos existentes --}}
        <div class="row  g-1 overflow-y-auto" style="max-height: 65vh;">
            @forelse ($videos as $video)
                <div class="col-md-4">
                    <div class="card">
                        {{-- Video actual --}}
                        <video src="{{ $video->url }}" class="card-img-top" controls
                            style="height: 200px; object-fit: cover;">
                            Tu navegador no soporta la etiqueta de video.
                        </video>

                        <div class="card-body">
                            {{-- Formulario para editar o eliminar comentarios --}}
                            <form action="{{ route('video.update', $video->id) }}" method="POST"
                                class="row g-1 d-flex justify-content-between align-items-center" autocomplete="off">
                                @csrf
                                @method('PUT')
                                <div class="col-md-9">
                                    <textarea name="notes" class="form-control text-center" rows="3" placeholder="Escribe una nota...">{{ $video->notes }}</textarea>
                                </div>
                                <div class="col-md-3">
                                    <button type="submit" class="btn btn-primary btn-sm w-100">Guardar</button>
                                </div>
                            </form>

                            <hr>
                            {{-- Formulario para reemplazar una video --}}
                            <form action="{{ route('video.update', $video->id) }}" method="POST"
                                enctype="multipart/form-data" class="row g-1" autocomplete="off">
                                @csrf
                                @method('PUT')
                                <div class="col-md-9">
                                    <input type="file" name="nueva_video" class="form-control" accept="video/*">
                                </div>
                                <div class="col-md-2">
                                    <button type="submit" class="btn btn-primary btn-sm">Actualizar</button>
                                </div>
                            </form>
                            <br>
                            {{-- Formulario para eliminar una video --}}
                            <form action="{{ route('video.destroy', $video->id) }}" method="POST"
                                onsubmit="return confirm('¿Estás seguro de eliminar esta video?')"
                                class="g-3 d-flex justify-content-end" autocomplete="off">
                                @csrf
                                @method('DELETE')

                                <button type="submit" class="btn btn-danger btn-sm w-100">Eliminar</button>
                            </form>
                        </div>
                    </div>
                </div>
            @empty
                <div class="text-center">No hay videos disponibles</div>
            @endforelse
        </div>
    </div>


    <script>
        // Mostrar alerta si hay un mensaje de error en la sesión
        @if(session('error'))
            alert("{{ session('error') }}");
        @endif

        document.getElementById('videos').addEventListener('change', function(event) {
            let videosDetallesDiv = document.getElementById('videosDetalles');
            videosDetallesDiv.innerHTML = ''; // Limpiar las diapositivas anteriores

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

                videosDetallesDiv.appendChild(slideContainer);

                // Leer el archivo como URL
                reader.readAsDataURL(file);
            }
        });
    </script>
@endsection
