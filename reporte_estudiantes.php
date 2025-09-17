<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$servername = "localhost";
$username = "adminphp";
$password = "TuContraseñaSegura";
$dbname = "myDB";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

$sql = "SELECT id, nombre, edad, carrera, notas, promedios FROM EJERCICIO7";
$result = $conn->query($sql);

function decodeNotas($notas_json) {
    $notas = json_decode($notas_json, true);
    return is_array($notas) ? $notas : [];
}

$mejorPromedio = 0;
$mejorEstudiante = "";

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

<?php if ($result && $result->num_rows > 0): ?>

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

    <?php while ($row = $result->fetch_assoc()): 
        $notasArray = decodeNotas($row['notas']);
        $notasTexto = implode(", ", $notasArray);
        $promedio = number_format($row['promedios'], 2);

        if ($row['promedios'] > $mejorPromedio) {
            $mejorPromedio = $row['promedios'];
            $mejorEstudiante = $row['nombre'];
        }
    ?>
        <tr>
            <td><?php echo htmlspecialchars($row['id']); ?></td>
            <td><?php echo htmlspecialchars($row['nombre']); ?></td>
            <td><?php echo htmlspecialchars($row['edad']); ?></td>
            <td><?php echo htmlspecialchars($row['carrera']); ?></td>
            <td><?php echo htmlspecialchars($notasTexto); ?></td>
            <td><?php echo $promedio; ?></td>
        </tr>
    <?php endwhile; ?>

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

<p><a href="introducirestudiante.html">Introducir nuevo estudiante</a></p>

<?php $conn->close(); ?>

</body>
</html>
