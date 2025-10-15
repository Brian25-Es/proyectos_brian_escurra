<?php
// Para mostrar errores (útil en desarrollo). Puedes comentar en producción.
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// --- 1. Conexión con base de datos MySQL ---
$host = "localhost";
$usuario = "adminphp";       // ajusta si tu usuario es distinto
$contrasena = "TuContraseñaSegura";        // ajusta si tu MySQL tiene contraseña
$bd = "gestion_productos";

// Crear conexión
$conn = new mysqli($host, $usuario, $contrasena, $bd);
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

// --- 2. Obtener productos de la base de datos ---
$sql = "SELECT * FROM productos";
$resultado = $conn->query($sql);

$productos = [];
if ($resultado) {
    while ($fila = $resultado->fetch_assoc()) {
        // Convertir tipos adecuados si es necesario
        $fila['precio'] = floatval($fila['precio']);
        $fila['stock'] = intval($fila['stock']);
        $productos[] = $fila;
    }
}
$conn->close();

// Convertir array PHP a JSON para que lo use JavaScript
$productos_json = json_encode($productos);
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Sistema de Gestión de Productos</title>

  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Google Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">

  <style>
    body {
      font-family: 'Inter', sans-serif;
      background-color: #f8fafc;
      color: #2c3e50;
      padding: 40px 20px;
    }
    h2 {
      text-align: center;
      font-weight: 700;
      color: #1f2d3d;
      margin-bottom: 40px;
    }
    .card {
      border: none;
      border-radius: 16px;
      box-shadow: 0 6px 20px rgba(0, 0, 0, 0.05);
    }
    .table-hover tbody tr:hover {
      background-color: #f0f9ff;
    }
    .sin-resultados {
      text-align: center;
      padding: 25px;
      font-size: 1.1rem;
      color: #888;
      font-style: italic;
    }
    tr[style*="background-color"] {
      background-color: #fff0f0 !important;
    }
  </style>
