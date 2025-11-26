<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Gestión de Pedidos</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="view-selector">
        <button onclick="changeView('cliente')" class="view-btn active" id="btn-cliente">
            🍔 Vista Cliente
        </button>
        <button onclick="changeView('admin')" class="view-btn" id="btn-admin">
            💼 Gestión Admin
        </button>
        <button onclick="changeView('cocina')" class="view-btn" id="btn-cocina">
            👨‍🍳 Vista Cocina
        </button>
    </div>

    <div id="vista-cliente" class="view active">
        <div class="header">
            <h1>🍔 Guacherna Burgers - Menú Digital</h1>
            <p>Realiza tu pedido de manera fácil y rápida</p>
        </div>

        <div class="menu-grid" id="menuContainer"></div>

        <div class="cart-float" onclick="verCarrito()">
            🛒 Ver Carrito
            <span class="cart-badge" id="cartBadge">0</span>
        </div>
    </div>

    <div id="vista-admin" class="view">
        <div class="header">
            <h1>💼 Gestión de Pedidos</h1>
            <p>Administra todos los pedidos en tiempo real</p>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-number" id="statPendientes">0</div>
                <div class="stat-label">Pendientes</div>
            </div>
            <div class="stat-card">
                <div class="stat-number" id="statPreparando">0</div>
                <div class="stat-label">En Preparación</div>
            </div>
            <div class="stat-card">
                <div class="stat-number" id="statListos">0</div>
                <div class="stat-label">Listos</div>
            </div>
            <div class="stat-card">
                <div class="stat-number" id="statHoy">$0</div>
                <div class="stat-label">Total Hoy</div>
            </div>
        </div>

        <div class="orders-container" id="ordersContainer"></div>
    </div>

    <div id="vista-cocina" class="view">
        <div class="header">
            <h1>👨‍🍳 Panel de Cocina</h1>
            <p>Pedidos pendientes de preparación</p>
        </div>

        <div class="kitchen-grid" id="kitchenGrid"></div>
    </div>

    <div id="modalVoucher" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>🎫 Voucher de Pago</h2>
                <p>Pedido #<span id="voucherOrderNumber"></span></p>
            </div>

            <div class="voucher" id="voucherContent"></div>

            <div class="payment-info">
                <p>Método de pago: <strong>Nequi</strong></p>
                <p>Número: <strong>300-123-4567</strong></p>
            </div>

            <div class="modal-actions">
                <button class="btn-enviar-notif" onclick="enviarNotificacion()">
                    📱 Enviar a WhatsApp
                </button>
                <button class="btn-cerrar" onclick="cerrarModal()">
                    ✖ Cerrar
                </button>
            </div>
        </div>
    </div>

    <div id="modalCarrito" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>🛒 Tu Carrito</h2>
            </div>

            <div class="voucher" id="carritoContent"></div>

            <div style="background:#f8f9fa; padding:15px; border-radius:10px; margin:15px 0;">
                <label style="display:block; margin-bottom:10px; font-weight:600; color:#2c3e50; font-size:16px;">
                    💳 Método de Pago
                </label>
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px;">
                    <label style="display:flex; align-items:center; padding:12px; background:white; border:2px solid #3498db; border-radius:8px; cursor:pointer; transition:all 0.3s;">
                        <input type="radio" name="metodo_pago" value="nequi" checked style="margin-right:10px; width:20px; height:20px; cursor:pointer;">
                        <div>
                            <div style="font-weight:600; color:#2c3e50;">📱 Nequi</div>
                            <small style="color:#7f8c8d;">Pago digital</small>
                        </div>
                    </label>
                    <label style="display:flex; align-items:center; padding:12px; background:white; border:2px solid #e0e0e0; border-radius:8px; cursor:pointer; transition:all 0.3s;">
                        <input type="radio" name="metodo_pago" value="efectivo" style="margin-right:10px; width:20px; height:20px; cursor:pointer;">
                        <div>
                            <div style="font-weight:600; color:#2c3e50;">💵 Efectivo</div>
                            <small style="color:#7f8c8d;">Pago contraentrega</small>
                        </div>
                    </label>
                </div>
            </div>

            <div class="modal-actions">
                <button class="btn-enviar-notif" onclick="confirmarPedido()">
                    ✅ Confirmar Pedido
                </button>
                <button class="btn-cerrar" onclick="cerrarModal()">
                    ✖ Cerrar
                </button>
            </div>
        </div>
    </div>

    <div id="modalIdentificacion" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>📱 Identificación</h2>
                <p>Para confirmar tu pedido, necesitamos tu número</p>
            </div>

            <div id="paso1-telefono">
                <div style="margin: 20px 0;">
                    <label style="display:block; margin-bottom:8px; font-weight:600; color:#2c3e50;">
                        Teléfono (WhatsApp)
                    </label>
                    <input 
                        type="tel" 
                        id="input-telefono" 
                        placeholder="300-123-4567"
                        style="width:100%; padding:12px; border:2px solid #e0e0e0; border-radius:8px; font-size:16px;"
                        maxlength="15"
                    >
                    <small style="color:#7f8c8d; display:block; margin-top:5px;">
                        Usaremos este número para enviarte el estado de tu pedido
                    </small>
                </div>

                <div class="modal-actions">
                    <button class="btn-enviar-notif" onclick="verificarCliente()">
                        ✅ Continuar
                    </button>
                    <button class="btn-cerrar" onclick="cerrarModal()">
                        ✖ Cancelar
                    </button>
                </div>
            </div>

            <div id="paso2-registro" style="display:none;">
                <div style="background:#e8f5e9; padding:15px; border-radius:8px; margin-bottom:20px; text-align:center;">
                    <p style="margin:0; color:#2e7d32; font-weight:600;">
                        🎉 ¡Bienvenido! Completa tu registro (solo toma 30 segundos)
                    </p>
                </div>

                <form id="form-registro">
                    <div style="margin-bottom:15px;">
                        <label style="display:block; margin-bottom:8px; font-weight:600; color:#2c3e50;">
                            Nombre completo *
                        </label>
                        <input 
                            type="text" 
                            id="input-nombre" 
                            placeholder="Juan Pérez"
                            required
                            style="width:100%; padding:12px; border:2px solid #e0e0e0; border-radius:8px; font-size:16px;"
                        >
                    </div>

                    <div style="margin-bottom:15px;">
                        <label style="display:block; margin-bottom:8px; font-weight:600; color:#2c3e50;">
                            Dirección de residencia *
                        </label>
                        <input 
                            type="text" 
                            id="input-direccion" 
                            placeholder="Calle 45 #23-10"
                            required
                            style="width:100%; padding:12px; border:2px solid #e0e0e0; border-radius:8px; font-size:16px;"
                        >
                    </div>

                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px; margin-bottom:15px;">
                        <div>
                            <label style="display:block; margin-bottom:8px; font-weight:600; color:#2c3e50;">
                                No. Casa/Apto *
                            </label>
                            <input 
                                type="text" 
                                id="input-numero" 
                                placeholder="Apto 302"
                                required
                                style="width:100%; padding:12px; border:2px solid #e0e0e0; border-radius:8px; font-size:16px;"
                            >
                        </div>
                    </div>

                    <div style="margin-bottom:20px;">
                        <label style="display:block; margin-bottom:8px; font-weight:600; color:#2c3e50;">
                            Fecha de nacimiento
                        </label>
                        <input 
                            type="date" 
                            id="input-fecha-nac"
                            style="width:100%; padding:12px; border:2px solid #e0e0e0; border-radius:8px; font-size:16px;"
                        >
                        <small style="color:#7f8c8d; display:block; margin-top:5px;">
                            🎂 Te daremos un regalo especial en tu cumpleaños
                        </small>
                    </div>

                    <div style="background:#fff3cd; padding:12px; border-radius:8px; margin-bottom:20px; text-align:center;">
                        <small style="color:#856404;">
                            ⭐ Los datos marcados con * son obligatorios
                        </small>
                    </div>

                    <div class="modal-actions">
                        <button type="button" class="btn-enviar-notif" onclick="registrarCliente()">
                            🎉 Registrar y Continuar
                        </button>
                        <button type="button" class="btn-cerrar" onclick="cerrarModal()">
                            ✖ Cancelar
                        </button>
                    </div>
                </form>
            </div>

            <div id="paso3-reconocido" style="display:none;">
                <div style="background:#e3f2fd; padding:20px; border-radius:10px; margin-bottom:20px;">
                    <div style="text-align:center; margin-bottom:15px;">
                        <div style="font-size:48px;">👋</div>
                        <h3 style="color:#1976d2; margin:10px 0;">¡Hola de nuevo <span id="cliente-nombre-reconocido"></span>!</h3>
                    </div>
                    
                    <div id="info-cliente-reconocido" style="background:white; padding:15px; border-radius:8px;"></div>
                </div>

                <div style="background:#fff3cd; padding:12px; border-radius:8px; margin-bottom:20px; text-align:center;">
                    <small style="color:#856404;">
                        📍 Entregaremos en la dirección registrada. ¿Todo correcto?
                    </small>
                </div>

                <div class="modal-actions">
                    <button class="btn-enviar-notif" onclick="finalizarPedidoClienteReconocido()">
                        ✅ Sí, Confirmar Pedido
                    </button>
                    <button class="btn-cerrar" onclick="cerrarModal()">
                        ✖ Cancelar
                    </button>
                </div>
            </div>

            <div id="paso4-editar-direccion" style="display:none;">
                <div style="background:#fff3cd; padding:15px; border-radius:8px; margin-bottom:20px; text-align:center;">
                    <p style="margin:0; color:#856404; font-weight:600;">
                        📍 Cambiar Dirección de Entrega
                    </p>
                    <small style="color:#856404; display:block; margin-top:5px;">
                        Esta dirección solo se usará para este pedido
                    </small>
                </div>

                <form id="form-editar-direccion">
                    <div style="margin-bottom:15px;">
                        <label style="display:block; margin-bottom:8px; font-weight:600; color:#2c3e50;">
                            Nueva Dirección *
                        </label>
                        <input 
                            type="text" 
                            id="input-direccion-edit" 
                            placeholder="Calle 45 #23-10"
                            required
                            style="width:100%; padding:12px; border:2px solid #e0e0e0; border-radius:8px; font-size:16px;"
                        >
                    </div>

                    <div style="margin-bottom:20px;">
                        <label style="display:block; margin-bottom:8px; font-weight:600; color:#2c3e50;">
                            No. Casa/Apto *
                        </label>
                        <input 
                            type="text" 
                            id="input-numero-edit" 
                            placeholder="Apto 302"
                            required
                            style="width:100%; padding:12px; border:2px solid #e0e0e0; border-radius:8px; font-size:16px;"
                        >
                    </div>

                    <div class="modal-actions">
                        <button type="button" class="btn-enviar-notif" onclick="guardarNuevaDireccion()">
                            ✅ Usar Esta Dirección
                        </button>
                        <button type="button" style="background:#95a5a6; color:white; flex:1; padding:12px; border:none; border-radius:5px; font-weight:600; cursor:pointer;" onclick="usarDireccionOriginal()">
                            ← Usar Dirección Original
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="assets/js/main.js"></script>
</body>
</html>