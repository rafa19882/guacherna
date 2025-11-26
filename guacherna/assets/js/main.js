let cart = [];
let currentOrderForVoucher = null;
let clienteActual = null;

document.addEventListener('DOMContentLoaded', function() {
    init();
    crearContenedorToast();
});

function init() {
    cargarProductos();
    cargarPedidosAdmin();
    cargarPedidosCocina();
    actualizarEstadisticas();
    
    setInterval(() => {
        const vistaActiva = document.querySelector('.view.active').id;
        
        if (vistaActiva === 'vista-admin') {
            cargarPedidosAdmin();
            actualizarEstadisticas();
        } else if (vistaActiva === 'vista-cocina') {
            cargarPedidosCocina();
        }
    }, 5000);
}

function changeView(view) {
    document.querySelectorAll('.view').forEach(v => v.classList.remove('active'));
    document.querySelectorAll('.view-btn').forEach(b => b.classList.remove('active'));
    
    document.getElementById(`vista-${view}`).classList.add('active');
    document.getElementById(`btn-${view}`).classList.add('active');
    
    if (view === 'admin') {
        cargarPedidosAdmin();
        actualizarEstadisticas();
    }
    if (view === 'cocina') {
        cargarPedidosCocina();
    }
}

function cargarProductos() {
    fetch('api/productos.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                renderMenu(data.productos);
            }
        })
        .catch(error => {
            console.error('Error al cargar productos:', error);
        });
}

// Helper: Detectar si es una ruta de imagen o un emoji
function esRutaImagen(valor) {
    if (!valor) return false;
    // Detectar si es una URL o ruta de archivo de imagen
    const extensionesImagen = ['.jpg', '.jpeg', '.png', '.gif', '.webp', '.svg'];
    const valorMinusculas = valor.toLowerCase();
    return extensionesImagen.some(ext => valorMinusculas.includes(ext)) || 
           valorMinusculas.startsWith('http://') || 
           valorMinusculas.startsWith('https://') ||
           valorMinusculas.startsWith('/') ||
           valorMinusculas.startsWith('./');
}

// Helper: Renderizar imagen o emoji
function renderizarImagenProducto(item) {
    if (esRutaImagen(item.emoji)) {
        // Es una imagen - renderizar como <img>
        return `<img src="${item.emoji}" alt="${item.nombre}" loading="lazy" onerror="this.parentElement.classList.add('emoji-mode'); this.style.display='none'; this.parentElement.innerHTML='🍔';">`;
    } else {
        // Es un emoji - renderizar directamente y agregar clase emoji-mode
        return `<span style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%);">${item.emoji || '🍔'}</span>`;
    }
}

function renderMenu(productos) {
    const container = document.getElementById('menuContainer');
    
    if (!productos || productos.length === 0) {
        container.innerHTML = '<p style="text-align:center; color:#7f8c8d;">No hay productos disponibles</p>';
        return;
    }
    
    // Agrupar productos por categoría
    const productosPorCategoria = {};
    productos.forEach(producto => {
        const categoria = producto.categoria;
        if (!productosPorCategoria[categoria]) {
            productosPorCategoria[categoria] = [];
        }
        productosPorCategoria[categoria].push(producto);
    });
    
    // Renderizar cada categoría con sistema colapsable
    container.innerHTML = Object.keys(productosPorCategoria).map((categoria, index) => `
        <div class="categoria-section">
            <div class="categoria-header" onclick="toggleCategoria(${index})">
                <h2 class="categoria-title">
                    <span class="categoria-icon" id="icon-${index}">▼</span>
                    ${categoria}
                    <span class="categoria-count">(${productosPorCategoria[categoria].length})</span>
                </h2>
            </div>
            <div class="categoria-content" id="categoria-${index}" style="display: ${index === 0 ? 'grid' : 'none'};">
                ${productosPorCategoria[categoria].map(item => `
                    <div class="menu-item">
                        <div class="menu-item-image ${esRutaImagen(item.emoji) ? '' : 'emoji-mode'}">
                            ${renderizarImagenProducto(item)}
                        </div>
                        <div class="menu-item-content">
                            <div class="menu-item-title">${item.nombre}</div>
                            <div class="menu-item-description">${item.descripcion}</div>
                            <div class="menu-item-footer">
                                <div class="menu-item-price">$${formatearPrecio(item.precio)}</div>
                                <button class="btn-add" onclick="addToCart(${item.id}, '${item.nombre}', ${item.precio})">Agregar</button>
                            </div>
                        </div>
                    </div>
                `).join('')}
            </div>
        </div>
    `).join('');
    
    // Inicializar iconos (primera categoría expandida, resto colapsado)
    Object.keys(productosPorCategoria).forEach((_, index) => {
        const icon = document.getElementById(`icon-${index}`);
        if (icon) {
            icon.textContent = index === 0 ? '▼' : '►';
        }
    });
}

