<?php
$servername = "localhost";
$username = "adminphp";
$password = "TuContraseñaSegura";
$dbname = "myDB";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

$sql = "CREATE TABLE EJERCICIO7 (
    id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(30) NOT NULL,
    carrera VARCHAR(30) NOT NULL,
    edad INT(6),
    notas TEXT,
    promedios DECIMAL(5,2),
    reg_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)";

if ($conn->query($sql) === TRUE) {
  echo "✅ Tabla EJERCICIO7 creada correctamente.";
} else {
  echo "❌ Error al crear la tabla: " . $conn->error;
}

$conn->close();
?>
