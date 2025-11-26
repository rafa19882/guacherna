@echo off
COLOR 0E
TITLE Verificar Impresora Compartida
CLS

echo ================================================
echo   VERIFICAR SI LA IMPRESORA ESTA COMPARTIDA
echo ================================================
echo.

echo Buscando impresoras compartidas...
echo.

REM Listar impresoras compartidas
wmic printer where shared=true get name,sharename /format:table

echo.
echo ================================================
echo   INSTRUCCIONES
echo ================================================
echo.
echo Si ves "POSPrinter POS80" arriba:
echo   → ✓ Esta compartida, ejecuta: php test_windows.php
echo.
echo Si NO aparece:
echo   → ✗ NO esta compartida
echo   → Sigue las instrucciones en:
echo      COMPARTIR_PASO_A_PASO.md
echo.
echo ================================================
echo.

pause