function toggleCategoria(index) {
    const content = document.getElementById(`categoria-${index}`);
    const icon = document.getElementById(`icon-${index}`);
    
    if (content.style.display === 'none' || content.style.display === '') {
        // Expandir
        content.style.display = 'grid';
        icon.textContent = '▼';
        
    } else {
        // Colapsar
        content.style.display = 'none';
        icon.textContent = '►';
        
    }
}

function addToCart(id, nombre, precio) {
    const existingItem = cart.find(i => i.id === id);
    
    if (existingItem) {
        existingItem.cantidad++;
    } else {
        cart.push({ id, nombre, precio, cantidad: 1 });
    }
    
    updateCartBadge();
    mostrarToast(`${nombre} agregado`, 'success');
}

function updateCartBadge() {
    const totalItems = cart.reduce((sum, item) => sum + item.cantidad, 0);
    document.getElementById('cartBadge').textContent = totalItems;
}

// ==================== CARRITO MEJORADO CON EDICIÓN ====================
function verCarrito() {
    if (cart.length === 0) {
        mostrarToast('Tu carrito está vacío', 'info');
        return;
    }
    
    renderizarCarrito();
    document.getElementById('modalCarrito').classList.add('active');
}

function renderizarCarrito() {
    const content = document.getElementById('carritoContent');
    const total = cart.reduce((sum, item) => sum + (item.precio * item.cantidad), 0);
    
    content.innerHTML = `
        <div style="max-height: 400px; overflow-y: auto;">
            ${cart.map((item, index) => `
                <div class="voucher-item" style="padding: 15px 0; border-bottom: 1px solid #e0e0e0; display: flex; align-items: center; gap: 10px;">
                    <div style="flex: 1;">
                        <div style="font-weight: 600; color: #2c3e50; margin-bottom: 5px;">${item.nombre}</div>
                        <div style="color: #27ae60; font-weight: 600;">$${formatearPrecio(item.precio * item.cantidad)}</div>
                    </div>
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <button 
                            onclick="modificarCantidad(${index}, -1)" 
                            style="width: 35px; height: 35px; border: none; background: #e74c3c; color: white; border-radius: 5px; cursor: pointer; font-size: 18px; font-weight: bold; display: flex; align-items: center; justify-content: center;"
                            title="Disminuir cantidad"
                        >−</button>
                        <div style="min-width: 40px; text-align: center; font-weight: 700; font-size: 18px; color: #2c3e50;">${item.cantidad}</div>
                        <button 
                            onclick="modificarCantidad(${index}, 1)" 
                            style="width: 35px; height: 35px; border: none; background: #27ae60; color: white; border-radius: 5px; cursor: pointer; font-size: 18px; font-weight: bold; display: flex; align-items: center; justify-content: center;"
                            title="Aumentar cantidad"
                        >+</button>
                        <button 
                            onclick="eliminarDelCarrito(${index})" 
                            style="width: 35px; height: 35px; border: none; background: #95a5a6; color: white; border-radius: 5px; cursor: pointer; font-size: 18px; display: flex; align-items: center; justify-content: center; margin-left: 5px;"
                            title="Eliminar producto"
                        >🗑️</button>
                    </div>
                </div>
            `).join('')}
        </div>
        <div class="voucher-total" style="margin-top: 20px; padding-top: 20px; border-top: 2px solid #2c3e50;">
            <span style="font-size: 20px;">TOTAL:</span>
            <span style="font-size: 24px; color: #27ae60;">$${formatearPrecio(total)}</span>
        </div>
        ${cart.length > 0 ? `
            <div style="text-align: center; margin-top: 15px;">
                <button 
                    onclick="vaciarCarrito()" 
                    style="background: #e74c3c; color: white; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer; font-weight: 600;"
                >
                    🗑️ Vaciar Carrito
                </button>
            </div>
        ` : ''}
    `;
}

