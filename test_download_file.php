<?php
session_start();

// Simuler une session utilisateur pour les tests
if (!isset($_SESSION['user_id'])) {
    $_SESSION['user_id'] = '3'; // Utilisateur Kabyr Diop
}

echo "<h1>Test de téléchargement de fichiers</h1>";

// Charger le fichier XML
$filesFile = __DIR__ . '/xml/files.xml';
$xml = simplexml_load_file($filesFile);

if ($xml === false) {
    echo "<p style='color: red;'>Erreur: Impossible de charger le fichier XML</p>";
    exit;
}

echo "<h2>Fichiers disponibles:</h2>";
echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
echo "<tr><th>ID</th><th>Nom</th><th>Expéditeur</th><th>Destinataire</th><th>Groupe</th><th>Chemin</th><th>Existe</th><th>Action</th></tr>";

foreach ($xml->file as $file) {
    $fileId = (string)$file['id'];
    $senderId = (string)$file->sender_id;
    $receiverId = (string)$file->receiver_id;
    $groupId = (string)$file->group_id;
    $fileName = (string)$file->original_name;
    $filePath = (string)$file->file_path;
    
    // Vérifier si le fichier physique existe
    $fileExists = file_exists($filePath) ? "Oui" : "Non";
    $fileExistsColor = file_exists($filePath) ? "green" : "red";
    
    // Vérifier si l'utilisateur actuel peut télécharger ce fichier
    $canDownload = ($senderId === $_SESSION['user_id'] || 
                   $receiverId === $_SESSION['user_id'] || 
                   !empty($groupId)) ? "Oui" : "Non";
    
    echo "<tr>";
    echo "<td>$fileId</td>";
    echo "<td>$fileName</td>";
    echo "<td>$senderId</td>";
    echo "<td>$receiverId</td>";
    echo "<td>$groupId</td>";
    echo "<td style='font-size: 12px;'>$filePath</td>";
    echo "<td style='color: $fileExistsColor;'>$fileExists</td>";
    echo "<td>";
    if ($canDownload === "Oui" && $fileExists === "Oui") {
        echo "<a href='php/files.php?action=download&file_id=$fileId' target='_blank'>Télécharger</a>";
    } else {
        echo "Non autorisé ou fichier manquant";
    }
    echo "</td>";
    echo "</tr>";
}

echo "</table>";

echo "<h2>Test de téléchargement direct:</h2>";
echo "<p>Cliquez sur le lien ci-dessous pour tester le téléchargement du fichier bijoux.jpeg:</p>";
echo "<a href='php/files.php?action=download&file_id=4' target='_blank'>Télécharger bijoux.jpeg</a>";

echo "<h2>Informations de session:</h2>";
echo "<p>User ID: " . $_SESSION['user_id'] . "</p>";

echo "<h2>Test de requête GET:</h2>";
if (isset($_GET['action']) && isset($_GET['file_id'])) {
    echo "<p>Action: " . $_GET['action'] . "</p>";
    echo "<p>File ID: " . $_GET['file_id'] . "</p>";
    
    // Simuler le code de téléchargement
    $fileId = $_GET['file_id'];
    $userId = $_SESSION['user_id'];
    
    $fileFound = false;
    $filePath = '';
    $fileName = '';
    
    foreach ($xml->file as $file) {
        if ((string)$file['id'] === $fileId) {
            $senderId = (string)$file->sender_id;
            $receiverId = (string)$file->receiver_id;
            $groupId = (string)$file->group_id;
            
            echo "<p>Vérification des permissions:</p>";
            echo "<ul>";
            echo "<li>Sender ID: $senderId</li>";
            echo "<li>Receiver ID: $receiverId</li>";
            echo "<li>Group ID: $groupId</li>";
            echo "<li>User ID: $userId</li>";
            echo "</ul>";
            
            if ($senderId === $userId || $receiverId === $userId || !empty($groupId)) {
                $filePath = (string)$file->file_path;
                $fileName = (string)$file->original_name;
                $fileFound = true;
                echo "<p style='color: green;'>✅ Fichier trouvé et autorisé</p>";
                echo "<p>Chemin: $filePath</p>";
                echo "<p>Nom: $fileName</p>";
                echo "<p>Existe: " . (file_exists($filePath) ? "Oui" : "Non") . "</p>";
                break;
            } else {
                echo "<p style='color: red;'>❌ Accès non autorisé</p>";
            }
        }
    }
    
    if (!$fileFound) {
        echo "<p style='color: red;'>❌ Fichier non trouvé</p>";
    }
}
?> 