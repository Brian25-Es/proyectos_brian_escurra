<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

$servername = "localhost";
$username = "adminphp";
$password = "TuContraseñaSegura";
$dbname = "myDB";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

if (isset($_POST['login'])) {
    $usuario = trim($_POST['usuario']);
    $contraseña = $_POST['contraseña'];

    $stmt = $conn->prepare("SELECT id, nombre, tipo, contraseña FROM estudiantes WHERE usuario=?");
    $stmt->bind_param("s", $usuario);
    $stmt->execute();
    $stmt->bind_result($id, $nombre, $tipo, $hash);
    $stmt->fetch();
    $stmt->close();

    if ($id && password_verify($contraseña, $hash)) {
        $_SESSION['usuario_id'] = $id;
        $_SESSION['nombre'] = $nombre;
        $_SESSION['tipo'] = $tipo;
    } else {
        $errorLogin = "Usuario o contraseña incorrectos.";
    }
}

if (!isset($_SESSION['usuario_id'])) {
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Login</title>
<style>
body{font-family:Arial; padding:50px;}
input{margin:5px; padding:5px;}
</style>
</head>
<body>
<h2>Iniciar Sesión</h2>
<?php if(!empty($errorLogin)) echo "<p style='color:red;'>$errorLogin</p>"; ?>
<form method="post" action="">
    <label>Usuario:</label><br>
    <input type="text" name="usuario" required><br>
    <label>Contraseña:</label><br>
    <input type="password" name="contraseña" required><br><br>
    <input type="submit" name="login" value="Ingresar">
</form>
</body>
</html>
<?php
exit();
}

$isAdmin = $_SESSION['tipo'] === 'admin';
$accion = $_POST['accion'] ?? '';
if ($accion === 'insertar' && $isAdmin) {
    if (empty($_POST['usuario']) || empty($_POST['contraseña'])) die("Debe ingresar usuario y contraseña.");
    $usuario = trim($_POST['usuario']);
    $contraseña = $_POST['contraseña'];
    $hashContraseña = password_hash($contraseña, PASSWORD_DEFAULT);

    $nombre = $_POST['nombre'];
    $edad = (int)$_POST['edad'];
    $carrera = $_POST['carrera'];
    $notas_array = array_map('floatval', explode(',', $_POST['notas']));
    $promedio = count($notas_array) ? array_sum($notas_array)/count($notas_array) : 0;

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

    $tipoEstudiante = 'alumno';
    $stmt = $conn->prepare("INSERT INTO estudiantes (nombre, edad, carrera_id, usuario, contraseña, tipo) VALUES (?,?,?,?,?,?)");
    $stmt->bind_param("siisss", $nombre, $edad, $carrera_id, $usuario, $hashContraseña, $tipoEstudiante);
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
}

