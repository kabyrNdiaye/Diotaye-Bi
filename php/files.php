<?php
session_start();
header('Content-Type: application/json');

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Non autorisé']);
    exit;
}

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'get_files':
        getFiles();
        break;
    case 'download_file':
        downloadFile();
        break;
    case 'delete_file':
        deleteFile();
        break;
    default:
        echo json_encode(['success' => false, 'message' => 'Action non reconnue']);
}

function getFiles() {
    $xmlFile = '../xml/files.xml';
    
    if (!file_exists($xmlFile)) {
        echo json_encode(['success' => true, 'files' => []]);
        return;
    }
    
    $xml = simplexml_load_file($xmlFile);
    $files = [];
    
    foreach ($xml->file as $file) {
        $fileData = [
            'id' => (string)$file['id'],
            'message_id' => (string)$file->message_id,
            'sender_id' => (string)$file->sender_id,
            'receiver_id' => (string)$file->receiver_id,
            'group_id' => (string)$file->group_id,
            'filename' => (string)$file->filename,
            'original_name' => (string)$file->original_name,
            'file_path' => (string)$file->file_path,
            'file_size' => (int)$file->file_size,
            'file_type' => (string)$file->file_type,
            'mime_type' => (string)$file->mime_type,
            'uploaded_at' => (string)$file->uploaded_at,
            'downloads' => (int)$file->downloads,
            'status' => (string)$file->status
        ];
        
        // Récupérer les informations de l'expéditeur
        $senderInfo = getUserInfo($fileData['sender_id']);
        $fileData['sender_name'] = $senderInfo['name'] ?? 'Utilisateur inconnu';
        
        // Récupérer les informations du destinataire ou du groupe
        if (!empty($fileData['receiver_id'])) {
            $receiverInfo = getUserInfo($fileData['receiver_id']);
            $fileData['receiver_name'] = $receiverInfo['name'] ?? 'Utilisateur inconnu';
        } elseif (!empty($fileData['group_id'])) {
            $groupInfo = getGroupInfo($fileData['group_id']);
            $fileData['group_name'] = $groupInfo['name'] ?? 'Groupe inconnu';
        }
        
        $files[] = $fileData;
    }
    
    echo json_encode(['success' => true, 'files' => $files]);
}

function downloadFile() {
    $fileId = $_GET['file_id'] ?? '';
    
    if (empty($fileId)) {
        echo json_encode(['success' => false, 'message' => 'ID de fichier manquant']);
        return;
    }
    
    $xmlFile = '../xml/files.xml';
    $xml = simplexml_load_file($xmlFile);
    
    $file = $xml->xpath("//file[@id='$fileId']")[0] ?? null;
    
    if (!$file) {
        echo json_encode(['success' => false, 'message' => 'Fichier non trouvé']);
        return;
    }
    
    $filePath = '../' . (string)$file->file_path;
    $originalName = (string)$file->original_name;
    
    if (!file_exists($filePath)) {
        echo json_encode(['success' => false, 'message' => 'Fichier physique non trouvé']);
        return;
    }
    
    // Incrémenter le compteur de téléchargements
    $file->downloads = (int)$file->downloads + 1;
    $xml->asXML($xmlFile);
    
    // Envoyer le fichier
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="' . $originalName . '"');
    header('Content-Length: ' . filesize($filePath));
    readfile($filePath);
    exit;
}

function deleteFile() {
    $fileId = $_POST['file_id'] ?? '';
    
    if (empty($fileId)) {
        echo json_encode(['success' => false, 'message' => 'ID de fichier manquant']);
        return;
    }
    
    $xmlFile = '../xml/files.xml';
    $xml = simplexml_load_file($xmlFile);
    
    $file = $xml->xpath("//file[@id='$fileId']")[0] ?? null;
    
    if (!$file) {
        echo json_encode(['success' => false, 'message' => 'Fichier non trouvé']);
        return;
    }
    
    // Vérifier que l'utilisateur est le propriétaire du fichier
    if ((string)$file->sender_id != $_SESSION['user_id']) {
        echo json_encode(['success' => false, 'message' => 'Non autorisé à supprimer ce fichier']);
        return;
    }
    
    // Supprimer le fichier physique
    $filePath = '../' . (string)$file->file_path;
    if (file_exists($filePath)) {
        unlink($filePath);
    }
    
    // Supprimer l'entrée XML
    unset($file[0]);
    $xml->asXML($xmlFile);
    
    echo json_encode(['success' => true, 'message' => 'Fichier supprimé avec succès']);
}

function getUserInfo($userId) {
    $xmlFile = '../xml/users.xml';
    if (!file_exists($xmlFile)) return null;
    
    $xml = simplexml_load_file($xmlFile);
    $user = $xml->xpath("//user[@id='$userId']")[0] ?? null;
    
    if ($user) {
        return [
            'name' => (string)$user->name,
            'email' => (string)$user->email
        ];
    }
    
    return null;
}

function getGroupInfo($groupId) {
    $xmlFile = '../xml/groups.xml';
    if (!file_exists($xmlFile)) return null;
    
    $xml = simplexml_load_file($xmlFile);
    $group = $xml->xpath("//group[@id='$groupId']")[0] ?? null;
    
    if ($group) {
        return [
            'name' => (string)$group->name,
            'description' => (string)$group->description
        ];
    }
    
    return null;
}
?> 