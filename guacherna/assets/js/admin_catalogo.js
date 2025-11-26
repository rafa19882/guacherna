// Variables globales
let productosData = [];
let categoriasData = [];

// Variables de paginación
let paginaActual = 1;
let productosPorPagina = 10;
let productosFiltrados = [];

// Inicialización
document.addEventListener('DOMContentLoaded', function() {
    cargarCategorias();
    cargarProductos();
    
    // Event listeners para los formularios
    document.getElementById('formProducto').addEventListener('submit', guardarProducto);
    document.getElementById('formCategoria').addEventListener('submit', guardarCategoria);
    
    // Event listener para cambiar productos por página
    const selectPorPagina = document.getElementById('productosPorPagina');
    if (selectPorPagina) {
        selectPorPagina.addEventListener('change', function() {
            productosPorPagina = parseInt(this.value);
            paginaActual = 1;
            renderizarProductos(productosFiltrados);
        });
    }
    
    // Cerrar modal al hacer click fuera
    document.querySelectorAll('.modal-overlay').forEach(modal => {
        modal.addEventListener('click', function(e) {
            if (e.target === this) cerrarModales();
        });
    });
});

// ==================== TABS ====================
function cambiarTab(tab) {
    // Cambiar tabs activos
    document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
    document.querySelectorAll('.tab-content').forEach(content => content.classList.remove('active'));
    
    document.querySelector(`[onclick="cambiarTab('${tab}')"]`).classList.add('active');
    document.getElementById(`tab-${tab}`).classList.add('active');
    
    // Recargar datos del tab activo
    if (tab === 'productos') {
        cargarProductos();
    } else if (tab === 'categorias') {
        cargarCategorias();
    }
}

// ==================== PRODUCTOS ====================
function cargarProductos() {
    fetch('../api/admin/productos_crud.php?action=listar')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                productosData = data.productos;
                productosFiltrados = [...productosData];
                paginaActual = 1;
                renderizarProductos(productosFiltrados);
            } else {
                mostrarError('Error al cargar productos');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            mostrarError('Error de conexión al cargar productos');
        });
}

