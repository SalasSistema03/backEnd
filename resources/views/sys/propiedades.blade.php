<!DOCTYPE html>
<html lang="es">
<head> 
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Listado de Propiedades</title>
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        th, td {
            padding: 10px;
            border: 1px solid #ddd;
            text-align: left;
        }
        th {
            background-color: #f4f4f4;
        }
    </style>
</head>
<body>
    <h2>Datos desde la segunda base de datos</h2>
    <table>
        <thead>
            <tr>
                <th>ID Padrón</th>
                <th>Razón Social</th>
            </tr>
        </thead>
        <tbody>
            {{-- @dd($propiedades) --}}
            @forelse ($propiedades as $p)
                <tr>
                    <td>{{ $p->id_casa }}</td>
                    <td>{{ $p->carpeta }}</td>
                    <td>{{ $p->rescicion }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="2">No hay datos disponibles</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