function modificarCantidad(index, cambio) {
    if (cart[index]) {
        cart[index].cantidad += cambio;
        
        if (cart[index].cantidad <= 0) {
            cart.splice(index, 1);
        }
        
        updateCartBadge();
        
        if (cart.length === 0) {
            cerrarModal();
            mostrarToast('Carrito vacío', 'info');
        } else {
            renderizarCarrito();
        }
    }
}

function eliminarDelCarrito(index) {
    if (confirm(`¿Eliminar "${cart[index].nombre}" del carrito?`)) {
        cart.splice(index, 1);
        updateCartBadge();
        
        if (cart.length === 0) {
            cerrarModal();
            mostrarToast('Carrito vacío', 'info');
        } else {
            renderizarCarrito();
        }
    }
}

function vaciarCarrito() {
    if (confirm('¿Estás seguro de vaciar todo el carrito?')) {
        cart = [];
        updateCartBadge();
        cerrarModal();
        mostrarToast('Carrito vaciado', 'info');
    }
}
// ==================== FIN CARRITO MEJORADO ====================

function confirmarPedido() {
    cerrarModal();
    
    document.getElementById('input-telefono').value = '';
    document.getElementById('paso1-telefono').style.display = 'block';
    document.getElementById('paso2-registro').style.display = 'none';
    document.getElementById('paso3-reconocido').style.display = 'none';
    document.getElementById('paso4-editar-direccion').style.display = 'none';
    
    document.getElementById('modalIdentificacion').classList.add('active');
}

function verificarCliente() {
    const telefono = document.getElementById('input-telefono').value.trim();
    
    if (!telefono) {
        mostrarToast('Ingresa tu número de teléfono', 'warning');
        return;
    }

    const telefonoLimpio = telefono.replace(/[^0-9]/g, '');
    
    if (telefonoLimpio.length < 10) {
        mostrarToast('Número debe tener al menos 10 dígitos', 'warning');
        return;
    }

    const formData = new FormData();
    formData.append('action', 'verificar');
    formData.append('telefono', telefonoLimpio);

    fetch('api/clientes.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            if (data.existe) {
                mostrarClienteReconocido(data.cliente);
            } else {
                mostrarFormularioRegistro(telefonoLimpio);
            }
        }
    })
    .catch(error => {
        console.error('Error:', error);
        mostrarToast('Error al verificar cliente', 'error');
    });
}

// ==================== CLIENTE RECONOCIDO CON OPCIÓN DE EDITAR DIRECCIÓN ====================
function mostrarClienteReconocido(cliente) {
    clienteActual = cliente;
    
    document.getElementById('paso1-telefono').style.display = 'none';
    document.getElementById('paso3-reconocido').style.display = 'block';
    
    document.getElementById('cliente-nombre-reconocido').textContent = cliente.nombre.split(' ')[0];
    
    const infoCliente = document.getElementById('info-cliente-reconocido');
    infoCliente.innerHTML = `
        <div style="margin-bottom:10px;">
            <strong style="color:#666;">📱 Teléfono:</strong> ${cliente.telefono}
        </div>
        <div style="margin-bottom:10px;">
            <strong style="color:#666;">📍 Dirección:</strong><br>
            ${cliente.direccion} ${cliente.numero_casa}
        </div>
        <div style="text-align: center; margin-top: 15px;">
            <button 
                onclick="mostrarEdicionDireccion()" 
                style="background: #3498db; color: white; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer; font-weight: 600;"
            >
                📍 Cambiar Dirección de Entrega
            </button>
        </div>
    `;
}

function mostrarEdicionDireccion() {
    document.getElementById('paso3-reconocido').style.display = 'none';
    document.getElementById('paso4-editar-direccion').style.display = 'block';
    
    // Pre-llenar con la dirección actual
    document.getElementById('input-direccion-edit').value = clienteActual.direccion;
    document.getElementById('input-numero-edit').value = clienteActual.numero_casa;
}

function usarDireccionOriginal() {
    // Volver a mostrar la pantalla de reconocido
    document.getElementById('paso4-editar-direccion').style.display = 'none';
    document.getElementById('paso3-reconocido').style.display = 'block';
}