function renderizarProductos(productos) {
    const tbody = document.getElementById('listaProductos');
    
    if (!productos || productos.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="7" class="empty-state">
                    <div class="empty-state-icon">📭</div>
                    <p>No hay productos registrados</p>
                    <button class="btn-primary" onclick="abrirModalProducto()" style="margin-top: 20px;">
                        ➕ Agregar el primer producto
                    </button>
                </td>
            </tr>
        `;
        actualizarInfoPaginacion(0, 0, 0);
        return;
    }
    
    // Calcular paginación
    const totalProductos = productos.length;
    const totalPaginas = Math.ceil(totalProductos / productosPorPagina);
    const inicio = (paginaActual - 1) * productosPorPagina;
    const fin = inicio + productosPorPagina;
    const productosPagina = productos.slice(inicio, fin);
    
    tbody.innerHTML = productosPagina.map(producto => {
        // Verificar si tiene oferta activa
        const tieneOferta = producto.en_oferta == 1 && producto.precio_oferta;
        const precioOferta = tieneOferta ? parseFloat(producto.precio_oferta) : 0;
        const descuento = tieneOferta ? parseInt(producto.porcentaje_descuento) : 0;
        
        // Detectar si es imagen o emoji
        const imagenUrl = producto.imagen_url || producto.emoji || '📦';
        const esImagen = imagenUrl.includes('/') || imagenUrl.includes('http') || imagenUrl.includes('assets');
        
        // Renderizar imagen o emoji
        let contenidoImagen = '';
        if (esImagen) {
            const rutaImagen = imagenUrl.startsWith('assets') ? '../' + imagenUrl : imagenUrl;
            contenidoImagen = `<img src="${rutaImagen}" alt="${producto.nombre}" class="producto-imagen" onerror="this.style.display='none'; this.parentElement.innerHTML='📦'">`;
        } else {
            contenidoImagen = imagenUrl;
        }
        
        return `
        <tr class="${tieneOferta ? 'producto-en-oferta' : ''}">
            <td class="producto-emoji">
                ${contenidoImagen}
                ${tieneOferta ? '<span class="oferta-badge">🔥</span>' : ''}
            </td>
            <td>
                <div class="producto-nombre">
                    ${producto.nombre}
                    ${tieneOferta ? `<span class="badge badge-oferta">-${descuento}% OFF</span>` : ''}
                </div>
                <div class="producto-descripcion">${producto.descripcion}</div>
            </td>
            <td>
                <span class="badge badge-categoria">${producto.categoria_nombre}</span>
            </td>
            <td class="producto-precio">
                ${tieneOferta ? `
                    <div class="precio-oferta-container">
                        <span class="precio-original">$${formatearPrecio(producto.precio)}</span>
                        <span class="precio-oferta-valor">$${formatearPrecio(precioOferta)}</span>
                    </div>
                ` : `$${formatearPrecio(producto.precio)}`}
            </td>
            <td>
                <span class="badge ${producto.activo == 1 ? 'badge-active' : 'badge-inactive'}">
                    ${producto.activo == 1 ? 'Activo' : 'Inactivo'}
                </span>
            </td>
            <td>
                ${tieneOferta ? `
                    <div class="oferta-info">
                        <span class="badge badge-oferta-activa">✓ En oferta</span>
                        ${producto.fecha_fin_oferta ? `
                            <small class="oferta-fecha">Hasta: ${formatearFecha(producto.fecha_fin_oferta)}</small>
                        ` : ''}
                    </div>
                ` : '<span class="badge badge-sin-oferta">Sin oferta</span>'}
            </td>
            <td>
                <div class="action-buttons">
                    <button class="btn-icon btn-edit" onclick="editarProducto(${producto.id})" title="Editar">
                        ✏️
                    </button>
                    <button class="btn-icon btn-oferta" onclick="abrirModalOferta(${producto.id}, '${producto.nombre.replace(/'/g, "\\'")}', ${producto.precio})" title="Gestionar oferta">
                        💰
                    </button>
                    ${producto.activo == 1 
                        ? `<button class="btn-icon btn-delete" onclick="toggleEstadoProducto(${producto.id}, 0)" title="Desactivar">
                            🚫
                        </button>`
                        : `<button class="btn-icon btn-activate" onclick="toggleEstadoProducto(${producto.id}, 1)" title="Activar">
                            ✅
                        </button>`
                    }
                    <button class="btn-icon btn-delete-permanent" onclick="eliminarProducto(${producto.id})" title="Eliminar permanentemente">
                        🗑️
                    </button>
                </div>
            </td>
        </tr>
    `}).join('');
    
    // Actualizar controles de paginación
    renderizarPaginacion(totalPaginas);
    actualizarInfoPaginacion(inicio + 1, Math.min(fin, totalProductos), totalProductos);
}

function renderizarPaginacion(totalPaginas) {
    const paginacionContainer = document.getElementById('paginacion');
    if (!paginacionContainer) return;
    
    if (totalPaginas <= 1) {
        paginacionContainer.innerHTML = '';
        return;
    }
    
    let html = '<div class="paginacion-botones">';
    
    // Botón anterior
    html += `
        <button 
            class="btn-paginacion" 
            onclick="cambiarPagina(${paginaActual - 1})"
            ${paginaActual === 1 ? 'disabled' : ''}
        >
            ← Anterior
        </button>
    `;
    
    // Páginas numeradas
    const rango = 2; // Páginas a mostrar antes y después de la actual
    let inicio = Math.max(1, paginaActual - rango);
    let fin = Math.min(totalPaginas, paginaActual + rango);
    
    // Primera página
    if (inicio > 1) {
        html += `<button class="btn-paginacion" onclick="cambiarPagina(1)">1</button>`;
        if (inicio > 2) {
            html += `<span class="paginacion-puntos">...</span>`;
        }
    }
    
    // Páginas del rango
    for (let i = inicio; i <= fin; i++) {
        html += `
            <button 
                class="btn-paginacion ${i === paginaActual ? 'active' : ''}" 
                onclick="cambiarPagina(${i})"
            >
                ${i}
            </button>
        `;
    }
    
    // Última página
    if (fin < totalPaginas) {
        if (fin < totalPaginas - 1) {
            html += `<span class="paginacion-puntos">...</span>`;
        }
        html += `<button class="btn-paginacion" onclick="cambiarPagina(${totalPaginas})">${totalPaginas}</button>`;
    }
    
    // Botón siguiente
    html += `
        <button 
            class="btn-paginacion" 
            onclick="cambiarPagina(${paginaActual + 1})"
            ${paginaActual === totalPaginas ? 'disabled' : ''}
        >
            Siguiente →
        </button>
    `;
    
    html += '</div>';
    paginacionContainer.innerHTML = html;
}

function actualizarInfoPaginacion(inicio, fin, total) {
    const infoContainer = document.getElementById('infoPaginacion');
    if (infoContainer) {
        if (total === 0) {
            infoContainer.textContent = 'No hay productos';
        } else {
            infoContainer.textContent = `Mostrando ${inicio} - ${fin} de ${total} productos`;
        }
    }
}

function cambiarPagina(nuevaPagina) {
    const totalPaginas = Math.ceil(productosFiltrados.length / productosPorPagina);
    if (nuevaPagina < 1 || nuevaPagina > totalPaginas) return;
    
    paginaActual = nuevaPagina;
    renderizarProductos(productosFiltrados);
    
    // Scroll al inicio de la tabla
    document.getElementById('listaProductos').parentElement.scrollIntoView({ behavior: 'smooth', block: 'start' });
}

function filtrarProductos() {
    const busqueda = document.getElementById('searchProductos').value.toLowerCase();
    productosFiltrados = productosData.filter(producto => 
        producto.nombre.toLowerCase().includes(busqueda) ||
        producto.descripcion.toLowerCase().includes(busqueda) ||
        producto.categoria_nombre.toLowerCase().includes(busqueda)
    );
    paginaActual = 1;
    renderizarProductos(productosFiltrados);
}

function formatearFecha(fecha) {
    if (!fecha) return '';
    
    // Manejar diferentes formatos de fecha
    let d;
    if (fecha instanceof Date) {
        d = fecha;
    } else if (typeof fecha === 'string') {
        // Reemplazar espacio por 'T' para compatibilidad ISO
        const fechaISO = fecha.replace(' ', 'T');
        d = new Date(fechaISO);
    } else {
        d = new Date(fecha);
    }
    
    // Verificar si la fecha es válida
    if (isNaN(d.getTime())) {
        return '';
    }
    
    const dia = String(d.getDate()).padStart(2, '0');
    const mes = String(d.getMonth() + 1).padStart(2, '0');
    const año = d.getFullYear();
    return `${dia}/${mes}/${año}`;
}

function abrirModalProducto(id = null) {
    document.getElementById('tituloModalProducto').textContent = id ? 'Editar Producto' : 'Agregar Producto';
    document.getElementById('formProducto').reset();
    document.getElementById('productoId').value = '';
    
    // Resetear vista previa de imagen
    document.getElementById('vistaPrevia').style.display = 'none';
    document.getElementById('selectorImagen').style.display = 'block';
    document.getElementById('imagenPreview').src = '';
    
    // Cargar categorías en el select
    cargarCategoriasSelect();
    
    if (id) {
        const producto = productosData.find(p => p.id == id);
        if (producto) {
            document.getElementById('productoId').value = producto.id;
            document.getElementById('productoNombre').value = producto.nombre;
            document.getElementById('productoDescripcion').value = producto.descripcion;
            document.getElementById('productoCategoria').value = producto.categoria_id;
            document.getElementById('productoPrecio').value = producto.precio;
            
            // Manejar imagen existente
            const imagenUrl = producto.imagen_url || producto.emoji || '';
            const esImagen = imagenUrl.includes('/') || imagenUrl.includes('http') || imagenUrl.includes('assets');
            
            if (esImagen) {
                // Mostrar imagen existente
                const rutaImagen = imagenUrl.startsWith('assets') ? '../' + imagenUrl : imagenUrl;
                document.getElementById('imagenPreview').src = rutaImagen;
                document.getElementById('vistaPrevia').style.display = 'block';
                document.getElementById('selectorImagen').style.display = 'none';
                document.getElementById('productoEmoji').value = '';
            } else {
                // Mostrar emoji
                document.getElementById('productoEmoji').value = imagenUrl;
            }
        }
    }
    
    document.getElementById('modalProducto').classList.add('active');
}

function editarProducto(id) {
    abrirModalProducto(id);
}

function guardarProducto(e) {
    e.preventDefault();
    
    const id = document.getElementById('productoId').value;
    const formData = new FormData();
    formData.append('action', id ? 'actualizar' : 'crear');
    if (id) formData.append('id', id);
    formData.append('nombre', document.getElementById('productoNombre').value);
    formData.append('descripcion', document.getElementById('productoDescripcion').value);
    formData.append('categoria_id', document.getElementById('productoCategoria').value);
    formData.append('precio', document.getElementById('productoPrecio').value);
    formData.append('emoji', document.getElementById('productoEmoji').value);
    
    // Primero guardar el producto
    fetch('../api/admin/productos_crud.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const productoId = id || data.producto_id;
            
            // Si hay una imagen seleccionada, subirla
            const inputImagen = document.getElementById('productoImagen');
            if (inputImagen.files && inputImagen.files[0]) {
                subirImagenProducto(productoId, inputImagen.files[0]);
            } else {
                mostrarExito(data.message);
                cerrarModales();
                cargarProductos();
            }
        } else {
            mostrarError(data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        mostrarError('Error al guardar producto');
    });
}

function subirImagenProducto(productoId, archivo) {
    const formData = new FormData();
    formData.append('action', 'subir_imagen');
    formData.append('producto_id', productoId);
    formData.append('imagen', archivo);
    
    fetch('../api/admin/upload_imagen.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            mostrarExito('Producto e imagen guardados exitosamente');
            cerrarModales();
            cargarProductos();
        } else {
            mostrarError('Producto guardado pero error al subir imagen: ' + data.message);
            cerrarModales();
            cargarProductos();
        }
    })
    .catch(error => {
        console.error('Error:', error);
        mostrarError('Producto guardado pero error al subir imagen');
        cerrarModales();
        cargarProductos();
    });
}

function previsualizarImagen(event) {
    const file = event.target.files[0];
    if (file) {
        // Validar tamaño (5MB máximo)
        if (file.size > 5 * 1024 * 1024) {
            mostrarError('La imagen es demasiado grande. Máximo 5MB');
            event.target.value = '';
            return;
        }
        
        // Validar tipo
        const tiposPermitidos = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp', 'image/gif'];
        if (!tiposPermitidos.includes(file.type)) {
            mostrarError('Formato no permitido. Solo JPG, PNG, WEBP o GIF');
            event.target.value = '';
            return;
        }
        
        // Mostrar vista previa
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('imagenPreview').src = e.target.result;
            document.getElementById('vistaPrevia').style.display = 'block';
        };
        reader.readAsDataURL(file);
    }
}

function eliminarImagenProducto() {
    const productoId = document.getElementById('productoId').value;
    
    if (!productoId) {
        // Si es un producto nuevo, solo limpiar el preview
        document.getElementById('vistaPrevia').style.display = 'none';
        document.getElementById('productoImagen').value = '';
        document.getElementById('imagenPreview').src = '';
        return;
    }
    
    if (!confirm('¿Eliminar la imagen de este producto? Se usará un emoji por defecto.')) {
        return;
    }
    
    const formData = new FormData();
    formData.append('action', 'eliminar_imagen');
    formData.append('producto_id', productoId);
    
    fetch('../api/admin/upload_imagen.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            mostrarExito('Imagen eliminada');
            document.getElementById('vistaPrevia').style.display = 'none';
            document.getElementById('selectorImagen').style.display = 'block';
            document.getElementById('productoImagen').value = '';
            document.getElementById('imagenPreview').src = '';
        } else {
            mostrarError(data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        mostrarError('Error al eliminar imagen');
    });
}

function toggleEstadoProducto(id, nuevoEstado) {
    const mensaje = nuevoEstado == 1 
        ? '¿Activar este producto?' 
        : '¿Desactivar este producto? Los clientes no podrán verlo';
    
    if (!confirm(mensaje)) return;
    
    const formData = new FormData();
    formData.append('action', 'cambiar_estado');
    formData.append('id', id);
    formData.append('activo', nuevoEstado);
    
    fetch('../api/admin/productos_crud.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            mostrarExito(data.message);
            cargarProductos();
        } else {
            mostrarError(data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        mostrarError('Error al cambiar estado');
    });
}

function eliminarProducto(id) {
    const producto = productosData.find(p => p.id == id);
    
    if (!confirm(`⚠️ ¿ELIMINAR PERMANENTEMENTE el producto "${producto.nombre}"?\n\n❌ Esta acción NO se puede deshacer.\n\nSi solo quieres ocultarlo temporalmente, usa "Desactivar" en su lugar.`)) {
        return;
    }
    
    // Segunda confirmación para evitar eliminaciones accidentales
    if (!confirm('⚠️ ÚLTIMA CONFIRMACIÓN:\n\n¿Estás COMPLETAMENTE SEGURO de eliminar este producto?\n\nSe verificará si tiene pedidos asociados antes de eliminar.')) {
        return;
    }
    
    const formData = new FormData();
    formData.append('action', 'eliminar');
    formData.append('id', id);
    
    fetch('../api/admin/productos_crud.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            mostrarExito(data.message);
            cargarProductos();
        } else {
            mostrarError(data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        mostrarError('Error al eliminar producto');
    });
}

// ==================== CATEGORÍAS ====================
function cargarCategorias() {
    fetch('../api/admin/categorias_crud.php?action=listar')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                categoriasData = data.categorias;
                renderizarCategorias(categoriasData);
            } else {
                mostrarError('Error al cargar categorías');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            mostrarError('Error de conexión al cargar categorías');
        });
}

function renderizarCategorias(categorias) {
    const tbody = document.getElementById('listaCategorias');
    
    if (!categorias || categorias.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="5" class="empty-state">
                    <div class="empty-state-icon">📁</div>
                    <p>No hay categorías registradas</p>
                    <button class="btn-primary" onclick="abrirModalCategoria()" style="margin-top: 20px;">
                        ➕ Agregar la primera categoría
                    </button>
                </td>
            </tr>
        `;
        return;
    }
    
    tbody.innerHTML = categorias.map(categoria => `
        <tr>
            <td>
                <div class="producto-nombre">${categoria.nombre}</div>
            </td>
            <td style="text-align: center; font-weight: 600;">${categoria.orden}</td>
            <td style="text-align: center;">
                <span class="badge" style="background: #fff3cd; color: #856404;">
                    ${categoria.total_productos || 0} productos
                </span>
            </td>
            <td>
                <span class="badge ${categoria.activo == 1 ? 'badge-active' : 'badge-inactive'}">
                    ${categoria.activo == 1 ? 'Activo' : 'Inactivo'}
                </span>
            </td>
            <td>
                <div class="action-buttons">
                    <button class="btn-icon btn-edit" onclick="editarCategoria(${categoria.id})" title="Editar">
                        ✏️
                    </button>
                    ${categoria.activo == 1 
                        ? `<button class="btn-icon btn-delete" onclick="toggleEstadoCategoria(${categoria.id}, 0)" title="Desactivar">
                            🚫
                        </button>`
                        : `<button class="btn-icon btn-activate" onclick="toggleEstadoCategoria(${categoria.id}, 1)" title="Activar">
                            ✅
                        </button>`
                    }
                    <button class="btn-icon btn-delete-permanent" onclick="eliminarCategoria(${categoria.id})" title="Eliminar permanentemente">
                        🗑️
                    </button>
                </div>
            </td>
        </tr>
    `).join('');
}

