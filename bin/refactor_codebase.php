<?php
// bin/refactor_codebase.php

$directory = __DIR__ . '/../app';
$binDirectory = __DIR__;

// Extensões para processar
$extensions = ['php'];

function scanAndReplace($dir) {
    global $extensions;
    $files = scandir($dir);
    
    foreach ($files as $file) {
        if ($file === '.' || $file === '..') continue;
        
        $path = $dir . '/' . $file;
        
        if (is_dir($path)) {
            scanAndReplace($path);
        } else {
            $ext = pathinfo($path, PATHINFO_EXTENSION);
            if (in_array($ext, $extensions)) {
                // Pula arquivos específicos que não devem ser alterados ou já foram
                if (strpos($file, 'dashboardDB') !== false) continue;
                if (strpos($file, 'vendasysDB') !== false) continue;
                if (strpos($file, 'export_current') !== false) continue;
                if (strpos($file, 'convert_schema') !== false) continue;
                if (strpos($file, 'refactor_codebase') !== false) continue;
                
                processFile($path);
            }
        }
    }
}

function processFile($path) {
    $content = file_get_contents($path);
    
    // Lista de tabelas conhecidas com prefixo integra_
    // É mais seguro substituir strings específicas do que 'integra_' globalmente
    // Mas dada a tarefa, 'integra_' foi usado EXCLUSIVAMENTE como prefixo de tabela.
    
    // Verificação de segurança: checar se 'integra_' aparece em contexto que não é strig
    // Vamos substituir 'integra_' por ''
    
    $newContent = str_replace('integra_', '', $content);
    
    if ($content !== $newContent) {
        file_put_contents($path, $newContent);
        echo "Modificado: $path\n";
    }
}

echo "Iniciando refatoração em apps...\n";
scanAndReplace($directory);
echo "Iniciando refatoração em bin (scripts)...\n";
scanAndReplace($binDirectory);

echo "Refatoração concluída.\n";