function guardarNuevaDireccion() {
    const nuevaDireccion = document.getElementById('input-direccion-edit').value.trim();
    const nuevoNumero = document.getElementById('input-numero-edit').value.trim();
    
    if (!nuevaDireccion) {
        mostrarToast('La dirección es obligatoria', 'warning');
        return;
    }
    
    if (!nuevoNumero) {
        mostrarToast('El número de casa/apto es obligatorio', 'warning');
        return;
    }
    
    // Actualizar temporalmente para este pedido
    clienteActual.direccion = nuevaDireccion;
    clienteActual.numero_casa = nuevoNumero;
    clienteActual.direccion_temporal = true; // Marcar que es temporal
    
    mostrarToast('Dirección actualizada para este pedido', 'success');
    finalizarPedido();
}
// ==================== FIN CLIENTE RECONOCIDO ====================

function mostrarFormularioRegistro(telefono) {
    document.getElementById('paso1-telefono').style.display = 'none';
    document.getElementById('paso2-registro').style.display = 'block';
    
    clienteActual = { telefono: telefono };
}

function registrarCliente() {
    const nombre = document.getElementById('input-nombre').value.trim();
    const direccion = document.getElementById('input-direccion').value.trim();
    const numero = document.getElementById('input-numero').value.trim();
    const fechaNac = document.getElementById('input-fecha-nac').value;

    if (!nombre) {
        mostrarToast('El nombre es obligatorio', 'warning');
        return;
    }

    if (!direccion) {
        mostrarToast('La dirección es obligatoria', 'warning');
        return;
    }

    if (!numero) {
        mostrarToast('El número de casa/apto es obligatorio', 'warning');
        return;
    }

    const formData = new FormData();
    formData.append('action', 'registrar');
    formData.append('telefono', clienteActual.telefono);
    formData.append('nombre', nombre);
    formData.append('direccion', direccion);
    formData.append('numero_casa', numero);
    if (fechaNac) formData.append('fecha_nacimiento', fechaNac);

    fetch('api/clientes.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            clienteActual.id = data.cliente_id;
            clienteActual.nombre = nombre;
            finalizarPedido();
        } else {
            mostrarToast(data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        mostrarToast('Error al registrar cliente', 'error');
    });
}

function finalizarPedidoClienteReconocido() {
    finalizarPedido();
}

function finalizarPedido() {
    // Capturar método de pago seleccionado
    const metodoPagoSeleccionado = document.querySelector('input[name="metodo_pago"]:checked');
    const metodoPago = metodoPagoSeleccionado ? metodoPagoSeleccionado.value : 'nequi';
    
    const formData = new FormData();
    formData.append('action', 'crear');
    formData.append('cliente_id', clienteActual.id);
    formData.append('items', JSON.stringify(cart));
    formData.append('metodo_pago', metodoPago);
    
    // Enviar dirección de entrega (temporal o registrada)
    formData.append('direccion_entrega', clienteActual.direccion);
    formData.append('numero_entrega', clienteActual.numero_casa);

    fetch('api/pedidos.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            mostrarToast(`¡Pedido #${data.pedido_id} creado exitosamente! Total: $${formatearPrecio(data.total)}`, 'success');
            cart = [];
            updateCartBadge();
            cerrarModal();
            cargarPedidosAdmin();
            cargarPedidosCocina();
        } else {
            mostrarToast(data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        mostrarToast('Error al crear pedido', 'error');
    });
}

function cargarPedidosAdmin() {
    fetch('api/pedidos.php?action=obtener_admin')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                renderOrders(data.pedidos);
            }
        })
        .catch(error => {
            console.error('Error:', error);
        });
}

function renderOrders(pedidos) {
    const container = document.getElementById('ordersContainer');
    
    if (!pedidos || pedidos.length === 0) {
        container.innerHTML = '<p style="text-align:center; color:#7f8c8d;">No hay pedidos activos</p>';
        return;
    }
    
    container.innerHTML = pedidos.map(order => `
        <div class="order-card ${order.estado}">
            <div class="order-header">
                <div>
                    <div class="order-number">Pedido #${order.id}</div>
                    <small style="color:#7f8c8d">
                        ${order.cliente} | ${order.telefono} | ${order.hora}
                        <br>
                        📍 ${order.direccion_entrega || 'Sin dirección'} ${order.numero_entrega || ''}
                    </small>
                </div>
                <span class="order-status ${order.estado}">${order.estado}</span>
            </div>
            <div class="order-items">
                ${order.items.map(item => `
                    <div class="order-item">
                        <span>${item.cantidad}x ${item.nombre}</span>
                        <span>$${formatearPrecio(item.precio)}</span>
                    </div>
                `).join('')}
            </div>
            <div class="order-footer">
                <div class="order-total">$${formatearPrecio(order.total)}</div>
                <div class="order-actions">
                    ${getOrderActions(order)}
                </div>
            </div>
        </div>
    `).join('');
}

