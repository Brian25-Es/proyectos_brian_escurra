<?php
// 🔧 Mostrar errores
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// --- Conexión con base de datos ---
$host = "localhost";
$usuario = "adminphp";
$contrasena = "TuContraseñaSegura";
$bd = "gestion_productos";

$conn = new mysqli($host, $usuario, $contrasena, $bd);
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Sistema de Gestión de Productos</title>

  <!-- Bootstrap -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

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
    tr[style*="background-color"] {
      background-color: #fff0f0 !important;
    }
  </style>
</head>
<body>

  <div class="container">
    <h2>📦 Sistema de Gestión de Productos</h2>

    <!-- 📊 Filtros -->
    <div class="card mb-4">
      <div class="card-body">
        <div class="row g-3 align-items-end">
          <div class="col-md-4">
            <label class="form-label">Buscar:</label>
            <input type="text" id="buscar" class="form-control" placeholder="Buscar por nombre...">
          </div>
          <div class="col-md-4">
            <label class="form-label">Categoría:</label>
            <select id="categoria" class="form-select">
              <option value="todas">Todas</option>
              <option value="Electrónica">Electrónica</option>
              <option value="Ropa">Ropa</option>
              <option value="Alimentos">Alimentos</option>
              <option value="Hogar">Hogar</option>
            </select>
          </div>
          <div class="col-md-4">
            <button id="btnNuevo" class="btn btn-success w-100">➕ Nuevo Producto</button>
          </div>
        </div>
      </div>
    </div>

    <!-- 📋 Tabla -->
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
                <th>Acciones</th>
              </tr>
            </thead>
            <tbody id="tablaProductos"></tbody>
          </table>
        </div>
      </div>
    </div>
  </div>

  <!-- 🪟 Modal -->
  <div class="modal fade" id="modalProducto" tabindex="-1">
    <div class="modal-dialog">
      <div class="modal-content">
        <form id="formProducto">
          <div class="modal-header">
            <h5 class="modal-title">Producto</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <input type="hidden" id="productoId">
            <div class="mb-3">
              <label class="form-label">Nombre</label>
              <input type="text" id="nombre" class="form-control" required>
            </div>
            <div class="mb-3">
              <label class="form-label">Categoría</label>
              <input type="text" id="categoriaInput" class="form-control" required>
            </div>
            <div class="mb-3">
              <label class="form-label">Precio</label>
              <input type="number" id="precio" class="form-control" step="0.01" required>
            </div>
            <div class="mb-3">
              <label class="form-label">Stock</label>
              <input type="number" id="stock" class="form-control" required>
            </div>
          </div>
          <div class="modal-footer">
            <button type="submit" class="btn btn-primary">💾 Guardar</button>
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

  <!-- ✅ SCRIPT SIMPLIFICADO -->
  <script>
  document.addEventListener("DOMContentLoaded", () => {
    const tabla = document.getElementById("tablaProductos");
    const buscar = document.getElementById("buscar");
    const categoria = document.getElementById("categoria");
    const modal = new bootstrap.Modal(document.getElementById("modalProducto"));
    const form = document.getElementById("formProducto");

    cargarProductos();

    // Cargar productos
    function cargarProductos() {
      const params = new URLSearchParams({
        buscar: buscar.value,
        categoria: categoria.value
      });
      fetch("filtrar_productos.php?" + params.toString())
        .then(res => res.json())
        .then(data => mostrarProductos(data));
    }

    // Mostrar en tabla
    function mostrarProductos(productos) {
      tabla.innerHTML = "";
      if (productos.length === 0) {
        tabla.innerHTML = `<tr><td colspan="6" class="text-center text-muted">Sin resultados</td></tr>`;
        return;
      }
      productos.forEach(p => {
        tabla.innerHTML += `
          <tr>
            <td>${p.id}</td>
            <td>${p.nombre}</td>
            <td>${p.categoria}</td>
            <td>$${p.precio}</td>
            <td>${p.stock}</td>
            <td>
              <button class="btn btn-warning btn-sm" onclick="editarProducto(${p.id})">✏️</button>
              <button class="btn btn-danger btn-sm" onclick="eliminarProducto(${p.id})">🗑️</button>
            </td>
          </tr>`;
      });
    }

    // Buscar y filtrar
    buscar.addEventListener("input", cargarProductos);
    categoria.addEventListener("change", cargarProductos);

    // Nuevo producto
    document.getElementById("btnNuevo").addEventListener("click", () => {
      form.reset();
      document.getElementById("productoId").value = "";
      modal.show();
    });

    // Guardar (insertar o editar)
    form.addEventListener("submit", (e) => {
      e.preventDefault();
      const id = document.getElementById("productoId").value;
      const data = {
        accion: id ? "editar" : "insertar",
        id,
        nombre: document.getElementById("nombre").value,
        categoria: document.getElementById("categoriaInput").value,
        precio: document.getElementById("precio").value,
        stock: document.getElementById("stock").value
      };

      fetch("acciones_productos.php", {
        method: "POST",
        headers: {"Content-Type": "application/json"},
        body: JSON.stringify(data)
      })
      .then(res => res.json())
      .then(() => {
        modal.hide();
        cargarProductos();
      });
    });

    // Funciones globales
    window.editarProducto = (id) => {
      fetch("obtener_producto.php?id=" + id)
        .then(res => res.json())
        .then(p => {
          document.getElementById("productoId").value = p.id;
          document.getElementById("nombre").value = p.nombre;
          document.getElementById("categoriaInput").value = p.categoria;
          document.getElementById("precio").value = p.precio;
          document.getElementById("stock").value = p.stock;
          modal.show();
        });
    };

    window.eliminarProducto = (id) => {
      if (!confirm("¿Seguro que quieres eliminar este producto?")) return;
      fetch("acciones_productos.php", {
        method: "POST",
        headers: {"Content-Type": "application/json"},
        body: JSON.stringify({accion: "eliminar", id})
      })
      .then(res => res.json())
      .then(() => cargarProductos());
    };
  });
  </script>
</body>
</html>