// ========================================
// VARIABLES GLOBALES
// ========================================

let cart = [];
let allProducts = [];
let categoriesData = {};
let clienteActual = null;
let currentFilter = 'all';

// Emojis para categorías (mapeo por ID)
const categoryEmojis = {
    1: '🍟',    // Entradas
    2: '🍔',    // Burgers
    3: '🥩',    // Asados
    4: '🥪',    // Sandwichs
    5: '🍟',    // Salchipapas
    6: '🌽',    // Mazorcas
    7: '🥡',    // Chuzos Desgranados
    8: '🌭',    // Perros
    9: '🍴',    // Adicionales
    10: '🥤'    // Bebidas
};

// ========================================
// INICIALIZACIÓN
// ========================================

document.addEventListener('DOMContentLoaded', function() {
    init();
});

function init() {
    cargarProductos();
    setupEventListeners();
    updateCartDisplay();
}

function setupEventListeners() {
    // Búsqueda
    const searchInput = document.getElementById('searchInput');
    if (searchInput) {
        searchInput.addEventListener('input', handleSearch);
    }

    // Filtros
    const filterChips = document.querySelectorAll('.filter-chip');
    filterChips.forEach(chip => {
        chip.addEventListener('click', () => {
            const filter = chip.dataset.filter;
            setActiveFilter(filter);
        });
    });

    // Carrito flotante
    const cartBtn = document.getElementById('cartFloatBtn');
    if (cartBtn) {
        cartBtn.addEventListener('click', abrirCarrito);
    }

    // Forms
    setupFormHandlers();
}

function setupFormHandlers() {
    // Form teléfono
    const formTelefono = document.getElementById('form-telefono');
    if (formTelefono) {
        formTelefono.addEventListener('submit', (e) => {
            e.preventDefault();
            verificarCliente();
        });
    }

    // Form registro
    const formRegistro = document.getElementById('form-registro');
    if (formRegistro) {
        formRegistro.addEventListener('submit', (e) => {
            e.preventDefault();
            registrarCliente();
        });
    }

    // Form editar dirección
    const formEditDir = document.getElementById('form-editar-direccion');
    if (formEditDir) {
        formEditDir.addEventListener('submit', (e) => {
            e.preventDefault();
            guardarNuevaDireccion();
        });
    }
}

// ========================================
// CARGAR PRODUCTOS
// ========================================

function cargarProductos() {
    fetch('api/productos.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                allProducts = data.productos;
                procesarProductos(allProducts);
                renderCategorias();
                renderPopulares();
                renderTodasCategorias();
            }
        })
        .catch(error => {
            console.error('Error al cargar productos:', error);
            mostrarToast('Error al cargar el menú', 'error');
        });
}

function procesarProductos(productos) {
    categoriesData = {};
    
    productos.forEach(producto => {
        const categoria = producto.categoria;
        if (!categoriesData[categoria]) {
            categoriesData[categoria] = [];
        }
        categoriesData[categoria].push(producto);
    });
}

// ========================================
// RENDERIZAR CARRUSEL DE CATEGORÍAS
// ========================================

function renderCategorias() {
    const container = document.getElementById('categoriesCarousel');
    if (!container) return;

    const categoriasArray = Object.keys(categoriesData);
    
    container.innerHTML = categoriasArray.map((categoria, index) => {
        const productos = categoriesData[categoria];
        const emoji = getEmojiForCategory(categoria, productos[0]);
        
        return `
            <div class="category-card" onclick="seleccionarCategoria('${categoria}')">
                <div class="category-content">
                    <div class="category-emoji">${emoji}</div>
                    <div class="category-name">${categoria}</div>
                    <div class="category-count">${productos.length}</div>
                </div>
            </div>
        `;
    }).join('');
}

function getEmojiForCategory(categoryName, firstProduct) {
    // Intentar obtener emoji del primer producto
    if (firstProduct && firstProduct.emoji && !esRutaImagen(firstProduct.emoji)) {
        return firstProduct.emoji;
    }
    
    // Fallback basado en el nombre
    const name = categoryName.toLowerCase();
    if (name.includes('entrada')) return '🍟';
    if (name.includes('burger')) return '🍔';
    if (name.includes('asado')) return '🥩';
    if (name.includes('sandwich')) return '🥪';
    if (name.includes('salchipapa')) return '🍟';
    if (name.includes('mazorca')) return '🌽';
    if (name.includes('chuzo')) return '🥡';
    if (name.includes('perro')) return '🌭';
    if (name.includes('adicional')) return '🍴';
    if (name.includes('bebida')) return '🥤';
    
    return '🍽️';
}

