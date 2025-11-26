
<?php
/**
 * Configuración de conexión a la base de datos
 * ⭐ CON ZONA HORARIA DE COLOMBIA CONFIGURADA
 */

// ⭐⭐⭐ AGREGAR ESTA LÍNEA AL INICIO ⭐⭐⭐
date_default_timezone_set('America/Bogota');

define('DB_HOST', 'localhost');
define('DB_NAME', 'restaurante_pedidos');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');


$opciones = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET,
        DB_USER,
        DB_PASS,
        $opciones
    );
    
    // ⭐⭐⭐ AGREGAR ESTA LÍNEA DESPUÉS DE CREAR LA CONEXIÓN ⭐⭐⭐
    $pdo->exec("SET time_zone = '-05:00'");
    
} catch (PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}

function ejecutarConsulta($sql, $params = []) {
    global $pdo;
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    } catch (PDOException $e) {
        error_log("Error en consulta: " . $e->getMessage());
        return false;
    }
}

function ejecutarAccion($sql, $params = []) {
    global $pdo;
    try {
        $stmt = $pdo->prepare($sql);
        return $stmt->execute($params);
    } catch (PDOException $e) {
        error_log("Error en acción: " . $e->getMessage());
        return false;
    }
}

function ultimoId() {
    global $pdo;
    return $pdo->lastInsertId();
}

function iniciarTransaccion() {
    global $pdo;
    return $pdo->beginTransaction();
}

function confirmarTransaccion() {
    global $pdo;
    return $pdo->commit();
}

function revertirTransaccion() {
    global $pdo;
    return $pdo->rollBack();
}
?>