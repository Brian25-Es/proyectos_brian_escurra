<?php
header('Content-Type: application/json');

$host = "localhost";
$usuario = "adminphp";
$contrasena = "TuContraseñaSegura";
$bd = "gestion_productos";

$conn = new mysqli($host, $usuario, $contrasena, $bd);
if ($conn->connect_error) {
    die(json_encode(["error" => "Error de conexión"]));
}

$id = intval($_GET['id'] ?? 0);
$sql = "SELECT * FROM productos WHERE id = $id";
$res = $conn->query($sql);

if ($res && $res->num_rows > 0) {
    echo json_encode($res->fetch_assoc());
} else {
    echo json_encode(["error" => "Producto no encontrado"]);
}

$conn->close();
?>