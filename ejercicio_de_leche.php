<?php
$listacompras = ["pan", "leche", "huevos", "arroz", "pollo"];

$listacompras[] = "queso";
$listacompras[] = "tomate";

echo "<h1>Lista de Compras</h1>";
echo "<ul>";
foreach ($listacompras as $indice => $producto) {
    echo "<li>Producto " . ($indice + 1) . ": " . $producto . "</li>";
}
echo "</ul>";

echo "<p>Total de productos: " . count($listacompras) . "</p>";

if (in_array("leche", $listacompras)) {
    echo "<p>Leche está en la lista de compras.</p>";
} else {
    echo "<p>Leche no está en la lista de compras.</p>";
}
?>
