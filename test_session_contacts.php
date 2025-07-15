<?php
session_start();

echo "<h2>🧪 Test Session et Contacts</h2>";

// Simuler une session utilisateur
if (!isset($_SESSION['user_id'])) {
    $_SESSION['user_id'] = '1';
    $_SESSION['user_name'] = 'Narou Sagne';
    $_SESSION['user_phone'] = '774123456';
    $_SESSION['user_email'] = 'narou.sagne@example.com';
    echo "<div style='color: green; padding: 10px; background: #d4edda; border-radius: 5px; margin: 10px 0;'>✅ Session utilisateur créée</div>";
} else {
    echo "<div style='color: blue; padding: 10px; background: #d1ecf1; border-radius: 5px; margin: 10px 0;'>ℹ️ Session utilisateur déjà existante</div>";
}

echo "<h3>📋 Informations de Session</h3>";
echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
echo "<strong>User ID:</strong> " . $_SESSION['user_id'] . "<br>";
echo "<strong>Nom:</strong> " . $_SESSION['user_name'] . "<br>";
echo "<strong>Téléphone:</strong> " . $_SESSION['user_phone'] . "<br>";
echo "<strong>Email:</strong> " . $_SESSION['user_email'] . "<br>";
echo "</div>";

// Test de l'API check_session
echo "<h3>🔍 Test API check_session</h3>";
$sessionData = [
    'logged_in' => true,
    'user' => [
        'id' => $_SESSION['user_id'],
        'name' => $_SESSION['user_name'],
        'phone' => $_SESSION['user_phone'],
        'email' => $_SESSION['user_email']
    ]
];
echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
echo "<strong>Réponse API:</strong><br>";
echo "<pre>" . json_encode($sessionData, JSON_PRETTY_PRINT) . "</pre>";
echo "</div>";

// Test de l'API contacts
echo "<h3>📞 Test API contacts</h3>";
$contactsFile = 'xml/contacts.xml';
$usersFile = 'xml/users.xml';

if (!file_exists($contactsFile)) {
    echo "<div style='color: red; padding: 10px; background: #f8d7da; border-radius: 5px; margin: 10px 0;'>❌ Fichier contacts.xml non trouvé</div>";
} else {
    $contactsXml = simplexml_load_file($contactsFile);
    $currentUserId = $_SESSION['user_id'];
    
    echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
    echo "<strong>Contacts pour l'utilisateur $currentUserId:</strong><br>";
    
    $userContacts = [];
    foreach ($contactsXml->contact as $contact) {
        if ((string)$contact->user_id === $currentUserId) {
            $userContacts[] = [
                'contact_id' => (string)$contact['id'],
                'contact_user_id' => (string)$contact->contact_user_id,
                'nickname' => (string)$contact->nickname,
                'favorite' => (string)$contact->favorite === 'true',
                'created_at' => (string)$contact->created_at
            ];
        }
    }
    
    if (empty($userContacts)) {
        echo "<div style='color: orange; padding: 10px; background: #fff3cd; border-radius: 5px; margin: 10px 0;'>⚠️ Aucun contact trouvé pour cet utilisateur</div>";
    } else {
        echo "<div style='color: green; padding: 10px; background: #d4edda; border-radius: 5px; margin: 10px 0;'>✅ " . count($userContacts) . " contact(s) trouvé(s)</div>";
        
        foreach ($userContacts as $contact) {
            echo "<div style='border: 1px solid #ddd; padding: 10px; margin: 5px 0; border-radius: 5px;'>";
            echo "<strong>Contact ID:</strong> " . $contact['contact_id'] . "<br>";
            echo "<strong>User ID:</strong> " . $contact['contact_user_id'] . "<br>";
            echo "<strong>Nickname:</strong> " . $contact['nickname'] . "<br>";
            echo "<strong>Favori:</strong> " . ($contact['favorite'] ? 'Oui' : 'Non') . "<br>";
            echo "<strong>Créé le:</strong> " . $contact['created_at'] . "<br>";
            echo "</div>";
        }
    }
    echo "</div>";
}

// Test d'ajout de contact
echo "<h3>➕ Test d'Ajout de Contact</h3>";
echo "<form method='post' style='background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
echo "<label>ID de l'utilisateur à ajouter: <input type='text' name='add_user_id' value='2' style='padding: 5px; margin: 5px;'></label><br>";
echo "<label>Surnom (optionnel): <input type='text' name='nickname' placeholder='Surnom personnalisé' style='padding: 5px; margin: 5px;'></label><br>";
echo "<input type='submit' name='add_contact' value='Ajouter le contact' style='background: #28a745; color: white; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer;'>";
echo "</form>";

