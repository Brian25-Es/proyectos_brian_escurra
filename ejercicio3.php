<?php
$listacompras = ["pan", "leche", "huevos", "arroz", "pollo"];

echo "<h1>Lista de Compras</h1>";
foreach ($listacompras as $indice => $producto) {
    echo "<p>Producto " . ($indice + 1) . ": " . $producto . "</p>";
}

$listacompras[] = "queso";
$listacompras[] = "tomate";

echo "<h2>total de productos:". count($listacompras)"</h2>";

if (in_array("leche", $listacompras)) {
    echo "<p>leche están en la lista de compras.</p>";
} else {
    echo "<p>leche no están en la lista de compras.</p>";
}
