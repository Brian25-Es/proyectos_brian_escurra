<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Estudiantes</title>
    <link rel="stylesheet" href="estilostp7.css">
</head>
<body>

<p>
    <input type="submit" value="Introducir Alumno" onclick="location.href='formulario7.html'">
</p>

<?php
$servername = "localhost";
$username = "adminphp";
$password = "TuContraseñaSegura";
$dbname = "myDB";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

$estudiantes = [];

$sql = "SELECT id, nombre, edad, carrera, notas FROM EJERCICIO7";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $id_formateado = "est" . str_pad($row["id"], 3, "0", STR_PAD_LEFT);
        
        $notas = json_decode($row["notas"], true);
        if (!is_array($notas)) {
            $notas = [];
        }

        $estudiantes[$id_formateado] = [
            "nombre" => $row["nombre"],
            "edad" => $row["edad"],
            "carrera" => $row["carrera"],
            "notas" => $notas
        ];
    }
} else {
    echo "<p>No hay estudiantes registrados en la base de datos.</p>";
}

function calcularPromedio($notas) {
    return count($notas) ? array_sum($notas) / count($notas) : 0;
}

echo "<h2>📋 Reporte de Estudiantes</h2>";

echo "<table class='tabla-estudiantes'>";
echo "<thead>
        <tr>
            <th>ID</th>
            <th>Nombre</th>
            <th>Edad</th>
            <th>Carrera</th>
            <th>Notas</th>
            <th>Promedio</th>
        </tr>
      </thead>
      <tbody>";

$mejorPromedio = 0;
$mejorEstudiante = "";

foreach ($estudiantes as $id => $estudiante) {
    $promedio = calcularPromedio($estudiante["notas"]);
    
    echo "<tr>";
    echo "<td>$id</td>";
    echo "<td>{$estudiante['nombre']}</td>";
    echo "<td>{$estudiante['edad']}</td>";
    echo "<td>{$estudiante['carrera']}</td>";
    echo "<td>" . implode(", ", $estudiante['notas']) . "</td>";
    echo "<td>" . number_format($promedio, 2) . "</td>";
    echo "</tr>";
    
    if ($promedio > $mejorPromedio) {
        $mejorPromedio = $promedio;
        $mejorEstudiante = $estudiante["nombre"];
    }
}

echo "</tbody></table>";

if (!empty($mejorEstudiante)) {
    echo "<h3>🏆 Mejor Estudiante</h3>";
    echo "<p class='mejor-estudiante'><strong>Nombre:</strong> $mejorEstudiante<br>";
    echo "<strong>Promedio:</strong> " . number_format($mejorPromedio, 2) . "</p>";
}

$conn->close();
?>

</body>
</html>
