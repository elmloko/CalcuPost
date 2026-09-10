<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <style>
        @page { margin: 24px; }
        body { color: #153257; font-family: DejaVu Sans, sans-serif; font-size: 10px; }
        h1 { color: #0f4aa1; font-size: 20px; margin: 0 0 4px; }
        .accent { background: #f4c52a; height: 4px; margin: 0 0 16px; width: 76px; }
        .meta { color: #49678c; margin-bottom: 14px; }
        table { border-collapse: collapse; width: 100%; }
        th { background: #edf4ff; color: #082f68; text-align: left; }
        th, td { border-bottom: 1px solid #d9e4f2; padding: 8px 6px; }
        .status { color: #0f4aa1; font-weight: bold; }
    </style>
</head>
<body>
    <h1>Reporte de usuarios</h1>
    <div class="accent"></div>
    <p class="meta">Generado: {{ now()->format('d/m/Y H:i') }} | Total: {{ $users->count() }}</p>
    <table>
        <thead>
            <tr>
                <th>N.º</th>
                <th>Nombre completo</th>
                <th>Correo electrónico</th>
                <th>Regional</th>
                <th>Carnet de identidad</th>
                <th>Estado</th>
                <th>Tipo de cuenta</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($users as $user)
                <tr>
                    <td>{{ $user->id }}</td>
                    <td>{{ $user->name }}</td>
                    <td>{{ $user->email }}</td>
                    <td>{{ $user->city ?: '-' }}</td>
                    <td>{{ $user->ci ?: '-' }}</td>
                    <td class="status">{{ $user->deleted_at ? 'Inactivo' : 'Activo' }}</td>
                    <td>{{ $user->isAdministrator() ? 'Administrador' : 'Usuario' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
