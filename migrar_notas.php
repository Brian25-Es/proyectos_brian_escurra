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

$sql = "SELECT nombre, notas FROM EJERCICIO7";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $nombre = $row['nombre'];
        $notas_json = $row['notas'];
        $notas_array = json_decode($notas_json);

        if (!is_array($notas_array)) {
            $notas_array = array_map('floatval', explode(',', trim($notas_json, "[]")));
        }

        $stmt = $conn->prepare("SELECT id FROM estudiantes WHERE nombre = ?");
        $stmt->bind_param("s", $nombre);
        $stmt->execute();
        $stmt->bind_result($estudiante_id);
        $stmt->fetch();
        $stmt->close();

        if ($estudiante_id) {
            foreach ($notas_array as $nota) {
                $nota = floatval($nota);
                $insert = $conn->prepare("INSERT INTO notas (estudiante_id, valor) VALUES (?, ?)");
                $insert->bind_param("id", $estudiante_id, $nota);
                $insert->execute();
                $insert->close();
            }
            echo "Notas insertadas para $nombre (ID $estudiante_id)<br>";
        } else {
            echo "Estudiante '$nombre' no encontrado.<br>";
        }
    }
} else {
    echo "No hay datos en EJERCICIO7.";
}

$conn->close();
?>
