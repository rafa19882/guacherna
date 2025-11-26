<?php
/**
 * PRUEBA CON COM DE WINDOWS
 * 
 * Este método NO requiere compartir la impresora
 * Usa el objeto COM de Windows para imprimir directamente
 */

echo "\n";
echo "========================================\n";
echo "   PRUEBA CON COM DE WINDOWS\n";
echo "========================================\n";
echo "Impresora: POSPrinter POS80\n";
echo "Metodo: Windows COM (Directo)\n";
echo "Sin necesidad de compartir\n";
echo "========================================\n\n";

// Verificar que COM esté disponible
if (!class_exists('COM')) {
    echo "[ERROR] Extension COM no disponible en PHP\n";
    echo "\nPara habilitarla:\n";
    echo "1. Abre: C:\\xampp\\php\\php.ini\n";
    echo "2. Busca: ;extension=com_dotnet\n";
    echo "3. Quita el ; para que quede: extension=com_dotnet\n";
    echo "4. Reinicia Apache\n";
    echo "5. Ejecuta este script de nuevo\n\n";
    exit(1);
}

// Comandos ESC/POS
class ESC {
    const INIT = "\x1B\x40";
    const BEEP = "\x1B\x42\x05\x09";
    const ALIGN_CENTER = "\x1B\x61\x01";
    const ALIGN_LEFT = "\x1B\x61\x00";
    const BOLD_ON = "\x1B\x45\x01";
    const BOLD_OFF = "\x1B\x45\x00";
    const FONT_LARGE = "\x1D\x21\x11";
    const FONT_NORMAL = "\x1D\x21\x00";
    const CUT = "\x1D\x56\x00";
    const LF = "\x0A";
}

// Generar ticket
$ticket = "";
$ticket .= ESC::INIT;
$ticket .= ESC::BEEP;
$ticket .= ESC::ALIGN_CENTER;
$ticket .= ESC::FONT_LARGE;
$ticket .= ESC::BOLD_ON;
$ticket .= "GUACHERNA BURGERS" . ESC::LF;
$ticket .= ESC::BOLD_OFF;
$ticket .= ESC::FONT_NORMAL;
$ticket .= "Prueba COM Windows" . ESC::LF;
$ticket .= ESC::LF;
$ticket .= ESC::ALIGN_LEFT;
$ticket .= str_repeat("=", 48) . ESC::LF;
$ticket .= "Fecha: " . date('d/m/Y H:i:s') . ESC::LF;
$ticket .= "Impresora: POSPrinter POS80" . ESC::LF;
$ticket .= "Metodo: COM Windows" . ESC::LF;
$ticket .= ESC::LF;
$ticket .= ESC::BOLD_ON;
$ticket .= "Si ves esto impreso:" . ESC::LF;
$ticket .= ESC::BOLD_OFF;
$ticket .= "TU IMPRESORA FUNCIONA" . ESC::LF;
$ticket .= "SIN NECESIDAD DE COMPARTIR" . ESC::LF;
$ticket .= ESC::LF;
$ticket .= str_repeat("=", 48) . ESC::LF;
$ticket .= ESC::LF;
$ticket .= ESC::LF;
$ticket .= ESC::CUT;

echo "Generando ticket...\n";
echo "Bytes: " . strlen($ticket) . "\n\n";

echo "Intentando imprimir con COM de Windows...\n\n";

try {
    // Crear objeto COM para shell de Windows
    $shell = new COM("WScript.Shell");
    
    // Guardar archivo temporal
    $tempFile = sys_get_temp_dir() . "\\comanda_com.prn";
    file_put_contents($tempFile, $ticket);
    
    echo "Archivo temporal: $tempFile\n";
    
    // Método 1: Usar print de Windows
    $comando = 'print /D:"POSPrinter POS80" "' . $tempFile . '"';
    echo "Comando: $comando\n\n";
    
    $resultado = $shell->Run($comando, 0, true);
    
    echo "========================================\n";
    echo "Resultado: $resultado\n";
    echo "========================================\n\n";
    
    if ($resultado === 0) {
        echo "✓ COMANDO EJECUTADO\n\n";
        echo "VERIFICA TU IMPRESORA:\n";
        echo "Deberia haber impreso el ticket\n\n";
    } else {
        echo "✗ ERROR (codigo: $resultado)\n\n";
    }
    
    // Limpiar
    sleep(2);
    @unlink($tempFile);
    
} catch (Exception $e) {
    echo "[ERROR] " . $e->getMessage() . "\n\n";
    echo "Si el error es sobre COM:\n";
    echo "1. Habilita extension com_dotnet en php.ini\n";
    echo "2. Reinicia Apache\n\n";
}

echo "========================================\n\n";