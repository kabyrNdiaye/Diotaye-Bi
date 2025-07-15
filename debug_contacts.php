<?php
session_start();

echo "<h2>🔍 Debug Contacts - Diagnostic Complet</h2>";

// Simuler la session de Kabyr Diop
if (!isset($_SESSION['user_id'])) {
    $_SESSION['user_id'] = '3';
    $_SESSION['user_name'] = 'Kabyr Diop';
    $_SESSION['user_phone'] = '774964932';
    $_SESSION['user_email'] = 'kabyr.diop@example.com';
    echo "<div style='color: green; padding: 10px; background: #d4edda; border-radius: 5px; margin: 10px 0;'>✅ Session Kabyr Diop créée</div>";
}

$currentUserId = $_SESSION['user_id'];
echo "<h3>📋 Session Actuelle</h3>";
echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
echo "<strong>User ID:</strong> $currentUserId<br>";
echo "<strong>Nom:</strong> " . $_SESSION['user_name'] . "<br>";
echo "<strong>Téléphone:</strong> " . $_SESSION['user_phone'] . "<br>";
echo "<strong>Email:</strong> " . $_SESSION['user_email'] . "<br>";
echo "</div>";

// Vérifier les fichiers XML
echo "<h3>📁 Vérification des Fichiers XML</h3>";

$contactsFile = 'xml/contacts.xml';
$usersFile = 'xml/users.xml';

echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 10px 0;'>";

if (!file_exists($contactsFile)) {
    echo "<div style='color: red;'>❌ Fichier contacts.xml non trouvé</div>";
} else {
    echo "<div style='color: green;'>✅ Fichier contacts.xml trouvé</div>";
    echo "<strong>Contenu contacts.xml:</strong><br>";
    echo "<pre style='background: white; padding: 10px; border-radius: 5px; margin: 10px 0;'>";
    echo htmlspecialchars(file_get_contents($contactsFile));
    echo "</pre>";
}

if (!file_exists($usersFile)) {
    echo "<div style='color: red;'>❌ Fichier users.xml non trouvé</div>";
} else {
    echo "<div style='color: green;'>✅ Fichier users.xml trouvé</div>";
    echo "<strong>Contenu users.xml:</strong><br>";
    echo "<pre style='background: white; padding: 10px; border-radius: 5px; margin: 10px 0; max-height: 300px; overflow-y: auto;'>";
    echo htmlspecialchars(file_get_contents($usersFile));
    echo "</pre>";
}

echo "</div>";

// Analyser les contacts
echo "<h3>🔍 Analyse des Contacts</h3>";

