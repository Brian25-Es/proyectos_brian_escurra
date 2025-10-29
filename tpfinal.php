<?php
// tpfinal.php
// Archivo principal - interfaz y lógica cliente (fetch)
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width,initial-scale=1" />
<title>Gestión de Productos</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
  body { font-family: Inter, sans-serif; padding: 28px; background:#f6f8fb; }
  .bajo-stock { background:#fff0f0; }
</style>
</head>
<body>
<div class="container">
  <h2 class="mb-4">📦 Gestión de Productos</h2>

  <div class="mb-3 d-flex gap-2">
    <input id="buscar" class="form-control" placeholder="Buscar por nombre...">
    <select id="categoria" class="form-select" style="max-width:220px">
      <option value="todas">Todas</option>
      <option>Electrónica</option><option>Ropa</option><option>Alimentos</option><option>Hogar</option>
    </select>
    <button id="btnNuevo" class="btn btn-success">➕ Nuevo</button>
  </div>

  <div class="table-responsive">
    <table class="table table-hover">
      <thead class="table-dark">
        <tr><th>ID</th><th>Nombre</th><th>Categoría</th><th>Precio</th><th>Stock</th><th>Acciones</th></tr>
      </thead>
      <tbody id="tablaProductos"></tbody>
    </table>
  </div>
</div>

<!-- Modal -->
<div class="modal fade" id="modalProducto" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form id="formProducto">
        <div class="modal-header">
          <h5 class="modal-title" id="tituloModal">Producto</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" id="productoId">
          <div class="mb-3">
            <label class="form-label">Nombre</label>
            <input id="nombre" class="form-control" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Categoría</label>
            <input id="categoriaInput" class="form-control" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Precio</label>
            <input id="precio" type="number" step="0.01" class="form-control" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Stock</label>
            <input id="stock" type="number" class="form-control" required>
          </div>
          <div id="formError" class="text-danger" style="display:none"></div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-primary">Guardar</button>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
(() => {
  const tabla = document.getElementById('tablaProductos');
  const buscar = document.getElementById('buscar');
  const categoria = document.getElementById('categoria');
  const btnNuevo = document.getElementById('btnNuevo');
  const modalEl = document.getElementById('modalProducto');
  const modal = new bootstrap.Modal(modalEl);
  const form = document.getElementById('formProducto');
  const productoIdInput = document.getElementById('productoId');
  const nombreInput = document.getElementById('nombre');
  const categoriaInput = document.getElementById('categoriaInput');
  const precioInput = document.getElementById('precio');
  const stockInput = document.getElementById('stock');
  const formError = document.getElementById('formError');

  // carga inicial
  cargarProductos();

  // eventos
  buscar.addEventListener('input', cargarProductos);
  categoria.addEventListener('change', cargarProductos);
  btnNuevo.addEventListener('click', abrirNuevo);

  // abrir modal nuevo
  function abrirNuevo(){
    formError.style.display = 'none';
    productoIdInput.value = '';
    nombreInput.value = '';
    categoriaInput.value = '';
    precioInput.value = '';
    stockInput.value = '';
    document.getElementById('tituloModal').innerText = 'Agregar producto';
    modal.show();
  }

  // cargar productos según filtros (usa filtrar_productos.php)
  function cargarProductos(){
    const params = new URLSearchParams({
      buscar: buscar.value,
      categoria: categoria.value
    });
    fetch('filtrar_productos.php?' + params.toString())
      .then(r => r.json())
      .then(data => {
        renderTabla(data);
      })
      .catch(err => {
        console.error('Error al cargar productos:', err);
        tabla.innerHTML = '<tr><td colspan="6" class="text-danger">Error cargando productos (ver consola)</td></tr>';
      });
  }

  // render tabla
  function renderTabla(items){
    tabla.innerHTML = '';
    if (!items || items.length === 0) {
      tabla.innerHTML = '<tr><td colspan="6" class="text-muted text-center">Sin resultados</td></tr>';
      return;
    }
    items.forEach(p => {
      const tr = document.createElement('tr');
      if (p.stock < 10) tr.classList.add('bajo-stock');
      tr.innerHTML = `
        <td>${p.id}</td>
        <td>${escapeHtml(p.nombre)}</td>
        <td>${escapeHtml(p.categoria)}</td>
        <td>$${Number(p.precio).toFixed(2)}</td>
        <td>${p.stock}</td>
        <td>
          <button class="btn btn-warning btn-sm" data-id="${p.id}" data-accion="editar">✏️</button>
          <button class="btn btn-danger btn-sm" data-id="${p.id}" data-accion="eliminar">🗑️</button>
        </td>
      `;
      tabla.appendChild(tr);
    });

    // delegación de eventos en la tabla
    tabla.querySelectorAll('button[data-accion]').forEach(btn => {
      btn.onclick = (e) => {
        const id = parseInt(btn.getAttribute('data-id'));
        const accion = btn.getAttribute('data-accion');
        if (accion === 'editar') abrirEditar(id);
        if (accion === 'eliminar') eliminarProducto(id);
      };
    });
  }

  // abrir editar: trae datos y carga el modal
  function abrirEditar(id){
    formError.style.display = 'none';
    fetch('obtener_producto.php?id=' + encodeURIComponent(id))
      .then(r => r.json())
      .then(p => {
        if (p && p.error) {
          alert('Error: ' + p.error);
          return;
        }
        // cargar campos
        productoIdInput.value = p.id;
        nombreInput.value = p.nombre;
        categoriaInput.value = p.categoria;
        precioInput.value = p.precio;
        stockInput.value = p.stock;
        document.getElementById('tituloModal').innerText = 'Editar producto';
        modal.show();
      })
      .catch(err => {
        console.error('Error obtener producto:', err);
        alert('Error al obtener producto (ver consola)');
      });
  }

  // eliminar
  function eliminarProducto(id){
    if (!confirm('¿Eliminar producto?')) return;
    fetch('acciones_productos.php', {
      method: 'POST',
      headers: {'Content-Type':'application/json'},
      body: JSON.stringify({accion: 'eliminar', id: id})
    })
    .then(r => r.json())
    .then(res => {
      if (!res.ok && !res.ok === undefined) {
        // some older responses may use ok key or not — show mensaje siempre
      }
      if (res.mensaje) alert(res.mensaje);
      cargarProductos();
    })
    .catch(err => {
      console.error('Error eliminar:', err);
      alert('Error al eliminar (ver consola)');
    });
  }

  // submit formulario (insertar o editar)
  form.addEventListener('submit', (e) => {
    e.preventDefault();
    formError.style.display = 'none';

    // validaciones simples
    const nombre = nombreInput.value.trim();
    const categoriaVal = categoriaInput.value.trim();
    const precioVal = parseFloat(precioInput.value);
    const stockVal = parseInt(stockInput.value);

    if (!nombre || !categoriaVal) {
      formError.innerText = 'Nombre y categoría son obligatorios';
      formError.style.display = 'block';
      return;
    }
    if (isNaN(precioVal) || isNaN(stockVal)) {
      formError.innerText = 'Precio y stock deben ser números válidos';
      formError.style.display = 'block';
      return;
    }

    const idRaw = productoIdInput.value;
    const isEdit = idRaw !== '' && idRaw !== null;
    const payload = {
      accion: isEdit ? 'editar' : 'insertar',
      // enviamos id como número solo si edit
      id: isEdit ? parseInt(idRaw) : undefined,
      nombre: nombre,
      categoria: categoriaVal,
      precio: precioVal,
      stock: stockVal
    };

    fetch('acciones_productos.php', {
      method: 'POST',
      headers: {'Content-Type':'application/json'},
      body: JSON.stringify(payload)
    })
    .then(r => r.json())
    .then(res => {
      if (!res || (res.ok !== undefined && !res.ok)) {
        alert('Error: ' + (res.mensaje || 'Respuesta inválida del servidor'));
        console.error('Respuesta server:', res);
        return;
      }
      // éxito
      if (res.mensaje) alert(res.mensaje);
      modal.hide();
      cargarProductos();
    })
    .catch(err => {
      console.error('Error guardar:', err);
      alert('Error al guardar producto (ver consola)');
    });
  });

  // ayuda: evitar inyección simple en render
  function escapeHtml(str) {
    if (!str) return '';
    return String(str).replace(/[&<>"'`=\/]/g, function(s) {
      return ({
        '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;','/':'&#47;','`':'&#96;','=':'&#61;'
      })[s];
    });
  }

})(); 
</script>
</body>
</html>