<?php
/**
 * DIAGNÓSTICO DE NOTAS
 * Pon este archivo en: api/test_notas.php
 * Ábrelo en: http://localhost/guacherna/api/test_notas.php
 */

require_once '../config/database.php';

echo "<h1>🔍 DIAGNÓSTICO DE NOTAS</h1>";
echo "<style>
    body { font-family: Arial; padding: 20px; background: #f5f5f5; }
    .ok { color: green; font-weight: bold; }
    .error { color: red; font-weight: bold; }
    .box { background: white; padding: 15px; margin: 10px 0; border-radius: 5px; }
    table { width: 100%; border-collapse: collapse; margin-top: 10px; }
    th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
    th { background: #4CAF50; color: white; }
    pre { background: #f0f0f0; padding: 10px; border-radius: 3px; }
</style>";

// 1. Verificar estructura de pedido_items
echo "<div class='box'>";
echo "<h2>1️⃣ Estructura de tabla pedido_items</h2>";

$sql = "SHOW COLUMNS FROM pedido_items";
$stmt = ejecutarConsulta($sql);
$columns = $stmt->fetchAll();

echo "<table>";
echo "<tr><th>Campo</th><th>Tipo</th><th>Null</th><th>Default</th></tr>";
$tieneNotas = false;
foreach ($columns as $col) {
    echo "<tr>";
    echo "<td>" . $col['Field'] . "</td>";
    echo "<td>" . $col['Type'] . "</td>";
    echo "<td>" . $col['Null'] . "</td>";
    echo "<td>" . ($col['Default'] ? $col['Default'] : 'NULL') . "</td>";
    echo "</tr>";
    
    if ($col['Field'] === 'notas') {
        $tieneNotas = true;
    }
}
echo "</table>";

if ($tieneNotas) {
    echo "<p class='ok'>✅ La columna 'notas' existe</p>";
} else {
    echo "<p class='error'>❌ La columna 'notas' NO existe. Ejecuta:</p>";
    echo "<pre>ALTER TABLE pedido_items ADD COLUMN notas TEXT NULL AFTER subtotal;</pre>";
}

echo "</div>";

// 2. Ver últimos 5 pedidos con sus items y notas
echo "<div class='box'>";
echo "<h2>2️⃣ Últimos 5 pedidos con items y notas</h2>";

$sql = "SELECT 
            p.id as pedido_id,
            p.fecha_hora,
            pi.id as item_id,
            prod.nombre as producto,
            pi.cantidad,
            pi.notas
        FROM pedidos p
        INNER JOIN pedido_items pi ON p.id = pi.pedido_id
        INNER JOIN productos prod ON pi.producto_id = prod.id
        ORDER BY p.id DESC, pi.id DESC
        LIMIT 10";

$stmt = ejecutarConsulta($sql);
$items = $stmt->fetchAll();

if (count($items) > 0) {
    echo "<table>";
    echo "<tr><th>Pedido ID</th><th>Fecha/Hora</th><th>Producto</th><th>Cant.</th><th>Notas</th></tr>";
    
    $conNotas = 0;
    $sinNotas = 0;
    
    foreach ($items as $item) {
        $tieneNota = !empty($item['notas']);
        if ($tieneNota) $conNotas++;
        else $sinNotas++;
        
        $notasDisplay = $tieneNota ? htmlspecialchars($item['notas']) : '<em style="color:#999;">Sin notas</em>';
        
        echo "<tr style='background:" . ($tieneNota ? '#e8f5e9' : 'white') . "'>";
        echo "<td><strong>#" . $item['pedido_id'] . "</strong></td>";
        echo "<td>" . $item['fecha_hora'] . "</td>";
        echo "<td>" . $item['producto'] . "</td>";
        echo "<td>" . $item['cantidad'] . "</td>";
        echo "<td>" . $notasDisplay . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    echo "<p>📊 <strong>Items con notas:</strong> $conNotas | <strong>Items sin notas:</strong> $sinNotas</p>";
    
    if ($conNotas === 0) {
        echo "<p class='error'>⚠️ NINGÚN item tiene notas guardadas</p>";
        echo "<p>Esto significa que las notas NO se están enviando desde el frontend.</p>";
    } else {
        echo "<p class='ok'>✅ Hay items con notas en la base de datos</p>";
    }
    
} else {
    echo "<p class='error'>❌ No hay pedidos en la base de datos</p>";
}

echo "</div>";

// 3. Simular creación de pedido con notas
echo "<div class='box'>";
echo "<h2>3️⃣ Prueba: Crear pedido de prueba con notas</h2>";

echo "<form method='POST' style='background:#f0f0f0; padding:15px; border-radius:5px;'>";
echo "<p>Esta prueba creará un pedido temporal para verificar que las notas se guardan correctamente.</p>";
echo "<button type='submit' name='test_crear' style='background:#2196F3; color:white; border:none; padding:10px 20px; border-radius:5px; cursor:pointer;'>🧪 Crear Pedido de Prueba</button>";
echo "</form>";

if (isset($_POST['test_crear'])) {
    echo "<hr style='margin:15px 0;'>";
    
    try {
        // Obtener un cliente y producto de prueba
        $sqlCliente = "SELECT id FROM clientes LIMIT 1";
        $stmtCliente = ejecutarConsulta($sqlCliente);
        $cliente = $stmtCliente->fetch();
        
        $sqlProducto = "SELECT id, nombre, precio FROM productos WHERE activo = 1 LIMIT 1";
        $stmtProducto = ejecutarConsulta($sqlProducto);
        $producto = $stmtProducto->fetch();
        
        if (!$cliente || !$producto) {
            echo "<p class='error'>❌ No hay clientes o productos en la BD para hacer la prueba</p>";
        } else {
            // Crear pedido
            $sqlPedido = "INSERT INTO pedidos (cliente_id, total, estado, direccion_entrega, numero_entrega, metodo_pago) 
                         VALUES (?, ?, 'pendiente', 'Test', '123', 'efectivo')";
            ejecutarAccion($sqlPedido, [$cliente['id'], $producto['precio']]);
            $pedidoId = ultimoId();
            
            // Crear item con notas
            $notasTest = "PRUEBA: Sin cebolla, con extra queso";
            $sqlItem = "INSERT INTO pedido_items (pedido_id, producto_id, cantidad, precio_unitario, subtotal, notas) 
                       VALUES (?, ?, 1, ?, ?, ?)";
            ejecutarAccion($sqlItem, [
                $pedidoId,
                $producto['id'],
                $producto['precio'],
                $producto['precio'],
                $notasTest
            ]);
            
            echo "<p class='ok'>✅ Pedido de prueba creado: #$pedidoId</p>";
            echo "<p>📝 Notas guardadas: <strong>$notasTest</strong></p>";
            
            // Verificar que se guardó
            $sqlVerify = "SELECT notas FROM pedido_items WHERE pedido_id = ?";
            $stmtVerify = ejecutarConsulta($sqlVerify, [$pedidoId]);
            $itemVerify = $stmtVerify->fetch();
            
            if ($itemVerify && !empty($itemVerify['notas'])) {
                echo "<p class='ok'>✅ Las notas se guardaron correctamente en la BD</p>";
                echo "<p><a href='generar_comanda.php?pedido_id=$pedidoId' target='_blank' style='display:inline-block; background:#4CAF50; color:white; padding:10px 20px; text-decoration:none; border-radius:5px;'>🖨️ Ver Comanda del Pedido #$pedidoId</a></p>";
            } else {
                echo "<p class='error'>❌ Las notas NO se guardaron correctamente</p>";
            }
        }
        
    } catch (Exception $e) {
        echo "<p class='error'>❌ Error: " . $e->getMessage() . "</p>";
    }
}

echo "</div>";

// 4. Verificar el código JavaScript
echo "<div class='box'>";
echo "<h2>4️⃣ Verificando app-modern.js</h2>";

if (file_exists('../js/app-modern.js')) {
    $jsContent = file_get_contents('../js/app-modern.js');
    
    // Verificar que las notas se envían en el JSON
    if (strpos($jsContent, "JSON.stringify(cart)") !== false) {
        echo "<p class='ok'>✅ El carrito se envía como JSON al servidor</p>";
    }
    
    if (strpos($jsContent, "actualizarNotasItem") !== false) {
        echo "<p class='ok'>✅ Función para actualizar notas existe</p>";
        
        // Mostrar la función
        $lines = explode("\n", $jsContent);
        $inFunction = false;
        $functionCode = "";
        
        foreach ($lines as $line) {
            if (strpos($line, "function actualizarNotasItem") !== false) {
                $inFunction = true;
            }
            if ($inFunction) {
                $functionCode .= $line . "\n";
                if (trim($line) === "}" && $inFunction) {
                    break;
                }
            }
        }
        
        echo "<p>Código de la función:</p>";
        echo "<pre>" . htmlspecialchars($functionCode) . "</pre>";
    }
    
} else {
    echo "<p class='error'>❌ No se encuentra app-modern.js</p>";
}

echo "</div>";

// 5. Instrucciones
echo "<div class='box' style='background:#fff3cd; border:2px solid #ffc107;'>";
echo "<h2>🎯 ¿Qué hacer ahora?</h2>";

echo "<h3>Si NO ves notas en los últimos pedidos:</h3>";
echo "<ol>";
echo "<li><strong>El problema está en el frontend</strong> - Las notas no se están enviando</li>";
echo "<li>Haz la prueba: Crea un pedido nuevo desde el catálogo</li>";
echo "<li>En el carrito, escribe algo en el campo de notas</li>";
echo "<li>Completa el pedido</li>";
echo "<li>Vuelve a esta página y verifica si las notas aparecen en la tabla</li>";
echo "</ol>";

echo "<h3>Si SÍ ves notas en la tabla pero NO en la comanda:</h3>";
echo "<ol>";
echo "<li><strong>El problema está en generar_comanda.php</strong></li>";
echo "<li>Presiona F12 en la comanda</li>";
echo "<li>Ve a la pestaña 'Console' y busca errores</li>";
echo "</ol>";

echo "<h3>Para probar todo el flujo:</h3>";
echo "<ol>";
echo "<li>Haz clic en el botón azul arriba para crear un pedido de prueba</li>";
echo "<li>Abre la comanda del pedido de prueba</li>";
echo "<li>Verifica que las notas aparecen con asterisco (*)</li>";
echo "</ol>";

echo "</div>";

echo "<hr style='margin:30px 0;'>";
echo "<p style='text-align:center; color:#666;'>Diagnóstico de Notas - " . date('d/m/Y H:i:s') . "</p>";
?>