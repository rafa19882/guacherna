<?php
// Habilitar reporte de errores para debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');

// Verificar que database.php existe
$database_paths = [
    '../../config/database.php',
    '../config/database.php',
    dirname(__FILE__) . '/../../config/database.php',
    dirname(dirname(dirname(__FILE__))) . '/config/database.php'
];

$database_found = false;
foreach ($database_paths as $path) {
    if (file_exists($path)) {
        require_once $path;
        $database_found = true;
        break;
    }
}

if (!$database_found) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: No se encontró config/database.php',
        'rutas_probadas' => $database_paths,
        'directorio_actual' => __DIR__
    ]);
    exit;
}

$action = $_POST['action'] ?? '';

try {
    switch ($action) {
        case 'subir_imagen':
            subirImagen();
            break;
        
        case 'eliminar_imagen':
            eliminarImagen();
            break;
        
        case 'verificar_sistema':
            verificarSistema();
            break;
        
        default:
            throw new Exception('Acción no válida. Acciones disponibles: subir_imagen, eliminar_imagen, verificar_sistema');
    }
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'archivo' => basename($e->getFile()),
        'linea' => $e->getLine()
    ]);
}

function verificarSistema() {
    // Verificar estructura de BD
    $sql = "SHOW COLUMNS FROM productos";
    $stmt = ejecutarConsulta($sql);
    $columns = $stmt->fetchAll();
    
    $hasEmoji = false;
    $hasImagenUrl = false;
    $allColumns = [];
    
    foreach ($columns as $col) {
        $allColumns[] = $col['Field'];
        if ($col['Field'] === 'emoji') $hasEmoji = true;
        if ($col['Field'] === 'imagen_url') $hasImagenUrl = true;
    }
    
    // Verificar directorio
    $uploadDir = '../../assets/images/productos/';
    $dirExists = file_exists($uploadDir);
    $dirWritable = is_writable($uploadDir);
    
    // Contar imágenes
    $imageCount = 0;
    if ($dirExists) {
        $files = glob($uploadDir . '*.{jpg,jpeg,png,gif,webp}', GLOB_BRACE);
        $imageCount = count($files);
    }
    
    echo json_encode([
        'success' => true,
        'base_datos' => [
            'tiene_emoji' => $hasEmoji,
            'tiene_imagen_url' => $hasImagenUrl,
            'columnas_disponibles' => $allColumns,
            'columna_a_usar' => $hasImagenUrl ? 'imagen_url' : ($hasEmoji ? 'emoji' : 'NINGUNA')
        ],
        'directorio' => [
            'ruta' => $uploadDir,
            'existe' => $dirExists,
            'escribible' => $dirWritable,
            'imagenes_guardadas' => $imageCount
        ],
        'recomendacion' => getRecomendacion($hasEmoji, $hasImagenUrl, $dirExists, $dirWritable)
    ]);
}

function getRecomendacion($hasEmoji, $hasImagenUrl, $dirExists, $dirWritable) {
    $problemas = [];
    $soluciones = [];
    
    if (!$hasEmoji && !$hasImagenUrl) {
        $problemas[] = 'No existe columna para guardar imágenes';
        $soluciones[] = 'Ejecutar script SQL: actualizar_bd.sql';
    }
    
    if (!$dirExists) {
        $problemas[] = 'El directorio de imágenes no existe';
        $soluciones[] = 'Crear: assets/images/productos/ con permisos 755';
    }
    
    if ($dirExists && !$dirWritable) {
        $problemas[] = 'El directorio existe pero no tiene permisos de escritura';
        $soluciones[] = 'Ejecutar: chmod 755 assets/images/productos/';
    }
    
    if (empty($problemas)) {
        return '✅ Sistema listo para subir imágenes';
    }
    
    return [
        'problemas' => $problemas,
        'soluciones' => $soluciones
    ];
}

