<?php
header('Content-Type: application/json');
require_once '../config/database.php';

$action = $_POST['action'] ?? '';

try {
    switch ($action) {
        case 'verificar':
            verificarCliente();
            break;
        
        case 'registrar':
            registrarCliente();
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

function verificarCliente() {
    $telefono = limpiarTelefono($_POST['telefono'] ?? '');
    
    if (empty($telefono)) {
        throw new Exception('Teléfono requerido');
    }
    
    $sql = "SELECT id, telefono, nombre, direccion, numero_casa, fecha_nacimiento 
            FROM clientes 
            WHERE telefono = ? AND activo = 1";
    
    $stmt = ejecutarConsulta($sql, [$telefono]);
    $cliente = $stmt->fetch();
    
    if ($cliente) {
        echo json_encode([
            'success' => true,
            'existe' => true,
            'cliente' => $cliente
        ]);
    } else {
        echo json_encode([
            'success' => true,
            'existe' => false
        ]);
    }
}

function registrarCliente() {
    $telefono = limpiarTelefono($_POST['telefono'] ?? '');
    $nombre = trim($_POST['nombre'] ?? '');
    $direccion = trim($_POST['direccion'] ?? '');
    $numero_casa = trim($_POST['numero_casa'] ?? '');
    $fecha_nacimiento = $_POST['fecha_nacimiento'] ?? null;
    
    if (empty($telefono) || empty($nombre) || empty($direccion) || empty($numero_casa)) {
        throw new Exception('Todos los campos obligatorios deben estar completos');
    }
    
    $sql = "SELECT id FROM clientes WHERE telefono = ?";
    $stmt = ejecutarConsulta($sql, [$telefono]);
    
    if ($stmt->fetch()) {
        throw new Exception('El teléfono ya está registrado');
    }
    
    $sql = "INSERT INTO clientes (telefono, nombre, direccion, numero_casa, fecha_nacimiento) 
            VALUES (?, ?, ?, ?, ?)";
    
    if (ejecutarAccion($sql, [$telefono, $nombre, $direccion, $numero_casa, $fecha_nacimiento])) {
        $clienteId = ultimoId();
        
        echo json_encode([
            'success' => true,
            'message' => 'Cliente registrado exitosamente',
            'cliente_id' => $clienteId
        ]);
    } else {
        throw new Exception('Error al registrar cliente');
    }
}

function limpiarTelefono($telefono) {
    return preg_replace('/[^0-9]/', '', $telefono);
}
?>