function abrirModalCategoria(id = null) {
    document.getElementById('tituloModalCategoria').textContent = id ? 'Editar Categoría' : 'Agregar Categoría';
    document.getElementById('formCategoria').reset();
    document.getElementById('categoriaId').value = '';
    
    if (id) {
        const categoria = categoriasData.find(c => c.id == id);
        if (categoria) {
            document.getElementById('categoriaId').value = categoria.id;
            document.getElementById('categoriaNombre').value = categoria.nombre;
            document.getElementById('categoriaOrden').value = categoria.orden;
        }
    } else {
        // Sugerir el siguiente orden
        const maxOrden = Math.max(...categoriasData.map(c => parseInt(c.orden) || 0), 0);
        document.getElementById('categoriaOrden').value = maxOrden + 1;
    }
    
    document.getElementById('modalCategoria').classList.add('active');
}

function editarCategoria(id) {
    abrirModalCategoria(id);
}

function guardarCategoria(e) {
    e.preventDefault();
    
    const id = document.getElementById('categoriaId').value;
    const formData = new FormData();
    formData.append('action', id ? 'actualizar' : 'crear');
    if (id) formData.append('id', id);
    formData.append('nombre', document.getElementById('categoriaNombre').value);
    formData.append('orden', document.getElementById('categoriaOrden').value);
    
    fetch('../api/admin/categorias_crud.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            mostrarExito(data.message);
            cerrarModales();
            cargarCategorias();
            cargarProductos(); // Recargar para actualizar nombres de categorías
        } else {
            mostrarError(data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        mostrarError('Error al guardar categoría');
    });
}