if ($accion === 'editar' && $isAdmin) {
    $id = (int)$_POST['id'];
    $nombre = $_POST['nombre'];
    $edad = (int)$_POST['edad'];
    $carrera = $_POST['carrera'];
    $notas_array = array_map('floatval', explode(',', $_POST['notas']));
    $promedio = count($notas_array) ? array_sum($notas_array)/count($notas_array) : 0;

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

    $stmt = $conn->prepare("UPDATE estudiantes SET nombre=?, edad=?, carrera_id=? WHERE id=?");
    $stmt->bind_param("siii", $nombre, $edad, $carrera_id, $id);
    $stmt->execute();
    $stmt->close();

    $conn->query("DELETE FROM notas WHERE estudiante_id=$id");
    $stmt = $conn->prepare("INSERT INTO notas (estudiante_id, valor) VALUES (?, ?)");
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

if (isset($_GET['eliminar']) && $isAdmin) {
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

$sql = "SELECT * FROM EJERCICIO7 ORDER BY id ASC";
$result = $conn->query($sql);
$estudiantes = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $estudiantes[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Reporte de Estudiantes</title>
<link rel="stylesheet" href="estilostp7.css">
<style>
.tabla-estudiantes { border-collapse: collapse; width: 100%; }
.tabla-estudiantes th, .tabla-estudiantes td { border:1px solid #ccc; padding:8px; text-align:center; }
.btn { padding:4px 8px; margin:2px; text-decoration:none; border-radius:4px; color:white; }
.btn-editar { background-color:#4CAF50; }
.btn-eliminar { background-color:#f44336; }
.mejor-estudiante { background-color:#ffeb3b; padding:5px; border-radius:5px; }
form { margin-bottom:20px; }
input[type="text"], input[type="number"], input[type="password"] { width:200px; padding:5px; }
input[type="submit"], button { padding:5px 10px; margin-right:5px; }
</style>
</head>
<body>

<h2>📋 Reporte de Estudiantes</h2>
<p>Bienvenido, <?= htmlspecialchars($_SESSION['nombre']) ?> (<?= $_SESSION['tipo'] ?>) | <a href="logout.php">Cerrar sesión</a></p>

<?php if($isAdmin): ?>
<h3>📝 Introducir / Editar Estudiante</h3>
<form method="post" action="" id="formEstudiante">
    <?php if(isset($_GET['editar'])): 
        $idEditar = (int)$_GET['editar'];
        $stmt = $conn->prepare("SELECT * FROM EJERCICIO7 WHERE id=?");
        $stmt->bind_param("i", $idEditar);
        $stmt->execute();
        $resultEditar = $stmt->get_result();
        $estudianteEditar = $resultEditar->fetch_assoc();
        $stmt->close();
        $accionForm = 'editar';
    else:
        $accionForm = 'insertar';
    endif; ?>
    <input type="hidden" name="accion" value="<?= $accionForm ?>">
    <?php if($accionForm==='editar'): ?>
        <input type="hidden" name="id" value="<?= $estudianteEditar['id'] ?>">
    <?php endif; ?>
    
    <label>Nombre:</label><br>
    <input type="text" name="nombre" required value="<?= $accionForm==='editar' ? $estudianteEditar['nombre'] : '' ?>"><br><br>
    
    <label>Edad:</label><br>
    <input type="number" name="edad" required value="<?= $accionForm==='editar' ? $estudianteEditar['edad'] : '' ?>"><br><br>
    
    <label>Carrera:</label><br>
    <input type="text" name="carrera" required value="<?= $accionForm==='editar' ? $estudianteEditar['carrera'] : '' ?>">    <br><br>

    <label>Usuario:</label><br>
    <input type="text" name="usuario" required value="<?= $accionForm==='editar' ? $estudianteEditar['usuario'] : '' ?>"><br><br>

    <label>Contraseña:</label><br>
    <input type="password" name="contraseña" <?= $accionForm==='insertar' ? 'required' : '' ?> placeholder="<?= $accionForm==='editar' ? 'Dejar vacío si no cambia' : '' ?>"><br><br>

    <input type="submit" value="<?= $accionForm==='editar' ? '💾 Guardar Cambios' : 'Guardar Estudiante' ?>">

    <?php if($accionForm==='editar'): ?>
        <button type="button" onclick="cancelarEdicion()">Cancelar / Nuevo</button>
    <?php endif; ?>
</form>

<script>
function cancelarEdicion() {
    const form = document.getElementById('formEstudiante');
    form.reset();
    form.querySelector('input[name="accion"]').value = 'insertar';
    const idInput = form.querySelector('input[name="id"]');
    if(idInput) idInput.remove();
    document.querySelector('h3').textContent = "📝 Introducir Nuevo Estudiante";
}
</script>
<?php endif;?>

<table class="tabla-estudiantes">
    <thead>
        <tr>
            <th>ID</th>
            <th>Nombre</th>
            <th>Edad</th>
            <th>Carrera</th>
            <th>Notas</th>
            <th>Promedio</th>
            <?php if($isAdmin) echo '<th>Acciones</th>'; ?>
        </tr>
    </thead>
    <tbody>
        <?php
        $mejorPromedio = 0;
        $mejorEstudiante = "";
        foreach($estudiantes as $est):
            if($est['promedios'] > $mejorPromedio) {
                $mejorPromedio = $est['promedios'];
                $mejorEstudiante = $est['nombre'];
            }
        ?>
        <tr>
            <td><?= $est['id'] ?></td>
            <td><?= htmlspecialchars($est['nombre']) ?></td>
            <td><?= $est['edad'] ?></td>
            <td><?= htmlspecialchars($est['carrera']) ?></td>
            <td><?= implode(", ", json_decode($est['notas'])) ?></td>
            <td><?= number_format($est['promedios'],2) ?></td>
            <?php if($isAdmin): ?>
            <td>
                <a class="btn btn-editar" href="?editar=<?= $est['id'] ?>">Editar</a>
                <a class="btn btn-eliminar" href="?eliminar=<?= $est['id'] ?>" onclick="return confirm('¿Seguro que quieres eliminar este estudiante?')">Eliminar</a>
            </td>
            <?php endif; ?>
        </tr>
        <?php endforeach; ?>
        <?php if(empty($estudiantes)): ?>
            <tr><td colspan="<?= $isAdmin ? 7 : 6 ?>">No hay estudiantes registrados.</td></tr>
        <?php endif; ?>
    </tbody>
</table>

<?php if(!empty($mejorEstudiante)): ?>
<h3>🏆 Mejor Estudiante</h3>
<p class="mejor-estudiante"><strong>Nombre:</strong> <?= htmlspecialchars($mejorEstudiante) ?><br>
<strong>Promedio:</strong> <?= number_format($mejorPromedio,2) ?></p>
<?php endif; ?>

</body>
</html>
<?php $conn->close(); ?>