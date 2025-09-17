<?php
$servername = "localhost";
$username   = "adminphp";
$password   = "TuContraseñaSegura";
$dbname     = "myDB";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("❌ Conexión fallida: " . $conn->connect_error);
}

$id = (int) $_GET['id'];

$sql = "SELECT e.id, e.nombre, e.edad, c.nombre AS carrera
        FROM estudiantes e
        JOIN carreras c ON e.carrera_id = c.id
        WHERE e.id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$estudiante = $result->fetch_assoc();
$stmt->close();

$notas = [];
$res = $conn->query("SELECT valor FROM notas WHERE estudiante_id = $id");
while ($row = $res->fetch_assoc()) {
    $notas[] = $row['valor'];
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Estudiante</title>
</head>
<body>
<h2>✏️ Editar Estudiante</h2>

<form action="actualizar_estudiante.php" method="post">
    <input type="hidden" name="id" value="<?php echo $estudiante['id']; ?>">

    <label>Nombre:</label><br>
    <input type="text" name="nombre" value="<?php echo htmlspecialchars($estudiante['nombre']); ?>" required><br><br>

    <label>Edad:</label><br>
    <input type="number" name="edad" value="<?php echo $estudiante['edad']; ?>" required><br><br>

    <label>Carrera:</label><br>
    <input type="text" name="carrera" value="<?php echo htmlspecialchars($estudiante['carrera']); ?>" required><br><br>

    <label>Notas (separadas por coma):</label><br>
    <input type="text" name="notas" value="<?php echo implode(", ", $notas); ?>" required><br><br>

    <input type="submit" value="Actualizar">
</form>

<br>
<a href="reporte_estudiantes.php">⬅️ Volver al Reporte</a>
</body>
</html>