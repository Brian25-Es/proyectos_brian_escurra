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

$nombre = $_POST['nombre'];
$edad = (int) $_POST['edad'];
$carrera = $_POST['carrera'];
$notas_input = $_POST['notas'];

$notas_array = array_map('floatval', explode(',', $notas_input));
if (count($notas_array) == 0 || in_array(false, $notas_array, true)) {
    die("❌ Error: Asegúrate de ingresar notas válidas separadas por coma.");
}

$notas_json = json_encode($notas_array);
$promedio = array_sum($notas_array) / count($notas_array);

$sql = "INSERT INTO EJERCICIO7 (nombre, carrera, edad, notas, promedios)
        VALUES (?, ?, ?, ?, ?)";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ssisd", $nombre, $carrera, $edad, $notas_json, $promedio);

if ($stmt->execute()) {
    echo "<p>✅ Estudiante guardado correctamente.</p>";
    echo "<button onclick=\"window.location.href='reporte_estudiantes.php'\">Ver Reporte</button>";
} else {
    echo "❌ Error al guardar el estudiante: " . $conn->error;
}

$stmt->close();
$conn->close();
?>
