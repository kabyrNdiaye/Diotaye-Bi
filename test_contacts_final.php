<?php
session_start();

echo "<h2>🎯 Test Final - Contacts Kabyr Diop</h2>";

// Simuler la session de Kabyr Diop
$_SESSION['user_id'] = '3';
$_SESSION['user_name'] = 'Kabyr Diop';

$currentUserId = $_SESSION['user_id'];
echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
echo "<strong>Utilisateur actuel:</strong> " . $_SESSION['user_name'] . " (ID: $currentUserId)<br>";
echo "</div>";

// Test direct de l'API
echo "<h3>🔗 Test Direct de l'API</h3>";

// Capturer la sortie de l'API
ob_start();
$_GET['action'] = 'get_contacts';
include 'php/contacts.php';
$apiOutput = ob_get_clean();

echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
echo "<strong>Réponse brute de l'API:</strong><br>";
echo "<pre style='background: white; padding: 10px; border-radius: 5px; margin: 10px 0;'>";
echo htmlspecialchars($apiOutput);
echo "</pre>";

// Décoder la réponse JSON
$apiData = json_decode($apiOutput, true);
if ($apiData === null) {
    echo "<div style='color: red;'>❌ Erreur de décodage JSON</div>";
} else {
    echo "<div style='color: green;'>✅ JSON valide</div>";
    echo "<strong>Données décodées:</strong><br>";
    echo "<pre style='background: white; padding: 10px; border-radius: 5px; margin: 10px 0;'>";
    echo json_encode($apiData, JSON_PRETTY_PRINT);
    echo "</pre>";
    
    if ($apiData['success']) {
        $contacts = $apiData['contacts'];
        if (empty($contacts)) {
            echo "<div style='color: orange;'>⚠️ Aucun contact retourné par l'API</div>";
        } else {
            echo "<div style='color: green;'>✅ " . count($contacts) . " contact(s) retourné(s) par l'API</div>";
            
            foreach ($contacts as $contact) {
                echo "<div style='border: 1px solid #ddd; padding: 10px; margin: 5px 0; border-radius: 5px; background: #e8f5e8;'>";
                echo "<strong>Nom:</strong> " . $contact['name'] . "<br>";
                echo "<strong>ID:</strong> " . $contact['id'] . "<br>";
                echo "<strong>Contact ID:</strong> " . $contact['contact_id'] . "<br>";
                echo "<strong>Nickname:</strong> " . $contact['nickname'] . "<br>";
                echo "<strong>Téléphone:</strong> " . $contact['phone'] . "<br>";
                echo "<strong>Email:</strong> " . $contact['email'] . "<br>";
                echo "<strong>Status:</strong> " . $contact['status'] . "<br>";
                echo "<strong>En ligne:</strong> " . ($contact['online'] ? 'Oui' : 'Non') . "<br>";
                echo "<strong>Favori:</strong> " . ($contact['favorite'] ? 'Oui' : 'Non') . "<br>";
                echo "<strong>Créé le:</strong> " . $contact['created_at'] . "<br>";
                echo "</div>";
            }
        }
    } else {
        echo "<div style='color: red;'>❌ Erreur API: " . $apiData['message'] . "</div>";
    }
}
echo "</div>";

// Vérification manuelle des fichiers
echo "<h3>📁 Vérification Manuelle des Fichiers</h3>";

$contactsFile = 'xml/contacts.xml';
$usersFile = 'xml/users.xml';

echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 10px 0;'>";

if (file_exists($contactsFile)) {
    $contactsXml = simplexml_load_file($contactsFile);
    echo "<div style='color: green;'>✅ Fichier contacts.xml chargé</div>";
    
    $userContacts = [];
    foreach ($contactsXml->contact as $contact) {
        if ((string)$contact->user_id === $currentUserId) {
            $userContacts[] = [
                'contact_id' => (string)$contact['id'],
                'contact_user_id' => (string)$contact->contact_user_id,
                'nickname' => (string)$contact->nickname,
                'created_at' => (string)$contact->created_at
            ];
        }
    }
    
    echo "<strong>Contacts trouvés pour l'utilisateur $currentUserId:</strong><br>";
    if (empty($userContacts)) {
        echo "<div style='color: orange;'>⚠️ Aucun contact trouvé</div>";
    } else {
        foreach ($userContacts as $contact) {
            echo "<div style='border: 1px solid #ddd; padding: 10px; margin: 5px 0; border-radius: 5px;'>";
            echo "<strong>Contact ID:</strong> " . $contact['contact_id'] . "<br>";
            echo "<strong>Contact User ID:</strong> " . $contact['contact_user_id'] . "<br>";
            echo "<strong>Nickname:</strong> " . $contact['nickname'] . "<br>";
            echo "<strong>Créé le:</strong> " . $contact['created_at'] . "<br>";
            echo "</div>";
        }
    }
} else {
    echo "<div style='color: red;'>❌ Fichier contacts.xml non trouvé</div>";
}

