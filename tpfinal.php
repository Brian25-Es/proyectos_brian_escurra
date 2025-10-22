<?php
// Solo para inicializar la conexión si es necesario
$host = "localhost";
$usuario = "adminphp";
$contrasena = "TuContraseñaSegura";
$bd = "gestion_productos";
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Gestión de Productos</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body { font-family: 'Inter', sans-serif; background-color: #f8fafc; padding: 30px; }
h2 { text-align: center; margin-bottom: 20px; }
.bajo-stock { background-color: #ffecec !important; }
</style>
</head>
<body>

<div class="container">
<h2>📦 CRUD de Productos</h2>

<!-- Botón agregar -->
<button class="btn btn-success mb-3" data-bs-toggle="modal" data-bs-target="#modalProducto" onclick="abrirAgregar()">Agregar Producto</button>

<!-- Filtros -->
<div class="row g-2 mb-3">
  <div class="col-md-3"><input type="text" id="buscar" class="form-control" placeholder="Buscar..."></div>
  <div class="col-md-3">
    <select id="categoria" class="form-select">
      <option value="todas">Todas</option>
      <option>Electrónica</option>
      <option>Ropa</option>
      <option>Alimentos</option>
      <option>Hogar</option>
    </select>
  </div>
  <div class="col-md-2"><input type="number" id="stockMinimo" class="form-control" placeholder="Stock mínimo" min="0"></div>
  <div class="col-md-2">
    <select id="ordenar" class="form-select">
      <option value="nombre_asc">Nombre (A-Z)</option>
      <option value="nombre_desc">Nombre (Z-A)</option>
      <option value="precio_asc">Precio ↑</option>
      <option value="precio_desc">Precio ↓</option>
    </select>
  </div>
  <div class="col-md-2 d-flex gap-2">
    <button class="btn btn-primary w-100" onclick="cargarProductos()">Filtrar</button>
    <button class="btn btn-danger w-100" onclick="limpiarFiltros()">Limpiar</button>
  </div>
</div>

<!-- Tabla -->
<div class="table-responsive">
  <table class="table table-hover align-middle">
    <thead class="table-dark">
      <tr><th>ID</th><th>Nombre</th><th>Categoría</th><th>Precio</th><th>Stock</th><th>Total</th><th>Acciones</th></tr>
    </thead>
    <tbody id="tabla"></tbody>
  </table>
</div>
</div>

<!-- Modal Agregar/Editar -->
<div class="modal fade" id="modalProducto" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalTitulo"></h5>
        <button class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" id="productoId">
        <div class="mb-2"><input type="text" id="nombre" class="form-control" placeholder="Nombre"></div>
        <div class="mb-2"><input type="text" id="categoriaInput" class="form-control" placeholder="Categoría"></div>
        <div class="mb-2"><input type="number" id="precio" class="form-control" placeholder="Precio"></div>
        <div class="mb-2"><input type="number" id="stock" class="form-control" placeholder="Stock"></div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button class="btn btn-primary" id="guardarBtn">Guardar</button>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
let modal = new bootstrap.Modal(document.getElementById('modalProducto'));

// --- Cargar Productos con filtros ---
function cargarProductos() {
  const filtros = {
    buscar: document.getElementById("buscar").value,
    categoria: document.getElementById("categoria").value,
    stockMinimo: document.getElementById("stockMinimo").value || 0,
    ordenar: document.getElementById("ordenar").value
  };
  fetch(`filtrar_productos.php?${new URLSearchParams(filtros)}`)
    .then(res => res.json())
    .then(datos => mostrarTabla(datos));
}

function mostrarTabla(productos) {
  const tabla = document.getElementById("tabla");
  tabla.innerHTML = "";
  if(productos.length===0){tabla.innerHTML='<tr><td colspan="7" class="text-center text-muted">No hay productos</td></tr>';return;}
  productos.forEach(p=>{
    const total = (p.precio*p.stock).toFixed(2);
    const fila = document.createElement("tr");
    if(p.stock<10) fila.classList.add("bajo-stock");
    fila.innerHTML = `
      <td>${p.id}</td>
      <td>${p.nombre}</td>
      <td>${p.categoria}</td>
      <td>$${p.precio}</td>
      <td>${p.stock}</td>
      <td>$${total}</td>
      <td>
        <button class="btn btn-sm btn-warning" onclick="abrirEditar(${p.id})">Editar</button>
        <button class="btn btn-sm btn-danger" onclick="eliminar(${p.id})">Eliminar</button>
      </td>
    `;
    tabla.appendChild(fila);
  });
}

function limpiarFiltros() {
  document.getElementById("buscar").value="";
  document.getElementById("categoria").value="todas";
  document.getElementById("stockMinimo").value="";
  document.getElementById("ordenar").value="nombre_asc";
  cargarProductos();
}

// --- CRUD unificado ---
function abrirAgregar() {
  document.getElementById("modalTitulo").innerText="Agregar Producto";
  document.getElementById("productoId").value="";
  document.getElementById("nombre").value="";
  document.getElementById("categoriaInput").value="";
  document.getElementById("precio").value="";
  document.getElementById("stock").value="";
  document.getElementById("guardarBtn").onclick = guardarProducto;
  modal.show();
}

function abrirEditar(id) {
  fetch(`obtener_producto.php?id=${id}`)
    .then(res=>res.json())
    .then(p=>{
      if(!p.id){alert("Producto no encontrado"); return;}
      document.getElementById("modalTitulo").innerText="Editar Producto";
      document.getElementById("productoId").value=p.id;
      document.getElementById("nombre").value=p.nombre;
      document.getElementById("categoriaInput").value=p.categoria;
      document.getElementById("precio").value=p.precio;
      document.getElementById("stock").value=p.stock;
      document.getElementById("guardarBtn").onclick = guardarProducto;
      modal.show();
    });
}

function guardarProducto() {
  const id = document.getElementById("productoId").value;
  const data = {
    accion: id ? "editar" : "insertar",
    ...(id && {id: parseInt(id)}),
    nombre: document.getElementById("nombre").value,
    categoria: document.getElementById("categoriaInput").value,
    precio: parseFloat(document.getElementById("precio").value) || 0,
    stock: parseInt(document.getElementById("stock").value) || 0
  };

  fetch("acciones_productos.php", {
    method: "POST",
    headers: {"Content-Type":"application/json"},
    body: JSON.stringify(data)
  })
  .then(res=>res.json())
  .then(res=>{
    alert(res.mensaje);
    modal.hide();
    cargarProductos();
  })
  .catch(err=>console.error(err));
}

function eliminar(id){
  if(!confirm("¿Eliminar producto?")) return;

  fetch("acciones_productos.php", {
    method:"POST",
    headers: {"Content-Type":"application/json"},
    body: JSON.stringify({accion:"eliminar",id})
  })
  .then(res=>res.json())
  .then(res=>{
    alert(res.mensaje);
    cargarProductos();
  })
  .catch(err=>console.error(err));
}

// Cargar tabla al inicio
cargarProductos();
</script>
</body>
</html>