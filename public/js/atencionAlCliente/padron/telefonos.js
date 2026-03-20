
        let contador = 1;

        function agregarTelefono() {
            const div = document.createElement('div');
            div.classList.add('telefono');
            div.innerHTML = `
            
            <div class="telefono d-flex gap-2 pt-2">
            
            <input class="form-control"type="text" name="telefonos[${contador}][phone_number]" placeholder="Teléfono" required>
            <input class="form-control"type="text" name="telefonos[${contador}][notes]" placeholder="Notas">
            </div>
            `;
            document.getElementById('telefonos').appendChild(div);
            contador++;
        }
