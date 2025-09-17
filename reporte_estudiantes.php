<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$servername = "localhost";
$username   = "adminphp";
$password   = "TuContraseñaSegura";
$dbname     = "myDB";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("❌ Conexión fallida: " . $conn->connect_error);
}

$sql = "
    SELECT e.id, e.nombre, e.edad, c.nombre AS carrera, n.valor
    FROM estudiantes e
    JOIN carreras c ON e.carrera_id = c.id
    LEFT JOIN notas n ON e.id = n.estudiante_id
    ORDER BY e.id
";

$result = $conn->query($sql);

$estudiantes = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $id = $row['id'];
        if (!isset($estudiantes[$id])) {
            $estudiantes[$id] = [
                'nombre' => $row['nombre'],
                'edad' => $row['edad'],
                'carrera' => $row['carrera'],
                'notas' => []
            ];
        }
        if ($row['valor'] !== null) {
            $estudiantes[$id]['notas'][] = $row['valor'];
        }
    }
}

function calcularPromedio($notas) {
    return count($notas) ? array_sum($notas) / count($notas) : 0;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Estudiantes</title>
    <link rel="stylesheet" href="estilostp7.css">
</head>
<body>

<h2>📋 Reporte de Estudiantes</h2>

<?php if (!empty($estudiantes)): ?>
<table class="tabla-estudiantes">
    <thead>
        <tr>
            <th>ID</th>
            <th>Nombre</th>
            <th>Edad</th>
            <th>Carrera</th>
            <th>Notas</th>
            <th>Promedio</th>
        </tr>
    </thead>
    <tbody>
    <?php 
    $mejorPromedio = 0;
    $mejorEstudiante = "";
    foreach ($estudiantes as $id => $est): 
        $promedio = calcularPromedio($est['notas']);
        if ($promedio > $mejorPromedio) {
            $mejorPromedio = $promedio;
            $mejorEstudiante = $est['nombre'];
        }
    ?>
        <tr>
            <td><?php echo $id; ?></td>
            <td><?php echo htmlspecialchars($est['nombre']); ?></td>
            <td><?php echo htmlspecialchars($est['edad']); ?></td>
            <td><?php echo htmlspecialchars($est['carrera']); ?></td>
            <td><?php echo implode(", ", $est['notas']); ?></td>
            <td><?php echo number_format($promedio, 2); ?></td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>

<h3>🏆 Mejor Estudiante</h3>
<p class="mejor-estudiante">
    <strong>Nombre:</strong> <?php echo htmlspecialchars($mejorEstudiante); ?><br>
    <strong>Promedio:</strong> <?php echo number_format($mejorPromedio, 2); ?>
</p>

<?php else: ?>
<p>No hay estudiantes registrados.</p>
<?php endif; ?>

<p><a href="introducirestudiante.html">➕ Introducir nuevo estudiante</a></p>

</body>
</html>
<?php $conn->close(); ?>