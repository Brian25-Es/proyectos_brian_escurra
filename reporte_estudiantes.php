<?php
$servername = "localhost";
$username   = "adminphp";
$password   = "TuContraseñaSegura";
$dbname     = "myDB";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("❌ Conexión fallida: " . $conn->connect_error);
}

$sql = "SELECT e.id, e.nombre, e.edad, c.nombre AS carrera
        FROM estudiantes e
        JOIN carreras c ON e.carrera_id = c.id";
$result = $conn->query($sql);

$estudiantes = [];
while ($row = $result->fetch_assoc()) {
    $id = $row['id'];
    $estudiantes[$id] = [
        'nombre'  => $row['nombre'],
        'edad'    => $row['edad'],
        'carrera' => $row['carrera'],
        'notas'   => []
    ];
}

$sql = "SELECT estudiante_id, valor FROM notas";
$result = $conn->query($sql);
while ($row = $result->fetch_assoc()) {
    $estudiantes[$row['estudiante_id']]['notas'][] = $row['valor'];
}

$conn->close();

function calcularPromedio($notas) {
    return count($notas) > 0 ? array_sum($notas) / count($notas) : 0;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Estudiantes</title>
    <style>
        table { border-collapse: collapse; width: 80%; margin: 20px auto; }
        th, td { border: 1px solid black; padding: 8px; text-align: center; }
        th { background-color: #f2f2f2; }
        .acciones button { margin: 2px; padding: 5px 10px; cursor: pointer; }
        .acciones form { display: inline; }
    </style>
</head>
<body>
<h2 style="text-align:center;">📋 Reporte de Estudiantes</h2>
<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Nombre</th>
            <th>Edad</th>
            <th>Carrera</th>
            <th>Notas</th>
            <th>Promedio</th>
            <th>Acciones</th>
        </tr>
    </thead>
    <tbody>
    <?php foreach ($estudiantes as $id => $est): 
        $promedio = calcularPromedio($est['notas']);
    ?>
    <tr>
        <td><?php echo $id; ?></td>
        <td><?php echo htmlspecialchars($est['nombre']); ?></td>
        <td><?php echo htmlspecialchars($est['edad']); ?></td>
        <td><?php echo htmlspecialchars($est['carrera']); ?></td>
        <td><?php echo implode(", ", $est['notas']); ?></td>
        <td><?php echo number_format($promedio, 2); ?></td>
        <td class="acciones">
            <form action="editar_estudiante.php" method="get">
                <input type="hidden" name="id" value="<?php echo $id; ?>">
                <button type="submit">✏️ Editar</button>
            </form>
            <form action="eliminar_estudiante.php" method="post" onsubmit="return confirm('¿Seguro que quieres eliminar este alumno?');">
                <input type="hidden" name="id" value="<?php echo $id; ?>">
                <button type="submit">🗑️ Borrar</button>
            </form>
        </td>
    </tr>
    <?php endforeach; ?>
    </tbody>
</table>
</body>
</html>