<?php
session_start();

$servername = "localhost";
$username = "adminphp";
$password = "TuContraseñaSegura";
$dbname = "myDB";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) die("Conexión fallida: " . $conn->connect_error);

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario = $_POST['usuario'];
    $pass = $_POST['password'];

    $stmt = $conn->prepare("SELECT id, nombre, tipo FROM estudiantes WHERE usuario=? AND contraseña=?");
    $stmt->bind_param("ss", $usuario, $pass);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        $_SESSION['logueado'] = true;
        $_SESSION['usuario'] = $row['nombre'];
        $_SESSION['tipo'] = $row['tipo'];
        $_SESSION['id_usuario'] = $row['id'];
        header("Location: reporte_estudiantes.php");
        exit();
    } else {
        $error = "Usuario o contraseña incorrectos.";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <style>
        body { font-family: Arial; background: #f5f5f5; text-align: center; margin-top: 100px; }
        form { background: white; padding: 20px; border-radius: 8px; display: inline-block; }
        input { margin: 10px; padding: 8px; width: 200px; }
        button { padding: 8px 15px; background:#4caf50; color:white; border:none; border-radius:4px; }
    </style>
</head>
<body>
    <h2>🔐 Iniciar Sesión</h2>
    <?php if ($error) echo "<p style='color:red;'>$error</p>"; ?>
    <form method="post">
        <input type="text" name="usuario" placeholder="Usuario" required><br>
        <input type="password" name="password" placeholder="Contraseña" required><br>
        <button type="submit">Ingresar</button>
    </form>
</body>
</html>