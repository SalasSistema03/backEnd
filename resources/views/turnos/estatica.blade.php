<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="Visualizador">
    <title>Visualizador</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .numeric-keypad {
            max-width: 300px;
            margin: 10px auto;
            height: 300px;
        }
        
        .keypad-button {
            width: 60px;
            height: 60px;
            font-size: 18px;
            font-weight: bold;
            margin: 2px;
            border: 2px solid #007bff;
            background-color: #f8f9fa;
            color: #007bff;
            border-radius: 8px;
            cursor: pointer;
            -webkit-user-select: none;
            -moz-user-select: none;
            -ms-user-select: none;
            user-select: none;
            -webkit-tap-highlight-color: transparent;
        }
        
        .keypad-button:hover,
        .keypad-button:active,
        .keypad-button.pressed {
            background-color: #007bff;
            color: white;
        }
        
        .keypad-button.action-btn {
            background-color: #dc3545;
            border-color: #dc3545;
            color: white;
        }
        
        .keypad-button.action-btn:hover,
        .keypad-button.action-btn:active,
        .keypad-button.action-btn.pressed {
            background-color: #c82333;
            border-color: #bd2130;
        }
        
        .number-display {
            font-size: 24px;
            font-weight: bold;
            text-align: center;
            background-color: #f8f9fa;
            border: 2px solid #dee2e6;
            border-radius: 8px;
            padding: 15px;
            min-height: 60px;
            display: block;
            line-height: 30px;
        }
        
        .keypad-container {
            border: 1px solid #dee2e6;
            border-radius: 10px;
            padding: 15px;
            background-color: white;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .keypad-row {
            text-align: center;
            margin-bottom: 5px;
        }
    </style>
</head>
<body>
    <div class="px-3 mt-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Visualizador</h5>
            </div>
            <div class="card-body">
                <div class="row g-3 d-flex justify-content-between align-items-center">
                    <div class="col-md-2 justify-content-end d-flex text-center">
                        <label for="tipoIdentificador" class="form-label">Tipo Identificador</label>
                    </div>
                    <div class="col-md-10">
                        <select name="tipoIdentificador" id="tipoIdentificador" class="form-control">
                            <option value="FOLIO">FOLIO</option>
                            <option value="DNI">DNI</option>
                        </select>
                    </div>

                    <div class="col-md-2 justify-content-end d-flex">
                        <label for="numero" class="form-label">Número</label>
                    </div>
                    <div class="col-md-10">
                        <!-- Display del número -->
                        <div class="number-display" id="numberDisplay">0</div>
                        
                        <!-- Teclado numérico -->
                        <div class="keypad-container mt-3">
                            <div class="numeric-keypad">
                                <div class="keypad-row">
                                    <button type="button" class="keypad-button" onclick="addNumber('1')">1</button>
                                    <button type="button" class="keypad-button" onclick="addNumber('2')">2</button>
                                    <button type="button" class="keypad-button" onclick="addNumber('3')">3</button>
                                </div>
                                <div class="keypad-row">
                                    <button type="button" class="keypad-button" onclick="addNumber('4')">4</button>
                                    <button type="button" class="keypad-button" onclick="addNumber('5')">5</button>
                                    <button type="button" class="keypad-button" onclick="addNumber('6')">6</button>
                                </div>
                                <div class="keypad-row">
                                    <button type="button" class="keypad-button" onclick="addNumber('7')">7</button>
                                    <button type="button" class="keypad-button" onclick="addNumber('8')">8</button>
                                    <button type="button" class="keypad-button" onclick="addNumber('9')">9</button>
                                </div>
                                <div class="keypad-row">
                                    <button type="button" class="keypad-button action-btn" onclick="clearAll()">C</button>
                                    <button type="button" class="keypad-button" onclick="addNumber('0')">0</button>
                                    <button type="button" class="keypad-button action-btn" onclick="backspace()">←</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-12 d-flex justify-content-center">
                        <button type="button" class="btn btn-primary w-50" onclick="asignar()">Asignar</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        var currentNumber = '0';
        
        // Función para obtener elementos por ID (compatible con navegadores antiguos)
        function getElement(id) {
            return document.getElementById(id);
        }

        // Función para actualizar el display
        function updateDisplay() {
            var displayElement = getElement('numberDisplay');
            if (displayElement) {
                displayElement.innerHTML = currentNumber === '' ? '0' : currentNumber;
            }
        }

        // Agregar número
        function addNumber(num) {
            if (currentNumber === '0') {
                currentNumber = num;
            } else if (currentNumber.length < 15) {
                currentNumber += num;
            }
            updateDisplay();
            
            // Efecto visual para el botón presionado
            highlightButton(num);
        }

        // Limpiar todo
        function clearAll() {
            currentNumber = '0';
            updateDisplay();
            
            // Efecto visual para el botón C
            highlightActionButton('C');
        }

        // Borrar último dígito
        function backspace() {
            if (currentNumber.length > 1) {
                currentNumber = currentNumber.substring(0, currentNumber.length - 1);
            } else {
                currentNumber = '0';
            }
            updateDisplay();
            
            // Efecto visual para el botón backspace
            highlightActionButton('←');
        }

        // Función para asignar
        function asignar() {
            var tipoSelect = getElement('tipoIdentificador');
            var tipoIdentificador = tipoSelect ? tipoSelect.value : '';
            var numero = currentNumber;
            
            alert('Tipo: ' + tipoIdentificador + '\nNúmero: ' + numero);
            
            // Aquí puedes agregar la lógica para enviar los datos
            console.log('Tipo Identificador:', tipoIdentificador);
            console.log('Número:', numero);
        }

        // Efecto visual para botones numéricos
        function highlightButton(num) {
            var buttons = document.getElementsByClassName('keypad-button');
            for (var i = 0; i < buttons.length; i++) {
                if (buttons[i].innerHTML === num && !hasClass(buttons[i], 'action-btn')) {
                    addClass(buttons[i], 'pressed');
                    setTimeout(function() {
                        removeClass(buttons[i], 'pressed');
                    }, 150);
                    break;
                }
            }
        }

        // Efecto visual para botones de acción
        function highlightActionButton(text) {
            var buttons = document.getElementsByClassName('keypad-button');
            for (var i = 0; i < buttons.length; i++) {
                if (buttons[i].innerHTML === text && hasClass(buttons[i], 'action-btn')) {
                    addClass(buttons[i], 'pressed');
                    setTimeout(function() {
                        removeClass(buttons[i], 'pressed');
                    }, 150);
                    break;
                }
            }
        }

        // Funciones auxiliares para manejar clases (compatibles con navegadores antiguos)
        function hasClass(element, className) {
            return (' ' + element.className + ' ').indexOf(' ' + className + ' ') > -1;
        }

        function addClass(element, className) {
            if (!hasClass(element, className)) {
                element.className += ' ' + className;
            }
        }

        function removeClass(element, className) {
            var classes = element.className.split(' ');
            var newClasses = [];
            for (var i = 0; i < classes.length; i++) {
                if (classes[i] !== className) {
                    newClasses.push(classes[i]);
                }
            }
            element.className = newClasses.join(' ');
        }

        // Soporte básico para teclado físico
        document.onkeydown = function(event) {
            var key = event.keyCode || event.which;
            
            // Números 0-9
            if (key >= 48 && key <= 57) {
                var num = String.fromCharCode(key);
                addNumber(num);
            }
            // Números del teclado numérico
            else if (key >= 96 && key <= 105) {
                var num = (key - 96).toString();
                addNumber(num);
            }
            // Backspace
            else if (key === 8) {
                if (event.preventDefault) {
                    event.preventDefault();
                } else {
                    event.returnValue = false;
                }
                backspace();
            }
            // Delete o Escape
            else if (key === 46 || key === 27) {
                clearAll();
            }
            // Enter
            else if (key === 13) {
                asignar();
            }
        };

        // Inicializar cuando la página esté cargada
        window.onload = function() {
            updateDisplay();
        };
    </script>
</body>
</html>