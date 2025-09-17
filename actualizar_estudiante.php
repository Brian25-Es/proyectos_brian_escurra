<?php
$servername = "localhost";
$username   = "adminphp";
$password   = "TuContraseñaSegura";
$dbname     = "myDB";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("❌ Conexión fallida: " . $conn->connect_error);
}

$id      = intval($_POST['id']);
$nombre  = $_POST['nombre'];
$carrera = $_POST['carrera'];
$edad    = intval($_POST['edad']);
$notas   = $_POST['notas'];

$notas_array = array_map('floatval', explode(',', $notas));
$promedio = array_sum($notas_array) / count($notas_array);

$stmt = $conn->prepare("UPDATE EJERCICIO7 SET nombre=?, carrera=?, edad=?, notas=?, promedios=? WHERE id=?");
$stmt->bind_param("ssidsi", $nombre, $carrera, $edad, $notas, $promedio, $id);
$stmt->execute();
$stmt->close();

$stmt = $conn->prepare("UPDATE estudiantes SET nombre=?, edad=? WHERE id=?");
$stmt->bind_param("sii", $nombre, $edad, $id);
$stmt->execute();
$stmt->close();

$conn->query("DELETE FROM notas WHERE estudiante_id=$id");
$stmt = $conn->prepare("INSERT INTO notas (estudiante_id, valor) VALUES (?, ?)");
foreach ($notas_array as $nota) {
    $stmt->bind_param("id", $id, $nota);
    $stmt->execute();
}
$stmt->close();

$conn->close();

header("Location: reporte_estudiantes.php");
exit();
?>
