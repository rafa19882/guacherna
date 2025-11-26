<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Guacherna Burgers 🍔</title>
    
    <!-- Open Graph / WhatsApp -->
    <meta property="og:type" content="website">
    <meta property="og:title" content="Guacherna Burgers 🍔">
    <meta property="og:description" content="Las mejores hamburguesas y asados de la ciudad. ⭐ 4.8 - Domicilio gratis - 30-40 min">
    <meta property="og:image" content="assets/images/bann-menu.jpg">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <link rel="stylesheet" href="style-modern.css">
</head>
<body>
    <!-- Header Fijo -->
    <header class="app-header">
        <div class="header-brand">
            <h1 class="brand-name">🍔 Guacherna Burgers</h1>
        </div>
        
        <!-- Barra de búsqueda -->
        <div class="search-bar">
            <span class="search-icon">🔍</span>
            <input 
                type="text" 
                id="searchInput" 
                class="search-input" 
                placeholder="Buscar platos, bebidas..."
            >
        </div>
    </header>

    <!-- Contenido Principal -->
    <main class="main-content">
        <!-- Hero Banner -->
        <section class="hero-section">
            <div class="hero-content">
                <h1 class="hero-title">Guacherna Burgers</h1>
                <p class="hero-subtitle">🍔 Las mejores hamburguesas y asados de la ciudad</p>
                <div class="hero-stats">
                    <div class="stat-item">
                        <span class="stat-icon">⭐</span>
                        <span class="stat-value">4.8</span>
                    </div>
                    <div class="stat-separator">•</div>
                    <div class="stat-item">
                        <span class="stat-icon">🕐</span>
                        <span class="stat-value">30-40 min</span>
                    </div>
                    <div class="stat-separator">•</div>
                    <div class="stat-item">
                        <span class="stat-icon">🚚</span>
                        <span class="stat-value">Domicilio gratis</span>
                    </div>
                </div>
            </div>
        </section>

        <!-- Chips de filtros -->
        <section class="filters-section">
            <div class="filters-scroll">
                <button class="filter-chip active" data-filter="all">
                    <span class="chip-icon">⭐</span>
                    <span class="chip-text">Todos</span>
                </button>
                <button class="filter-chip" data-filter="popular">
                    <span class="chip-icon">🔥</span>
                    <span class="chip-text">Populares</span>
                </button>
                <button class="filter-chip" data-filter="ofertas">
                    <span class="chip-icon">💰</span>
                    <span class="chip-text">Ofertas</span>
                </button>
                <button class="filter-chip" data-filter="nuevo">
                    <span class="chip-icon">✨</span>
                    <span class="chip-text">Nuevos</span>
                </button>
            </div>
        </section>

        <!-- Carrusel de Categorías -->
        <section class="categories-section">
            <h2 class="section-title">Categorías</h2>
            <div class="categories-carousel" id="categoriesCarousel">
                <!-- Se llenará dinámicamente con JavaScript -->
            </div>
        </section>

        <!-- Lo Más Pedido -->
        <section class="popular-section" id="popularSection">
            <h2 class="section-title">⭐ Lo más pedido</h2>
            <div class="popular-grid" id="popularGrid">
                <!-- Se llenará dinámicamente -->
            </div>
        </section>

        <!-- Productos por Categoría -->
        <section class="products-section" id="productsSection">
            <!-- Se llenará dinámicamente cuando seleccionen categoría -->
        </section>

        <!-- Todas las Categorías -->
        <section class="all-categories-section" id="allCategoriesSection">
            <!-- Se llenará con todas las categorías y productos -->
        </section>
    </main>

    <!-- Botón Flotante del Carrito -->
    <div class="cart-float" id="cartFloatBtn" style="display: none;">
        <div class="cart-float-content">
            <div class="cart-badge" id="cartBadge">0</div>
            <div class="cart-info">
                <div class="cart-label">Ver carrito</div>
                <div class="cart-total" id="cartTotalDisplay">$0</div>
            </div>
            <span class="cart-arrow">→</span>
        </div>
    </div>

    <!-- Modal del Carrito -->
    <div id="modalCart" class="modal">
        <div class="modal-overlay"></div>
        <div class="modal-container">
            <div class="modal-header">
                <h2 class="modal-title">
                    <span class="modal-icon">🛒</span>
                    Tu pedido
                </h2>
                <button class="modal-close" onclick="cerrarModal()">✕</button>
            </div>
            <div class="modal-body">
                <div id="cartItems" class="cart-items-list">
                    <!-- Items del carrito -->
                </div>
                <div class="cart-summary">
                    <div class="summary-row">
                        <span>Subtotal</span>
                        <span id="cartSubtotal">$0</span>
                    </div>
                    <div class="summary-row total">
                        <span>Total</span>
                        <span id="cartTotal">$0</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn-primary btn-block" onclick="iniciarProcesoPedido()">
                    <span>Continuar</span>
                    <span class="btn-icon">→</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Modal de Checkout -->
    <div id="modalCheckout" class="modal">
        <div class="modal-overlay"></div>
        <div class="modal-container modal-checkout">
            <div class="modal-header">
                <button class="modal-back" onclick="volverAlCarrito()">←</button>
                <h2 class="modal-title">Finalizar pedido</h2>
                <button class="modal-close" onclick="cerrarModal()">✕</button>
            </div>
            <div class="modal-body">
                <!-- Paso 1: Ingresar teléfono -->
                <div id="paso1-telefono" class="checkout-step active">
                    <div class="step-header">
                        <div class="step-icon">📱</div>
                        <h3 class="step-title">Ingresa tu número</h3>
                        <p class="step-subtitle">Te identificaremos con tu teléfono</p>
                    </div>
                    <form id="form-telefono" class="checkout-form">
                        <div class="form-group">
                            <label class="form-label">Número de teléfono</label>
                            <input 
                                type="tel" 
                                id="input-telefono" 
                                class="form-input"
                                placeholder="300 123 4567"
                                required
                            >
                        </div>
                        <button type="submit" class="btn-primary btn-block">
                            Continuar
                        </button>
                    </form>
                </div>

                <!-- Paso 2: Registro nuevo cliente -->
                <div id="paso2-registro" class="checkout-step">
                    <div class="step-header">
                        <div class="step-icon">✨</div>
                        <h3 class="step-title">Completa tu registro</h3>
                        <p class="step-subtitle">Es tu primera vez, necesitamos algunos datos</p>
                    </div>
                    <form id="form-registro" class="checkout-form">
                        <div class="form-group">
                            <label class="form-label">Nombre completo *</label>
                            <input 
                                type="text" 
                                id="input-nombre" 
                                class="form-input"
                                placeholder="Juan Pérez"
                                required
                            >
                        </div>
                        <div class="form-group">
                            <label class="form-label">Dirección *</label>
                            <input 
                                type="text" 
                                id="input-direccion" 
                                class="form-input"
                                placeholder="Calle 45 #23-10"
                                required
                            >
                        </div>
                        <div class="form-group">
                            <label class="form-label">No. Casa/Apto *</label>
                            <input 
                                type="text" 
                                id="input-numero" 
                                class="form-input"
                                placeholder="Apto 302"
                                required
                            >
                        </div>
                        <div class="form-group">
                            <label class="form-label">Fecha de nacimiento (opcional)</label>
                            <input 
                                type="date" 
                                id="input-fecha-nac" 
                                class="form-input"
                            >
                            <small class="form-hint">🎂 Te daremos un regalo en tu cumpleaños</small>
                        </div>
                        <button type="submit" class="btn-primary btn-block">
                            Registrar y continuar
                        </button>
                    </form>
                </div>

                <!-- Paso 3: Cliente reconocido -->
                <div id="paso3-reconocido" class="checkout-step">
                    <div class="step-header">
                        <div class="step-icon">👋</div>
                        <h3 class="step-title">¡Hola <span id="cliente-nombre-reconocido"></span>!</h3>
                        <p class="step-subtitle">Nos alegra verte de nuevo</p>
                    </div>
                    <div class="cliente-info" id="info-cliente-reconocido">
                        <!-- Info del cliente -->
                    </div>
                    <div class="form-group">
                        <label class="form-label">Método de pago</label>
                        <div class="payment-methods">
                            <label class="payment-method active">
                                <input type="radio" name="metodo_pago" value="nequi" checked>
                                <div class="payment-content">
                                    <span class="payment-icon">💳</span>
                                    <span class="payment-name">Nequi</span>
                                </div>
                            </label>
                            <label class="payment-method">
                                <input type="radio" name="metodo_pago" value="efectivo">
                                <div class="payment-content">
                                    <span class="payment-icon">💵</span>
                                    <span class="payment-name">Efectivo</span>
                                </div>
                            </label>
                        </div>
                    </div>
                    <button class="btn-primary btn-block" onclick="finalizarPedidoClienteReconocido()">
                        Confirmar pedido
                    </button>
                    <button class="btn-secondary btn-block" onclick="mostrarCambioDireccion()">
                        Cambiar dirección
                    </button>
                </div>

                <!-- Paso 4: Cambiar dirección -->
                <div id="paso4-editar-direccion" class="checkout-step">
                    <div class="step-header">
                        <div class="step-icon">📍</div>
                        <h3 class="step-title">Cambiar dirección</h3>
                        <p class="step-subtitle">Solo para este pedido</p>
                    </div>
                    <form id="form-editar-direccion" class="checkout-form">
                        <div class="form-group">
                            <label class="form-label">Nueva dirección *</label>
                            <input 
                                type="text" 
                                id="input-direccion-edit" 
                                class="form-input"
                                placeholder="Calle 45 #23-10"
                                required
                            >
                        </div>
                        <div class="form-group">
                            <label class="form-label">No. Casa/Apto *</label>
                            <input 
                                type="text" 
                                id="input-numero-edit" 
                                class="form-input"
                                placeholder="Apto 302"
                                required
                            >
                        </div>
                        <button type="submit" class="btn-primary btn-block">
                            Usar esta dirección
                        </button>
                        <button type="button" class="btn-secondary btn-block" onclick="usarDireccionOriginal()">
                            Usar dirección original
                        </button>
                    </form>
                </div>

                <!-- Paso 5: Confirmación -->
                <div id="paso5-confirmacion" class="checkout-step">
                    <div class="success-animation">
                        <div class="success-icon">✅</div>
                        <h3 class="success-title">¡Pedido confirmado!</h3>
                        <p class="success-subtitle">Pedido #<span id="pedidoNumero"></span></p>
                    </div>
                    <div class="order-summary" id="orderSummary">
                        <!-- Resumen del pedido -->
                    </div>
                    <button class="btn-primary btn-block" onclick="volverAlMenu()">
                        Volver al menú
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Toast Notifications -->
    <div id="toastContainer" class="toast-container"></div>

    <script src="app-modern.js"></script>
</body>
</html>