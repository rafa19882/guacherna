<?php
/**
 * IMPRESOR DIRECTO - MÉTODO SIMPLE
 * 
 * Envía texto directo al puerto de la impresora
 * Funciona con impresoras térmicas USB/Serie
 */

require_once 'config/database.php';

// =====================================
// CONFIGURACIÓN SIMPLE
// =====================================

// MÉTODO 1: Busca automáticamente la impresora
$BUSCAR_AUTOMATICO = true;

// MÉTODO 2: Si conoces el puerto, ponlo aquí
$PUERTO_MANUAL = "USB001"; // Puede ser: USB001, COM1, COM2, LPT1, etc.

$INTERVALO = 3;
$ARCHIVO_CONTROL = __DIR__ . '/ultimo_pedido_impreso.txt';

// =====================================
// FUNCIONES
// =====================================

function obtenerUltimoPedido() {
    global $ARCHIVO_CONTROL;
    return file_exists($ARCHIVO_CONTROL) ? (int)file_get_contents($ARCHIVO_CONTROL) : 0;
}

function guardarUltimoPedido($id) {
    global $ARCHIVO_CONTROL;
    file_put_contents($ARCHIVO_CONTROL, $id);
}

function obtenerPedidosNuevos($ultimo_id) {
    $sql = "SELECT id FROM pedidos WHERE id > ? ORDER BY id ASC";
    $stmt = ejecutarConsulta($sql, [$ultimo_id]);
    return $stmt->fetchAll(PDO::FETCH_COLUMN);
}

function generarTextoComanda($pedido_id) {
    // Datos del pedido
    $sql = "SELECT 
                p.id,
                DATE_FORMAT(p.fecha_hora, '%d/%m/%Y %H:%i') as fecha_hora,
                c.nombre as cliente,
                c.telefono
            FROM pedidos p
            INNER JOIN clientes c ON p.cliente_id = c.id
            WHERE p.id = ?";
    
    $stmt = ejecutarConsulta($sql, [$pedido_id]);
    $pedido = $stmt->fetch();
    
    if (!$pedido) return false;
    
    // Items CON NOTAS
    $sql = "SELECT 
                pi.cantidad,
                pr.nombre,
                pi.notas
            FROM pedido_items pi
            INNER JOIN productos pr ON pi.producto_id = pr.id
            WHERE pi.pedido_id = ?";
    
    $stmt = ejecutarConsulta($sql, [$pedido_id]);
    $items = $stmt->fetchAll();
    
    // Generar texto simple
    $t = "\n\n";
    $t .= "========================================\n";
    $t .= "        GUACHERNA BURGERS\n";
    $t .= "        COMANDA DE COCINA\n";
    $t .= "========================================\n\n";
    $t .= "    PEDIDO #" . $pedido['id'] . "\n\n";
    $t .= "----------------------------------------\n";
    $t .= $pedido['fecha_hora'] . "\n";
    $t .= "Cliente: " . $pedido['cliente'] . "\n";
    $t .= "----------------------------------------\n\n";
    $t .= "        ITEMS DEL PEDIDO\n\n";
    
    foreach ($items as $item) {
        $t .= sprintf("%2d x %s\n", $item['cantidad'], $item['nombre']);
        
        // NOTAS
        if (!empty($item['notas'])) {
            $t .= "    ** " . $item['notas'] . " **\n";
        }
        $t .= "\n";
    }
    
    $t .= "----------------------------------------\n";
    $t .= "  Verificar items antes de entregar\n";
    $t .= "========================================\n\n\n\n";
    
    return $t;
}

function encontrarPuertoImpresora() {
    echo "Buscando impresora...\n";
    
    // Puertos comunes para impresoras térmicas
    $puertos = [
        'USB001', 'USB002', 'USB003',
        'COM1', 'COM2', 'COM3', 'COM4',
        'LPT1', 'LPT2'
    ];
    
    foreach ($puertos as $puerto) {
        echo "  Probando $puerto... ";
        
        // Intentar abrir el puerto
        $handle = @fopen("\\\\.\\$puerto", "wb");
        
        if ($handle) {
            echo "✓ ENCONTRADO\n";
            fclose($handle);
            return $puerto;
        }
        
        echo "✗\n";
    }
    
    return false;
}