function subirImagen() {
    // Verificar archivo
    if (!isset($_FILES['imagen'])) {
        throw new Exception('No se recibió ningún archivo. Verifica el nombre del campo: debe ser "imagen"');
    }
    
    if ($_FILES['imagen']['error'] !== UPLOAD_ERR_OK) {
        $errores = [
            UPLOAD_ERR_INI_SIZE => 'El archivo excede el tamaño máximo permitido por PHP',
            UPLOAD_ERR_FORM_SIZE => 'El archivo excede el tamaño máximo del formulario',
            UPLOAD_ERR_PARTIAL => 'El archivo se subió parcialmente',
            UPLOAD_ERR_NO_FILE => 'No se subió ningún archivo',
            UPLOAD_ERR_NO_TMP_DIR => 'Falta carpeta temporal',
            UPLOAD_ERR_CANT_WRITE => 'Error al escribir en disco',
            UPLOAD_ERR_EXTENSION => 'Extensión PHP bloqueó la subida'
        ];
        $error_msg = $errores[$_FILES['imagen']['error']] ?? 'Error desconocido: ' . $_FILES['imagen']['error'];
        throw new Exception($error_msg);
    }
    
    $file = $_FILES['imagen'];
    $producto_id = intval($_POST['producto_id'] ?? 0);
    
    if ($producto_id <= 0) {
        throw new Exception('ID de producto inválido: ' . ($producto_id ?: 'vacío'));
    }
    
    // Verificar producto existe
    $sql = "SELECT nombre FROM productos WHERE id = ?";
    $stmt = ejecutarConsulta($sql, [$producto_id]);
    $producto = $stmt->fetch();
    if (!$producto) {
        throw new Exception("El producto #$producto_id no existe en la base de datos");
    }
    
    // Validar tipo MIME
    $tiposPermitidos = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp', 'image/gif'];
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    
    if (!in_array($mimeType, $tiposPermitidos)) {
        throw new Exception("Tipo de archivo no permitido: $mimeType. Solo JPG, PNG, WEBP y GIF");
    }
    
    // Validar tamaño
    $maxSize = 5 * 1024 * 1024; // 5MB
    if ($file['size'] > $maxSize) {
        $sizeMB = round($file['size'] / 1024 / 1024, 2);
        throw new Exception("Archivo demasiado grande: {$sizeMB}MB. Máximo: 5MB");
    }
    
    // Validar que es imagen real
    $imageInfo = getimagesize($file['tmp_name']);
    if ($imageInfo === false) {
        throw new Exception('El archivo no es una imagen válida');
    }
    
    // Crear directorio si no existe
    $uploadDir = '../../assets/images/productos/';
    if (!file_exists($uploadDir)) {
        if (!mkdir($uploadDir, 0755, true)) {
            throw new Exception('No se pudo crear el directorio: ' . $uploadDir);
        }
    }
    
    // Verificar permisos
    if (!is_writable($uploadDir)) {
        throw new Exception('El directorio no tiene permisos de escritura: ' . $uploadDir);
    }
    
    // Generar nombre único
    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (empty($extension)) {
        $extension = 'jpg';
    }
    $nombreArchivo = 'producto_' . $producto_id . '_' . time() . '.' . $extension;
    $rutaDestino = $uploadDir . $nombreArchivo;
    
    // Detectar columna a usar
    $sql = "SHOW COLUMNS FROM productos";
    $stmt = ejecutarConsulta($sql);
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    if (!in_array('emoji', $columns) && !in_array('imagen_url', $columns)) {
        throw new Exception('La tabla productos no tiene columna para guardar imágenes (ni emoji ni imagen_url). Ejecuta el script SQL.');
    }
    
    $columnName = in_array('imagen_url', $columns) ? 'imagen_url' : 'emoji';
    
    // Obtener imagen anterior
    $sql = "SELECT $columnName as imagen_actual FROM productos WHERE id = ?";
    $stmt = ejecutarConsulta($sql, [$producto_id]);
    $productoAnterior = $stmt->fetch();
    $imagenAnterior = $productoAnterior['imagen_actual'] ?? '';
    
    // Mover archivo
    if (!move_uploaded_file($file['tmp_name'], $rutaDestino)) {
        throw new Exception('Error al mover el archivo a: ' . $rutaDestino);
    }
    
    // Verificar que el archivo se guardó
    if (!file_exists($rutaDestino)) {
        throw new Exception('El archivo se movió pero no existe en destino: ' . $rutaDestino);
    }
    
    // Actualizar BD
    $rutaBD = 'assets/images/productos/' . $nombreArchivo;
    $sql = "UPDATE productos SET $columnName = ? WHERE id = ?";
    
    try {
        $resultado = ejecutarAccion($sql, [$rutaBD, $producto_id]);
        
        if ($resultado === false) {
            // Eliminar archivo si falla BD
            @unlink($rutaDestino);
            throw new Exception('ejecutarAccion() devolvió false. No se pudo actualizar la BD.');
        }
        
    } catch (Exception $e) {
        // Eliminar archivo si falla BD
        @unlink($rutaDestino);
        throw new Exception('Error al actualizar BD: ' . $e->getMessage());
    }
    
    // Verificar que se guardó correctamente
    $sqlVerificar = "SELECT $columnName as imagen_guardada FROM productos WHERE id = ?";
    $stmtVerificar = ejecutarConsulta($sqlVerificar, [$producto_id]);
    $verificacion = $stmtVerificar->fetch();
    
    if (!$verificacion || $verificacion['imagen_guardada'] !== $rutaBD) {
        // Eliminar archivo si la verificación falla
        @unlink($rutaDestino);
        $guardado = $verificacion['imagen_guardada'] ?? 'NULL';
        throw new Exception("Verificación falló. Esperado: $rutaBD, Guardado: $guardado");
    }
    
    // Eliminar imagen anterior
    if (!empty($imagenAnterior) && strpos($imagenAnterior, '/') !== false && $imagenAnterior !== $rutaBD) {
        $rutaImagenAnterior = '../../' . $imagenAnterior;
        if (file_exists($rutaImagenAnterior)) {
            @unlink($rutaImagenAnterior);
        }
    }
    
    echo json_encode([
        'success' => true,
        'message' => '✅ Imagen subida y verificada exitosamente',
        'detalles' => [
            'producto_id' => $producto_id,
            'producto_nombre' => $producto['nombre'],
            'archivo' => $nombreArchivo,
            'ruta_bd' => $rutaBD,
            'columna_usada' => $columnName,
            'tamano' => round($file['size'] / 1024, 2) . ' KB',
            'tipo' => $mimeType,
            'dimensiones' => $imageInfo[0] . 'x' . $imageInfo[1]
        ]
    ]);
}

