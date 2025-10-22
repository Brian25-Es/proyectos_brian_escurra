<?php
$host = "localhost";
$usuario = "adminphp";
$contrasena = "TuContraseñaSegura";
$bd = "gestion_productos";

$conn = new mysqli($host, $usuario, $contrasena, $bd);
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

$accion = $_POST['accion'] ?? '';

if ($accion === 'insertar') {
    $nombre = $conn->real_escape_string($_POST['nombre']);
    $categoria = $conn->real_escape_string($_POST['categoria']);
    $precio = floatval($_POST['precio']);
    $stock = intval($_POST['stock']);

    $sql = "INSERT INTO productos (nombre, categoria, precio, stock) 
            VALUES ('$nombre', '$categoria', $precio, $stock)";
    echo $conn->query($sql) ? "✅ Producto agregado" : "❌ Error: " . $conn->error;
}

if ($accion === 'editar') {
    $id = intval($_POST['id']);
    $nombre = $conn->real_escape_string($_POST['nombre']);
    $categoria = $conn->real_escape_string($_POST['categoria']);
    $precio = floatval($_POST['precio']);
    $stock = intval($_POST['stock']);

    $sql = "UPDATE productos 
            SET nombre='$nombre', categoria='$categoria', precio=$precio, stock=$stock 
            WHERE id=$id";
    echo $conn->query($sql) ? "✅ Producto actualizado" : "❌ Error: " . $conn->error;
}

if ($accion === 'eliminar') {
    $id = intval($_POST['id']);
    $sql = "DELETE FROM productos WHERE id=$id";
    echo $conn->query($sql) ? "🗑️ Producto eliminado" : "❌ Error: " . $conn->error;
}

$conn->close();
?>