function seleccionarCategoria(categoria) {
    const section = document.getElementById('productsSection');
    if (!section) return;

    const productos = categoriesData[categoria];
    
    // Mostrar la sección
    section.style.display = 'block';
    
    // Renderizar productos
    section.innerHTML = `
        <h2 class="section-title">${categoria}</h2>
        <div class="products-grid">
            ${productos.map(producto => renderProductCard(producto)).join('')}
        </div>
    `;

    // Scroll suave a la sección
    section.scrollIntoView({ behavior: 'smooth', block: 'start' });
}

// ========================================
// RENDERIZAR POPULARES
// ========================================

function renderPopulares() {
    const container = document.getElementById('popularGrid');
    if (!container) return;

    // Obtener productos populares reales del servidor
    fetch('api/productos.php?action=populares')
        .then(response => response.json())
        .then(data => {
            if (data.success && data.productos.length > 0) {
                const populares = data.productos.slice(0, 6);
                container.innerHTML = populares.map(producto => 
                    renderProductCard(producto, true)
                ).join('');
            } else {
                // Fallback: mostrar primeros 6 productos si no hay populares
                const fallback = allProducts.slice(0, 6);
                container.innerHTML = fallback.map(producto => 
                    renderProductCard(producto, true)
                ).join('');
            }
        })
        .catch(error => {
            console.error('Error al cargar populares:', error);
            // Fallback en caso de error
            const fallback = allProducts.slice(0, 6);
            container.innerHTML = fallback.map(producto => 
                renderProductCard(producto, true)
            ).join('');
        });
}


// ========================================
// RENDERIZAR OFERTAS
// ========================================

function renderOfertas() {
    const container = document.getElementById('popularGrid');
    if (!container) return;

    // Obtener productos en oferta del servidor
    fetch('api/productos.php?action=ofertas')
        .then(response => response.json())
        .then(data => {
            if (data.success && data.productos.length > 0) {
                const ofertas = data.productos.slice(0, 6);
                container.innerHTML = ofertas.map(producto => 
                    renderProductCard(producto, false, true)
                ).join('');
            } else {
                // Si no hay ofertas
                container.innerHTML = `
                    <div style="grid-column: 1/-1; text-align: center; padding: 60px 20px; color: #7f8c8d;">
                        <div style="font-size: 64px; margin-bottom: 20px;">🎉</div>
                        <h3 style="margin: 0 0 10px 0; color: #2c3e50;">No hay ofertas activas</h3>
                        <p style="margin: 0;">Vuelve pronto para ver nuestras próximas promociones</p>
                    </div>
                `;
            }
        })
        .catch(error => {
            console.error('Error al cargar ofertas:', error);
            container.innerHTML = `
                <div style="grid-column: 1/-1; text-align: center; padding: 40px; color: #e74c3c;">
                    Error al cargar las ofertas. Por favor intenta de nuevo.
                </div>
            `;
        });
}

// ========================================
// RENDERIZAR TODAS LAS CATEGORÍAS
// ========================================

function renderTodasCategorias() {
    const container = document.getElementById('allCategoriesSection');
    if (!container) return;

    const categoriasArray = Object.keys(categoriesData);
    
    container.innerHTML = categoriasArray.map(categoria => {
        const productos = categoriesData[categoria];
        const emoji = getEmojiForCategory(categoria, productos[0]);
        
        return `
            <div class="category-group">
                <div class="category-group-header">
                    <h3 class="category-group-title">
                        <span class="category-group-icon">${emoji}</span>
                        ${categoria}
                    </h3>
                    <span class="category-group-count">${productos.length} items</span>
                </div>
                <div class="products-grid">
                    ${productos.map(producto => renderProductCard(producto)).join('')}
                </div>
            </div>
        `;
    }).join('');
}

// ========================================
// RENDERIZAR CARD DE PRODUCTO
// ========================================

