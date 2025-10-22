<?php
header('Content-Type: application/json');

$host = "localhost";
$usuario = "adminphp";
$contrasena = "TuContraseñaSegura";
$bd = "gestion_productos";

$conn = new mysqli($host, $usuario, $contrasena, $bd);
if ($conn->connect_error) {
    echo json_encode(["ok"=>false,"mensaje"=>"Error de conexión"]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);
$accion = $data['accion'] ?? '';

if (!$accion) {
    echo json_encode(["ok"=>false,"mensaje"=>"No se envió acción"]);
    exit;
}

if ($accion === 'insertar') {
    $nombre = $conn->real_escape_string($data['nombre'] ?? '');
    $categoria = $conn->real_escape_string($data['categoria'] ?? '');
    $precio = floatval($data['precio'] ?? 0);
    $stock = intval($data['stock'] ?? 0);

    $sql = "INSERT INTO productos (nombre,categoria,precio,stock) VALUES ('$nombre','$categoria',$precio,$stock)";
    $res = $conn->query($sql);
    echo json_encode(["ok"=>$res,"mensaje"=>$res?"Producto agregado":"Error: ".$conn->error]);
}

if ($accion === 'editar') {
    if(!isset($data['id'])){
        echo json_encode(["ok"=>false,"mensaje"=>"ID no enviado"]);
        exit;
    }
    $id = intval($data['id']);
    $nombre = $conn->real_escape_string($data['nombre'] ?? '');
    $categoria = $conn->real_escape_string($data['categoria'] ?? '');
    $precio = floatval($data['precio'] ?? 0);
    $stock = intval($data['stock'] ?? 0);

    $sql = "UPDATE productos SET nombre='$nombre', categoria='$categoria', precio=$precio, stock=$stock WHERE id=$id";
    $res = $conn->query($sql);
    echo json_encode(["ok"=>$res,"mensaje"=>$res?"Producto actualizado":"Error: ".$conn->error]);
}

if ($accion === 'eliminar') {
    if(!isset($data['id'])){
        echo json_encode(["ok"=>false,"mensaje"=>"ID no enviado"]);
        exit;
    }
    $id = intval($data['id']);
    $sql = "DELETE FROM productos WHERE id=$id";
    $res = $conn->query($sql);
    echo json_encode(["ok"=>$res,"mensaje"=>$res?"Producto eliminado":"Error: ".$conn->error]);
}

$conn->close();
?>