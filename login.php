<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = $_POST['usuario'];
    $pass = $_POST['password'];

    if ($user === "admin" && $pass === "1234") {
        $_SESSION['logueado'] = true;
        header("Location: reporte_estudiantes.php");
        exit();
    } else {
        $error = "❌ Usuario o contraseña incorrectos.";
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
        form { background: white; padding: 20px; border-radius: 8px; display: inline-block; box-shadow: 0px 2px 6px rgba(0,0,0,0.2); }
        input { margin: 10px; padding: 8px; width: 200px; }
        button { padding: 8px 15px; cursor: pointer; }
    </style>
</head>
<body>
    <h2>🔐 Iniciar Sesión</h2>
    <?php if (!empty($error)) echo "<p style='color:red;'>$error</p>"; ?>
    <form method="post">
        <input type="text" name="usuario" placeholder="Usuario" required><br>
        <input type="password" name="password" placeholder="Contraseña" required><br>
        <button type="submit">Ingresar</button>
    </form>
</body>
</html>
