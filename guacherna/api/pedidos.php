<?php
header('Content-Type: application/json');
require_once '../config/database.php';

$action = $_POST['action'] ?? $_GET['action'] ?? '';

try {
    switch ($action) {
        case 'crear':
            crearPedido();
            break;
        
        case 'cambiar_estado':
            cambiarEstado();
            break;
        
        case 'obtener_admin':
            obtenerPedidosAdmin();
            break;
        
        case 'obtener_cocina':
            obtenerPedidosCocina();
            break;
        
        case 'obtener_detalle':
            obtenerDetallePedido();
            break;
        
        case 'estadisticas':
            obtenerEstadisticas();
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

function crearPedido() {
    $cliente_id = intval($_POST['cliente_id'] ?? 0);
    $items = json_decode($_POST['items'] ?? '[]', true);
    
    // Recibir dirección de entrega (puede ser temporal o la registrada)
    $direccion_entrega = trim($_POST['direccion_entrega'] ?? '');
    $numero_entrega = trim($_POST['numero_entrega'] ?? '');
    
    // Recibir método de pago (por defecto 'nequi')
    $metodo_pago = trim($_POST['metodo_pago'] ?? 'nequi');
    if (!in_array($metodo_pago, ['nequi', 'efectivo'])) {
        $metodo_pago = 'nequi';
    }
    
    if ($cliente_id <= 0 || empty($items)) {
        throw new Exception('Datos incompletos');
    }
    
    // Si no hay dirección temporal, usar la del cliente
    if (empty($direccion_entrega)) {
        $sql = "SELECT direccion, numero_casa FROM clientes WHERE id = ?";
        $stmt = ejecutarConsulta($sql, [$cliente_id]);
        $cliente = $stmt->fetch();
        $direccion_entrega = $cliente['direccion'];
        $numero_entrega = $cliente['numero_casa'];
    }
    
    $total = 0;
    foreach ($items as $item) {
        $total += floatval($item['precio']) * intval($item['cantidad']);
    }
    
    iniciarTransaccion();
    
    try {
        $sql = "INSERT INTO pedidos (cliente_id, total, estado, direccion_entrega, numero_entrega, metodo_pago) 
                VALUES (?, ?, 'pendiente', ?, ?, ?)";
        ejecutarAccion($sql, [$cliente_id, $total, $direccion_entrega, $numero_entrega, $metodo_pago]);
        $pedido_id = ultimoId();
        
        $sql = "INSERT INTO pedido_items (pedido_id, producto_id, cantidad, precio_unitario, subtotal) 
                VALUES (?, ?, ?, ?, ?)";
        
        foreach ($items as $item) {
            $producto_id = intval($item['id']);
            $cantidad = intval($item['cantidad']);
            $precio_unitario = floatval($item['precio']);
            $subtotal = $precio_unitario * $cantidad;
            
            ejecutarAccion($sql, [$pedido_id, $producto_id, $cantidad, $precio_unitario, $subtotal]);
        }
        
        $sql = "UPDATE clientes 
                SET total_pedidos = total_pedidos + 1, 
                    total_gastado = total_gastado + ? 
                WHERE id = ?";
        ejecutarAccion($sql, [$total, $cliente_id]);
        
        confirmarTransaccion();
        
        echo json_encode([
            'success' => true,
            'message' => 'Pedido creado exitosamente',
            'pedido_id' => $pedido_id,
            'total' => $total
        ]);
        
    } catch (Exception $e) {
        revertirTransaccion();
        throw $e;
    }
}

function cambiarEstado() {
    $pedido_id = intval($_POST['pedido_id'] ?? 0);
    $nuevo_estado = $_POST['estado'] ?? '';
    
    $estados_validos = ['pendiente', 'preparando', 'listo', 'pagado', 'entregado'];
    
    if ($pedido_id <= 0 || !in_array($nuevo_estado, $estados_validos)) {
        throw new Exception('Datos inválidos');
    }
    
    $sql = "UPDATE pedidos SET estado = ? WHERE id = ?";
    
    if (ejecutarAccion($sql, [$nuevo_estado, $pedido_id])) {
        echo json_encode([
            'success' => true,
            'message' => 'Estado actualizado'
        ]);
    } else {
        throw new Exception('Error al actualizar estado');
    }
}

function obtenerPedidosAdmin() {
    $sql = "SELECT 
                p.id,
                p.total,
                p.estado,
                p.direccion_entrega,
                p.numero_entrega,
                DATE_FORMAT(p.fecha_hora, '%H:%i') as hora,
                c.nombre as cliente,
                c.telefono
            FROM pedidos p
            INNER JOIN clientes c ON p.cliente_id = c.id
            WHERE p.estado != 'entregado'
            ORDER BY p.fecha_hora DESC";
    
    $stmt = ejecutarConsulta($sql);
    $pedidos = $stmt->fetchAll();
    
    foreach ($pedidos as &$pedido) {
        $sql = "SELECT 
                    pi.cantidad,
                    p.nombre,
                    pi.subtotal as precio
                FROM pedido_items pi
                INNER JOIN productos p ON pi.producto_id = p.id
                WHERE pi.pedido_id = ?";
        
        $stmt = ejecutarConsulta($sql, [$pedido['id']]);
        $pedido['items'] = $stmt->fetchAll();
    }
    
    echo json_encode([
        'success' => true,
        'pedidos' => $pedidos
    ]);
}

function obtenerPedidosCocina() {
    $sql = "SELECT 
                p.id,
                p.estado,
                DATE_FORMAT(p.fecha_hora, '%H:%i') as hora
            FROM pedidos p
            WHERE p.estado IN ('pendiente', 'preparando')
            ORDER BY p.fecha_hora ASC";
    
    $stmt = ejecutarConsulta($sql);
    $pedidos = $stmt->fetchAll();
    
    foreach ($pedidos as &$pedido) {
        $sql = "SELECT 
                    pi.cantidad,
                    p.nombre
                FROM pedido_items pi
                INNER JOIN productos p ON pi.producto_id = p.id
                WHERE pi.pedido_id = ?";
        
        $stmt = ejecutarConsulta($sql, [$pedido['id']]);
        $pedido['items'] = $stmt->fetchAll();
    }
    
    echo json_encode([
        'success' => true,
        'pedidos' => $pedidos
    ]);
}

function obtenerDetallePedido() {
    $pedido_id = intval($_GET['pedido_id'] ?? 0);
    
    if ($pedido_id <= 0) {
        throw new Exception('ID de pedido inválido');
    }
    
    $sql = "SELECT 
                p.id,
                p.total,
                p.estado,
                p.direccion_entrega,
                p.numero_entrega,
                p.metodo_pago,
                p.fecha_hora,
                c.nombre as cliente,
                c.telefono
            FROM pedidos p
            INNER JOIN clientes c ON p.cliente_id = c.id
            WHERE p.id = ?";
    
    $stmt = ejecutarConsulta($sql, [$pedido_id]);
    $pedido = $stmt->fetch();
    
    if (!$pedido) {
        throw new Exception('Pedido no encontrado');
    }
    
    $sql = "SELECT 
                pi.cantidad,
                p.nombre,
                pi.subtotal as precio
            FROM pedido_items pi
            INNER JOIN productos p ON pi.producto_id = p.id
            WHERE pi.pedido_id = ?";
    
    $stmt = ejecutarConsulta($sql, [$pedido_id]);
    $pedido['items'] = $stmt->fetchAll();
    
    echo json_encode([
        'success' => true,
        'pedido' => $pedido
    ]);
}

function obtenerEstadisticas() {
    $sql = "SELECT 
                COUNT(CASE WHEN estado = 'pendiente' THEN 1 END) as pendientes,
                COUNT(CASE WHEN estado = 'preparando' THEN 1 END) as preparando,
                COUNT(CASE WHEN estado = 'listo' THEN 1 END) as listos,
                COALESCE(SUM(total), 0) as total_hoy
            FROM pedidos
            WHERE DATE(fecha_hora) = CURDATE()";
    
    $stmt = ejecutarConsulta($sql);
    $stats = $stmt->fetch();
    
    echo json_encode([
        'success' => true,
        'estadisticas' => $stats
    ]);
}
?>