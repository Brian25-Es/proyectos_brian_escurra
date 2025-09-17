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

$id      = (int) $_POST['id'];
$nombre  = $_POST['nombre'];
$edad    = (int) $_POST['edad'];
$carrera = $_POST['carrera'];
$notas   = array_map('floatval', explode(',', $_POST['notas']));
$promedio = count($notas) ? array_sum($notas) / count($notas) : 0;

$stmt = $conn->prepare("SELECT id FROM carreras WHERE nombre = ?");
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

$stmt = $conn->prepare("UPDATE estudiantes SET nombre=?, edad=?, carrera_id=? WHERE id=?");
$stmt->bind_param("siii", $nombre, $edad, $carrera_id, $id);
$stmt->execute();
$stmt->close();

$stmt = $conn->prepare("DELETE FROM notas WHERE estudiante_id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$stmt->close();

$stmt = $conn->prepare("INSERT INTO notas (estudiante_id, valor) VALUES (?, ?)");
foreach ($notas as $nota) {
    $stmt->bind_param("id", $id, $nota);
    $stmt->execute();
}
$stmt->close();

$notas_json = json_encode($notas);
$stmt = $conn->prepare("UPDATE EJERCICIO7 SET nombre=?, carrera=?, edad=?, notas=?, promedios=? WHERE id=?");
$stmt->bind_param("ssidsi", $nombre, $carrera, $edad, $notas_json, $promedio, $id);
$stmt->execute();
$stmt->close();

$conn->close();

header("Location: reporte_estudiantes.php");
exit;
?>