function eliminarImagen() {
    $producto_id = intval($_POST['producto_id'] ?? 0);
    
    if ($producto_id <= 0) {
        throw new Exception('ID de producto inválido');
    }
    
    // Detectar columna
    $sql = "SHOW COLUMNS FROM productos";
    $stmt = ejecutarConsulta($sql);
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    $columnName = in_array('imagen_url', $columns) ? 'imagen_url' : 'emoji';
    
    // Obtener imagen actual
    $sql = "SELECT $columnName as imagen_actual FROM productos WHERE id = ?";
    $stmt = ejecutarConsulta($sql, [$producto_id]);
    $producto = $stmt->fetch();
    
    if (!$producto) {
        throw new Exception('El producto no existe');
    }
    
    $imagenActual = $producto['imagen_actual'] ?? '';
    
    // Actualizar BD
    $sql = "UPDATE productos SET $columnName = '🍔' WHERE id = ?";
    
    if (!ejecutarAccion($sql, [$producto_id])) {
        throw new Exception('Error al actualizar la base de datos');
    }
    
    // Eliminar archivo físico
    if (!empty($imagenActual) && strpos($imagenActual, '/') !== false) {
        $rutaImagen = '../../' . $imagenActual;
        if (file_exists($rutaImagen)) {
            if (@unlink($rutaImagen)) {
                $archivoEliminado = true;
            } else {
                $archivoEliminado = false;
            }
        } else {
            $archivoEliminado = 'no_existia';
        }
    } else {
        $archivoEliminado = 'no_era_archivo';
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Imagen eliminada. Se usará emoji por defecto (🍔)',
        'archivo_eliminado' => $archivoEliminado
    ]);
}
?>