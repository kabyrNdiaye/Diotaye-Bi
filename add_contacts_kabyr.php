<?php
session_start();

echo "<h2>➕ Ajout de Contacts pour Kabyr Diop</h2>";

// Simuler la session de Kabyr Diop
$_SESSION['user_id'] = '3';
$_SESSION['user_name'] = 'Kabyr Diop';
$_SESSION['user_phone'] = '774964932';
$_SESSION['user_email'] = 'kabyr.diop@example.com';

$currentUserId = $_SESSION['user_id'];
echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
echo "<strong>Utilisateur actuel:</strong> " . $_SESSION['user_name'] . " (ID: $currentUserId)<br>";
echo "</div>";

$contactsFile = 'xml/contacts.xml';
$usersFile = 'xml/users.xml';

if (!file_exists($contactsFile) || !file_exists($usersFile)) {
    echo "<div style='color: red; padding: 10px; background: #f8d7da; border-radius: 5px; margin: 10px 0;'>❌ Fichiers XML manquants</div>";
    exit;
}

$contactsXml = simplexml_load_file($contactsFile);
$usersXml = simplexml_load_file($usersFile);

// Vérifier les contacts existants
$existingContacts = [];
foreach ($contactsXml->contact as $contact) {
    if ((string)$contact->user_id === $currentUserId) {
        $existingContacts[] = (string)$contact->contact_user_id;
    }
}

echo "<h3>📞 Contacts existants pour Kabyr Diop</h3>";
if (empty($existingContacts)) {
    echo "<div style='color: orange; padding: 10px; background: #fff3cd; border-radius: 5px; margin: 10px 0;'>⚠️ Aucun contact existant</div>";
} else {
    echo "<div style='color: green; padding: 10px; background: #d4edda; border-radius: 5px; margin: 10px 0;'>✅ " . count($existingContacts) . " contact(s) existant(s): " . implode(', ', $existingContacts) . "</div>";
}

// Ajouter des contacts manquants
echo "<h3>➕ Ajout de nouveaux contacts</h3>";

$contactsToAdd = [];
foreach ($usersXml->user as $user) {
    $userId = (string)$user['id'];
    if ($userId !== $currentUserId && !in_array($userId, $existingContacts)) {
        $contactsToAdd[] = $userId;
    }
}

if (empty($contactsToAdd)) {
    echo "<div style='color: green; padding: 10px; background: #d4edda; border-radius: 5px; margin: 10px 0;'>✅ Tous les contacts sont déjà ajoutés</div>";
} else {
    echo "<div style='color: blue; padding: 10px; background: #d1ecf1; border-radius: 5px; margin: 10px 0;'>📝 Contacts à ajouter: " . implode(', ', $contactsToAdd) . "</div>";
    
    foreach ($contactsToAdd as $userId) {
        // Trouver l'utilisateur
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
            
            echo "<div style='color: green; padding: 10px; background: #d4edda; border-radius: 5px; margin: 10px 0;'>✅ Contact ajouté: " . $userFound->name . " (ID: $userId)</div>";
        }
    }
    
    // Sauvegarder le fichier
    $contactsXml->asXML($contactsFile);
    echo "<div style='color: green; padding: 10px; background: #d4edda; border-radius: 5px; margin: 10px 0;'>✅ Fichier contacts.xml mis à jour</div>";
}

// Afficher les contacts finaux
echo "<h3>📋 Contacts finaux pour Kabyr Diop</h3>";
$finalContacts = [];
foreach ($contactsXml->contact as $contact) {
    if ((string)$contact->user_id === $currentUserId) {
        $contactUserId = (string)$contact->contact_user_id;
        
        // Trouver les informations de l'utilisateur
        $userFound = null;
        foreach ($usersXml->user as $user) {
            if ((string)$user['id'] === $contactUserId) {
                $userFound = $user;
                break;
            }
        }
        
        if ($userFound) {
            $finalContacts[] = [
                'id' => $contactUserId,
                'name' => (string)$userFound->name,
                'phone' => (string)$userFound->phone,
                'email' => (string)$userFound->email,
                'nickname' => (string)$contact->nickname,
                'created_at' => (string)$contact->created_at
            ];
        }
    }
}

