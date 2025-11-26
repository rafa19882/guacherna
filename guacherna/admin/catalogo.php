<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Gestión del Catálogo | Guacherna Burgers</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f7fa;
            padding: 20px;
        }
        
        .container {
            max-width: 1600px;
            margin: 0 auto;
        }
        
        /* ========== HEADER ========== */
        .header {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
        
        .header h1 {
            color: #2c3e50;
            font-size: 32px;
            margin-bottom: 10px;
        }
        
        /* ========== TABS ========== */
        .tabs {
            display: flex;
            gap: 10px;
            margin-top: 20px;
            border-bottom: 2px solid #ecf0f1;
            padding-bottom: 10px;
        }
        
        .tab-btn {
            padding: 12px 25px;
            background: transparent;
            border: none;
            color: #7f8c8d;
            font-weight: 600;
            cursor: pointer;
            border-radius: 8px 8px 0 0;
            transition: all 0.3s;
            font-size: 15px;
        }
        
        .tab-btn:hover {
            background: #ecf0f1;
            color: #2c3e50;
        }
        
        .tab-btn.active {
            background: #3498db;
            color: white;
        }
        
        .tab-content {
            display: none;
        }
        
        .tab-content.active {
            display: block;
        }
        
        /* ========== CONTROLES DE TABLA ========== */
        .table-controls {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            gap: 20px;
            flex-wrap: wrap;
        }
        
        .search-box {
            position: relative;
            flex: 1;
            min-width: 300px;
        }
        
        .search-box input {
            width: 100%;
            padding: 12px 15px 12px 45px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 15px;
            transition: all 0.3s;
        }
        
        .search-box input:focus {
            outline: none;
            border-color: #3498db;
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
        }
        
        .search-box::before {
            content: '🔍';
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 18px;
        }
        
        .btn-primary {
            background: #3498db;
            color: white;
            padding: 12px 25px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s;
            font-size: 15px;
            white-space: nowrap;
        }
        
        .btn-primary:hover {
            background: #2980b9;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(52, 152, 219, 0.3);
        }
        
        /* ========== TABLA ========== */
        .table-container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            overflow: hidden;
            margin-bottom: 20px;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        thead {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        
        thead th {
            padding: 15px 12px;
            text-align: left;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 12px;
            letter-spacing: 0.5px;
        }
        
        tbody tr {
            border-bottom: 1px solid #ecf0f1;
            transition: all 0.3s;
        }
        
        tbody tr:hover {
            background: #f8f9fa;
        }
        
        tbody td {
            padding: 15px 12px;
            vertical-align: middle;
        }
        
        .producto-emoji {
            font-size: 36px;
            text-align: center;
            width: 80px;
            position: relative;
        }
        
        .producto-imagen {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            display: block;
            margin: 0 auto;
        }
        
        .producto-nombre {
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 5px;
            font-size: 15px;
        }
        
        .producto-descripcion {
            font-size: 13px;
            color: #7f8c8d;
            line-height: 1.4;
        }
        
        .producto-precio {
            font-size: 18px;
            font-weight: 700;
            color: #27ae60;
            white-space: nowrap;
        }
        
        .badge {
            display: inline-block;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            white-space: nowrap;
        }
        
        .badge-categoria {
            background: #e3f2fd;
            color: #1976d2;
        }
        
        .badge-active {
            background: #d4edda;
            color: #155724;
        }
        
        .badge-inactive {
            background: #f8d7da;
            color: #721c24;
        }
        
        /* ========== INDICADORES DE OFERTAS ========== */
        .producto-en-oferta {
            background: linear-gradient(90deg, 
                rgba(255, 193, 7, 0.05) 0%, 
                rgba(255, 193, 7, 0.02) 50%, 
                rgba(255, 193, 7, 0.05) 100%
            );
            border-left: 3px solid #ffc107 !important;
        }
        
        .producto-en-oferta:hover {
            background: linear-gradient(90deg, 
                rgba(255, 193, 7, 0.08) 0%, 
                rgba(255, 193, 7, 0.05) 50%, 
                rgba(255, 193, 7, 0.08) 100%
            );
        }
        
        .oferta-badge {
            position: absolute;
            top: 0;
            right: 5px;
            font-size: 18px;
            animation: pulso 2s infinite;
            z-index: 10;
        }
        
        @keyframes pulso {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.2); }
        }
        
        .badge-oferta {
            background: linear-gradient(135deg, #ff6b6b 0%, #ee5a6f 100%);
            color: white;
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 700;
            margin-left: 8px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            box-shadow: 0 2px 5px rgba(238, 90, 111, 0.3);
        }
        
        .precio-oferta-container {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }
        
        .precio-original {
            font-size: 14px;
            color: #95a5a6;
            text-decoration: line-through;
            font-weight: 500;
        }
        
        .precio-oferta-valor {
            font-size: 20px;
            font-weight: 700;
            color: #27ae60;
        }
        
        .oferta-info {
            display: flex;
            flex-direction: column;
            gap: 5px;
            align-items: flex-start;
        }
        
        .badge-oferta-activa {
            background: linear-gradient(135deg, #27ae60 0%, #229954 100%);
            color: white;
            padding: 4px 10px;
            border-radius: 15px;
            font-size: 12px;
            font-weight: 600;
            box-shadow: 0 2px 5px rgba(39, 174, 96, 0.3);
        }
        
        .badge-sin-oferta {
            background: #ecf0f1;
            color: #7f8c8d;
            padding: 4px 10px;
            border-radius: 15px;
            font-size: 12px;
            font-weight: 500;
        }
        
        .oferta-fecha {
            font-size: 11px;
            color: #7f8c8d;
            font-style: italic;
        }
        
        /* ========== PAGINACIÓN ========== */
        .paginacion-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 20px;
            padding: 15px 20px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            flex-wrap: wrap;
            gap: 15px;
        }
        
        .paginacion-info {
            display: flex;
            align-items: center;
            gap: 15px;
            flex-wrap: wrap;
        }
        
        #infoPaginacion {
            font-size: 14px;
            color: #7f8c8d;
            font-weight: 500;
        }
        
        .productos-por-pagina-container {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .productos-por-pagina-container label {
            font-size: 14px;
            color: #7f8c8d;
            font-weight: 500;
        }
        
        #productosPorPagina {
            padding: 6px 12px;
            border: 2px solid #e0e0e0;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 600;
            color: #2c3e50;
            background: white;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        #productosPorPagina:hover {
            border-color: #3498db;
        }
        
        #productosPorPagina:focus {
            outline: none;
            border-color: #3498db;
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
        }
        
        .paginacion-botones {
            display: flex;
            gap: 8px;
            align-items: center;
            flex-wrap: wrap;
        }
        
        .btn-paginacion {
            padding: 8px 15px;
            border: 2px solid #e0e0e0;
            background: white;
            color: #2c3e50;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            min-width: 40px;
        }
        
        .btn-paginacion:hover:not(:disabled) {
            background: #3498db;
            color: white;
            border-color: #3498db;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(52, 152, 219, 0.3);
        }
        
        .btn-paginacion.active {
            background: #3498db;
            color: white;
            border-color: #3498db;
            box-shadow: 0 2px 8px rgba(52, 152, 219, 0.4);
        }
        
        .btn-paginacion:disabled {
            background: #f5f5f5;
            color: #bdc3c7;
            border-color: #e0e0e0;
            cursor: not-allowed;
            opacity: 0.6;
        }
        
        .paginacion-puntos {
            padding: 0 8px;
            color: #95a5a6;
            font-weight: 600;
        }
        
        /* ========== BOTONES DE ACCIÓN ========== */
        .action-buttons {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }
        
        .btn-icon {
            width: 35px;
            height: 35px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 16px;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }
        
        .btn-icon:hover {
            transform: scale(1.1);
        }
        
        .btn-edit {
            background: #fff3cd;
        }
        
        .btn-delete {
            background: #f8d7da;
        }
        
        .btn-activate {
            background: #d4edda;
        }
        
        .btn-delete-permanent {
            background: #ffcccc;
        }
        
        .btn-oferta {
            background: #ffe5b4;
        }
        
        /* ========== EMPTY STATE ========== */
        .empty-state {
            text-align: center;
            padding: 60px 20px;
        }
        
        .empty-state-icon {
            font-size: 64px;
            margin-bottom: 20px;
        }
        
        .empty-state p {
            color: #7f8c8d;
            font-size: 16px;
            margin-bottom: 20px;
        }
        
        /* ========== MODALES ========== */
        .modal-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.6);
            z-index: 9999;
            align-items: center;
            justify-content: center;
        }
        
        .modal-overlay.active {
            display: flex;
        }
        
        .modal {
            background: white;
            border-radius: 15px;
            max-width: 600px;
            width: 90%;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
        }
        
        .modal-header {
            padding: 25px 30px;
            border-bottom: 2px solid #ecf0f1;
        }
        
        .modal-header h2 {
            color: #2c3e50;
            font-size: 24px;
        }
        
        .modal-body {
            padding: 30px;
        }
        
        .modal-footer {
            padding: 20px 30px;
            border-top: 2px solid #ecf0f1;
            display: flex;
            justify-content: flex-end;
            gap: 10px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #2c3e50;
            font-size: 14px;
        }
        
        .form-group input[type="text"],
        .form-group input[type="number"],
        .form-group input[type="datetime-local"],
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 12px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 15px;
            transition: all 0.3s;
        }
        
        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #3498db;
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
        }
        
        .form-group textarea {
            resize: vertical;
            min-height: 80px;
        }
        
        /* ========== SISTEMA DE SUBIDA DE IMÁGENES ========== */
        #productoImagen {
            width: 100%;
            padding: 10px;
            border: 2px dashed #e0e0e0;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        #productoImagen:hover {
            border-color: #3498db;
            background: #f8f9fa;
        }
        
        #vistaPrevia img {
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }
        
        #vistaPrevia button {
            transition: all 0.3s;
        }
        
        #vistaPrevia button:hover {
            background: #c0392b !important;
            transform: scale(1.1);
        }
        
        .btn-secondary {
            background: #95a5a6;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s;
        }
        
        .btn-secondary:hover {
            background: #7f8c8d;
        }
        
        .btn-danger {
            background: #e74c3c;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s;
        }
        
        .btn-danger:hover {
            background: #c0392b;
        }
        
        /* ========== RESPONSIVE ========== */
        @media (max-width: 768px) {
            .table-controls {
                flex-direction: column;
                align-items: stretch;
            }
            
            .search-box {
                min-width: 100%;
            }
            
            .btn-primary {
                width: 100%;
            }
            
            .paginacion-container {
                flex-direction: column;
                align-items: stretch;
            }
            
            .paginacion-botones {
                justify-content: center;
            }
            
            table {
                font-size: 13px;
            }
            
            thead th,
            tbody td {
                padding: 10px 8px;
            }
            
            .producto-emoji {
                font-size: 28px;
                width: 50px;
            }
            
            .action-buttons {
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- HEADER -->
        <div class="header">
            <h1>📋 Administración de Catálogo</h1>
            <p style="color: #7f8c8d; margin-top: 5px;">Gestiona productos, categorías y ofertas</p>
            
            <div class="tabs">
                <button class="tab-btn active" onclick="cambiarTab('productos')">
                    🍔 Productos
                </button>
                <button class="tab-btn" onclick="cambiarTab('categorias')">
                    📁 Categorías
                </button>
            </div>
        </div>

        <!-- TAB PRODUCTOS -->
        <div id="tab-productos" class="tab-content active">
            <div class="table-controls">
                <div class="search-box">
                    <input 
                        type="text" 
                        id="searchProductos" 
                        placeholder="Buscar por nombre, descripción o categoría..."
                        onkeyup="filtrarProductos()"
                    >
                </div>
                
                <button class="btn-primary" onclick="abrirModalProducto()">
                    ➕ Agregar Producto
                </button>
            </div>

            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Imagen</th>
                            <th>Producto</th>
                            <th>Categoría</th>
                            <th>Precio</th>
                            <th>Estado</th>
                            <th>Oferta</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="listaProductos">
                        <tr>
                            <td colspan="7" class="empty-state">
                                <div class="empty-state-icon">⏳</div>
                                <p>Cargando productos...</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- PAGINACIÓN -->
            <div class="paginacion-container">
                <div class="paginacion-info">
                    <span id="infoPaginacion">Mostrando productos...</span>
                    
                    <div class="productos-por-pagina-container">
                        <label for="productosPorPagina">Mostrar:</label>
                        <select id="productosPorPagina">
                            <option value="5">5</option>
                            <option value="10" selected>10</option>
                            <option value="20">20</option>
                            <option value="50">50</option>
                            <option value="100">100</option>
                        </select>
                    </div>
                </div>
                
                <div id="paginacion"></div>
            </div>
        </div>

        <!-- TAB CATEGORÍAS -->
        <div id="tab-categorias" class="tab-content">
            <div class="table-controls">
                <div class="search-box">
                    <input 
                        type="text" 
                        id="searchCategorias" 
                        placeholder="Buscar categorías..."
                        onkeyup="filtrarCategorias()"
                    >
                </div>
                
                <button class="btn-primary" onclick="abrirModalCategoria()">
                    ➕ Agregar Categoría
                </button>
            </div>

            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Nombre</th>
                            <th style="text-align: center;">Orden</th>
                            <th style="text-align: center;">Productos</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="listaCategorias">
                        <tr>
                            <td colspan="5" class="empty-state">
                                <div class="empty-state-icon">⏳</div>
                                <p>Cargando categorías...</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- MODAL PRODUCTO -->
    <div id="modalProducto" class="modal-overlay">
        <div class="modal">
            <div class="modal-header">
                <h2 id="tituloModalProducto">Agregar Producto</h2>
            </div>
            <form id="formProducto">
                <div class="modal-body">
                    <input type="hidden" id="productoId">
                    
                    <div class="form-group">
                        <label for="productoNombre">Nombre del Producto *</label>
                        <input type="text" id="productoNombre" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="productoDescripcion">Descripción *</label>
                        <textarea id="productoDescripcion" required></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="productoCategoria">Categoría *</label>
                        <select id="productoCategoria" required>
                            <option value="">Seleccionar...</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="productoPrecio">Precio *</label>
                        <input type="number" id="productoPrecio" step="0.01" required>
                    </div>
                    
                    <!-- SECCIÓN DE IMAGEN -->
                    <div class="form-group">
                        <label>Imagen del Producto</label>
                        
                        <!-- Vista previa -->
                        <div id="vistaPrevia" style="margin-bottom: 15px; display: none;">
                            <div style="position: relative; display: inline-block;">
                                <img id="imagenPreview" src="" alt="Vista previa" style="max-width: 200px; max-height: 200px; border-radius: 8px; border: 2px solid #e0e0e0;">
                                <button type="button" onclick="eliminarImagenProducto()" style="position: absolute; top: -10px; right: -10px; background: #e74c3c; color: white; border: none; border-radius: 50%; width: 30px; height: 30px; cursor: pointer; font-size: 16px;">×</button>
                            </div>
                        </div>
                        
                        <!-- Selector de archivo -->
                        <div id="selectorImagen">
                            <input type="file" id="productoImagen" accept="image/jpeg,image/jpg,image/png,image/webp,image/gif" onchange="previsualizarImagen(event)" style="margin-bottom: 10px;">
                            <small style="display: block; color: #7f8c8d; margin-bottom: 10px;">
                                📷 Formato recomendado: 1200x630px (aspect ratio 1.9:1)<br>
                                Formatos: JPG, PNG, WEBP, GIF (Max: 5MB)
                            </small>
                        </div>
                        
                        <div style="text-align: center; margin: 15px 0; color: #95a5a6; font-weight: 600;">- O -</div>
                        
                        <!-- Campo de emoji alternativo -->
                        <input type="text" id="productoEmoji" placeholder="🍔 Emoji alternativo (opcional)" style="text-align: center; font-size: 24px;">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-secondary" onclick="cerrarModales()">Cancelar</button>
                    <button type="submit" class="btn-primary">Guardar</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL CATEGORÍA -->
    <div id="modalCategoria" class="modal-overlay">
        <div class="modal">
            <div class="modal-header">
                <h2 id="tituloModalCategoria">Agregar Categoría</h2>
            </div>
            <form id="formCategoria">
                <div class="modal-body">
                    <input type="hidden" id="categoriaId">
                    
                    <div class="form-group">
                        <label for="categoriaNombre">Nombre de la Categoría *</label>
                        <input type="text" id="categoriaNombre" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="categoriaOrden">Orden de visualización *</label>
                        <input type="number" id="categoriaOrden" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-secondary" onclick="cerrarModales()">Cancelar</button>
                    <button type="submit" class="btn-primary">Guardar</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL OFERTAS -->
    <div id="modalOferta" class="modal-overlay">
        <div class="modal">
            <div class="modal-header">
                <h2>💰 Gestionar Oferta</h2>
            </div>
            <form id="formOferta" onsubmit="guardarOferta(event)">
                <div class="modal-body">
                    <input type="hidden" id="oferta-producto-id">
                    
                    <div class="form-group">
                        <label>Producto</label>
                        <input type="text" id="oferta-producto-nombre" readonly style="background: #f8f9fa; cursor: not-allowed;">
                    </div>
                    
                    <div class="form-group">
                        <label>Precio Original</label>
                        <input type="text" id="oferta-precio-original" readonly style="background: #f8f9fa; cursor: not-allowed;">
                    </div>
                    
                    <div class="form-group">
                        <label for="oferta-precio-oferta">Precio de Oferta *</label>
                        <input type="number" 
                               id="oferta-precio-oferta" 
                               step="0.01" 
                               placeholder="Ej: 10000"
                               oninput="calcularDescuento()"
                               required>
                        <small id="descuento-info" style="display: block; margin-top: 8px; font-weight: 600;"></small>
                    </div>
                    
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                        <div class="form-group">
                            <label for="oferta-fecha-inicio">Fecha Inicio (opcional)</label>
                            <input type="datetime-local" id="oferta-fecha-inicio">
                            <small style="display: block; margin-top: 5px; color: #7f8c8d; font-size: 12px;">Dejar vacío = sin límite</small>
                        </div>
                        
                        <div class="form-group">
                            <label for="oferta-fecha-fin">Fecha Fin (opcional)</label>
                            <input type="datetime-local" id="oferta-fecha-fin">
                            <small style="display: block; margin-top: 5px; color: #7f8c8d; font-size: 12px;">Dejar vacío = sin límite</small>
                        </div>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="submit" class="btn-primary">
                        ✅ Activar Oferta
                    </button>
                    <button type="button" class="btn-danger" onclick="desactivarOferta()" id="btn-desactivar-oferta" style="display: none;">
                        ❌ Desactivar Oferta
                    </button>
                    <button type="button" class="btn-secondary" onclick="cerrarModalOferta()">
                        Cancelar
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- JavaScript -->
    <script src="../assets/js/admin_catalogo.js"></script>
</body>
</html>