function toggleEstadoCategoria(id, nuevoEstado) {
    const categoria = categoriasData.find(c => c.id == id);
    
    if (nuevoEstado == 0 && categoria && categoria.total_productos > 0) {
        if (!confirm(`Esta categoría tiene ${categoria.total_productos} producto(s). ¿Desactivar de todas formas? Los productos de esta categoría no se mostrarán.`)) {
            return;
        }
    }
    
    const formData = new FormData();
    formData.append('action', 'cambiar_estado');
    formData.append('id', id);
    formData.append('activo', nuevoEstado);
    
    fetch('../api/admin/categorias_crud.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            mostrarExito(data.message);
            cargarCategorias();
        } else {
            mostrarError(data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        mostrarError('Error al cambiar estado');
    });
}

function eliminarCategoria(id) {
    const categoria = categoriasData.find(c => c.id == id);
    
    if (categoria.total_productos > 0) {
        mostrarError(`❌ No se puede eliminar la categoría "${categoria.nombre}" porque tiene ${categoria.total_productos} producto(s) asociado(s).\n\nPrimero debes:\n1. Mover los productos a otra categoría, o\n2. Eliminar los productos\n\nAlternativamente, puedes DESACTIVAR la categoría en lugar de eliminarla.`);
        return;
    }
    
    if (!confirm(`⚠️ ¿ELIMINAR PERMANENTEMENTE la categoría "${categoria.nombre}"?\n\n❌ Esta acción NO se puede deshacer.\n\nSi solo quieres ocultarla temporalmente, usa "Desactivar" en su lugar.`)) {
        return;
    }
    
    // Segunda confirmación
    if (!confirm('⚠️ ÚLTIMA CONFIRMACIÓN:\n\n¿Estás COMPLETAMENTE SEGURO de eliminar esta categoría?')) {
        return;
    }
    
    const formData = new FormData();
    formData.append('action', 'eliminar');
    formData.append('id', id);
    
    fetch('../api/admin/categorias_crud.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            mostrarExito(data.message);
            cargarCategorias();
            cargarProductos(); // Recargar por si acaso
        } else {
            mostrarError(data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        mostrarError('Error al eliminar categoría');
    });
}

