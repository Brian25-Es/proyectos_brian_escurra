<?php
$servername = "localhost";
$username   = "adminphp";
$password   = "TuContraseñaSegura";
$dbname     = "myDB";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("❌ Conexión fallida: " . $conn->connect_error);
}

$id = intval($_GET['id']);
$result = $conn->query("SELECT * FROM EJERCICIO7 WHERE id=$id");
$estudiante = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Estudiante</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <h2>✏️ Editar Estudiante</h2>

    <form method="post" action="update_estudiante.php">
        <input type="hidden" name="id" value="<?= $estudiante['id'] ?>">

        <label>Nombre:</label>
        <input type="text" name="nombre" value="<?= $estudiante['nombre'] ?>" required><br><br>

        <label>Carrera:</label>
        <input type="text" name="carrera" value="<?= $estudiante['carrera'] ?>" required><br><br>

        <label>Edad:</label>
        <input type="number" name="edad" value="<?= $estudiante['edad'] ?>" required><br><br>

        <label>Notas (separadas por coma):</label>
        <input type="text" name="notas" value="<?= $estudiante['notas'] ?>" required><br><br>

        <button type="submit">💾 Guardar Cambios</button>
    </form>
</body>
</html>