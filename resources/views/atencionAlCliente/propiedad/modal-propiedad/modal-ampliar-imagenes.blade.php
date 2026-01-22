 <div class="modal fade" id="imageModal" tabindex="-1" aria-labelledby="imageModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-fullscreen-xxl-down">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="imageModalLabel">{{ $propiedad->calle->name ?? '-' }}
                        {{ $propiedad->numero_calle ?? '-' }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <div id="modalCarousel" class="carousel slide">
                        <div class="carousel-inner">
                            @foreach ($fotos as $index => $foto)
                                <div class="carousel-item {{ $loop->first ? 'active' : '' }}">
                                    <div class="d-flex justify-content-center align-items-center div-foto">
                                        <img src="{{ $foto->url }}" alt="Imagen de propiedad"
                                            style="object-fit: cover;
                                            height: auto;
                                            width: auto;
                                            min-height: 75vh;
                                            max-height: 77vh;">
                                    </div>
                                    <div class="p-2 bg-white">
                                        <!-- Notas siempre en la parte inferior -->
                                        <input type="text" class="form-control" value="{{ $foto->notes }}" disabled>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <button class="carousel-control-prev" type="button" data-bs-target="#modalCarousel"
                            data-bs-slide="prev">
                            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                            <span class="visually-hidden">Previous</span>
                        </button>
                        <button class="carousel-control-next" type="button" data-bs-target="#modalCarousel"
                            data-bs-slide="next">
                            <span class="carousel-control-next-icon" aria-hidden="true"></span>
                            <span class="visually-hidden">Next</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