if (isset($_POST['add_contact'])) {
    $userId = $_POST['add_user_id'];
    $nickname = trim($_POST['nickname'] ?? '');
    
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
        echo "<div style='color: red; padding: 10px; background: #f8d7da; border-radius: 5px; margin: 10px 0;'>❌ Contact déjà existant</div>";
    } else {
        // Récupérer les informations de l'utilisateur
        $usersXml = simplexml_load_file($usersFile);
        $userFound = null;
        foreach ($usersXml->user as $user) {
            if ((string)$user['id'] === $userId) {
                $userFound = $user;
                break;
            }
        }
        
        if ($userFound) {
            // Utiliser le surnom personnalisé s'il est fourni, sinon utiliser le nom de l'utilisateur
            $displayName = !empty($nickname) ? $nickname : (string)$userFound->name;
            
            // Ajouter le contact
            $contactId = uniqid();
            $contact = $contactsXml->addChild('contact');
            $contact->addAttribute('id', $contactId);
            $contact->addChild('user_id', $currentUserId);
            $contact->addChild('contact_user_id', $userId);
            $contact->addChild('nickname', $displayName);
            $contact->addChild('favorite', 'false');
            $contact->addChild('blocked', 'false');
            $contact->addChild('created_at', date('c'));
            $contact->addChild('last_contact', date('c'));
            
            $contactsXml->asXML($contactsFile);
            echo "<div style='color: green; padding: 10px; background: #d4edda; border-radius: 5px; margin: 10px 0;'>✅ Contact ajouté avec succès: " . $displayName . "</div>";
            
            // Recharger la page pour voir les changements
            echo "<script>setTimeout(() => window.location.reload(), 2000);</script>";
        } else {
            echo "<div style='color: red; padding: 10px; background: #f8d7da; border-radius: 5px; margin: 10px 0;'>❌ Utilisateur non trouvé</div>";
        }
    }
}

// Test de l'API get_contacts
echo "<h3>🔗 Test API get_contacts</h3>";
echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
echo "<strong>URL de test:</strong> <a href='php/contacts.php?action=get_contacts' target='_blank'>php/contacts.php?action=get_contacts</a><br><br>";

// Simuler la réponse de l'API
$contacts = [];
if (file_exists($contactsFile) && file_exists($usersFile)) {
    $contactsXml = simplexml_load_file($contactsFile);
    $usersXml = simplexml_load_file($usersFile);
    
    $userContacts = [];
    foreach ($contactsXml->contact as $contact) {
        if ((string)$contact->user_id === $currentUserId) {
            $contactUserId = (string)$contact->contact_user_id;
            $userContacts[$contactUserId] = [
                'contact_id' => (string)$contact['id'],
                'nickname' => (string)$contact->nickname,
                'favorite' => (string)$contact->favorite === 'true',
                'blocked' => (string)$contact->blocked === 'true',
                'created_at' => (string)$contact->created_at,
                'last_contact' => (string)$contact->last_contact
            ];
        }
    }
    
    foreach ($userContacts as $contactUserId => $contactInfo) {
        $user = null;
        foreach ($usersXml->user as $u) {
            if ((string)$u['id'] === $contactUserId) {
                $user = $u;
                break;
            }
        }
        
        if ($user) {
            $contacts[] = [
                'id' => $contactUserId,
                'contact_id' => $contactInfo['contact_id'],
                'name' => (string)$user->name,
                'phone' => (string)$user->phone,
                'email' => (string)$user->email,
                'status' => (string)$user->status,
                'online' => (string)$user->status === 'Online',
                'avatar' => (string)$user->avatar,
                'bio' => (string)$user->bio,
                'nickname' => $contactInfo['nickname'],
                'favorite' => $contactInfo['favorite'],
                'blocked' => $contactInfo['blocked'],
                'created_at' => $contactInfo['created_at'],
                'last_contact' => $contactInfo['last_contact']
            ];
        }
    }
}

$apiResponse = ['success' => true, 'contacts' => $contacts];
echo "<strong>Réponse simulée:</strong><br>";
echo "<pre>" . json_encode($apiResponse, JSON_PRETTY_PRINT) . "</pre>";
echo "</div>";

// Instructions pour tester
echo "<h3>🎯 Instructions de Test</h3>";
echo "<div style='background: #e7f3ff; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
echo "<strong>Pour tester l'application complète:</strong><br>";
echo "1. Ouvrez <a href='index.html' target='_blank'>index.html</a> dans un nouvel onglet<br>";
echo "2. Allez dans l'onglet 'Contacts'<br>";
echo "3. Cliquez sur 'Ajouter un contact'<br>";
echo "4. Entrez un numéro de téléphone (ex: 775987654)<br>";
echo "5. Ajoutez le contact et vérifiez qu'il apparaît en bas<br>";
echo "</div>";

echo "<h3>🔧 Debug</h3>";
echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
echo "<strong>Console JavaScript:</strong><br>";
echo "- Ouvrez les outils de développement (F12)<br>";
echo "- Allez dans l'onglet Console<br>";
echo "- Rechargez la page et observez les logs<br>";
echo "- Vérifiez les erreurs éventuelles<br>";
echo "</div>";
?> 