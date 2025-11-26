<?php
/**
 * DIAGNÓSTICO DE OFERTAS - TIEMPO REAL
 * Coloca este archivo en la raíz de tu proyecto y ábrelo en el navegador
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: text/html; charset=utf-8');

require_once 'config/database.php';

echo "<!DOCTYPE html>
<html lang='es'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Diagnóstico de Ofertas</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; background: #f5f5f5; }
        .container { max-width: 1200px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { color: #2c3e50; border-bottom: 3px solid #3498db; padding-bottom: 10px; }
        h2 { color: #34495e; margin-top: 30px; background: #ecf0f1; padding: 10px; border-radius: 5px; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th { background: #3498db; color: white; padding: 12px; text-align: left; }
        td { padding: 10px; border-bottom: 1px solid #ddd; }
        tr:hover { background: #f8f9fa; }
        .badge { padding: 5px 10px; border-radius: 5px; font-weight: bold; font-size: 12px; }
        .badge-success { background: #27ae60; color: white; }
        .badge-danger { background: #e74c3c; color: white; }
        .badge-warning { background: #f39c12; color: white; }
        .alert { padding: 15px; margin: 20px 0; border-radius: 5px; }
        .alert-success { background: #d4edda; border: 1px solid #c3e6cb; color: #155724; }
        .alert-danger { background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; }
        .alert-info { background: #d1ecf1; border: 1px solid #bee5eb; color: #0c5460; }
        .big-number { font-size: 48px; font-weight: bold; color: #e74c3c; text-align: center; margin: 20px 0; }
    </style>
</head>
<body>
<div class='container'>";

echo "<h1>🔍 DIAGNÓSTICO DE OFERTAS EN TIEMPO REAL</h1>";
echo "<p><b>Fecha/Hora del servidor:</b> " . date('Y-m-d H:i:s') . "</p>";
echo "<hr>";

try {
    // 1. Verificar columnas
    echo "<h2>1. ✅ Verificación de Estructura</h2>";
    $sql = "SHOW COLUMNS FROM productos";
    $stmt = ejecutarConsulta($sql);
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $columnas_necesarias = ['en_oferta', 'precio_oferta', 'fecha_inicio_oferta', 'fecha_fin_oferta'];
    $columnas_encontradas = array_column($columns, 'Field');
    
    echo "<table>";
    echo "<tr><th>Columna</th><th>Estado</th></tr>";
    foreach ($columnas_necesarias as $col) {
        $existe = in_array($col, $columnas_encontradas);
        $badge = $existe ? "<span class='badge badge-success'>✓ Existe</span>" : "<span class='badge badge-danger'>✗ Falta</span>";
        echo "<tr><td>$col</td><td>$badge</td></tr>";
    }
    echo "</table>";
    
    // 2. Conteo general
    echo "<h2>2. 📊 Estadísticas Generales</h2>";
    $sql = "SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN en_oferta = 1 THEN 1 ELSE 0 END) as con_oferta,
                SUM(CASE WHEN en_oferta = 1 AND activo = 1 THEN 1 ELSE 0 END) as ofertas_activas
            FROM productos";
    $stmt = ejecutarConsulta($sql);
    $stats = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "<table>";
    echo "<tr><th>Métrica</th><th>Cantidad</th></tr>";
    echo "<tr><td>Total de productos</td><td><b>{$stats['total']}</b></td></tr>";
    echo "<tr><td>Productos con en_oferta=1</td><td><b>{$stats['con_oferta']}</b></td></tr>";
    echo "<tr><td><b>🎯 Ofertas activas (en_oferta=1 AND activo=1)</b></td><td><b style='color: #e74c3c; font-size: 24px;'>{$stats['ofertas_activas']}</b></td></tr>";
    echo "</table>";
    
    // 3. Productos con en_oferta = 1
    echo "<h2>3. 🔥 Productos con en_oferta = 1</h2>";
    $sql = "SELECT 
                id,
                nombre,
                precio,
                precio_oferta,
                activo,
                en_oferta,
                fecha_inicio_oferta,
                fecha_fin_oferta,
                ROUND(((precio - COALESCE(precio_oferta, precio)) / precio) * 100, 0) as descuento
            FROM productos 
            WHERE en_oferta = 1
            ORDER BY activo DESC, id ASC";
    $stmt = ejecutarConsulta($sql);
    $productos_oferta = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($productos_oferta) > 0) {
        echo "<table>";
        echo "<tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Precio</th>
                <th>Oferta</th>
                <th>Descuento</th>
                <th>Activo</th>
                <th>Fecha Inicio</th>
                <th>Fecha Fin</th>
              </tr>";
        
        foreach ($productos_oferta as $prod) {
            $activo_badge = $prod['activo'] == 1 
                ? "<span class='badge badge-success'>✓ ACTIVO</span>" 
                : "<span class='badge badge-danger'>✗ INACTIVO</span>";
            
            echo "<tr>";
            echo "<td><b>{$prod['id']}</b></td>";
            echo "<td>{$prod['nombre']}</td>";
            echo "<td>\${$prod['precio']}</td>";
            echo "<td><b style='color: #27ae60;'>\${$prod['precio_oferta']}</b></td>";
            echo "<td><b>-{$prod['descuento']}%</b></td>";
            echo "<td>{$activo_badge}</td>";
            echo "<td>" . ($prod['fecha_inicio_oferta'] ?: '<i>Sin fecha</i>') . "</td>";
            echo "<td>" . ($prod['fecha_fin_oferta'] ?: '<i>Sin fecha</i>') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<div class='alert alert-danger'>❌ NO hay productos con en_oferta = 1</div>";
    }
    
    // 4. Filtro de fechas (como lo hace productos.php)
    echo "<h2>4. 🎯 Ofertas que DEBERÍAN mostrarse (con filtro de fechas)</h2>";
    $sql = "SELECT 
                p.id,
                p.nombre,
                p.precio,
                p.precio_oferta,
                p.activo,
                p.en_oferta,
                p.fecha_inicio_oferta,
                p.fecha_fin_oferta,
                ROUND(((p.precio - p.precio_oferta) / p.precio) * 100, 0) as descuento,
                c.nombre as categoria
            FROM productos p
            INNER JOIN categorias c ON p.categoria_id = c.id
            WHERE p.activo = 1 
                AND c.activo = 1 
                AND p.en_oferta = 1
                AND p.precio_oferta IS NOT NULL
                AND p.precio_oferta < p.precio
                AND (p.fecha_inicio_oferta IS NULL OR p.fecha_inicio_oferta <= NOW())
                AND (p.fecha_fin_oferta IS NULL OR p.fecha_fin_oferta >= NOW())
            ORDER BY descuento DESC";
    
    $stmt = ejecutarConsulta($sql);
    $ofertas_visibles = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<div class='big-number'>" . count($ofertas_visibles) . "</div>";
    echo "<p style='text-align: center; font-size: 18px;'><b>Ofertas que deberían aparecer en el catálogo</b></p>";
    
    if (count($ofertas_visibles) > 0) {
        echo "<table>";
        echo "<tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Categoría</th>
                <th>Precio Original</th>
                <th>Precio Oferta</th>
                <th>Descuento</th>
                <th>Vigencia</th>
              </tr>";
        
        foreach ($ofertas_visibles as $oferta) {
            $vigencia = "Permanente";
            if ($oferta['fecha_inicio_oferta'] && $oferta['fecha_fin_oferta']) {
                $vigencia = "Hasta " . date('d/m/Y', strtotime($oferta['fecha_fin_oferta']));
            } elseif ($oferta['fecha_fin_oferta']) {
                $vigencia = "Hasta " . date('d/m/Y', strtotime($oferta['fecha_fin_oferta']));
            } elseif ($oferta['fecha_inicio_oferta']) {
                $vigencia = "Desde " . date('d/m/Y', strtotime($oferta['fecha_inicio_oferta']));
            }
            
            echo "<tr style='background: #d4edda;'>";
            echo "<td><b>{$oferta['id']}</b></td>";
            echo "<td><b>{$oferta['nombre']}</b></td>";
            echo "<td>{$oferta['categoria']}</td>";
            echo "<td><s>\${$oferta['precio']}</s></td>";
            echo "<td><b style='color: #27ae60; font-size: 18px;'>\${$oferta['precio_oferta']}</b></td>";
            echo "<td><span class='badge badge-warning'>-{$oferta['descuento']}%</span></td>";
            echo "<td>$vigencia</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        echo "<div class='alert alert-success'>";
        echo "<b>✅ ESTAS " . count($ofertas_visibles) . " OFERTA(S) ESTÁN CORRECTAMENTE CONFIGURADAS</b><br>";
        echo "Si no aparecen en tu catálogo, el problema está en el frontend (JavaScript).";
        echo "</div>";
    } else {
        echo "<div class='alert alert-danger'>";
        echo "<b>❌ NO HAY OFERTAS QUE CUMPLAN TODOS LOS CRITERIOS</b><br><br>";
        echo "<b>Criterios que se verifican:</b><br>";
        echo "• Producto activo (activo = 1)<br>";
        echo "• Categoría activa<br>";
        echo "• En oferta (en_oferta = 1)<br>";
        echo "• Tiene precio de oferta (precio_oferta IS NOT NULL)<br>";
        echo "• Precio oferta es menor al precio original<br>";
        echo "• Fecha inicio ya pasó (o sin fecha)<br>";
        echo "• Fecha fin no ha pasado (o sin fecha)<br>";
        echo "</div>";
    }
    
    // 5. Prueba del endpoint
    echo "<h2>5. 🔌 Prueba del Endpoint API</h2>";
    echo "<p>Llamando a <code>api/productos.php?action=ofertas</code>...</p>";
    
    $apiUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . 
              "://" . $_SERVER['HTTP_HOST'] . 
              dirname($_SERVER['PHP_SELF']) . "/api/productos.php?action=ofertas";
    
    $response = @file_get_contents($apiUrl);
    
    if ($response) {
        $data = json_decode($response, true);
        if ($data && isset($data['success']) && $data['success']) {
            $count = isset($data['total_ofertas']) ? $data['total_ofertas'] : count($data['productos']);
            echo "<div class='alert alert-success'>";
            echo "<b>✅ API funcionando correctamente</b><br>";
            echo "Devuelve <b>$count</b> oferta(s)";
            echo "</div>";
            
            if ($count > 0) {
                echo "<pre style='background: #f5f5f5; padding: 15px; border-radius: 5px; overflow-x: auto;'>";
                echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
                echo "</pre>";
            }
        } else {
            echo "<div class='alert alert-danger'>";
            echo "<b>❌ API devuelve error</b><br>";
            echo "<pre>" . htmlspecialchars($response) . "</pre>";
            echo "</div>";
        }
    } else {
        echo "<div class='alert alert-warning'>";
        echo "<b>⚠️ No se pudo conectar al API</b><br>";
        echo "URL probada: <code>$apiUrl</code>";
        echo "</div>";
    }
    
    // 6. Consulta SQL para copiar/pegar
    echo "<h2>6. 📝 Consulta SQL Recomendada</h2>";
    echo "<p>Esta es la consulta que usa tu sistema para obtener ofertas:</p>";
    echo "<pre style='background: #2c3e50; color: #fff; padding: 15px; border-radius: 5px; overflow-x: auto;'>";
    echo "SELECT 
    p.id,
    p.nombre,
    p.descripcion,
    p.precio,
    p.en_oferta,
    p.precio_oferta,
    p.fecha_inicio_oferta,
    p.fecha_fin_oferta,
    p.imagen_url as emoji,
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
ORDER BY porcentaje_descuento DESC;";
    echo "</pre>";
    
    echo "<p><b>Puedes copiar y ejecutar esta consulta en phpMyAdmin para ver los resultados</b></p>";
    
} catch (Exception $e) {
    echo "<div class='alert alert-danger'>";
    echo "<b>❌ ERROR:</b> " . $e->getMessage();
    echo "</div>";
}

echo "</div></body></html>";
?>