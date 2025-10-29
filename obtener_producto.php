<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: application/json; charset=utf-8');

$host = "localhost";
$usuario = "adminphp";
$contrasena = "TuContraseñaSegura";
$bd = "gestion_productos";

$conn = new mysqli($host, $usuario, $contrasena, $bd);
if ($conn->connect_error) {
    echo json_encode(['error'=>'Error de conexión: '.$conn->connect_error]);
    exit;
}

$id = intval($_GET['id'] ?? 0);
if ($id <= 0) {
    echo json_encode(['error'=>'ID inválido']);
    exit;
}

$sql = "SELECT * FROM productos WHERE id = $id";
$res = $conn->query($sql);

if ($res && $res->num_rows > 0) {
    $p = $res->fetch_assoc();
    $p['precio'] = floatval($p['precio']);
    $p['stock'] = intval($p['stock']);
    echo json_encode($p);
} else {
    echo json_encode(['error'=>'Producto no encontrado']);
}

$conn->close();
?>