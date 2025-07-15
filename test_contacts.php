<?php
session_start();
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

// Simuler une session utilisateur pour le test
if (!isset($_SESSION['user_id'])) {
    $_SESSION['user_id'] = '1';
    $_SESSION['user_name'] = 'Narou Sagne';
}

echo "<h2>Test des contacts - Chat Platform</h2>";

// Test 1: Vérifier la structure des contacts
echo "<h3>1. Structure des contacts</h3>";
$contactsFile = 'xml/contacts.xml';
if (file_exists($contactsFile)) {
    $xml = simplexml_load_file($contactsFile);
    echo "✅ Fichier contacts.xml chargé<br>";
    
    foreach ($xml->contact as $contact) {
        echo "Contact ID: " . $contact['id'] . "<br>";
        echo "- User ID: " . $contact->user_id . "<br>";
        echo "- Contact User ID: " . $contact->contact_user_id . "<br>";
        echo "- Nickname: " . $contact->nickname . "<br>";
        echo "- Favorite: " . $contact->favorite . "<br>";
        echo "<br>";
    }
} else {
    echo "❌ Fichier contacts.xml non trouvé<br>";
}

// Test 2: Vérifier les utilisateurs
echo "<h3>2. Utilisateurs disponibles</h3>";
$usersFile = 'xml/users.xml';
if (file_exists($usersFile)) {
    $xml = simplexml_load_file($usersFile);
    echo "✅ Fichier users.xml chargé<br>";
    
    foreach ($xml->user as $user) {
        echo "User ID: " . $user['id'] . "<br>";
        echo "- Nom: " . $user->name . "<br>";
        echo "- Téléphone: " . $user->phone . "<br>";
        echo "- Email: " . $user->email . "<br>";
        echo "- Statut: " . $user->status . "<br>";
        echo "<br>";
    }
} else {
    echo "❌ Fichier users.xml non trouvé<br>";
}

// Test 3: Test de l'API contacts
echo "<h3>3. Test de l'API contacts</h3>";
echo "<p>Résultat de l'API :</p>";

// Simuler l'appel à l'API
$currentUserId = $_SESSION['user_id'];
$contacts = [];
$usersXml = simplexml_load_file($usersFile);
$contactsXml = simplexml_load_file($contactsFile);

foreach ($contactsXml->contact as $contact) {
    if ((string)$contact->user_id === $currentUserId) {
        $contactUserId = (string)$contact->contact_user_id;
        
        $contactUser = null;
        foreach ($usersXml->user as $user) {
            if ((string)$user['id'] === $contactUserId) {
                $contactUser = $user;
                break;
            }
        }
        
        if ($contactUser) {
            $contacts[] = [
                'id' => (string)$contact['id'],
                'user_id' => $contactUserId,
                'name' => (string)$contactUser->name,
                'phone' => (string)$contactUser->phone,
                'email' => (string)$contactUser->email,
                'status' => (string)$contactUser->status,
                'online' => (string)$contactUser->status === 'Online',
                'nickname' => (string)$contact->nickname,
                'favorite' => (string)$contact->favorite === 'true',
                'last_contact' => (string)$contact->last_contact
            ];
        }
    }
}

echo "<pre>" . json_encode($contacts, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "</pre>";

// Test 4: Interface de test
echo "<h3>4. Interface de test</h3>";
echo "<div style='border: 1px solid #ccc; padding: 20px; margin: 20px 0;'>";
echo "<h4>Contacts de l'utilisateur " . $_SESSION['user_name'] . " :</h4>";

if (empty($contacts)) {
    echo "<p>Aucun contact trouvé</p>";
} else {
    foreach ($contacts as $contact) {
        $statusClass = $contact['online'] ? 'online' : 'offline';
        $statusText = $contact['online'] ? 'En ligne' : 'Hors ligne';
        
        echo "<div style='border: 1px solid #ddd; padding: 10px; margin: 10px 0; border-radius: 5px;'>";
        echo "<div style='font-weight: bold;'>" . $contact['name'] . "</div>";
        echo "<div style='color: " . ($contact['online'] ? 'green' : 'gray') . ";'>" . $statusText . "</div>";
        echo "<div style='font-size: 12px; color: #666;'>📱 " . $contact['phone'] . "</div>";
        echo "<div style='font-size: 12px; color: #666;'>📧 " . $contact['email'] . "</div>";
        if ($contact['nickname'] && $contact['nickname'] !== $contact['name']) {
            echo "<div style='font-size: 12px; color: #666;'>💬 " . $contact['nickname'] . "</div>";
        }
        if ($contact['favorite']) {
            echo "<div style='color: #FFD700;'>⭐ Favori</div>";
        }
        echo "</div>";
    }
}
echo "</div>";

// Test 5: Ajouter un contact de test
echo "<h3>5. Ajouter un contact de test</h3>";
echo "<form method='post'>";
echo "<label>ID de l'utilisateur à ajouter: <input type='text' name='add_user_id' value='2'></label><br>";
echo "<input type='submit' name='add_contact' value='Ajouter le contact'>";
echo "</form>";

if (isset($_POST['add_contact'])) {
    $userId = $_POST['add_user_id'];
    
    // Vérifier si le contact existe déjà
    $exists = false;
    foreach ($contactsXml->contact as $contact) {
        if ((string)$contact->user_id === $currentUserId && 
            (string)$contact->contact_user_id === $userId) {
            $exists = true;
            break;
        }
    }
    
    if ($exists) {
        echo "<p style='color: red;'>❌ Contact déjà existant</p>";
    } else {
        // Récupérer les informations de l'utilisateur
        $userFound = null;
        foreach ($usersXml->user as $user) {
            if ((string)$user['id'] === $userId) {
                $userFound = $user;
                break;
            }
        }
        
        if ($userFound) {
            // Ajouter le contact
            $contactId = uniqid();
            $contact = $contactsXml->addChild('contact');
            $contact->addAttribute('id', $contactId);
            $contact->addChild('user_id', $currentUserId);
            $contact->addChild('contact_user_id', $userId);
            $contact->addChild('nickname', (string)$userFound->name);
            $contact->addChild('favorite', 'false');
            $contact->addChild('blocked', 'false');
            $contact->addChild('created_at', date('c'));
            $contact->addChild('last_contact', date('c'));
            
            $contactsXml->asXML($contactsFile);
            echo "<p style='color: green;'>✅ Contact ajouté avec succès</p>";
        } else {
            echo "<p style='color: red;'>❌ Utilisateur non trouvé</p>";
        }
    }
}
?> 