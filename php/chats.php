<?php
session_start();
header('Content-Type: application/json');

if (!extension_loaded('xml')) {
    echo json_encode(['error' => 'Extension XML non chargée']);
    exit;
}

$xmlFile = __DIR__ . '/../xml/chats.xml';

if (!file_exists($xmlFile)) {
    file_put_contents($xmlFile, '<?xml version="1.0" encoding="UTF-8"?><chats></chats>');
}

$xml = simplexml_load_file($xmlFile);
if ($xml === false) {
    echo json_encode(['error' => 'Erreur lors du chargement du fichier XML']);
    exit;
}

if ($_GET['action'] === 'get_chats') {
    $chats = [];
    foreach ($xml->chat as $chat) {
        $chats[] = [
            'id' => (string)$chat['id'],
            'name' => (string)$chat->name,
            'type' => (string)$chat->type,
            'last_message' => (string)$chat->last_message,
            'last_time' => (string)$chat->last_time,
            'online' => (string)$chat->online === 'true'
        ];
    }
    echo json_encode(['success' => true, 'chats' => $chats]);
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if ($input['action'] === 'create_chat') {
        $chatId = uniqid();
        $chat = $xml->addChild('chat');
        $chat->addAttribute('id', $chatId);
        $chat->addChild('name', htmlspecialchars($input['name']));
        $chat->addChild('type', $input['type']);
        $chat->addChild('last_message', '');
        $chat->addChild('last_time', '');
        $chat->addChild('online', 'false');
        
        $xml->asXML($xmlFile);
        echo json_encode(['success' => true, 'chat_id' => $chatId]);
    }
}
?> 