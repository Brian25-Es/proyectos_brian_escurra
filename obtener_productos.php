<?php
header('Content-Type: application/json');
$host="localhost"; $usuario="adminphp"; $contrasena="TuContraseñaSegura"; $bd="gestion_productos";
$conn = new mysqli($host,$usuario,$contrasena,$bd);
if($conn->connect_error){echo json_encode([]); exit;}

$id=intval($_GET['id'] ?? 0);
$sql="SELECT * FROM productos WHERE id=$id";
$res=$conn->query($sql);
echo json_encode($res && $res->num_rows>0 ? $res->fetch_assoc() : []);
$conn->close();
?>