// ==================== ACCIONES MEJORADAS CON BOTÓN PAGADO ====================
function getOrderActions(order) {
    switch(order.estado) {
        case 'pendiente':
            return `<button class="btn-preparar" onclick="cambiarEstado(${order.id}, 'preparando')">En Preparación</button>`;
        case 'preparando':
            return `<button class="btn-listo" onclick="cambiarEstado(${order.id}, 'listo')">Marcar Listo</button>`;
        case 'listo':
            return `
                <button class="btn-notificar" onclick="mostrarVoucher(${order.id})">📱 Notificar Cliente</button>
                <button class="btn-pagado" onclick="cambiarEstado(${order.id}, 'pagado')">💰 Marcar Pagado</button>
            `;
        case 'pagado':
            return `<button class="btn-entregar" onclick="cambiarEstado(${order.id}, 'entregado')">✅ Marcar Entregado</button>`;
        default:
            return '';
    }
}
// ==================== FIN ACCIONES MEJORADAS ====================

function cambiarEstado(pedidoId, nuevoEstado) {
    const formData = new FormData();
    formData.append('action', 'cambiar_estado');
    formData.append('pedido_id', pedidoId);
    formData.append('estado', nuevoEstado);

    fetch('api/pedidos.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            let mensaje = '';
            switch(nuevoEstado) {
                case 'preparando':
                    mensaje = `Pedido #${pedidoId} en preparación`;
                    break;
                case 'listo':
                    mensaje = `Pedido #${pedidoId} listo`;
                    break;
                case 'pagado':
                    mensaje = `Pedido #${pedidoId} pagado`;
                    break;
                case 'entregado':
                    mensaje = `Pedido #${pedidoId} entregado`;
                    break;
                default:
                    mensaje = `Estado actualizado`;
            }
            mostrarToast(mensaje, 'success');
            cargarPedidosAdmin();
            cargarPedidosCocina();
            actualizarEstadisticas();
        }
    })
    .catch(error => {
        console.error('Error:', error);
        mostrarToast('Error al cambiar estado', 'error');
    });
}