function renderProductCard(producto, showBadge = false, esOferta = false) {
    const imagenContent = esRutaImagen(producto.emoji)
        ? `<img src="${producto.emoji}" alt="${producto.nombre}" class="product-image" onerror="this.style.display='none'">`
        : `<div class="product-emoji">${producto.emoji || '🍔'}</div>`;

    // Calcular precio a mostrar
    const precioOriginal = parseFloat(producto.precio);
    let precioFinal = precioOriginal;
    let enOferta = false;
    let porcentajeDescuento = 0;
    
    if (esOferta && producto.en_oferta == 1 && producto.precio_oferta) {
        precioFinal = parseFloat(producto.precio_oferta);
        porcentajeDescuento = Math.round(((precioOriginal - precioFinal) / precioOriginal) * 100);
        enOferta = true;
    }

    return `
        <div class="product-card" onclick="agregarAlCarrito(${producto.id}, '${escapeHtml(producto.nombre)}', ${precioFinal}, '${escapeHtml(producto.emoji || '🍔')}')">
            <div class="product-image-container">
                ${imagenContent}
                ${showBadge && !enOferta ? '<div class="product-badge">Popular</div>' : ''}
                ${enOferta ? `<div class="product-badge-oferta">-${porcentajeDescuento}%</div>` : ''}
            </div>
            <div class="product-info">
                <div class="product-name">${producto.nombre}</div>
                <div class="product-description">${producto.descripcion || ''}</div>
                <div class="product-footer">
                    ${enOferta 
                        ? `<div class="product-price-oferta">
                               <span class="precio-original">$${formatearPrecio(precioOriginal)}</span>
                               <span class="precio-oferta">$${formatearPrecio(precioFinal)}</span>
                           </div>`
                        : `<div class="product-price">$${formatearPrecio(precioFinal)}</div>`
                    }
                    <button class="btn-add-product" onclick="event.stopPropagation(); agregarAlCarrito(${producto.id}, '${escapeHtml(producto.nombre)}', ${precioFinal}, '${escapeHtml(producto.emoji || '🍔')}')">
                        +
                    </button>
                </div>
            </div>
        </div>
    `;
}


// ========================================
// BÚSQUEDA
// ========================================

function handleSearch(e) {
    const searchTerm = e.target.value.toLowerCase().trim();
    
    const popularSection = document.getElementById('popularSection');
    const productsSection = document.getElementById('productsSection');
    const allCategoriesSection = document.getElementById('allCategoriesSection');
    
    // Si no hay búsqueda, mostrar vista normal
    if (searchTerm === '') {
        if (popularSection) popularSection.style.display = 'block';
        if (productsSection) productsSection.style.display = 'none';
        if (allCategoriesSection) allCategoriesSection.style.display = 'block';
        renderTodasCategorias();
        return;
    }

    // Ocultar secciones normales durante búsqueda
    if (popularSection) popularSection.style.display = 'none';
    if (productsSection) productsSection.style.display = 'none';

    const resultados = allProducts.filter(producto => 
        producto.nombre.toLowerCase().includes(searchTerm) ||
        (producto.descripcion && producto.descripcion.toLowerCase().includes(searchTerm)) ||
        producto.categoria.toLowerCase().includes(searchTerm)
    );

    if (!allCategoriesSection) return;

    if (resultados.length === 0) {
        allCategoriesSection.innerHTML = `
            <div style="text-align: center; padding: 60px 20px; color: var(--gray-600);">
                <div style="font-size: 64px; margin-bottom: 16px;">🔍</div>
                <h3 style="font-size: 20px; margin-bottom: 8px;">No encontramos resultados</h3>
                <p>Intenta con otro término de búsqueda</p>
            </div>
        `;
        return;
    }

    allCategoriesSection.style.display = 'block';
    allCategoriesSection.innerHTML = `
        <div class="category-group">
            <div class="category-group-header">
                <h3 class="category-group-title">
                    <span class="category-group-icon">🔍</span>
                    Resultados de búsqueda
                </h3>
                <span class="category-group-count">${resultados.length} items</span>
            </div>
            <div class="products-grid">
                ${resultados.map(producto => renderProductCard(producto)).join('')}
            </div>
        </div>
    `;
}

// ========================================
// FILTROS
// ========================================

