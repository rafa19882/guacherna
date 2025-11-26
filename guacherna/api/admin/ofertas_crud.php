<?php
header('Content-Type: application/json');
require_once '../../config/database.php';

$action = $_POST['action'] ?? $_GET['action'] ?? '';

try {
    switch ($action) {
        case 'activar':
            activarOferta();
            break;
        
        case 'desactivar':
            desactivarOferta();
            break;
        
        case 'listar':
            listarProductosConOfertas();
            break;
        
        case 'actualizar':
            actualizarOferta();
            break;
        
        default:
            throw new Exception('Acción no válida');
    }
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

function verificarSistemaOfertas() {
    // Verificar si existen las columnas de ofertas
    $sql = "SHOW COLUMNS FROM productos LIKE 'en_oferta'";
    $stmt = ejecutarConsulta($sql);
    
    if ($stmt->rowCount() === 0) {
        throw new Exception('Sistema de ofertas no configurado. Por favor ejecute el script SQL: actualizar_bd_ofertas.sql');
    }
}

function activarOferta() {
    verificarSistemaOfertas();
    
    $producto_id = intval($_POST['producto_id'] ?? 0);
    $precio_oferta = floatval($_POST['precio_oferta'] ?? 0);
    $fecha_inicio = $_POST['fecha_inicio'] ?? null;
    $fecha_fin = $_POST['fecha_fin'] ?? null;
    
    // Validaciones
    if ($producto_id <= 0) {
        throw new Exception('ID de producto inválido');
    }
    
    if ($precio_oferta <= 0) {
        throw new Exception('El precio de oferta debe ser mayor a 0');
    }
    
    // Obtener precio original
    $sql = "SELECT precio FROM productos WHERE id = ? AND activo = 1";
    $stmt = ejecutarConsulta($sql, [$producto_id]);
    $producto = $stmt->fetch();
    
    if (!$producto) {
        throw new Exception('El producto no existe o está inactivo');
    }
    
    if ($precio_oferta >= $producto['precio']) {
        throw new Exception('El precio de oferta debe ser menor al precio original');
    }
    
    // Calcular porcentaje de descuento
    $descuento = round((($producto['precio'] - $precio_oferta) / $producto['precio']) * 100);
    
    // Activar oferta
    $sql = "UPDATE productos 
            SET en_oferta = 1, 
                precio_oferta = ?,
                fecha_inicio_oferta = ?,
                fecha_fin_oferta = ?
            WHERE id = ?";
    
    if (ejecutarAccion($sql, [$precio_oferta, $fecha_inicio, $fecha_fin, $producto_id])) {
        echo json_encode([
            'success' => true,
            'message' => 'Oferta activada exitosamente',
            'descuento' => $descuento
        ]);
    } else {
        throw new Exception('Error al activar la oferta');
    }
}

function desactivarOferta() {
    verificarSistemaOfertas();
    
    $producto_id = intval($_POST['producto_id'] ?? 0);
    
    if ($producto_id <= 0) {
        throw new Exception('ID de producto inválido');
    }
    
    $sql = "UPDATE productos 
            SET en_oferta = 0,
                precio_oferta = NULL,
                fecha_inicio_oferta = NULL,
                fecha_fin_oferta = NULL
            WHERE id = ?";
    
    if (ejecutarAccion($sql, [$producto_id])) {
        echo json_encode([
            'success' => true,
            'message' => 'Oferta desactivada exitosamente'
        ]);
    } else {
        throw new Exception('Error al desactivar la oferta');
    }
}

function actualizarOferta() {
    verificarSistemaOfertas();
    
    $producto_id = intval($_POST['producto_id'] ?? 0);
    $precio_oferta = floatval($_POST['precio_oferta'] ?? 0);
    $fecha_inicio = $_POST['fecha_inicio'] ?? null;
    $fecha_fin = $_POST['fecha_fin'] ?? null;
    
    // Validaciones
    if ($producto_id <= 0) {
        throw new Exception('ID de producto inválido');
    }
    
    if ($precio_oferta <= 0) {
        throw new Exception('El precio de oferta debe ser mayor a 0');
    }
    
    // Obtener precio original
    $sql = "SELECT precio FROM productos WHERE id = ? AND activo = 1";
    $stmt = ejecutarConsulta($sql, [$producto_id]);
    $producto = $stmt->fetch();
    
    if (!$producto) {
        throw new Exception('El producto no existe o está inactivo');
    }
    
    if ($precio_oferta >= $producto['precio']) {
        throw new Exception('El precio de oferta debe ser menor al precio original');
    }
    
    // Actualizar oferta
    $sql = "UPDATE productos 
            SET precio_oferta = ?,
                fecha_inicio_oferta = ?,
                fecha_fin_oferta = ?
            WHERE id = ? AND en_oferta = 1";
    
    if (ejecutarAccion($sql, [$precio_oferta, $fecha_inicio, $fecha_fin, $producto_id])) {
        echo json_encode([
            'success' => true,
            'message' => 'Oferta actualizada exitosamente'
        ]);
    } else {
        throw new Exception('Error al actualizar la oferta');
    }
}

function listarProductosConOfertas() {
    verificarSistemaOfertas();
    
    // Detectar columna de imagen
    $testSql = "SHOW COLUMNS FROM productos LIKE 'imagen_url'";
    $testStmt = ejecutarConsulta($testSql);
    $hasImagenUrl = $testStmt->rowCount() > 0;
    $columnName = $hasImagenUrl ? 'p.imagen_url' : 'p.emoji';
    
    $sql = "SELECT 
                p.id,
                p.nombre,
                p.precio,
                p.en_oferta,
                p.precio_oferta,
                p.fecha_inicio_oferta,
                p.fecha_fin_oferta,
                $columnName as imagen_url,
                c.nombre as categoria_nombre,
                CASE 
                    WHEN p.en_oferta = 1 AND p.precio_oferta IS NOT NULL 
                    THEN ROUND(((p.precio - p.precio_oferta) / p.precio) * 100)
                    ELSE 0
                END as porcentaje_descuento
            FROM productos p
            INNER JOIN categorias c ON p.categoria_id = c.id
            WHERE p.activo = 1
            ORDER BY p.en_oferta DESC, c.orden, p.nombre";
    
    $stmt = ejecutarConsulta($sql);
    $productos = $stmt->fetchAll();
    
    echo json_encode([
        'success' => true,
        'productos' => $productos
    ]);
}
?>