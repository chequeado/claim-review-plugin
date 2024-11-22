<?php
function negacion_a_afirmacion_simple($texto) {
    $texto_original = $texto;
    
    // 0. Eliminar todo lo que está antes de "|" si existe
    if (strpos($texto, '|') !== false) {
        $partes = explode('|', $texto);
        $texto = trim(end($partes));
    }

    // 1. Encontrar negaciones y reemplazar
    $texto = preg_replace('/^No,\s/i', '', $texto);
    $texto = preg_replace('/\bno\s/i', '', $texto);
    $texto = preg_replace('/\bni\s/i', 'y ', $texto);
    
    // 2. Reemplazar "Detector:" si existe
    $texto = preg_replace('/^Detector:\s/', '', $texto);

   // 3. Si la frase está separada por ":", comparar longitudes y quedarse con la más larga,
    // manteniendo el caso especial de las comillas
    $partes = explode(':', $texto, 2);
    if (count($partes) > 1) {
        $primera_parte = trim($partes[0]);
        $segunda_parte = trim($partes[1]);
        
        // Verificar si la segunda parte está entre comillas
        if (substr($segunda_parte, 0, 1) === '"' && substr($segunda_parte, -1) === '"') {
            // Si está entre comillas, usar la segunda parte
            $texto = $segunda_parte;
        } else {
            // Comparar longitudes y quedarse con la más larga
            $longitud_primera = strlen($primera_parte);
            $longitud_segunda = strlen($segunda_parte);
            
            $texto = ($longitud_primera >= $longitud_segunda) ? $primera_parte : $segunda_parte;
        }
    }
    
    // 4. Borrar frases compuestas específicas
    $frases_a_borrar = [
        '/son falsas/i',
        '/son falsos/i',
        '/es falso que/i',
        '/falso que/i',
        '/es falsa la/i',
        '/es un mito/i',
        '/es falsa/i',
        '/es un montaje/i',
        '/es falso/i',
        '/ningún/i'
    ];
    foreach ($frases_a_borrar as $frase) {
        $texto = preg_replace($frase, '', $texto);
    }
    
    // 5. Capitalizar la primera letra de la frase final
    $texto = trim($texto);
    if ($texto) {
        $texto = ucfirst($texto);
    }
    
    // Si no hubo ningún cambio significativo, retornar null
    if (strtolower($texto) === strtolower($texto_original)) {
        return null;
    }
    
    return $texto;
}

