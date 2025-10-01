<?php
session_start();
if (!isset($_SESSION['logueado']) || $_SESSION['logueado'] !== true) {
    header("Location: login.php");
    exit();
}

$admin = $_SESSION['tipo'] === 'admin';

$servername = "localhost";
$username = "adminphp";
$password = "TuContraseñaSegura";
$dbname = "myDB";
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) die("Conexión fallida: " . $conn->connect_error);

if ($admin && isset($_POST['accion'])) {
    $accion = $_POST['accion'];
    $nombre = $_POST['nombre'];
    $edad = (int)$_POST['edad'];
    $carrera = $_POST['carrera'];
    $notas_in = $_POST['notas'];
    $notas_array = array_map('floatval', explode(',', $notas_in));
    $promedio = count($notas_array) ? array_sum($notas_array)/count($notas_array) : 0;

    if ($accion === 'insertar') {
        $stmt = $conn->prepare("INSERT INTO estudiantes (nombre, edad, carrera, usuario, contraseña, tipo) VALUES (?, ?, ?, ?, ?, 'alumno')");
        $stmt->bind_param("siss", $nombre, $edad, $carrera, $nombre, $nombre); // usuario y pass iguales al nombre por default
        $stmt->execute();
        $estudiante_id = $stmt->insert_id;
        $stmt->close();

        $stmt = $conn->prepare("INSERT INTO EJERCICIO7 (id, nombre, edad, carrera, notas, promedios) VALUES (?, ?, ?, ?, ?, ?)");
        $notas_json = json_encode($notas_array);
        $stmt->bind_param("iisssd", $estudiante_id, $nombre, $edad, $carrera, $notas_json, $promedio);
        $stmt->execute();
        $stmt->close();

    } elseif ($accion === 'editar') {
        $id = (int)$_POST['id'];
        $stmt = $conn->prepare("UPDATE estudiantes SET nombre=?, edad=?, carrera=? WHERE id=?");
        $stmt->bind_param("sisi", $nombre, $edad, $carrera, $id);
        $stmt->execute();
        $stmt->close();

        $notas_json = json_encode($notas_array);
        $stmt = $conn->prepare("UPDATE EJERCICIO7 SET nombre=?, edad=?, carrera=?, notas=?, promedios=? WHERE id=?");
        $stmt->bind_param("sissdi", $nombre, $edad, $carrera, $notas_json, $promedio, $id);
        $stmt->execute();
        $stmt->close();
    }
    header("Location: reporte_estudiantes.php");
    exit();
}

if ($admin && isset($_GET['eliminar'])) {
    $id = (int)$_GET['eliminar'];
    $conn->query("DELETE FROM estudiantes WHERE id=$id");
    $conn->query("DELETE FROM EJERCICIO7 WHERE id=$id");
    header("Location: reporte_estudiantes.php");
    exit();
}

$sql = "SELECT * FROM EJERCICIO7 ORDER BY id ASC";
$result = $conn->query($sql);
$estudiantes = [];
$mejorPromedio = 0;
$mejorEstudiante = "";
while ($row = $result->fetch_assoc()) {
    $estudiantes[] = $row;
    if ($row['promedios'] > $mejorPromedio) {
        $mejorPromedio = $row['promedios'];
        $mejorEstudiante = $row['nombre'];
    }
}

