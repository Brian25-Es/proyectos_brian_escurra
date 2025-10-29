<?php
// acciones_productos.php
header('Content-Type: application/json; charset=utf-8');

$host = "localhost";
$usuario = "adminphp";
$contrasena = "TuContraseñaSegura";
$bd = "gestion_productos";

$conn = new mysqli($host, $usuario, $contrasena, $bd);
if ($conn->connect_error) {
    echo json_encode(['ok'=>false, 'mensaje'=>'Error de conexión: '.$conn->connect_error]);
    exit;
}

// leer JSON
$raw = file_get_contents('php://input');
$data = json_decode($raw, true);
if (!is_array($data)) {
    echo json_encode(['ok'=>false, 'mensaje'=>'No se recibió JSON válido']);
    exit;
}

$accion = $data['accion'] ?? '';

if ($accion === 'insertar') {
    $nombre = $conn->real_escape_string($data['nombre'] ?? '');
    $categoria = $conn->real_escape_string($data['categoria'] ?? '');
    $precio = floatval($data['precio'] ?? 0);
    $stock = intval($data['stock'] ?? 0);

    $sql = "INSERT INTO productos (nombre,categoria,precio,stock) VALUES ('$nombre','$categoria',$precio,$stock)";
    $ok = $conn->query($sql);
    if ($ok) {
        echo json_encode(['ok'=>true, 'mensaje'=>'Producto agregado']);
    } else {
        echo json_encode(['ok'=>false, 'mensaje'=>'Error al agregar: '.$conn->error]);
    }
    exit;
}

if ($accion === 'editar') {
    if (!isset($data['id']) || intval($data['id']) <= 0) {
        echo json_encode(['ok'=>false, 'mensaje'=>'ID inválido para editar']);
        exit;
    }
    $id = intval($data['id']);
    $nombre = $conn->real_escape_string($data['nombre'] ?? '');
    $categoria = $conn->real_escape_string($data['categoria'] ?? '');
    $precio = floatval($data['precio'] ?? 0);
    $stock = intval($data['stock'] ?? 0);

    $sql = "UPDATE productos SET nombre='$nombre', categoria='$categoria', precio=$precio, stock=$stock WHERE id=$id";
    $ok = $conn->query($sql);
    if ($ok) {
        echo json_encode(['ok'=>true, 'mensaje'=>'Producto actualizado']);
    } else {
        echo json_encode(['ok'=>false, 'mensaje'=>'Error al actualizar: '.$conn->error]);
    }
    exit;
}

if ($accion === 'eliminar') {
    if (!isset($data['id']) || intval($data['id']) <= 0) {
        echo json_encode(['ok'=>false, 'mensaje'=>'ID inválido para eliminar']);
        exit;
    }
    $id = intval($data['id']);
    $sql = "DELETE FROM productos WHERE id=$id";
    $ok = $conn->query($sql);
    if ($ok) {
        echo json_encode(['ok'=>true, 'mensaje'=>'Producto eliminado']);
    } else {
        echo json_encode(['ok'=>false, 'mensaje'=>'Error al eliminar: '.$conn->error]);
    }
    exit;
}

echo json_encode(['ok'=>false, 'mensaje'=>'Acción no reconocida']);
$conn->close();
?>