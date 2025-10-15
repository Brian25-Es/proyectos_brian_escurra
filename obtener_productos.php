<?php
header('Content-Type: application/json');

// ⚙️ Conexión a la base de datos
$host = "localhost";
$usuario = "root";        // Cambia si tu usuario es distinto
$contrasena = "";         // Agrega contraseña si tu MySQL la requiere
$bd = "gestion_productos"; // Nombre de tu base de datos

// Conectar a la base de datos
$conn = new mysqli($host, $usuario, $contrasena, $bd);

// Verificar conexión
if ($conn->connect_error) {
    die(json_encode(["error" => "Error de conexión: " . $conn->connect_error]));
}

// Consulta SQL
$sql = "SELECT * FROM productos";
$resultado = $conn->query($sql);

$productos = [];

if ($resultado->num_rows > 0) {
    while ($fila = $resultado->fetch_assoc()) {
        $fila['precio'] = floatval($fila['precio']);
        $fila['stock'] = intval($fila['stock']);
        $productos[] = $fila;
    }
}

$conn->close();

// Devolver productos como JSON
echo json_encode($productos);
