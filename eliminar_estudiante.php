<?php
$servername = "localhost";
$username   = "adminphp";
$password   = "TuContraseñaSegura";
$dbname     = "myDB";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("❌ Conexión fallida: " . $conn->connect_error);
}

$id = intval($_GET['id']);

$sql = "DELETE FROM EJERCICIO7 WHERE id=$id";
$conn->query($sql);

$conn->query("DELETE FROM notas WHERE estudiante_id=$id");
$conn->query("DELETE FROM estudiantes WHERE id=$id");

$conn->close();

header("Location: reporte_estudiantes.php");
exit();
?>