function mostrarVoucher(pedidoId) {
    fetch(`api/pedidos.php?action=obtener_detalle&pedido_id=${pedidoId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const order = data.pedido;
                currentOrderForVoucher = order;
                
                document.getElementById('voucherOrderNumber').textContent = order.id;
                
                // Formatear fecha y hora
                const fechaPedido = order.fecha_hora ? new Date(order.fecha_hora) : new Date();
                const fechaFormateada = fechaPedido.toLocaleDateString('es-CO', { 
                    weekday: 'long', 
                    year: 'numeric', 
                    month: 'long', 
                    day: 'numeric' 
                });
                const horaFormateada = fechaPedido.toLocaleTimeString('es-CO', { 
                    hour: '2-digit', 
                    minute: '2-digit' 
                });
                
                // Determinar emoji y texto del método de pago
                const metodoPagoInfo = order.metodo_pago === 'efectivo' 
                    ? { emoji: '💵', texto: 'Efectivo (Contraentrega)' }
                    : { emoji: '📱', texto: 'Nequi: 300-123-4567' };
                
                const content = document.getElementById('voucherContent');
                content.innerHTML = `
                    <div style="background:#e3f2fd; padding:12px; border-radius:8px; margin-bottom:15px; text-align:center;">
                        <div style="font-size:14px; color:#1976d2; font-weight:600;">
                            📅 ${fechaFormateada}
                        </div>
                        <div style="font-size:16px; color:#0d47a1; font-weight:700; margin-top:5px;">
                            🕐 ${horaFormateada}
                        </div>
                    </div>
                    
                    <div style="margin-bottom:15px; padding-bottom:15px; border-bottom:2px dashed #e0e0e0;">
                        <strong>👤 Cliente:</strong> ${order.cliente}<br>
                        <strong>📞 Teléfono:</strong> ${order.telefono}<br>
                        <strong>📍 Entregar en:</strong><br>
                        <div style="padding-left:20px; color:#555;">
                            ${order.direccion_entrega || 'Sin dirección'} ${order.numero_entrega || ''}
                        </div>
                    </div>
                    
                    <div style="background:#fff3cd; padding:10px; border-radius:8px; margin-bottom:15px; text-align:center;">
                        <strong style="color:#856404;">${metodoPagoInfo.emoji} Método de Pago:</strong><br>
                        <span style="color:#856404; font-weight:600;">${metodoPagoInfo.texto}</span>
                    </div>
                    
                    ${order.items.map(item => `
                        <div class="voucher-item">
                            <span>${item.cantidad}x ${item.nombre}</span>
                            <strong>$${formatearPrecio(item.precio)}</strong>
                        </div>
                    `).join('')}
                    <div class="voucher-total">
                        <span>TOTAL A PAGAR:</span>
                        <span>$${formatearPrecio(order.total)}</span>
                    </div>
                `;
                
                document.getElementById('modalVoucher').classList.add('active');
            }
        })
        .catch(error => {
            console.error('Error:', error);
        });
}

function enviarNotificacion() {
    if (currentOrderForVoucher) {
        const order = currentOrderForVoucher;
        
        // Formatear fecha y hora
        const fechaPedido = order.fecha_hora ? new Date(order.fecha_hora) : new Date();
        const fechaFormateada = fechaPedido.toLocaleDateString('es-CO');
        const horaFormateada = fechaPedido.toLocaleTimeString('es-CO', { 
            hour: '2-digit', 
            minute: '2-digit' 
        });
        
        // Construir lista de productos
        const listaProductos = order.items.map(item => 
            `• ${item.cantidad}x ${item.nombre} - $${formatearPrecio(item.precio)}`
        ).join('\n');
        
        // Determinar instrucciones de pago
        const instruccionesPago = order.metodo_pago === 'efectivo'
            ? '*Pago en Efectivo* (contraentrega)\nTen el monto exacto listo para agilizar la entrega.'
            : '*Pago por Nequi*\nNúmero: 3117570862\nEnvía tu comprobante de pago.';
        
        // Construir mensaje completo para WhatsApp
        const mensaje = `*GUACHERNA BURGERS*
¡_Tu Pedido está LISTO_!
============================

*Pedido #${order.id}*
${fechaFormateada} • ${horaFormateada}

*Cliente:* ${order.cliente}
*Dirección:* ${order.direccion_entrega} ${order.numero_entrega}

*PRODUCTOS:*
${listaProductos}

============================
*TOTAL: $${formatearPrecio(order.total)}*
============================

${instruccionesPago}

¡Gracias por tu compra!, _Guacherna Burgers_`;

        // Limpiar teléfono (remover caracteres especiales)
        const telefonoLimpio = order.telefono.replace(/[^0-9]/g, '');
        
        // Codificar mensaje para URL - PRESERVANDO EMOJIS
        const mensajeCodificado = codificarMensajeWhatsApp(mensaje);
        
        // Crear URL de WhatsApp
        const whatsappURL = `https://wa.me/57${telefonoLimpio}?text=${mensajeCodificado}`;
        
        // Abrir WhatsApp en nueva pestaña
        window.open(whatsappURL, '_blank');
        
        mostrarToast('Abriendo WhatsApp...', 'success');
        cerrarModal();
    }
}

function cargarPedidosCocina() {
    fetch('api/pedidos.php?action=obtener_cocina')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                renderKitchen(data.pedidos);
            }
        })
        .catch(error => {
            console.error('Error:', error);
        });
}

function renderKitchen(pedidos) {
    const grid = document.getElementById('kitchenGrid');
    
    if (!pedidos || pedidos.length === 0) {
        grid.innerHTML = '<p style="text-align:center; color:#7f8c8d; grid-column:1/-1;">No hay pedidos pendientes en cocina</p>';
        return;
    }
    
    grid.innerHTML = pedidos.map(order => `
        <div class="kitchen-order ${order.estado === 'pendiente' ? 'urgent' : ''}">
            <div class="kitchen-order-header">
                <div class="kitchen-order-number">#${order.id}</div>
                <div class="kitchen-time">${order.hora}</div>
            </div>
            <div class="kitchen-items">
                ${order.items.map(item => `
                    <div class="kitchen-item">
                        ${item.cantidad}x ${item.nombre}
                    </div>
                `).join('')}
            </div>
            ${order.estado === 'preparando' ? `
                <button class="btn-marcar-listo" onclick="cambiarEstado(${order.id}, 'listo')">
                    ✅ MARCAR COMO LISTO
                </button>
            ` : `
                <button class="btn-marcar-listo" style="background:#f39c12" onclick="cambiarEstado(${order.id}, 'preparando')">
                    👨‍🍳 COMENZAR A PREPARAR
                </button>
            `}
        </div>
    `).join('');
}