$accion_form = 'insertar';
$estudianteEditar = null;
if ($admin && isset($_GET['editar'])) {
    $idEditar = (int)$_GET['editar'];
    $stmt = $conn->prepare("SELECT * FROM EJERCICIO7 WHERE id=?");
    $stmt->bind_param("i", $idEditar);
    $stmt->execute();
    $res = $stmt->get_result();
    $estudianteEditar = $res->fetch_assoc();
    $stmt->close();
    $accion_form = 'editar';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Reporte de Estudiantes</title>
<style>
body { font-family: Arial; margin:20px; }
.tabla-estudiantes { border-collapse: collapse; width: 100%; margin-top:20px; }
.tabla-estudiantes th, .tabla-estudiantes td { border:1px solid #ccc; padding:8px; text-align:center; }
.btn { padding: 4px 8px; margin:2px; text-decoration:none; border-radius:4px; color:white; }
.btn-editar { background-color:#4CAF50; }
.btn-eliminar { background-color:#f44336; }
.mejor-estudiante { background:#ffeb3b; padding:5px; border-radius:5px; }
input, select { padding:5px; margin:5px; }
input[type="submit"], button { padding:5px 10px; margin-right:5px; }
</style>
</head>
<body>
<h2>📋 Reporte de Estudiantes</h2>
<p>Bienvenido(a): <?= $_SESSION['usuario'] ?> (<?= $_SESSION['tipo'] ?>) | <a href="logout.php">Cerrar sesión</a></p>

<?php if($admin): ?>
<h3><?= $accion_form==='editar' ? "✏️ Editar Estudiante" : "📝 Introducir Nuevo Estudiante" ?></h3>
<form method="post" action="reporte_estudiantes.php" id="formEstudiante">
    <?php if($accion_form==='editar'): ?>
        <input type="hidden" name="id" value="<?= $estudianteEditar['id'] ?>">
    <?php endif; ?>
    <input type="hidden" name="accion" value="<?= $accion_form ?>">

    <label>Nombre:</label><br>
    <input type="text" name="nombre" required value="<?= $accion_form==='editar' ? $estudianteEditar['nombre'] : '' ?>"><br>
    <label>Edad:</label><br>
    <input type="number" name="edad" required value="<?= $accion_form==='editar' ? $estudianteEditar['edad'] : '' ?>"><br>
    <label>Carrera:</label><br>
    <input type="text" name="carrera" required value="<?= $accion_form==='editar' ? $estudianteEditar['carrera'] : '' ?>"><br>
    <label>Notas (separadas por coma):</label><br>
    <input type="text" name="notas" required value="<?= $accion_form==='editar' ? implode(",", json_decode($estudianteEditar['notas'])) : '' ?>"><br><br>
    <input type="submit" value="<?= $accion_form==='editar' ? '💾 Guardar Cambios' : 'Guardar Estudiante' ?>">
    <?php if($accion_form==='editar'): ?>
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
<?php endif; ?>

<table class="tabla-estudiantes">
    <thead>
        <tr>
            <th>ID</th><th>Nombre</th><th>Edad</th><th>Carrera</th><th>Notas</th><th>Promedio</th>
            <?php if($admin) echo "<th>Acciones</th>"; ?>
        </tr>
    </thead>
    <tbody>
        <?php if($estudiantes): foreach($estudiantes as $est): ?>
            <tr>
                <td><?= $est['id'] ?></td>
                <td><?= $est['nombre'] ?></td>
                <td><?= $est['edad'] ?></td>
                <td><?= $est['carrera'] ?></td>
                <td><?= implode(", ", json_decode($est['notas'])) ?></td>
                <td><?= number_format($est['promedios'],2) ?></td>
                <?php if($admin): ?>
                <td>
                    <a class="btn btn-editar" href="?editar=<?= $est['id'] ?>">Editar</a>
                    <a class="btn btn-eliminar" href="?eliminar=<?= $est['id'] ?>" onclick="return confirm('¿Seguro que quieres eliminar este estudiante?')">Eliminar</a>
                </td>
                <?php endif; ?>
            </tr>
        <?php endforeach; else: ?>
            <tr><td colspan="<?= $admin ? 7 : 6 ?>">No hay estudiantes registrados.</td></tr>
        <?php endif; ?>
    </tbody>
</table>

<?php if($mejorEstudiante): ?>
<h3>🏆 Mejor Estudiante</h3>
<p class="mejor-estudiante"><strong>Nombre:</strong> <?= $mejorEstudiante ?><br>
<strong>Promedio:</strong> <?= number_format($mejorPromedio,2) ?></p>
<?php endif; ?>

</body>
</html>
<?php $conn->close(); ?>