<?php
header('Content-Type: application/json');
require_once '../../config/database.php';

$action = $_POST['action'] ?? $_GET['action'] ?? '';

try {
    switch ($action) {
        case 'listar':
            listarProductos();
            break;
        
        case 'crear':
            crearProducto();
            break;
        
        case 'actualizar':
            actualizarProducto();
            break;
        
        case 'cambiar_estado':
            cambiarEstadoProducto();
            break;
        
        case 'eliminar':
            eliminarProducto();
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

function listarProductos() {
    // Detectar si la columna se llama 'emoji' o 'imagen_url'
    try {
        $testSql = "SHOW COLUMNS FROM productos LIKE 'imagen_url'";
        $testStmt = ejecutarConsulta($testSql);
        $hasImagenUrl = $testStmt->rowCount() > 0;
    } catch (Exception $e) {
        $hasImagenUrl = false;
    }
    
    // Usar el nombre correcto de la columna
    $columnName = $hasImagenUrl ? 'p.imagen_url' : 'p.emoji';
    
    // Incluir información completa de ofertas
    $sql = "SELECT 
                p.id,
                p.nombre,
                p.descripcion,
                p.precio,
                $columnName as imagen_url,
                $columnName as emoji,
                p.activo,
                p.categoria_id,
                c.nombre as categoria_nombre,
                p.en_oferta,
                p.precio_oferta,
                p.fecha_inicio_oferta,
                p.fecha_fin_oferta,
                CASE 
                    WHEN p.en_oferta = 1 AND p.precio_oferta IS NOT NULL 
                    THEN ROUND(((p.precio - p.precio_oferta) / p.precio) * 100)
                    ELSE 0
                END as porcentaje_descuento
            FROM productos p
            INNER JOIN categorias c ON p.categoria_id = c.id
            ORDER BY c.orden, p.nombre";
    
    $stmt = ejecutarConsulta($sql);
    $productos = $stmt->fetchAll();
    
    echo json_encode([
        'success' => true,
        'productos' => $productos
    ]);
}

function crearProducto() {
    $nombre = trim($_POST['nombre'] ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '');
    $categoria_id = intval($_POST['categoria_id'] ?? 0);
    $precio = floatval($_POST['precio'] ?? 0);
    $emoji = trim($_POST['emoji'] ?? '');
    
    // Validaciones
    if (empty($nombre)) {
        throw new Exception('El nombre es obligatorio');
    }
    
    if (empty($descripcion)) {
        throw new Exception('La descripción es obligatoria');
    }
    
    if ($categoria_id <= 0) {
        throw new Exception('Debe seleccionar una categoría válida');
    }
    
    if ($precio <= 0) {
        throw new Exception('El precio debe ser mayor a 0');
    }
    
    // Verificar que la categoría existe
    $sql = "SELECT id FROM categorias WHERE id = ? AND activo = 1";
    $stmt = ejecutarConsulta($sql, [$categoria_id]);
    if (!$stmt->fetch()) {
        throw new Exception('La categoría seleccionada no existe o está inactiva');
    }
    
    // Detectar nombre de columna
    try {
        $testSql = "SHOW COLUMNS FROM productos LIKE 'imagen_url'";
        $testStmt = ejecutarConsulta($testSql);
        $hasImagenUrl = $testStmt->rowCount() > 0;
        $columnName = $hasImagenUrl ? 'imagen_url' : 'emoji';
    } catch (Exception $e) {
        $columnName = 'emoji';
    }
    
    // Insertar producto
    $sql = "INSERT INTO productos (nombre, descripcion, categoria_id, precio, $columnName, activo) 
            VALUES (?, ?, ?, ?, ?, 1)";
    
    if (ejecutarAccion($sql, [$nombre, $descripcion, $categoria_id, $precio, $emoji])) {
        echo json_encode([
            'success' => true,
            'message' => 'Producto creado exitosamente',
            'producto_id' => ultimoId()
        ]);
    } else {
        throw new Exception('Error al crear el producto');
    }
}

function actualizarProducto() {
    $id = intval($_POST['id'] ?? 0);
    $nombre = trim($_POST['nombre'] ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '');
    $categoria_id = intval($_POST['categoria_id'] ?? 0);
    $precio = floatval($_POST['precio'] ?? 0);
    $emoji = trim($_POST['emoji'] ?? '');
    
    // Validaciones
    if ($id <= 0) {
        throw new Exception('ID de producto inválido');
    }
    
    if (empty($nombre)) {
        throw new Exception('El nombre es obligatorio');
    }
    
    if (empty($descripcion)) {
        throw new Exception('La descripción es obligatoria');
    }
    
    if ($categoria_id <= 0) {
        throw new Exception('Debe seleccionar una categoría válida');
    }
    
    if ($precio <= 0) {
        throw new Exception('El precio debe ser mayor a 0');
    }
    
    // Verificar que el producto existe
    $sql = "SELECT id FROM productos WHERE id = ?";
    $stmt = ejecutarConsulta($sql, [$id]);
    if (!$stmt->fetch()) {
        throw new Exception('El producto no existe');
    }
    
    // Detectar nombre de columna
    try {
        $testSql = "SHOW COLUMNS FROM productos LIKE 'imagen_url'";
        $testStmt = ejecutarConsulta($testSql);
        $hasImagenUrl = $testStmt->rowCount() > 0;
        $columnName = $hasImagenUrl ? 'imagen_url' : 'emoji';
    } catch (Exception $e) {
        $columnName = 'emoji';
    }
    
    // Actualizar producto
    $sql = "UPDATE productos 
            SET nombre = ?, descripcion = ?, categoria_id = ?, precio = ?, $columnName = ?
            WHERE id = ?";
    
    if (ejecutarAccion($sql, [$nombre, $descripcion, $categoria_id, $precio, $emoji, $id])) {
        echo json_encode([
            'success' => true,
            'message' => 'Producto actualizado exitosamente'
        ]);
    } else {
        throw new Exception('Error al actualizar el producto');
    }
}

function cambiarEstadoProducto() {
    $id = intval($_POST['id'] ?? 0);
    $activo = intval($_POST['activo'] ?? 0);
    
    if ($id <= 0) {
        throw new Exception('ID de producto inválido');
    }
    
    if (!in_array($activo, [0, 1])) {
        throw new Exception('Estado inválido');
    }
    
    $sql = "UPDATE productos SET activo = ? WHERE id = ?";
    
    if (ejecutarAccion($sql, [$activo, $id])) {
        $mensaje = $activo == 1 ? 'Producto activado' : 'Producto desactivado';
        echo json_encode([
            'success' => true,
            'message' => $mensaje
        ]);
    } else {
        throw new Exception('Error al cambiar el estado del producto');
    }
}

function eliminarProducto() {
    $id = intval($_POST['id'] ?? 0);
    
    if ($id <= 0) {
        throw new Exception('ID de producto inválido');
    }
    
    // Verificar que el producto existe
    $sql = "SELECT nombre FROM productos WHERE id = ?";
    $stmt = ejecutarConsulta($sql, [$id]);
    $producto = $stmt->fetch();
    
    if (!$producto) {
        throw new Exception('El producto no existe');
    }
    
    // Verificar si el producto tiene pedidos asociados
    $sql = "SELECT COUNT(*) as total FROM pedido_items WHERE producto_id = ?";
    $stmt = ejecutarConsulta($sql, [$id]);
    $result = $stmt->fetch();
    
    if ($result['total'] > 0) {
        throw new Exception('No se puede eliminar este producto porque tiene pedidos asociados. Puedes desactivarlo en su lugar.');
    }
    
    // Eliminar el producto
    $sql = "DELETE FROM productos WHERE id = ?";
    
    if (ejecutarAccion($sql, [$id])) {
        echo json_encode([
            'success' => true,
            'message' => 'Producto eliminado exitosamente'
        ]);
    } else {
        throw new Exception('Error al eliminar el producto');
    }
}
?>