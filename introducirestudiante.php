<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$servername = "localhost";
$username   = "adminphp";
$password   = "TuContraseñaSegura";
$dbname     = "myDB";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Introducir Estudiante</title>
    <link rel="stylesheet" href="estilostp7.css">
</head>
<body>

<h2>📝 Introducir Nuevo Estudiante</h2>

<form action="reporte_estudiantes.php" method="post">
    <input type="hidden" name="accion" value="insertar">

    <label>Nombre:</label><br>
    <input type="text" name="nombre" required><br><br>

    <label>Edad:</label><br>
    <input type="number" name="edad" required><br><br>

    <label>Carrera:</label><br>
    <input type="text" name="carrera" required><br><br>

    <label>Notas (separadas por coma):</label><br>
    <input type="text" name="notas" placeholder="Ej: 8.5,7.0,9.2" required><br><br>

    <label>Usuario:</label><br>
    <input type="text" name="usuario" required><br><br>

    <label>Contraseña:</label><br>
    <input type="password" name="contraseña" required><br><br>

    <input type="submit" value="Guardar Estudiante">
</form>

<br>
<button onclick="window.location.href='reporte_estudiantes.php'">Ver Reporte</button>

</body>
</html>

<?php
$conn->close();
?>