if (empty($finalContacts)) {
    echo "<div style='color: orange; padding: 10px; background: #fff3cd; border-radius: 5px; margin: 10px 0;'>⚠️ Aucun contact final trouvé</div>";
} else {
    echo "<div style='color: green; padding: 10px; background: #d4edda; border-radius: 5px; margin: 10px 0;'>✅ " . count($finalContacts) . " contact(s) final(aux):</div>";
    
    foreach ($finalContacts as $contact) {
        echo "<div style='border: 1px solid #ddd; padding: 10px; margin: 5px 0; border-radius: 5px; background: #f8f9fa;'>";
        echo "<strong>Nom:</strong> " . $contact['name'] . "<br>";
        echo "<strong>ID:</strong> " . $contact['id'] . "<br>";
        echo "<strong>Téléphone:</strong> " . $contact['phone'] . "<br>";
        echo "<strong>Email:</strong> " . $contact['email'] . "<br>";
        echo "<strong>Nickname:</strong> " . $contact['nickname'] . "<br>";
        echo "<strong>Ajouté le:</strong> " . $contact['created_at'] . "<br>";
        echo "</div>";
    }
}

// Test de l'API
echo "<h3>🔗 Test de l'API get_contacts</h3>";
echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
echo "<strong>URL de test:</strong> <a href='php/contacts.php?action=get_contacts' target='_blank'>php/contacts.php?action=get_contacts</a><br><br>";

// Simuler la réponse de l'API
$apiContacts = [];
foreach ($finalContacts as $contact) {
    $apiContacts[] = [
        'id' => $contact['id'],
        'contact_id' => 'test_id',
        'name' => $contact['name'],
        'phone' => $contact['phone'],
        'email' => $contact['email'],
        'status' => 'Offline',
        'online' => false,
        'avatar' => '',
        'bio' => '',
        'nickname' => $contact['nickname'],
        'favorite' => false,
        'blocked' => false,
        'created_at' => $contact['created_at'],
        'last_contact' => $contact['created_at']
    ];
}

$apiResponse = ['success' => true, 'contacts' => $apiContacts];
echo "<strong>Réponse API simulée:</strong><br>";
echo "<pre style='background: white; padding: 10px; border-radius: 5px; margin: 10px 0;'>";
echo json_encode($apiResponse, JSON_PRETTY_PRINT);
echo "</pre>";

if (empty($apiContacts)) {
    echo "<div style='color: red;'>❌ Aucun contact retourné par l'API</div>";
} else {
    echo "<div style='color: green;'>✅ " . count($apiContacts) . " contact(s) retourné(s) par l'API</div>";
}
echo "</div>";

echo "<h3>🎯 Instructions de Test</h3>";
echo "<div style='background: #e7f3ff; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
echo "<strong>Pour tester l'application:</strong><br>";
echo "1. Vérifiez que les contacts sont bien listés ci-dessus<br>";
echo "2. Ouvrez <a href='index.html' target='_blank'>index.html</a> dans un nouvel onglet<br>";
echo "3. Allez dans l'onglet 'Contacts'<br>";
echo "4. Vérifiez que les contacts s'affichent en bas de la liste<br>";
echo "5. Testez l'ajout d'un nouveau contact<br>";
echo "</div>";

echo "<h3>🔧 Debug</h3>";
echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
echo "<strong>Pour diagnostiquer les problèmes:</strong><br>";
echo "1. Ouvrez <a href='debug_contacts.php' target='_blank'>debug_contacts.php</a> pour un diagnostic complet<br>";
echo "2. Vérifiez la console du navigateur (F12) pour les erreurs JavaScript<br>";
echo "3. Testez l'API directement: <a href='php/contacts.php?action=get_contacts' target='_blank'>API Contacts</a><br>";
echo "</div>";
?> 