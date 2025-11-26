<?php
header('Content-Type: application/json');
require_once '../../config/database.php';

$action = $_POST['action'] ?? $_GET['action'] ?? '';

try {
    switch ($action) {
        case 'listar':
            listarCategorias();
            break;
        
        case 'listar_activas':
            listarCategoriasActivas();
            break;
        
        case 'crear':
            crearCategoria();
            break;
        
        case 'actualizar':
            actualizarCategoria();
            break;
        
        case 'cambiar_estado':
            cambiarEstadoCategoria();
            break;
        
        case 'eliminar':
            eliminarCategoria();
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

function listarCategorias() {
    $sql = "SELECT 
                c.id,
                c.nombre,
                c.orden,
                c.activo,
                COUNT(p.id) as total_productos
            FROM categorias c
            LEFT JOIN productos p ON c.id = p.categoria_id AND p.activo = 1
            GROUP BY c.id, c.nombre, c.orden, c.activo
            ORDER BY c.orden";
    
    $stmt = ejecutarConsulta($sql);
    $categorias = $stmt->fetchAll();
    
    echo json_encode([
        'success' => true,
        'categorias' => $categorias
    ]);
}

function listarCategoriasActivas() {
    $sql = "SELECT id, nombre, orden
            FROM categorias
            WHERE activo = 1
            ORDER BY orden";
    
    $stmt = ejecutarConsulta($sql);
    $categorias = $stmt->fetchAll();
    
    echo json_encode([
        'success' => true,
        'categorias' => $categorias
    ]);
}

function crearCategoria() {
    $nombre = trim($_POST['nombre'] ?? '');
    $orden = intval($_POST['orden'] ?? 0);
    
    // Validaciones
    if (empty($nombre)) {
        throw new Exception('El nombre es obligatorio');
    }
    
    if ($orden < 0) {
        throw new Exception('El orden debe ser un número positivo');
    }
    
    // Verificar que no exista una categoría con el mismo nombre
    $sql = "SELECT id FROM categorias WHERE LOWER(nombre) = LOWER(?)";
    $stmt = ejecutarConsulta($sql, [$nombre]);
    if ($stmt->fetch()) {
        throw new Exception('Ya existe una categoría con ese nombre');
    }
    
    // Insertar categoría
    $sql = "INSERT INTO categorias (nombre, orden, activo) VALUES (?, ?, 1)";
    
    if (ejecutarAccion($sql, [$nombre, $orden])) {
        echo json_encode([
            'success' => true,
            'message' => 'Categoría creada exitosamente',
            'categoria_id' => ultimoId()
        ]);
    } else {
        throw new Exception('Error al crear la categoría');
    }
}

function actualizarCategoria() {
    $id = intval($_POST['id'] ?? 0);
    $nombre = trim($_POST['nombre'] ?? '');
    $orden = intval($_POST['orden'] ?? 0);
    
    // Validaciones
    if ($id <= 0) {
        throw new Exception('ID de categoría inválido');
    }
    
    if (empty($nombre)) {
        throw new Exception('El nombre es obligatorio');
    }
    
    if ($orden < 0) {
        throw new Exception('El orden debe ser un número positivo');
    }
    
    // Verificar que la categoría existe
    $sql = "SELECT id FROM categorias WHERE id = ?";
    $stmt = ejecutarConsulta($sql, [$id]);
    if (!$stmt->fetch()) {
        throw new Exception('La categoría no existe');
    }
    
    // Verificar que no exista otra categoría con el mismo nombre
    $sql = "SELECT id FROM categorias WHERE LOWER(nombre) = LOWER(?) AND id != ?";
    $stmt = ejecutarConsulta($sql, [$nombre, $id]);
    if ($stmt->fetch()) {
        throw new Exception('Ya existe otra categoría con ese nombre');
    }
    
    // Actualizar categoría
    $sql = "UPDATE categorias SET nombre = ?, orden = ? WHERE id = ?";
    
    if (ejecutarAccion($sql, [$nombre, $orden, $id])) {
        echo json_encode([
            'success' => true,
            'message' => 'Categoría actualizada exitosamente'
        ]);
    } else {
        throw new Exception('Error al actualizar la categoría');
    }
}

function cambiarEstadoCategoria() {
    $id = intval($_POST['id'] ?? 0);
    $activo = intval($_POST['activo'] ?? 0);
    
    if ($id <= 0) {
        throw new Exception('ID de categoría inválido');
    }
    
    if (!in_array($activo, [0, 1])) {
        throw new Exception('Estado inválido');
    }
    
    // Verificar si la categoría tiene productos activos
    if ($activo == 0) {
        $sql = "SELECT COUNT(*) as total FROM productos WHERE categoria_id = ? AND activo = 1";
        $stmt = ejecutarConsulta($sql, [$id]);
        $result = $stmt->fetch();
        
        if ($result['total'] > 0) {
            // Advertencia: se desactivará la categoría y sus productos no se mostrarán
            // pero no desactivamos los productos automáticamente
        }
    }
    
    $sql = "UPDATE categorias SET activo = ? WHERE id = ?";
    
    if (ejecutarAccion($sql, [$activo, $id])) {
        $mensaje = $activo == 1 ? 'Categoría activada' : 'Categoría desactivada';
        echo json_encode([
            'success' => true,
            'message' => $mensaje
        ]);
    } else {
        throw new Exception('Error al cambiar el estado de la categoría');
    }
}

function eliminarCategoria() {
    $id = intval($_POST['id'] ?? 0);
    
    if ($id <= 0) {
        throw new Exception('ID de categoría inválido');
    }
    
    // Verificar que la categoría existe
    $sql = "SELECT nombre FROM categorias WHERE id = ?";
    $stmt = ejecutarConsulta($sql, [$id]);
    $categoria = $stmt->fetch();
    
    if (!$categoria) {
        throw new Exception('La categoría no existe');
    }
    
    // Verificar si la categoría tiene productos asociados
    $sql = "SELECT COUNT(*) as total FROM productos WHERE categoria_id = ?";
    $stmt = ejecutarConsulta($sql, [$id]);
    $result = $stmt->fetch();
    
    if ($result['total'] > 0) {
        throw new Exception('No se puede eliminar esta categoría porque tiene productos asociados. Primero debes mover o eliminar los productos, o puedes desactivar la categoría en su lugar.');
    }
    
    // Eliminar la categoría
    $sql = "DELETE FROM categorias WHERE id = ?";
    
    if (ejecutarAccion($sql, [$id])) {
        echo json_encode([
            'success' => true,
            'message' => 'Categoría eliminada exitosamente'
        ]);
    } else {
        throw new Exception('Error al eliminar la categoría');
    }
}
?>