<?php
session_start();
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

if (!isset($_SESSION['usuario'])) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
        $usuario = $_POST['usuario'];
        $clave = $_POST['contraseña'];

        $stmt = $conn->prepare("SELECT id, tipo, contraseña FROM estudiantes WHERE usuario=?");
        $stmt->bind_param("s", $usuario);
        $stmt->execute();
        $stmt->bind_result($id, $tipo, $hash);
        if ($stmt->fetch() && password_verify($clave, $hash)) {
            $_SESSION['usuario'] = $usuario;
            $_SESSION['tipo'] = $tipo;
        } else {
            $errorLogin = "Usuario o contraseña incorrectos";
        }
        $stmt->close();
    }

    if (!isset($_SESSION['usuario'])) {
        echo '<!DOCTYPE html><html lang="es"><head><meta charset="UTF-8"><title>Login</title></head><body>';
        echo '<h2>🔒 Iniciar Sesión</h2>';
        if (isset($errorLogin)) echo "<p style='color:red;'>$errorLogin</p>";
        echo '<form method="post">
                <label>Usuario:</label><br><input type="text" name="usuario" required><br><br>
                <label>Contraseña:</label><br><input type="password" name="contraseña" required><br><br>
                <input type="submit" name="login" value="Ingresar">
              </form></body></html>';
        exit();
    }
}

$isAdmin = ($_SESSION['tipo'] === 'admin');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['accion'])) {
        $accion = $_POST['accion'];

        $nombre = $_POST['nombre'];
        $edad = (int)$_POST['edad'];
        $carrera = $_POST['carrera'];
        $notas_in = $_POST['notas'];
        $notas_array = array_map('floatval', explode(',', $notas_in));
        $promedio = count($notas_array) ? array_sum($notas_array)/count($notas_array) : 0;

        if ($accion === 'insertar' && $isAdmin) {
            $usuario = $_POST['usuario'];
            $contraseña = password_hash($_POST['contraseña'], PASSWORD_DEFAULT);

            $stmtCheck = $conn->prepare("SELECT id FROM estudiantes WHERE usuario=?");
            $stmtCheck->bind_param("s", $usuario);
            $stmtCheck->execute();
            $stmtCheck->store_result();
            if ($stmtCheck->num_rows > 0) {
                die("El usuario '$usuario' ya existe.");
            }
            $stmtCheck->close();

            $stmt = $conn->prepare("SELECT id FROM carreras WHERE nombre=?");
            $stmt->bind_param("s", $carrera);
            $stmt->execute();
            $stmt->bind_result($carrera_id);
            $stmt->fetch();
            $stmt->close();

            if (!$carrera_id) {
                $stmt = $conn->prepare("INSERT INTO carreras (nombre) VALUES (?)");
                $stmt->bind_param("s", $carrera);
                $stmt->execute();
                $carrera_id = $stmt->insert_id;
                $stmt->close();
            }

            $stmt = $conn->prepare("INSERT INTO estudiantes (nombre, edad, carrera_id, usuario, contraseña, tipo) VALUES (?,?,?,?,?,?)");
            $tipoEstudiante = 'alumno';
            $stmt->bind_param("siisss", $nombre, $edad, $carrera_id, $usuario, $contraseña, $tipoEstudiante);
            $stmt->execute();
            $estudiante_id = $stmt->insert_id;
            $stmt->close();

            $stmt = $conn->prepare("INSERT INTO notas (estudiante_id, valor) VALUES (?,?)");
            foreach ($notas_array as $nota) {
                $stmt->bind_param("id", $estudiante_id, $nota);
                $stmt->execute();
            }
            $stmt->close();

            $notas_json = json_encode($notas_array);
            $stmt = $conn->prepare("INSERT INTO EJERCICIO7 (id,nombre,edad,carrera,notas,promedios) VALUES (?,?,?,?,?,?)");
            $stmt->bind_param("iisssd", $estudiante_id, $nombre, $edad, $carrera, $notas_json, $promedio);
            $stmt->execute();
            $stmt->close();

        } elseif ($accion === 'editar' && $isAdmin) {
            $id = (int)$_POST['id'];

            $stmt = $conn->prepare("UPDATE estudiantes SET nombre=?, edad=?, carrera_id=? WHERE id=?");
            $stmt->bind_param("siii", $nombre, $edad, $carrera_id, $id);
            $stmt->execute();
            $stmt->close();

            $conn->query("DELETE FROM notas WHERE estudiante_id=$id");
            $stmt = $conn->prepare("INSERT INTO notas (estudiante_id, valor) VALUES (?,?)");
            foreach ($notas_array as $nota) {
                $stmt->bind_param("id", $id, $nota);
                $stmt->execute();
            }
            $stmt->close();

            $notas_json = json_encode($notas_array);
            $stmt = $conn->prepare("UPDATE EJERCICIO7 SET nombre=?, edad=?, carrera=?, notas=?, promedios=? WHERE id=?");
            $stmt->bind_param("sissdi", $nombre, $edad, $carrera, $notas_json, $promedio, $id);
            $stmt->execute();
            $stmt->close();
        }
    }
}

if ($isAdmin && isset($_GET['eliminar'])) {
    $id = (int)$_GET['eliminar'];
    $stmt = $conn->prepare("DELETE FROM notas WHERE estudiante_id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();

    $stmt = $conn->prepare("DELETE FROM estudiantes WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();

    $stmt = $conn->prepare("DELETE FROM EJERCICIO7 WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
}

$sql = "SELECT e.id, e.nombre, e.edad, c.nombre AS carrera, ej.notas, ej.promedios, e.tipo
        FROM estudiantes e
        LEFT JOIN carreras c ON e.carrera_id=c.id
        LEFT JOIN EJERCICIO7 ej ON ej.id=e.id
        ORDER BY e.id ASC";
$result = $conn->query($sql);

$estudiantes = [];
$mejorPromedio = 0;
$mejorEstudiante = "";
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $estudiantes[] = $row;
        if ($row['promedios'] > $mejorPromedio) {
            $mejorPromedio = $row['promedios'];
            $mejorEstudiante = $row['nombre'];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Estudiantes</title>
    <link rel="stylesheet" href="estilostp7.css">
</head>
<body>
<h2>📋 Reporte de Estudiantes</h2>
<p>Usuario: <?= $_SESSION['usuario'] ?> (<?= $_SESSION['tipo'] ?>) | <a href="logout.php">Cerrar sesión</a></p>

<?php if ($isAdmin): ?>
<p><a href="introducirestudiante.php">➕ Agregar Estudiante</a></p>
<?php endif; ?>

<table class="tabla-estudiantes" border="1" cellpadding="5">
<tr>
<th>ID</th><th>Nombre</th><th>Edad</th><th>Carrera</th><th>Notas</th><th>Promedio</th>
<?php if ($isAdmin): ?><th>Acciones</th><?php endif; ?>
</tr>
<?php foreach($estudiantes as $est): ?>
<tr>
<td><?= $est['id'] ?></td>
<td><?= $est['nombre'] ?></td>
<td><?= $est['edad'] ?></td>
<td><?= $est['carrera'] ?></td>
<td><?= implode(", ", json_decode($est['notas'])) ?></td>
<td><?= number_format($est['promedios'],2) ?></td>
<?php if ($isAdmin): ?>
<td>
    <a href="introducirestudiante.php?editar=<?= $est['id'] ?>">Editar</a> |
    <a href="?eliminar=<?= $est['id'] ?>" onclick="return confirm('¿Seguro que quieres eliminar este estudiante?')">Eliminar</a>
</td>
<?php endif; ?>
</tr>
<?php endforeach; ?>