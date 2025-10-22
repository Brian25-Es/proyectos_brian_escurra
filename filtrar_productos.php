<?php
header('Content-Type: application/json');

// Conexión a la base de datos
$conn = new mysqli("localhost", "adminphp", "TuContraseñaSegura", "gestion_productos");
if ($conn->connect_error) {
  die(json_encode(["error" => "Error de conexión"]));
}

// Capturar los filtros enviados
$buscar = $_GET['buscar'] ?? '';
$categoria = $_GET['categoria'] ?? 'todas';
$stockMinimo = intval($_GET['stockMinimo'] ?? 0);
$ordenar = $_GET['ordenar'] ?? 'nombre_asc';

// Crear la consulta base
$sql = "SELECT * FROM productos WHERE stock >= $stockMinimo";

// Filtros opcionales
if ($buscar !== '') {
  $buscar = $conn->real_escape_string($buscar);
  $sql .= " AND nombre LIKE '%$buscar%'";
}

if ($categoria !== 'todas') {
  $categoria = $conn->real_escape_string($categoria);
  $sql .= " AND categoria = '$categoria'";
}

// Ordenar resultados
switch ($ordenar) {
  case "nombre_desc": $sql .= " ORDER BY nombre DESC"; break;
  case "precio_asc": $sql .= " ORDER BY precio ASC"; break;
  case "precio_desc": $sql .= " ORDER BY precio DESC"; break;
  default: $sql .= " ORDER BY nombre ASC";
}

// Ejecutar consulta
$result = $conn->query($sql);
$productos = [];

if ($result) {
  while ($fila = $result->fetch_assoc()) {
    $fila['precio'] = floatval($fila['precio']);
    $fila['stock'] = intval($fila['stock']);
    $productos[] = $fila;
  }
}

// Cerrar conexión y devolver JSON
$conn->close();
echo json_encode($productos);
?>