if (file_exists($contactsFile) && file_exists($usersFile)) {
    $contactsXml = simplexml_load_file($contactsFile);
    $usersXml = simplexml_load_file($usersFile);
    
    echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
    
    // Lister tous les contacts
    echo "<h4>📞 Tous les contacts dans contacts.xml:</h4>";
    $allContacts = [];
    foreach ($contactsXml->contact as $contact) {
        $allContacts[] = [
            'contact_id' => (string)$contact['id'],
            'user_id' => (string)$contact->user_id,
            'contact_user_id' => (string)$contact->contact_user_id,
            'nickname' => (string)$contact->nickname,
            'created_at' => (string)$contact->created_at
        ];
    }
    
    if (empty($allContacts)) {
        echo "<div style='color: orange;'>⚠️ Aucun contact dans le fichier</div>";
    } else {
        foreach ($allContacts as $contact) {
            echo "<div style='border: 1px solid #ddd; padding: 10px; margin: 5px 0; border-radius: 5px;'>";
            echo "<strong>Contact ID:</strong> " . $contact['contact_id'] . "<br>";
            echo "<strong>User ID (propriétaire):</strong> " . $contact['user_id'] . "<br>";
            echo "<strong>Contact User ID:</strong> " . $contact['contact_user_id'] . "<br>";
            echo "<strong>Nickname:</strong> " . $contact['nickname'] . "<br>";
            echo "<strong>Créé le:</strong> " . $contact['created_at'] . "<br>";
            echo "<strong>Est-ce pour l'utilisateur actuel?</strong> " . ($contact['user_id'] === $currentUserId ? "✅ OUI" : "❌ NON") . "<br>";
            echo "</div>";
        }
    }
    
    // Contacts pour l'utilisateur actuel
    echo "<h4>👤 Contacts pour l'utilisateur $currentUserId:</h4>";
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
    
    if (empty($userContacts)) {
        echo "<div style='color: orange;'>⚠️ Aucun contact trouvé pour l'utilisateur $currentUserId</div>";
    } else {
        echo "<div style='color: green;'>✅ " . count($userContacts) . " contact(s) trouvé(s) pour l'utilisateur $currentUserId</div>";
        
        foreach ($userContacts as $contactUserId => $contactInfo) {
            echo "<div style='border: 1px solid #ddd; padding: 10px; margin: 5px 0; border-radius: 5px; background: #e8f5e8;'>";
            echo "<strong>Contact User ID:</strong> $contactUserId<br>";
            echo "<strong>Contact ID:</strong> " . $contactInfo['contact_id'] . "<br>";
            echo "<strong>Nickname:</strong> " . $contactInfo['nickname'] . "<br>";
            echo "<strong>Favori:</strong> " . ($contactInfo['favorite'] ? 'Oui' : 'Non') . "<br>";
            echo "<strong>Créé le:</strong> " . $contactInfo['created_at'] . "<br>";
            
            // Vérifier si l'utilisateur contact existe dans users.xml
            $userFound = null;
            foreach ($usersXml->user as $user) {
                if ((string)$user['id'] === $contactUserId) {
                    $userFound = $user;
                    break;
                }
            }
            
            if ($userFound) {
                echo "<strong>Utilisateur trouvé:</strong> ✅ " . (string)$userFound->name . "<br>";
                echo "<strong>Téléphone:</strong> " . (string)$userFound->phone . "<br>";
                echo "<strong>Email:</strong> " . (string)$userFound->email . "<br>";
                echo "<strong>Status:</strong> " . (string)$userFound->status . "<br>";
            } else {
                echo "<strong>Utilisateur trouvé:</strong> ❌ Utilisateur ID $contactUserId non trouvé dans users.xml<br>";
            }
            
            echo "</div>";
        }
    }
    
    echo "</div>";
}

// Simuler l'API get_contacts
echo "<h3>🔗 Simulation API get_contacts</h3>";

if (file_exists($contactsFile) && file_exists($usersFile)) {
    $contactsXml = simplexml_load_file($contactsFile);
    $usersXml = simplexml_load_file($usersFile);
    
    $contacts = [];
    $userContacts = [];
    
    // Récupérer les contacts de l'utilisateur courant
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
    
    // Construire la réponse finale
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
    
    $apiResponse = ['success' => true, 'contacts' => $contacts];
    
    echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
    echo "<strong>Réponse API simulée:</strong><br>";
    echo "<pre style='background: white; padding: 10px; border-radius: 5px; margin: 10px 0;'>";
    echo json_encode($apiResponse, JSON_PRETTY_PRINT);
    echo "</pre>";
    
    if (empty($contacts)) {
        echo "<div style='color: red;'>❌ Aucun contact retourné par l'API</div>";
        } else {
        echo "<div style='color: green;'>✅ " . count($contacts) . " contact(s) retourné(s) par l'API</div>";
    }
    echo "</div>";
}

// Test d'ajout de contact
echo "<h3>➕ Test d'Ajout de Contact</h3>";
echo "<form method='post' style='background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
echo "<label>ID de l'utilisateur à ajouter: <input type='text' name='add_user_id' value='1' style='padding: 5px; margin: 5px;'></label><br>";
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

echo "<h3>🎯 Instructions</h3>";
echo "<div style='background: #e7f3ff; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
echo "<strong>Pour tester l'application:</strong><br>";
echo "1. Vérifiez que les contacts sont bien listés ci-dessus<br>";
echo "2. Ajoutez un nouveau contact si nécessaire<br>";
echo "3. Ouvrez <a href='index.html' target='_blank'>index.html</a> dans un nouvel onglet<br>";
echo "4. Allez dans l'onglet 'Contacts'<br>";
echo "5. Vérifiez que les contacts s'affichent<br>";
echo "</div>";
?> 