</head>
<body>

  <div class="container">
    <h2>📦 Sistema de Gestión de Productos</h2>

    <div class="row g-4 mb-4 text-center">
      <div class="col-md-4">
        <div class="card py-3">
          <div class="card-body">
            <h6 class="text-secondary mb-1">Total de productos</h6>
            <h3 class="text-primary fw-bold" id="totalProductos">0</h3>
          </div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="card py-3">
          <div class="card-body">
            <h6 class="text-secondary mb-1">Productos filtrados</h6>
            <h3 class="text-success fw-bold" id="productosFiltrados">0</h3>
          </div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="card py-3">
          <div class="card-body">
            <h6 class="text-secondary mb-1">Valor total stock</h6>
            <h3 class="text-info fw-bold">$<span id="valorTotal">0</span></h3>
          </div>
        </div>
      </div>
    </div>

    <div class="card mb-4">
      <div class="card-body">
        <h5 class="card-title mb-4">🔍 Filtros y Búsqueda</h5>
        <div class="row g-3">
          <div class="col-md-4">
            <label class="form-label fw-semibold">Buscar:</label>
            <input type="text" id="buscar" class="form-control" placeholder="Buscar por nombre...">
          </div>
          <div class="col-md-4">
            <label class="form-label fw-semibold">Categoría:</label>
            <select id="categoria" class="form-select">
              <option value="todas">Todas las categorías</option>
              <option value="Electrónica">Electrónica</option>
              <option value="Ropa">Ropa</option>
              <option value="Alimentos">Alimentos</option>
              <option value="Hogar">Hogar</option>
            </select>
          </div>
          <div class="col-md-4">
            <label class="form-label fw-semibold">Stock mínimo:</label>
            <input type="number" id="stockMinimo" class="form-control" value="0" min="0">
          </div>
          <div class="col-md-6">
            <label class="form-label fw-semibold">Precio mínimo:</label>
            <input type="number" id="precioMinimo" class="form-control" placeholder="Desde..." min="0">
          </div>
          <div class="col-md-6">
            <label class="form-label fw-semibold">Precio máximo:</label>
            <input type="number" id="precioMaximo" class="form-control" placeholder="Hasta..." min="0">
          </div>
          <div class="col-md-6">
            <label class="form-label fw-semibold">Ordenar por:</label>
            <select id="ordenar" class="form-select">
              <option value="nombre_asc">Nombre (A-Z)</option>
              <option value="nombre_desc">Nombre (Z-A)</option>
              <option value="precio_asc">Precio (Menor a Mayor)</option>
              <option value="precio_desc">Precio (Mayor a Menor)</option>
              <option value="stock_asc">Stock (Menor a Mayor)</option>
              <option value="stock_desc">Stock (Mayor a Menor)</option>
            </select>
          </div>
          <div class="col-12 d-flex justify-content-end mt-3 flex-wrap gap-2">
            <button id="aplicarFiltros" class="btn btn-primary">Aplicar Filtros</button>
            <button id="limpiarFiltros" class="btn btn-danger">Limpiar Filtros</button>
            <button id="exportarTabla" class="btn btn-success">Exportar Tabla</button>
          </div>
        </div>
      </div>
    </div>

    <div class="card">
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-hover align-middle">
            <thead class="table-dark">
              <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Categoría</th>
                <th>Precio</th>
                <th>Stock</th>
                <th>Valor Total</th>
              </tr>
            </thead>
            <tbody id="cuerpoTabla"></tbody>
          </table>
        </div>
      </div>
    </div>

  </div>

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
function mostrarProductos(productos) {
  const cuerpo = document.getElementById("cuerpoTabla");
  cuerpo.innerHTML = "";

  if (productos.length === 0) {
    cuerpo.innerHTML = `<tr><td colspan="6" class="sin-resultados">No hay productos para mostrar.</td></tr>`;
  } else {
    productos.forEach(p => {
      const fila = document.createElement("tr");
      if (p.stock < 10) fila.style.backgroundColor = "#fff0f0";

      const valorTotal = (p.precio * p.stock).toFixed(2);
      fila.innerHTML = `
        <td>${p.id}</td>
        <td>${p.nombre}</td>
        <td>${p.categoria}</td>
        <td>$${p.precio}</td>
        <td>${p.stock}</td>
        <td>$${valorTotal}</td>
      `;
      cuerpo.appendChild(fila);
    });
  }

  // Actualizar estadísticas
  document.getElementById("totalProductos").textContent = productos.length;
  const totalValor = productos.reduce((acc, p) => acc + (p.precio * p.stock), 0);
  document.getElementById("productosFiltrados").textContent = productos.length;
  document.getElementById("valorTotal").textContent = totalValor.toFixed(2);
}

function obtenerFiltros() {
  return {
    buscar: document.getElementById("buscar").value,
    categoria: document.getElementById("categoria").value,
    stockMin: document.getElementById("stockMinimo").value,
    precioMin: document.getElementById("precioMinimo").value,
    precioMax: document.getElementById("precioMaximo").value,
    ordenar: document.getElementById("ordenar").value
  };
}

function aplicarFiltrosAJAX() {
  const filtros = obtenerFiltros();
  const params = new URLSearchParams(filtros).toString();

  fetch(`filtrar_productos.php?${params}`)
    .then(res => res.json())
    .then(data => {
      mostrarProductos(data);
    })
    .catch(err => {
      console.error("Error al obtener productos:", err);
    });
}

function limpiarFiltros() {
  document.getElementById("buscar").value = "";
  document.getElementById("categoria").value = "todas";
  document.getElementById("stockMinimo").value = 0;
  document.getElementById("precioMinimo").value = "";
  document.getElementById("precioMaximo").value = "";
  document.getElementById("ordenar").value = "nombre_asc";
  aplicarFiltrosAJAX();
}

function exportarTabla() {
  console.clear();
  console.log("📤 Exportando tabla filtrada (consulta en consola):");
  alert("Los datos filtrados se muestran en consola. Abre F12 para verlos.");
}

// Eventos automáticos
document.querySelectorAll("#buscar, #categoria, #stockMinimo, #precioMinimo, #precioMaximo, #ordenar")
  .forEach(el => el.addEventListener("input", aplicarFiltrosAJAX));

document.getElementById("aplicarFiltros").addEventListener("click", aplicarFiltrosAJAX);
document.getElementById("limpiarFiltros").addEventListener("click", limpiarFiltros);
document.getElementById("exportarTabla").addEventListener("click", exportarTabla);

// Cargar productos al inicio
aplicarFiltrosAJAX();
</script>


</body>
</html>