function actualizarEstadisticas() {
    fetch('api/pedidos.php?action=estadisticas')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const stats = data.estadisticas;
                document.getElementById('statPendientes').textContent = stats.pendientes;
                document.getElementById('statPreparando').textContent = stats.preparando;
                document.getElementById('statListos').textContent = stats.listos;
                document.getElementById('statHoy').textContent = '$' + formatearPrecio(stats.total_hoy);
            }
        })
        .catch(error => {
            console.error('Error:', error);
        });
}

function cerrarModal() {
    document.querySelectorAll('.modal').forEach(m => m.classList.remove('active'));
}

document.querySelectorAll('.modal').forEach(modal => {
    modal.addEventListener('click', function(e) {
        if (e.target === this) cerrarModal();
    });
});

function formatearPrecio(precio) {
    return parseFloat(precio).toLocaleString('es-CO');
}

// ==================== CODIFICACIÓN WHATSAPP CON EMOJIS ====================
function codificarMensajeWhatsApp(mensaje) {
    return mensaje
        .split('')
        .map(char => {
            // Si es emoji o carácter especial unicode (> 127), preservarlo
            if (char.charCodeAt(0) > 127) {
                return char;
            }
            // Codificar solo caracteres básicos que lo necesitan
            switch (char) {
                case '\n': return '%0A';
                case ' ': return '%20';
                case '*': return '%2A';
                case '_': return '%5F';
                case '~': return '%7E';
                case '#': return '%23';
                case '&': return '%26';
                case '=': return '%3D';
                case '?': return '%3F';
                default: 
                    // Si es carácter alfanumérico o permitido, dejarlo
                    if (/[a-zA-Z0-9\-_.!()$]/.test(char)) {
                        return char;
                    }
                    return encodeURIComponent(char);
            }
        })
        .join('');
}
// ==================== FIN CODIFICACIÓN WHATSAPP ====================


// ==================== SISTEMA DE NOTIFICACIONES TOAST ====================
function crearContenedorToast() {
    if (!document.getElementById('toast-container')) {
        const container = document.createElement('div');
        container.id = 'toast-container';
        container.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
            display: flex;
            flex-direction: column;
            gap: 10px;
        `;
        document.body.appendChild(container);
    }
}

function mostrarToast(mensaje, tipo = 'info') {
    const container = document.getElementById('toast-container');
    const toast = document.createElement('div');
    
    const colores = {
        success: { bg: '#27ae60', icon: '✅' },
        error: { bg: '#e74c3c', icon: '❌' },
        warning: { bg: '#f39c12', icon: '⚠️' },
        info: { bg: '#3498db', icon: 'ℹ️' }
    };
    
    const config = colores[tipo] || colores.info;
    
    toast.style.cssText = `
        background: ${config.bg};
        color: white;
        padding: 15px 20px;
        border-radius: 8px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.3);
        font-weight: 600;
        min-width: 250px;
        max-width: 400px;
        animation: slideIn 0.3s ease-out;
        display: flex;
        align-items: center;
        gap: 10px;
    `;
    
    toast.innerHTML = `
        <span style="font-size: 20px;">${config.icon}</span>
        <span style="flex: 1;">${mensaje}</span>
    `;
    
    container.appendChild(toast);
    
    setTimeout(() => {
        toast.style.animation = 'slideOut 0.3s ease-in';
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}

// Agregar animaciones CSS
const style = document.createElement('style');
style.textContent = `
    @keyframes slideIn {
        from {
            transform: translateX(400px);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
    
    @keyframes slideOut {
        from {
            transform: translateX(0);
            opacity: 1;
        }
        to {
            transform: translateX(400px);
            opacity: 0;
        }
    }
`;
document.head.appendChild(style);
// ==================== FIN SISTEMA DE NOTIFICACIONES ====================