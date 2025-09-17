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

$nombre   = $_POST['nombre'];
$edad     = (int) $_POST['edad'];
$carrera  = $_POST['carrera'];
$notas_in = $_POST['notas'];

$notas_array = array_map('floatval', explode(',', $notas_in));
$promedio = array_sum($notas_array) / count($notas_array);

$stmt = $conn->prepare("SELECT id FROM carreras WHERE nombre=?");
$stmt->bind_param("s", $carrera);
$stmt->execute();
$stmt->bind_result($carrera_id);
$stmt->fetch();
$stmt->close();

if (!$carrera_id) {
    $stmt = $conn->prepare("INSERT INTO carreras (nombre) VALUES (?)");
    $stmt->bind_param("s", $carrera);
    $stmt->execute();
    $carrera_id = $stmt->insert_id;
    $stmt->close();
}

$stmt = $conn->prepare("INSERT INTO estudiantes (nombre, edad, carrera_id) VALUES (?, ?, ?)");
$stmt->bind_param("sii", $nombre, $edad, $carrera_id);
$stmt->execute();
$estudiante_id = $stmt->insert_id;
$stmt->close();

$stmt = $conn->prepare("INSERT INTO notas (estudiante_id, valor) VALUES (?, ?)");
foreach ($notas_array as $nota) {
    $stmt->bind_param("id", $estudiante_id, $nota);
    $stmt->execute();
}
$stmt->close();

$notas_json = json_encode($notas_array);
$stmt = $conn->prepare("INSERT INTO EJERCICIO7 (id, nombre, carrera, edad, notas, promedios) VALUES (?, ?, ?, ?, ?, ?)");
$stmt->bind_param("issisd", $estudiante_id, $nombre, $carrera, $edad, $notas_json, $promedio);
$stmt->execute();
$stmt->close();

$conn->close();

header("Location: reporte_estudiantes.php");
exit();
?>