function filtrarCategorias() {
    const busqueda = document.getElementById('searchCategorias').value.toLowerCase();
    const categoriasFiltradas = categoriasData.filter(categoria => 
        categoria.nombre.toLowerCase().includes(busqueda)
    );
    renderizarCategorias(categoriasFiltradas);
}

// ==================== UTILIDADES ====================
function cargarCategoriasSelect() {
    fetch('../api/admin/categorias_crud.php?action=listar_activas')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const select = document.getElementById('productoCategoria');
                select.innerHTML = '<option value="">Seleccionar categoría...</option>' +
                    data.categorias.map(cat => 
                        `<option value="${cat.id}">${cat.nombre}</option>`
                    ).join('');
            }
        })
        .catch(error => {
            console.error('Error al cargar categorías:', error);
        });
}

function cerrarModales() {
    document.querySelectorAll('.modal-overlay').forEach(modal => {
        modal.classList.remove('active');
    });
}

function formatearPrecio(precio) {
    return parseFloat(precio).toLocaleString('es-CO');
}

function mostrarExito(mensaje) {
    alert('✅ ' + mensaje);
}

function mostrarError(mensaje) {
    alert('❌ ' + mensaje);
}

// ==================== GESTIÓN DE OFERTAS ====================

function abrirModalOferta(id, nombre, precio) {
    document.getElementById('oferta-producto-id').value = id;
    document.getElementById('oferta-producto-nombre').value = nombre;
    document.getElementById('oferta-precio-original').value = '$' + formatearPrecio(precio);
    document.getElementById('oferta-precio-oferta').value = '';
    document.getElementById('oferta-fecha-inicio').value = '';
    document.getElementById('oferta-fecha-fin').value = '';
    document.getElementById('descuento-info').textContent = '';
    
    // Verificar si ya tiene oferta activa
    fetch('../api/admin/ofertas_crud.php?action=listar')
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                const producto = data.productos.find(p => p.id == id);
                if (producto && producto.en_oferta == 1) {
                    document.getElementById('oferta-precio-oferta').value = producto.precio_oferta;
                    
                    // Formatear fechas si existen
                    if (producto.fecha_inicio_oferta) {
                        const fechaInicio = new Date(producto.fecha_inicio_oferta.replace(' ', 'T'));
                        if (!isNaN(fechaInicio.getTime())) {
                            document.getElementById('oferta-fecha-inicio').value = 
                                fechaInicio.toISOString().slice(0, 16);
                        }
                    }
                    if (producto.fecha_fin_oferta) {
                        const fechaFin = new Date(producto.fecha_fin_oferta.replace(' ', 'T'));
                        if (!isNaN(fechaFin.getTime())) {
                            document.getElementById('oferta-fecha-fin').value = 
                                fechaFin.toISOString().slice(0, 16);
                        }
                    }
                    
                    document.getElementById('btn-desactivar-oferta').style.display = 'inline-block';
                    calcularDescuento();
                } else {
                    document.getElementById('btn-desactivar-oferta').style.display = 'none';
                }
            }
        })
        .catch(error => {
            console.error('Error:', error);
            document.getElementById('btn-desactivar-oferta').style.display = 'none';
        });
    
    document.getElementById('modalOferta').classList.add('active');
}

