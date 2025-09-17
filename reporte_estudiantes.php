<?php
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



if (isset($_POST['accion']) && $_POST['accion'] === 'insertar') {
    $nombre = $_POST['nombre'];
    $edad = (int)$_POST['edad'];
    $carrera = $_POST['carrera'];
    $notas_in = $_POST['notas'];

    $notas_array = array_map('floatval', explode(',', $notas_in));
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

    $stmt = $conn->prepare("INSERT INTO estudiantes (nombre, edad, carrera_id) VALUES (?, ?, ?)");
    $stmt->bind_param("sii", $nombre, $edad, $carrera_id);
    $stmt->execute();
    $estudiante_id = $stmt->insert_id;
    $stmt->close();

    $stmt = $conn->prepare("INSERT INTO notas (estudiante_id, valor) VALUES (?, ?)");
    foreach ($notas_array as $nota) {
        $stmt->bind_param("id", $estudiante_id, $nota);
        $stmt->execute();
    }
    $stmt->close();

    $notas_json = json_encode($notas_array);
    $stmt = $conn->prepare("INSERT INTO EJERCICIO7 (id, nombre, edad, carrera, notas, promedios) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("iisssd", $estudiante_id, $nombre, $edad, $carrera, $notas_json, $promedio);
    $stmt->execute();
    $stmt->close();

    header("Location: reporte_estudiantes.php");
    exit();
}

if (isset($_POST['accion']) && $_POST['accion'] === 'editar') {
    $id = (int)$_POST['id'];
    $nombre = $_POST['nombre'];
    $edad = (int)$_POST['edad'];
    $carrera = $_POST['carrera'];
    $notas_in = $_POST['notas'];

    $notas_array = array_map('floatval', explode(',', $notas_in));
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

    header("Location: reporte_estudiantes.php");
    exit();
}

if (isset($_GET['eliminar'])) {
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

    header("Location: reporte_estudiantes.php");
    exit();
}

$sql = "SELECT * FROM EJERCICIO7 ORDER BY id ASC";
$result = $conn->query($sql);

$mejorPromedio = 0;
$mejorEstudiante = "";
$estudiantes = [];
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
    <style>
        .tabla-estudiantes { border-collapse: collapse; width: 100%; }
        .tabla-estudiantes th, .tabla-estudiantes td { border: 1px solid #ccc; padding: 8px; text-align: center; }
        .btn { padding: 4px 8px; margin: 2px; text-decoration: none; border-radius: 4px; color: white; }
        .btn-editar { background-color: #4CAF50; }
        .btn-eliminar { background-color: #f44336; }
        .mejor-estudiante { background-color: #ffeb3b; padding: 5px; border-radius: 5px; }
        form { margin-bottom: 20px; }
        input[type="text"], input[type="number"] { width: 200px; padding: 5px; }
        input[type="submit"] { padding: 5px 10px; }
    </style>
</head>
<body>

<h2>📋 Reporte de Estudiantes</h2>

<?php
if (isset($_GET['editar'])) {
    $idEditar = (int)$_GET['editar'];
    $stmt = $conn->prepare("SELECT * FROM EJERCICIO7 WHERE id=?");
    $stmt->bind_param("i", $idEditar);
    $stmt->execute();
    $resultEditar = $stmt->get_result();
    $estudianteEditar = $resultEditar->fetch_assoc();
    $stmt->close();
    $accion = "editar";
} else {
    $accion = "insertar";
}
?>

<h3><?= $accion==='editar' ? "✏️ Editar Estudiante" : "📝 Introducir Nuevo Estudiante" ?></h3>
<form method="post" action="reporte_estudiantes.php">
    <?php if ($accion==='editar'): ?>
        <input type="hidden" name="id" value="<?= $estudianteEditar['id'] ?>">
    <?php endif; ?>
    <input type="hidden" name="accion" value="<?= $accion ?>">

    <label>Nombre:</label><br>
    <input type="text" name="nombre" required value="<?= $accion==='editar' ? $estudianteEditar['nombre'] : '' ?>"><br><br>

    <label>Edad:</label><br>
    <input type="number" name="edad" required value="<?= $accion==='editar' ? $estudianteEditar['edad'] : '' ?>"><br><br>

    <label>Carrera:</label><br>
    <input type="text" name="carrera" required value="<?= $accion==='editar' ? $estudianteEditar['carrera'] : '' ?>"><br><br>

    <label>Notas (separadas por coma):</label><br>
    <input type="text" name="notas" required value="<?= $accion==='editar' ? implode(",", json_decode($estudianteEditar['notas'])) : '' ?>"><br><br>

    <input type="submit" value="<?= $accion==='editar' ? '💾 Guardar Cambios' : 'Guardar Estudiante' ?>">
</form>

<table class="tabla-estudiantes">
    <thead>
        <tr>
            <th>ID</th>
            <th>Nombre</th>
            <th>Edad</th>
            <th>Carrera</th>
            <th>Notas</th>
            <th>Promedio</th>
            <th>Acciones</th>
        </tr>
    </thead>
    <tbody>
        <?php if (!empty($estudiantes)): ?>
            <?php foreach($estudiantes as $est): ?>
                <tr>
                    <td><?= $est['id'] ?></td>
                    <td><?= $est['nombre'] ?></td>
                    <td><?= $est['edad'] ?></td>
                    <td><?= $est['carrera'] ?></td>
                    <td><?= implode(", ", json_decode($est['notas'])) ?></td>
                    <td><?= number_format($est['promedios'],2) ?></td>
                    <td>
                        <a class="btn btn-editar" href="?editar=<?= $est['id'] ?>">Editar</a>
                        <a class="btn btn-eliminar" href="?eliminar=<?= $est['id'] ?>" onclick="return confirm('¿Seguro que quieres eliminar este estudiante?')">Eliminar</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr><td colspan="7">No hay estudiantes registrados.</td></tr>
        <?php endif; ?>
    </tbody>
</table>

<?php if (!empty($mejorEstudiante)): ?>
    <h3>🏆 Mejor Estudiante</h3>
    <p class="mejor-estudiante"><strong>Nombre:</strong> <?= $mejorEstudiante ?><br>
    <strong>Promedio:</strong> <?= number_format($mejorPromedio,2) ?></p>
<?php endif; ?>

</body>
</html>
<?php $conn->close(); ?>