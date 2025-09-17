<?php
$servername = "localhost";
$username   = "adminphp";
$password   = "TuContraseñaSegura";
$dbname     = "myDB";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("❌ Conexión fallida: " . $conn->connect_error);
}

$sql = "SELECT * FROM EJERCICIO7";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Estudiantes</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        .acciones {
            display: flex;
            gap: 10px;
        }

        .btn {
            padding: 8px 14px;
            border: none;
            border-radius: var(--radius);
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            transition: var(--transition);
        }

        .btn-editar {
            background-color: var(--color-accent);
            color: var(--color-white);
        }
        .btn-editar:hover {
            background-color: #218c74;
        }

        .btn-eliminar {
            background-color: #e74c3c;
            color: var(--color-white);
        }
        .btn-eliminar:hover {
            background-color: #c0392b;
        }
    </style>
</head>
<body>
    <h2>📋 Reporte de Estudiantes</h2>

    <table class="tabla-estudiantes">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Carrera</th>
                <th>Edad</th>
                <th>Notas</th>
                <th>Promedio</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
        <?php if ($result->num_rows > 0): ?>
            <?php while($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= $row["id"] ?></td>
                    <td><?= $row["nombre"] ?></td>
                    <td><?= $row["carrera"] ?></td>
                    <td><?= $row["edad"] ?></td>
                    <td><?= $row["notas"] ?></td>
                    <td><?= number_format($row["promedios"], 2) ?></td>
                    <td class="acciones">
                        <a class="btn btn-editar" href="editar_estudiante.php?id=<?= $row['id'] ?>">Editar</a>
                        <a class="btn btn-eliminar" href="eliminar_estudiante.php?id=<?= $row['id'] ?>" onclick="return confirm('¿Seguro que quieres eliminar este estudiante?')">Eliminar</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="7">No hay estudiantes registrados.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</body>
</html>

<?php $conn->close(); ?>