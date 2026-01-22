 <div class="modal fade" id="descripcionPropiedad" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
     <div class="modal-dialog modal-md modal-dialog-centered modal-dialog-scrollable">
         <div class="modal-content">
             <div class="modal-header">
                 <h1 class="modal-title fs-5" id="exampleModalLabel">Descripcion de la
                     Propiedad</h1>
                 <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
             </div>
             <div class="modal-body ">
                 <input type="hidden" name="formulario" id="formulario" value="">
                 <div class="row g-3">
                     <div class="col-md-12">
                         <div class="mb-6">
                             <textarea name="descipcion_propiedad" id="descripcion" class="form-control form-control-atcl" rows="10">{{ session('descipcion_propiedad', $propiedad->descipcion_propiedad) }}</textarea>
                         </div>
                     </div>
                 </div>
                 <div class="modal-footer">
                     <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                 </div>
             </div>
         </div>
     </div>
 </div>