function setActiveFilter(filter) {
    currentFilter = filter;
    
    // Actualizar UI de filtros
    document.querySelectorAll('.filter-chip').forEach(chip => {
        chip.classList.remove('active');
        if (chip.dataset.filter === filter) {
            chip.classList.add('active');
        }
    });

    const popularSection = document.getElementById('popularSection');
    const productsSection = document.getElementById('productsSection');
    const allCategoriesSection = document.getElementById('allCategoriesSection');
    
    // Limpiar búsqueda
    const searchInput = document.getElementById('searchInput');
    if (searchInput) searchInput.value = '';

    // Aplicar filtro
    if (filter === 'all') {
        // Mostrar todo normal
        if (popularSection) popularSection.style.display = 'block';
        if (productsSection) productsSection.style.display = 'none';
        if (allCategoriesSection) allCategoriesSection.style.display = 'block';
        renderTodasCategorias();
    } else if (filter === 'popular') {
        // Mostrar solo populares
        if (popularSection) popularSection.style.display = 'block';
        if (productsSection) productsSection.style.display = 'none';
        if (allCategoriesSection) allCategoriesSection.style.display = 'none';
        
        // Recargar productos populares reales
        renderPopulares();
        
        // Scroll a populares
        if (popularSection) {
            popularSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    } else if (filter === 'ofertas') {
        // Mostrar solo ofertas
        if (popularSection) {
            popularSection.style.display = 'block';
            const titleElement = popularSection.querySelector('.section-title');
            if (titleElement) titleElement.textContent = '💰 Ofertas Especiales';
        }
        if (productsSection) productsSection.style.display = 'none';
        if (allCategoriesSection) allCategoriesSection.style.display = 'none';
        
        // Cargar productos en oferta
        renderOfertas();
        
        // Scroll a la sección
        if (popularSection) {
            popularSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    } else if (filter === 'nuevo') {
        // Mostrar últimos 20 productos (simulando nuevos)
        const nuevos = allProducts.slice(-20);
        mostrarProductosFiltrados(nuevos, '✨ Nuevos', 'nuevos');
    }
}

function mostrarProductosFiltrados(productos, titulo, icono) {
    const popularSection = document.getElementById('popularSection');
    const productsSection = document.getElementById('productsSection');
    const allCategoriesSection = document.getElementById('allCategoriesSection');
    
    if (popularSection) popularSection.style.display = 'none';
    if (productsSection) productsSection.style.display = 'none';
    if (allCategoriesSection) allCategoriesSection.style.display = 'block';
    
    if (!allCategoriesSection) return;
    
    if (productos.length === 0) {
        allCategoriesSection.innerHTML = `
            <div style="text-align: center; padding: 60px 20px; color: var(--gray-600);">
                <div style="font-size: 64px; margin-bottom: 16px;">😕</div>
                <h3 style="font-size: 20px; margin-bottom: 8px;">No hay productos en esta categoría</h3>
            </div>
        `;
        return;
    }
    
    allCategoriesSection.innerHTML = `
        <div class="category-group">
            <div class="category-group-header">
                <h3 class="category-group-title">
                    <span class="category-group-icon">${icono}</span>
                    ${titulo}
                </h3>
                <span class="category-group-count">${productos.length} items</span>
            </div>
            <div class="products-grid">
                ${productos.map(producto => renderProductCard(producto)).join('')}
            </div>
        </div>
    `;
}

// ========================================
// CARRITO
// ========================================

function agregarAlCarrito(id, nombre, precio, emoji) {
    const existingItem = cart.find(item => item.id === id);
    
    if (existingItem) {
        existingItem.cantidad++;
    } else {
        cart.push({
            id: id,
            nombre: nombre,
            precio: precio,
            emoji: emoji,
            cantidad: 1
        });
    }
    
    updateCartDisplay();
    mostrarToast(`${nombre} agregado al carrito`, 'success');
}

function updateCartDisplay() {
    const badge = document.getElementById('cartBadge');
    const totalDisplay = document.getElementById('cartTotalDisplay');
    const cartBtn = document.getElementById('cartFloatBtn');
    
    const totalItems = cart.reduce((sum, item) => sum + item.cantidad, 0);
    const totalPrice = cart.reduce((sum, item) => sum + (item.precio * item.cantidad), 0);
    
    if (badge) badge.textContent = totalItems;
    if (totalDisplay) totalDisplay.textContent = '$' + formatearPrecio(totalPrice);
    
    if (cartBtn) {
        cartBtn.style.display = totalItems > 0 ? 'block' : 'none';
    }
}

function abrirCarrito() {
    if (cart.length === 0) {
        mostrarToast('Tu carrito está vacío', 'info');
        return;
    }
    
    renderizarCarrito();
    abrirModal('modalCart');
}

function renderizarCarrito() {
    const container = document.getElementById('cartItems');
    const subtotalEl = document.getElementById('cartSubtotal');
    const totalEl = document.getElementById('cartTotal');
    
    if (!container) return;

    const total = cart.reduce((sum, item) => sum + (item.precio * item.cantidad), 0);
    
    container.innerHTML = cart.map((item, index) => `
        <div class="cart-item">
            <div class="cart-item-image">
                ${esRutaImagen(item.emoji) 
                    ? `<img src="${item.emoji}" alt="${item.nombre}">` 
                    : item.emoji}
            </div>
            <div class="cart-item-info">
                <div class="cart-item-name">${item.nombre}</div>
                <div class="cart-item-controls">
                    <div class="quantity-controls">
                        <button class="quantity-btn" onclick="modificarCantidad(${index}, -1)">−</button>
                        <span class="quantity-value">${item.cantidad}</span>
                        <button class="quantity-btn" onclick="modificarCantidad(${index}, 1)">+</button>
                    </div>
                    <div class="cart-item-price">$${formatearPrecio(item.precio * item.cantidad)}</div>
                </div>
                <div style="margin-top: 10px;">
                    <textarea 
                        class="cart-item-notes"
                        placeholder="Notas: Ej. Sin salsas, sin cebolla..."
                        style="width: 100%; padding: 8px; border: 1px solid #e0e0e0; border-radius: 5px; font-size: 13px; font-family: inherit; resize: vertical; min-height: 40px;"
                        onchange="actualizarNotasItem(${index}, this.value)"
                    >${item.notas || ''}</textarea>
                </div>
            </div>
        </div>
    `).join('');
    
    if (subtotalEl) subtotalEl.textContent = '$' + formatearPrecio(total);
    if (totalEl) totalEl.textContent = '$' + formatearPrecio(total);
}

function actualizarNotasItem(index, notas) {
    if (cart[index]) {
        cart[index].notas = notas.trim();
    }
}

function modificarCantidad(index, cambio) {
    if (cart[index]) {
        cart[index].cantidad += cambio;
        
        if (cart[index].cantidad <= 0) {
            cart.splice(index, 1);
        }
        
        updateCartDisplay();
        
        if (cart.length === 0) {
            cerrarModal();
            mostrarToast('Carrito vacío', 'info');
        } else {
            renderizarCarrito();
        }
    }
}

// ========================================
// PROCESO DE PEDIDO
// ========================================

function iniciarProcesoPedido() {
    cerrarModal();
    
    // Reset checkout
    resetCheckout();
    
    abrirModal('modalCheckout');
}

function resetCheckout() {
    // Limpiar forms
    document.getElementById('input-telefono').value = '';
    document.getElementById('input-nombre').value = '';
    document.getElementById('input-direccion').value = '';
    document.getElementById('input-numero').value = '';
    document.getElementById('input-fecha-nac').value = '';
    
    // Mostrar solo paso 1
    document.querySelectorAll('.checkout-step').forEach(step => {
        step.classList.remove('active');
    });
    document.getElementById('paso1-telefono').classList.add('active');
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

function mostrarClienteReconocido(cliente) {
    clienteActual = cliente;
    
    cambiarPaso('paso3-reconocido');
    
    document.getElementById('cliente-nombre-reconocido').textContent = cliente.nombre.split(' ')[0];
    
    const infoContainer = document.getElementById('info-cliente-reconocido');
    infoContainer.innerHTML = `
        <div class="cliente-info-row">
            <span class="cliente-info-label">📱 Teléfono:</span>
            <span class="cliente-info-value">${cliente.telefono}</span>
        </div>
        <div class="cliente-info-row">
            <span class="cliente-info-label">📍 Dirección:</span>
            <span class="cliente-info-value">${cliente.direccion} ${cliente.numero_casa}</span>
        </div>
    `;
}

function mostrarFormularioRegistro(telefono) {
    cambiarPaso('paso2-registro');
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
            clienteActual.direccion = direccion;
            clienteActual.numero_casa = numero;
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

function mostrarCambioDireccion() {
    cambiarPaso('paso4-editar-direccion');
    
    document.getElementById('input-direccion-edit').value = clienteActual.direccion || '';
    document.getElementById('input-numero-edit').value = clienteActual.numero_casa || '';
}

function usarDireccionOriginal() {
    cambiarPaso('paso3-reconocido');
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
    
    clienteActual.direccion = nuevaDireccion;
    clienteActual.numero_casa = nuevoNumero;
    clienteActual.direccion_temporal = true;
    
    finalizarPedido();
}

function finalizarPedidoClienteReconocido() {
    finalizarPedido();
}

function finalizarPedido() {
    const metodoPagoSeleccionado = document.querySelector('input[name="metodo_pago"]:checked');
    const metodoPago = metodoPagoSeleccionado ? metodoPagoSeleccionado.value : 'nequi';
    
    const formData = new FormData();
    formData.append('action', 'crear');
    formData.append('cliente_id', clienteActual.id);
    formData.append('items', JSON.stringify(cart));
    formData.append('metodo_pago', metodoPago);
    formData.append('direccion_entrega', clienteActual.direccion);
    formData.append('numero_entrega', clienteActual.numero_casa);

    fetch('api/pedidos.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            mostrarConfirmacion(data.pedido_id, data.total);
        } else {
            mostrarToast(data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        mostrarToast('Error al crear pedido', 'error');
    });
}

function mostrarConfirmacion(pedidoId, total) {
    cambiarPaso('paso5-confirmacion');
    
    document.getElementById('pedidoNumero').textContent = pedidoId;
    
    const orderSummary = document.getElementById('orderSummary');
    orderSummary.innerHTML = `
        ${cart.map(item => `
            <div class="order-summary-row">
                <span>${item.cantidad}x ${item.nombre}</span>
                <span>$${formatearPrecio(item.precio * item.cantidad)}</span>
            </div>
        `).join('')}
        <div class="order-summary-row" style="border-top: 2px solid var(--gray-300); margin-top: 16px; padding-top: 16px; font-weight: 700; color: var(--primary);">
            <span>Total</span>
            <span>$${formatearPrecio(total)}</span>
        </div>
    `;
    
    
    // Abrir comanda automáticamente en nueva ventana
    setTimeout(() => {
        window.open(`api/generar_comanda.php?pedido_id=${pedidoId}`, '_blank', 'width=400,height=600');
    }, 500);
    
    // Limpiar carrito
    cart = [];
    updateCartDisplay();
}

function volverAlCarrito() {
    cerrarModal();
    setTimeout(() => abrirCarrito(), 300);
}

function volverAlMenu() {
    cerrarModal();
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

function cambiarPaso(pasoId) {
    document.querySelectorAll('.checkout-step').forEach(step => {
        step.classList.remove('active');
    });
    document.getElementById(pasoId).classList.add('active');
}

// ========================================
// MODALES
// ========================================

function abrirModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.add('active');
        document.body.style.overflow = 'hidden';
    }
}

function cerrarModal() {
    document.querySelectorAll('.modal').forEach(modal => {
        modal.classList.remove('active');
    });
    document.body.style.overflow = '';
}

// Cerrar modal al hacer clic en el overlay
document.addEventListener('click', (e) => {
    if (e.target.classList.contains('modal-overlay')) {
        cerrarModal();
    }
});

// ========================================
// TOAST NOTIFICATIONS
// ========================================

function mostrarToast(message, type = 'info') {
    const container = document.getElementById('toastContainer');
    if (!container) return;

    const icons = {
        success: '✅',
        error: '❌',
        warning: '⚠️',
        info: 'ℹ️'
    };

    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    toast.innerHTML = `
        <div class="toast-icon">${icons[type]}</div>
        <div class="toast-content">
            <div class="toast-message">${message}</div>
        </div>
    `;

    container.appendChild(toast);

    setTimeout(() => {
        toast.style.animation = 'slideInRight 0.3s ease reverse';
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}

// ========================================
// UTILIDADES
// ========================================

function formatearPrecio(precio) {
    return new Intl.NumberFormat('es-CO').format(precio);
}

function esRutaImagen(valor) {
    if (!valor) return false;
    const extensionesImagen = ['.jpg', '.jpeg', '.png', '.gif', '.webp', '.svg'];
    const valorMinusculas = valor.toLowerCase();
    return extensionesImagen.some(ext => valorMinusculas.includes(ext)) || 
           valorMinusculas.startsWith('http://') || 
           valorMinusculas.startsWith('https://') ||
           valorMinusculas.startsWith('/') ||
           valorMinusculas.startsWith('./');
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// ========================================
// PWA - SERVICE WORKER (OPCIONAL)
// ========================================

if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
        // Puedes descomentar esto si implementas un service worker
        // navigator.serviceWorker.register('/sw.js');
    });
}