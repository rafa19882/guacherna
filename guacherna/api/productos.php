<?php
header('Content-Type: application/json');
require_once '../config/database.php';

$action = $_GET['action'] ?? 'todos';

try {
    switch ($action) {
        case 'todos':
            obtenerTodosProductos();
            break;
        
        case 'populares':
            obtenerProductosPopulares();
            break;
        
        case 'ofertas':
            obtenerProductosEnOferta();
            break;
        
        default:
            obtenerTodosProductos();
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}

function obtenerTodosProductos() {
    // Detectar si la columna se llama 'emoji' o 'imagen_url'
    $testSql = "SHOW COLUMNS FROM productos LIKE 'imagen_url'";
    $testStmt = ejecutarConsulta($testSql);
    $hasImagenUrl = $testStmt->rowCount() > 0;
    
    // Usar el nombre correcto de la columna
    $columnName = $hasImagenUrl ? 'p.imagen_url' : 'p.emoji';
    
    // Verificar si existe columna en_oferta
    $testOfertaSql = "SHOW COLUMNS FROM productos LIKE 'en_oferta'";
    $testOfertaStmt = ejecutarConsulta($testOfertaSql);
    $hasOferta = $testOfertaStmt->rowCount() > 0;
    
    $ofertaFields = $hasOferta ? ', p.en_oferta, p.precio_oferta' : '';
    
    $sql = "SELECT 
                p.id,
                p.nombre,
                p.descripcion,
                p.precio,
                $columnName as emoji,
                c.nombre as categoria
                $ofertaFields
            FROM productos p
            INNER JOIN categorias c ON p.categoria_id = c.id
            WHERE p.activo = 1 AND c.activo = 1
            ORDER BY c.orden, p.nombre";
    
    $stmt = ejecutarConsulta($sql);
    $productos = $stmt->fetchAll();
    
    echo json_encode([
        'success' => true,
        'productos' => $productos
    ]);
}

function obtenerProductosPopulares() {
    // Detectar si la columna se llama 'emoji' o 'imagen_url'
    $testSql = "SHOW COLUMNS FROM productos LIKE 'imagen_url'";
    $testStmt = ejecutarConsulta($testSql);
    $hasImagenUrl = $testStmt->rowCount() > 0;
    
    // Usar el nombre correcto de la columna
    $columnName = $hasImagenUrl ? 'p.imagen_url' : 'p.emoji';
    
    // Verificar si existe columna en_oferta
    $testOfertaSql = "SHOW COLUMNS FROM productos LIKE 'en_oferta'";
    $testOfertaStmt = ejecutarConsulta($testOfertaSql);
    $hasOferta = $testOfertaStmt->rowCount() > 0;
    
    $ofertaFields = $hasOferta ? ', p.en_oferta, p.precio_oferta' : '';
    
    // Obtener productos más pedidos basados en la tabla pedido_items
    $sql = "SELECT 
                p.id,
                p.nombre,
                p.descripcion,
                p.precio,
                $columnName as emoji,
                c.nombre as categoria,
                COUNT(pi.id) as total_pedidos,
                SUM(pi.cantidad) as total_cantidad
                $ofertaFields
            FROM productos p
            INNER JOIN categorias c ON p.categoria_id = c.id
            LEFT JOIN pedido_items pi ON p.id = pi.producto_id
            WHERE p.activo = 1 AND c.activo = 1
            GROUP BY p.id
            HAVING total_cantidad > 0
            ORDER BY total_cantidad DESC, total_pedidos DESC
            LIMIT 20";
    
    $stmt = ejecutarConsulta($sql);
    $productos = $stmt->fetchAll();
    
    // Si no hay productos con pedidos, devolver los primeros 6 productos activos
    if (empty($productos)) {
        $sql = "SELECT 
                    p.id,
                    p.nombre,
                    p.descripcion,
                    p.precio,
                    $columnName as emoji,
                    c.nombre as categoria
                    $ofertaFields
                FROM productos p
                INNER JOIN categorias c ON p.categoria_id = c.id
                WHERE p.activo = 1 AND c.activo = 1
                ORDER BY c.orden, p.nombre
                LIMIT 6";
        
        $stmt = ejecutarConsulta($sql);
        $productos = $stmt->fetchAll();
    }
    
    echo json_encode([
        'success' => true,
        'productos' => $productos
    ]);
}

function obtenerProductosEnOferta() {
    // Detectar si la columna se llama 'emoji' o 'imagen_url'
    $testSql = "SHOW COLUMNS FROM productos LIKE 'imagen_url'";
    $testStmt = ejecutarConsulta($testSql);
    $hasImagenUrl = $testStmt->rowCount() > 0;
    
    // Usar el nombre correcto de la columna
    $columnName = $hasImagenUrl ? 'p.imagen_url' : 'p.emoji';
    
    // Verificar si existe columna en_oferta
    $testOfertaSql = "SHOW COLUMNS FROM productos LIKE 'en_oferta'";
    $testOfertaStmt = ejecutarConsulta($testOfertaSql);
    $hasOferta = $testOfertaStmt->rowCount() > 0;
    
    if (!$hasOferta) {
        // Si no existe la columna, devolver array vacío
        echo json_encode([
            'success' => true,
            'productos' => [],
            'mensaje' => 'Sistema de ofertas no configurado. Ejecute el script SQL de actualización.'
        ]);
        return;
    }
    
    // Obtener productos en oferta activos
    // Si hay fechas configuradas, validar que estén dentro del rango
    $sql = "SELECT 
                p.id,
                p.nombre,
                p.descripcion,
                p.precio,
                p.en_oferta,
                p.precio_oferta,
                p.fecha_inicio_oferta,
                p.fecha_fin_oferta,
                $columnName as emoji,
                c.nombre as categoria,
                ROUND(((p.precio - p.precio_oferta) / p.precio) * 100) as porcentaje_descuento
            FROM productos p
            INNER JOIN categorias c ON p.categoria_id = c.id
            WHERE p.activo = 1 
                AND c.activo = 1 
                AND p.en_oferta = 1
                AND p.precio_oferta IS NOT NULL
                AND p.precio_oferta < p.precio
                AND (p.fecha_inicio_oferta IS NULL OR p.fecha_inicio_oferta <= NOW())
                AND (p.fecha_fin_oferta IS NULL OR p.fecha_fin_oferta >= NOW())
            ORDER BY porcentaje_descuento DESC, p.nombre";
    
    $stmt = ejecutarConsulta($sql);
    $productos = $stmt->fetchAll();
    
    echo json_encode([
        'success' => true,
        'productos' => $productos,
        'total_ofertas' => count($productos)
    ]);
}
?>