function imprimirDirecto($pedido_id, $puerto) {
    echo "\n[" . date('H:i:s') . "] Imprimiendo comanda #$pedido_id...\n";
    
    $texto = generarTextoComanda($pedido_id);
    if (!$texto) {
        echo "[ERROR] No se pudo generar comanda\n";
        return false;
    }
    
    echo "Intentando imprimir en puerto: $puerto\n";
    
    // Abrir puerto de impresora
    $handle = @fopen("\\\\.\\$puerto", "wb");
    
    if (!$handle) {
        echo "[ERROR] No se pudo abrir el puerto $puerto\n";
        return false;
    }
    
    // Enviar texto a la impresora
    $bytes = fwrite($handle, $texto);
    fclose($handle);
    
    if ($bytes > 0) {
        echo "[OK] ✓✓✓ COMANDA #$pedido_id IMPRESA ($bytes bytes)\n\n";
        return true;
    }
    
    echo "[ERROR] No se enviaron datos\n";
    return false;
}

function imprimirConComandoCopy($pedido_id, $nombreImpresora) {
    echo "\n[" . date('H:i:s') . "] Método alternativo para #$pedido_id...\n";
    
    $texto = generarTextoComanda($pedido_id);
    if (!$texto) return false;
    
    // Guardar en archivo temporal
    $archivo = sys_get_temp_dir() . "\\comanda_$pedido_id.txt";
    file_put_contents($archivo, $texto);
    
    // Usar comando COPY de Windows
    $comando = "copy \"$archivo\" \"\\\\%COMPUTERNAME%\\$nombreImpresora\" /B";
    
    echo "Ejecutando: $comando\n";
    
    exec($comando, $output, $code);
    
    @unlink($archivo);
    
    if ($code === 0) {
        echo "[OK] ✓✓✓ Impreso con COPY\n\n";
        return true;
    }
    
    echo "[ERROR] Falló (código: $code)\n";
    return false;
}

// =====================================
// INICIO
// =====================================

echo "\n";
echo "========================================\n";
echo "   IMPRESOR DIRECTO - MÉTODO SIMPLE\n";
echo "========================================\n\n";

// Encontrar impresora
$puerto = null;

if ($BUSCAR_AUTOMATICO) {
    $puerto = encontrarPuertoImpresora();
    
    if (!$puerto) {
        echo "\n[WARN] No se encontró impresora automáticamente\n";
        echo "Intentaré usar: $PUERTO_MANUAL\n\n";
        $puerto = $PUERTO_MANUAL;
    }
} else {
    $puerto = $PUERTO_MANUAL;
    echo "Usando puerto configurado: $puerto\n\n";
}

echo "========================================\n";
echo "Puerto de impresión: $puerto\n";
echo "Intervalo: {$INTERVALO}s\n";
echo "========================================\n\n";

$ultimo = obtenerUltimoPedido();
echo "Último pedido: #$ultimo\n";
echo "Esperando nuevos pedidos...\n\n";

// Loop
while (true) {
    try {
        $nuevos = obtenerPedidosNuevos($ultimo);
        
        if (count($nuevos) > 0) {
            echo "\n¡¡¡ NUEVOS PEDIDOS !!! (" . count($nuevos) . ")\n";
            echo "========================================\n";
            
            foreach ($nuevos as $id) {
                // Intentar método directo al puerto
                $ok = imprimirDirecto($id, $puerto);
                
                // Si falla, intentar método COPY
                if (!$ok) {
                    echo "Probando método alternativo...\n";
                    $ok = imprimirConComandoCopy($id, "POS-80"); // Cambiar "POS-80" por tu impresora
                }
                
                if ($ok) {
                    guardarUltimoPedido($id);
                    $ultimo = $id;
                } else {
                    echo "[WARN] No se pudo imprimir #$id\n";
                    echo "Verifica:\n";
                    echo "  1. Impresora encendida\n";
                    echo "  2. Puerto correcto\n";
                    echo "  3. Cable conectado\n\n";
                }
            }
        }
        
        sleep($INTERVALO);
        
    } catch (Exception $e) {
        echo "[ERROR] " . $e->getMessage() . "\n";
        sleep($INTERVALO);
    }
}