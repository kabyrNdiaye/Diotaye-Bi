<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

echo json_encode([
    'status' => 'success',
    'message' => 'Serveur PHP fonctionne correctement',
    'timestamp' => date('Y-m-d H:i:s'),
    'php_version' => phpversion()
]);
?> 