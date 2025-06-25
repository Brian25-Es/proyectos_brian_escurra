<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="estilos.css">
    <title>Respuesta</title>
    <?php
    function obtenerColorFondo($anios) {
        if (is_numeric($anios)) {
            $anios = (int)$anios;
            if ($anios <= 12) {
                return "lightblue";
            } elseif ($anios >= 13 && $anios <= 19) {
                return "lightgreen";
            } else {
                return "lightgray";
            }
        }
        return "white";
    }

    function descuento($anios){
        $desc=0;
        $precio=100;
        if($anios<20){
            $desc=$precio*(10/100);
            $precio=$precio-$desc;
        }
        elseif($anios>50){
            $desc=$precio*(5/100);
            $precio=$precio-$desc;
        }
        return $precio;
    }



    $backgroundColor = "white";
    if (isset($_REQUEST["anios"])) {
        $backgroundColor = obtenerColorFondo($_REQUEST["anios"]);
    }
    ?>
    <style>
        body {
            background-color: <?php echo $backgroundColor; ?>;
        }
    </style>
</head>
<body>
<?php
if (isset($_REQUEST["nombre"]) && isset($_REQUEST["anios"])) {
    $nombre = $_REQUEST["nombre"];
    $anios = $_REQUEST["anios"];

    echo "<p>Nombre recibido: $nombre</p>";
    echo "<p>Años recibidos: $anios</p>";

    if (is_numeric($anios)) {
        $anios = (int)$anios;
        if ($anios > 12 && $anios < 20) {
            echo "<p>Usted $nombre es un adolescente.</p>";
        } else {
            echo "<p>Usted $nombre no es un adolescente.</p>";
        }
    } else {
        echo "<p>Por favor, ingrese un número válido en años.</p>";
    }
} else {
    echo "<p>No se recibieron los datos.</p>";
}

$funciondescuento=descuento($anios);
echo "<p>el precio de su entrada es: $funciondescuento</p>";
?>

<button class="back-to-top" onclick="scrollToTop()">Volver al inicio</button>

<script>
  function scrollToTop() {
    window.scrollTo({ top: 0, behavior: 'smooth' });
  }
</script>

</body>
</html>