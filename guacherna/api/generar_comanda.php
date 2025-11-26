<?php
require_once '../config/database.php';

$pedido_id = intval($_GET['pedido_id'] ?? 0);
$debug = isset($_GET['debug']); // Agregar ?debug=1 para ver información de depuración

if ($pedido_id <= 0) {
    die('ID de pedido inválido');
}

// Obtener datos del pedido
$sql = "SELECT 
            p.id,
            p.estado,
            DATE_FORMAT(p.fecha_hora, '%d/%m/%Y') as fecha,
            DATE_FORMAT(p.fecha_hora, '%H:%i') as hora,
            c.nombre as cliente,
            c.telefono
        FROM pedidos p
        INNER JOIN clientes c ON p.cliente_id = c.id
        WHERE p.id = ?";

$stmt = ejecutarConsulta($sql, [$pedido_id]);
$pedido = $stmt->fetch();

if (!$pedido) {
    die('Pedido no encontrado');
}

// Obtener items del pedido con notas - MEJORADO
$sql = "SELECT 
            pi.cantidad,
            p.nombre,
            pi.notas,
            pi.id as item_id
        FROM pedido_items pi
        INNER JOIN productos p ON pi.producto_id = p.id
        WHERE pi.pedido_id = ?
        ORDER BY p.nombre";

$stmt = ejecutarConsulta($sql, [$pedido_id]);
$items = $stmt->fetchAll();

// Debug: Mostrar información si se solicita
if ($debug) {
    echo "<pre style='background:#f0f0f0; padding:10px; border:2px solid #f00; margin:10px;'>";
    echo "=== DEBUG INFO ===\n";
    echo "Pedido ID: " . $pedido_id . "\n";
    echo "Total items: " . count($items) . "\n\n";
    
    foreach ($items as $idx => $item) {
        echo "Item #" . ($idx + 1) . ":\n";
        echo "  ID: " . $item['item_id'] . "\n";
        echo "  Producto: " . $item['nombre'] . "\n";
        echo "  Cantidad: " . $item['cantidad'] . "\n";
        echo "  Notas: " . ($item['notas'] ? '"' . $item['notas'] . '"' : 'NULL/VACÍO') . "\n";
        echo "  empty(notas): " . (empty($item['notas']) ? 'true' : 'false') . "\n";
        echo "  is_null(notas): " . (is_null($item['notas']) ? 'true' : 'false') . "\n";
        echo "\n";
    }
    echo "=================\n";
    echo "</pre>";
}

// Determinar estado en español
$estados = [
    'pendiente' => 'PENDIENTE',
    'preparando' => 'EN PREPARACIÓN',
    'listo' => 'LISTO',
    'pagado' => 'PAGADO',
    'entregado' => 'ENTREGADO'
];
$estado_texto = $estados[$pedido['estado']] ?? 'PENDIENTE';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Comanda #<?php echo $pedido['id']; ?></title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Courier New', monospace;
            font-size: 14px;
            line-height: 1.5;
            padding: 8px;
            width: 80mm;
        }
        
        @media print {
            body {
                padding: 0;
            }
            
            @page {
                margin: 0;
                size: 80mm auto;
            }
            
            .no-print {
                display: none !important;
            }
        }
        
        .header {
            text-align: center;
            margin-bottom: 8px;
        }
        
        .restaurant {
            font-size: 18px;
            font-weight: bold;
        }
        
        .subtitle {
            font-size: 13px;
            margin-top: 2px;
        }
        
        .separator {
            border-bottom: 1px dashed #000;
            margin: 8px 0;
        }
        
        .order-number {
            font-size: 28px;
            font-weight: bold;
            text-align: center;
            margin: 8px 0;
        }
        
        .priority {
            text-align: center;
            font-weight: bold;
            font-size: 13px;
            margin: 8px 0;
            padding: 5px 0;
            background: #000;
            color: #fff;
        }
        
        .info-row {
            display: flex;
            justify-content: space-between;
            margin: 3px 0;
            font-size: 13px;
        }
        
        .section-title {
            font-weight: bold;
            text-align: center;
            margin: 10px 0 5px 0;
            font-size: 15px;
        }
        
        .items-list {
            font-size: 15px;
            line-height: 1.7;
            margin: 5px 0;
        }
        
        .item {
            margin-bottom: 10px;
        }
        
        .item-line {
            font-weight: bold;
            font-size: 16px;
        }
        
        .item-notas {
            margin-left: 10px;
            font-size: 14px;
            color: #333;
            margin-top: 3px;
            line-height: 1.5;
        }
        
        .footer {
            text-align: center;
            margin-top: 10px;
            font-size: 12px;
            border-top: 1px dashed #000;
            padding-top: 8px;
        }
        
        .print-button {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background: #4CAF50;
            color: white;
            border: none;
            padding: 15px 30px;
            font-size: 16px;
            cursor: pointer;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.3);
            z-index: 1000;
        }
        
        .print-button:hover {
            background: #45a049;
        }
        
        .debug-button {
            position: fixed;
            bottom: 80px;
            right: 20px;
            background: #ff9800;
            color: white;
            border: none;
            padding: 10px 20px;
            font-size: 14px;
            cursor: pointer;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.3);
            z-index: 1000;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="restaurant">GUACHERNA BURGERS</div>
        <div class="subtitle">COMANDA DE COCINA</div>
    </div>
    
    <div class="separator"></div>
    
    <div class="order-number">#<?php echo $pedido['id']; ?></div>
    
    <div class="separator"></div>
    
    <div class="priority">PREPARAR INMEDIATAMENTE</div>
    
    <div class="separator"></div>
    
    <div class="info-row">
        <span>Fecha:</span>
        <span><?php echo $pedido['fecha']; ?></span>
    </div>
    <div class="info-row">
        <span>Hora:</span>
        <span><?php echo $pedido['hora']; ?></span>
    </div>
    <div class="info-row">
        <span>Estado:</span>
        <span><?php echo $estado_texto; ?></span>
    </div>
    <div class="info-row">
        <span>Cliente:</span>
        <span><?php echo substr($pedido['cliente'], 0, 20); ?></span>
    </div>
    
    <div class="separator"></div>
    
    <div class="section-title">ITEMS DEL PEDIDO</div>
    
    <div class="separator"></div>
    
    <div class="items-list">
        <?php foreach ($items as $item): ?>
            <div class="item">
                <div class="item-line">
                    <?php echo $item['cantidad']; ?>x <?php echo $item['nombre']; ?>
                </div>
                <?php 
                // Verificar si hay notas de múltiples formas
                $tieneNotas = !empty($item['notas']) && trim($item['notas']) !== '';
                ?>
                <?php if ($tieneNotas): ?>
                    <div class="item-notas">
                        * <?php echo htmlspecialchars(trim($item['notas'])); ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>
    
    <div class="footer">
        Verificar items antes de entregar
    </div>
    
    <button class="print-button no-print" onclick="window.print()">
        🖨️ IMPRIMIR
    </button>
    
    <?php if (!$debug): ?>
    <button class="debug-button no-print" onclick="window.location.href='?pedido_id=<?php echo $pedido_id; ?>&debug=1'">
        🐛 DEBUG
    </button>
    <?php endif; ?>
    
    <script>
        // Imprimir automáticamente al cargar (solo si no está en modo debug)
        window.onload = function() {
            <?php if (!$debug): ?>
            setTimeout(function() {
                window.print();
            }, 500);
            <?php endif; ?>
        };
    </script>
</body>
</html>