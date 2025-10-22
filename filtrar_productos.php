<?php
header('Content-Type: application/json');

// Conexión
$host = "localhost";
$usuario = "adminphp";
$contrasena = "TuContraseñaSegura";
$bd = "gestion_productos";

$conn = new mysqli($host, $usuario, $contrasena, $bd);
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(["error" => "Error de conexión"]);
    exit;
}

// Captura de filtros vía POST o GET
$buscar = $_GET['buscar'] ?? '';
$categoria = $_GET['categoria'] ?? 'todas';
$stockMin = intval($_GET['stockMin'] ?? 0);
$precioMin = $_GET['precioMin'] ?? '';
$precioMax = $_GET['precioMax'] ?? '';
$ordenar = $_GET['ordenar'] ?? 'nombre_asc';

// Armado del WHERE dinámico
$where = "WHERE 1=1";

if ($buscar !== '') {
    $buscar = $conn->real_escape_string($buscar);
    $where .= " AND nombre LIKE '%$buscar%'";
}

if ($categoria !== 'todas') {
    $categoria = $conn->real_escape_string($categoria);
    $where .= " AND categoria = '$categoria'";
}

$where .= " AND stock >= $stockMin";

if ($precioMin !== '') {
    $precioMin = floatval($precioMin);
    $where .= " AND precio >= $precioMin";
}
if ($precioMax !== '') {
    $precioMax = floatval($precioMax);
    $where .= " AND precio <= $precioMax";
}

// Ordenamiento
$orderBy = "";
switch ($ordenar) {
    case "nombre_asc": $orderBy = "ORDER BY nombre ASC"; break;
    case "nombre_desc": $orderBy = "ORDER BY nombre DESC"; break;
    case "precio_asc": $orderBy = "ORDER BY precio ASC"; break;
    case "precio_desc": $orderBy = "ORDER BY precio DESC"; break;
    case "stock_asc": $orderBy = "ORDER BY stock ASC"; break;
    case "stock_desc": $orderBy = "ORDER BY stock DESC"; break;
    default: $orderBy = "ORDER BY nombre ASC";
}

$sql = "SELECT * FROM productos $where $orderBy";
$result = $conn->query($sql);

$productos = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $row['precio'] = floatval($row['precio']);
        $row['stock'] = intval($row['stock']);
        $productos[] = $row;
    }
}

$conn->close();
echo json_encode($productos);