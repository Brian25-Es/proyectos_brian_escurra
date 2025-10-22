<?php
header('Content-Type: application/json');

$host = "localhost";
$usuario = "adminphp";
$contrasena = "TuContraseñaSegura";
$bd = "gestion_productos";

$conn = new mysqli($host, $usuario, $contrasena, $bd);
if ($conn->connect_error) {
    echo json_encode(["error"=>"Error de conexión"]);
    exit;
}

// Recibir datos
$data = json_decode(file_get_contents("php://input"), true);
$accion = $data['accion'] ?? '';

if ($accion === 'insertar') {
    $nombre = $conn->real_escape_string($data['nombre']);
    $categoria = $conn->real_escape_string($data['categoria']);
    $precio = floatval($data['precio']);
    $stock = intval($data['stock']);

    $sql = "INSERT INTO productos (nombre, categoria, precio, stock) VALUES ('$nombre','$categoria',$precio,$stock)";
    $res = $conn->query($sql);
    echo json_encode(["ok"=>$res ? true:false, "mensaje"=>$res?"Producto agregado":"Error: ".$conn->error]);
}

if ($accion === 'editar') {
    $id = intval($data['id']);
    $nombre = $conn->real_escape_string($data['nombre']);
    $categoria = $conn->real_escape_string($data['categoria']);
    $precio = floatval($data['precio']);
    $stock = intval($data['stock']);

    $sql = "UPDATE productos SET nombre='$nombre', categoria='$categoria', precio=$precio, stock=$stock WHERE id=$id";
    $res = $conn->query($sql);
    echo json_encode(["ok"=>$res ? true:false, "mensaje"=>$res?"Producto actualizado":"Error: ".$conn->error]);
}

if ($accion === 'eliminar') {
    $id = intval($data['id']);
    $sql = "DELETE FROM productos WHERE id=$id";
    $res = $conn->query($sql);
    echo json_encode(["ok"=>$res ? true:false, "mensaje"=>$res?"Producto eliminado":"Error: ".$conn->error]);
}

$conn->close();
?>