function cerrarModalOferta() {
    document.getElementById('modalOferta').classList.remove('active');
}

function calcularDescuento() {
    const precioOriginalText = document.getElementById('oferta-precio-original').value;
    const precioOriginal = parseFloat(precioOriginalText.replace('$', '').replace(/\./g, '').replace(',', '.'));
    const precioOferta = parseFloat(document.getElementById('oferta-precio-oferta').value);
    const infoElement = document.getElementById('descuento-info');
    
    if (precioOferta > 0 && precioOferta < precioOriginal) {
        const descuento = Math.round(((precioOriginal - precioOferta) / precioOriginal) * 100);
        const ahorro = precioOriginal - precioOferta;
        infoElement.textContent = `✅ ${descuento}% de descuento (Ahorro: $${formatearPrecio(ahorro)})`;
        infoElement.style.color = '#27ae60';
    } else if (precioOferta >= precioOriginal) {
        infoElement.textContent = '❌ El precio de oferta debe ser menor al original';
        infoElement.style.color = '#e74c3c';
    } else {
        infoElement.textContent = '';
    }
}

function guardarOferta(event) {
    event.preventDefault();
    
    const productoId = document.getElementById('oferta-producto-id').value;
    const precioOferta = document.getElementById('oferta-precio-oferta').value;
    const fechaInicio = document.getElementById('oferta-fecha-inicio').value || null;
    const fechaFin = document.getElementById('oferta-fecha-fin').value || null;
    
    if (!precioOferta || precioOferta <= 0) {
        mostrarError('Por favor ingresa un precio de oferta válido');
        return;
    }
    
    const formData = new FormData();
    formData.append('action', 'activar');
    formData.append('producto_id', productoId);
    formData.append('precio_oferta', precioOferta);
    formData.append('fecha_inicio', fechaInicio);
    formData.append('fecha_fin', fechaFin);
    
    fetch('../api/admin/ofertas_crud.php', {
        method: 'POST',
        body: formData
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            mostrarExito(data.message);
            cerrarModalOferta();
            cargarProductos();
        } else {
            mostrarError(data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        mostrarError('Error al guardar la oferta');
    });
}

function desactivarOferta() {
    const productoId = document.getElementById('oferta-producto-id').value;
    
    if (!confirm('¿Estás seguro de desactivar esta oferta?')) {
        return;
    }
    
    const formData = new FormData();
    formData.append('action', 'desactivar');
    formData.append('producto_id', productoId);
    
    fetch('../api/admin/ofertas_crud.php', {
        method: 'POST',
        body: formData
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            mostrarExito(data.message);
            cerrarModalOferta();
            cargarProductos();
        } else {
            mostrarError(data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        mostrarError('Error al desactivar la oferta');
    });
}