if (file_exists($usersFile)) {
    $usersXml = simplexml_load_file($usersFile);
    echo "<div style='color: green;'>✅ Fichier users.xml chargé</div>";
    
    // Vérifier les utilisateurs contact
    foreach ($userContacts as $contact) {
        $contactUserId = $contact['contact_user_id'];
        $userFound = null;
        
        foreach ($usersXml->user as $user) {
            if ((string)$user['id'] === $contactUserId) {
                $userFound = $user;
                break;
            }
        }
        
        if ($userFound) {
            echo "<div style='color: green;'>✅ Utilisateur contact trouvé: " . (string)$userFound->name . " (ID: $contactUserId)</div>";
        } else {
            echo "<div style='color: red;'>❌ Utilisateur contact non trouvé pour ID: $contactUserId</div>";
        }
    }
} else {
    echo "<div style='color: red;'>❌ Fichier users.xml non trouvé</div>";
}

echo "</div>";

// Test d'ajout d'un nouveau contact
echo "<h3>➕ Test d'Ajout de Contact</h3>";
echo "<form method='post' style='background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
echo "<label>ID de l'utilisateur à ajouter: <input type='text' name='add_user_id' value='1' style='padding: 5px; margin: 5px;'></label><br>";
echo "<label>Surnom (optionnel): <input type='text' name='nickname' placeholder='Surnom personnalisé' style='padding: 5px; margin: 5px;'></label><br>";
echo "<input type='submit' name='add_contact' value='Ajouter le contact' style='background: #28a745; color: white; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer;'>";
echo "</form>";

if (isset($_POST['add_contact'])) {
    $userId = $_POST['add_user_id'];
    $nickname = trim($_POST['nickname'] ?? '');
    
    // Simuler l'ajout via l'API
    ob_start();
    $_SERVER['REQUEST_METHOD'] = 'POST';
    $_POST = [];
    $_POST['action'] = 'add_contact';
    $_POST['user_id'] = $userId;
    $_POST['nickname'] = $nickname;
    
    // Rediriger l'entrée standard
    $input = json_encode([
        'action' => 'add_contact',
        'user_id' => $userId,
        'nickname' => $nickname
    ]);
    
    // Simuler l'appel API
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'http://localhost/chat_platform/php/contacts.php');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $input);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    
    $response = curl_exec($ch);
    curl_close($ch);
    
    if ($response) {
        $result = json_decode($response, true);
        if ($result && $result['success']) {
            echo "<div style='color: green; padding: 10px; background: #d4edda; border-radius: 5px; margin: 10px 0;'>✅ Contact ajouté avec succès</div>";
            echo "<script>setTimeout(() => window.location.reload(), 2000);</script>";
        } else {
            echo "<div style='color: red; padding: 10px; background: #f8d7da; border-radius: 5px; margin: 10px 0;'>❌ Erreur: " . ($result['message'] ?? 'Erreur inconnue') . "</div>";
        }
    } else {
        echo "<div style='color: red; padding: 10px; background: #f8d7da; border-radius: 5px; margin: 10px 0;'>❌ Erreur de connexion à l'API</div>";
    }
}

echo "<h3>🎯 Instructions Finales</h3>";
echo "<div style='background: #e7f3ff; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
echo "<strong>Pour tester l'application:</strong><br>";
echo "1. Vérifiez que l'API retourne bien les contacts ci-dessus<br>";
echo "2. Si l'API fonctionne, ouvrez <a href='index.html' target='_blank'>index.html</a><br>";
echo "3. Allez dans l'onglet 'Contacts'<br>";
echo "4. Les contacts doivent s'afficher en bas avec animation<br>";
echo "5. Testez l'ajout d'un nouveau contact<br>";
echo "</div>";

echo "<h3>🔧 Debug</h3>";
echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
echo "<strong>Logs de debug:</strong><br>";
echo "1. Vérifiez les logs PHP dans le fichier d'erreur de XAMPP<br>";
echo "2. Ouvrez la console du navigateur (F12) pour les erreurs JavaScript<br>";
echo "3. Testez l'API directement: <a href='php/contacts.php?action=get_contacts' target='_blank'>API Contacts</a><br>";
echo "</div>";
?> 