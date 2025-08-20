<?php

$estudiantes = [
    "est001" => [
        "nombre" => "Ana García",
        "edad" => 20,
        "carrera" => "Ingeniería",
        "notas" => [8.5, 9.0, 7.5, 8.0]
    ],
    "est002" => [
        "nombre" => "Carlos López",
        "edad" => 22,
        "carrera" => "Medicina", 
        "notas" => [9.5, 8.5, 9.0, 9.2]
    ],
    "est003" => [
        "nombre" => "María Rodríguez",
        "edad" => 21,
        "carrera" => "Arte",
        "notas" => [7.0, 8.0, 7.5, 8.5]
    ]
];

function calcularPromedio($notas) {
    return count($notas) ? array_sum($notas) / count($notas) : 0;
}

echo "<h2>📋 Reporte de Estudiantes</h2>";

echo "<table border='1' cellpadding='10' cellspacing='0'>";
echo "<tr>
        <th>ID</th>
        <th>Nombre</th>
        <th>Edad</th>
        <th>Carrera</th>
        <th>Notas</th>
        <th>Promedio</th>
      </tr>";

$mejorPromedio = 0;
$mejorEstudiante = "";

foreach ($estudiantes as $id => $estudiante) {
    $promedio = calcularPromedio($estudiante["notas"]);
    
    echo "<tr>";
    echo "<td>$id</td>";
    echo "<td>{$estudiante['nombre']}</td>";
    echo "<td>{$estudiante['edad']}</td>";
    echo "<td>{$estudiante['carrera']}</td>";
    echo "<td>" . implode(", ", $estudiante['notas']) . "</td>";
    echo "<td>" . number_format($promedio, 2) . "</td>";
    echo "</tr>";
    
    if ($promedio > $mejorPromedio) {
        $mejorPromedio = $promedio;
        $mejorEstudiante = $estudiante["nombre"];
    }
}

echo "</table>";

echo "<h3>🏆 Mejor Estudiante</h3>";
echo "<p><strong>Nombre:</strong> $mejorEstudiante<br>";
echo "<strong>Promedio:</strong> " . number_format($mejorPromedio, 2) . "</p>";

?>