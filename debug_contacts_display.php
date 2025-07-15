<?php
session_start();
header('Content-Type: text/html; charset=UTF-8');

echo "<h1>🔍 Débogage de l'affichage des contacts</h1>";

// Vérifier la session
echo "<h2>📋 Vérification de la session</h2>";
if (isset($_SESSION['user_id'])) {
    echo "<p style='color: green;'>✅ Session active - User ID: " . $_SESSION['user_id'] . "</p>";
} else {
    echo "<p style='color: red;'>❌ Aucune session active</p>";
    exit;
}

// Vérifier le fichier contacts.xml
echo "<h2>📄 Vérification du fichier contacts.xml</h2>";
$contactsFile = __DIR__ . '/xml/contacts.xml';
if (file_exists($contactsFile)) {
    echo "<p style='color: green;'>✅ Fichier contacts.xml trouvé</p>";
    
    $xml = simplexml_load_file($contactsFile);
    if ($xml === false) {
        echo "<p style='color: red;'>❌ Erreur lors du chargement du XML</p>";
    } else {
        echo "<p style='color: green;'>✅ XML chargé avec succès</p>";
        
        $currentUserId = $_SESSION['user_id'];
        $userContacts = [];
        
        echo "<h3>📋 Contacts trouvés pour l'utilisateur $currentUserId :</h3>";
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>Contact ID</th><th>User ID</th><th>Contact User ID</th><th>Nickname</th><th>Favorite</th><th>Created At</th></tr>";
        
        foreach ($xml->contact as $contact) {
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
                
                echo "<tr>";
                echo "<td>" . $contact['id'] . "</td>";
                echo "<td>" . $contact->user_id . "</td>";
                echo "<td>" . $contact->contact_user_id . "</td>";
                echo "<td>" . $contact->nickname . "</td>";
                echo "<td>" . $contact->favorite . "</td>";
                echo "<td>" . $contact->created_at . "</td>";
                echo "</tr>";
            }
        }
        echo "</table>";
        
        if (empty($userContacts)) {
            echo "<p style='color: orange;'>⚠️ Aucun contact trouvé pour cet utilisateur</p>";
        } else {
            echo "<p style='color: green;'>✅ " . count($userContacts) . " contact(s) trouvé(s)</p>";
        }
    }
} else {
    echo "<p style='color: red;'>❌ Fichier contacts.xml non trouvé</p>";
}

// Vérifier le fichier users.xml
echo "<h2>👥 Vérification du fichier users.xml</h2>";
$usersFile = __DIR__ . '/xml/users.xml';
if (file_exists($usersFile)) {
    echo "<p style='color: green;'>✅ Fichier users.xml trouvé</p>";
    
    $usersXml = simplexml_load_file($usersFile);
    if ($usersXml === false) {
        echo "<p style='color: red;'>❌ Erreur lors du chargement du XML</p>";
    } else {
        echo "<p style='color: green;'>✅ XML chargé avec succès</p>";
        
        echo "<h3>👤 Utilisateurs disponibles :</h3>";
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>ID</th><th>Nom</th><th>Téléphone</th><th>Email</th><th>Status</th></tr>";
        
        foreach ($usersXml->user as $user) {
            $id = (string)$user['id'];
            $name = (string)$user->name;
            $phone = (string)$user->phone;
            $email = (string)$user->email;
            $status = (string)$user->status;
            
            $isCurrentUser = ($id === $_SESSION['user_id']) ? " (VOUS)" : "";
            $isContact = isset($userContacts[$id]) ? " (CONTACT)" : "";
            
            echo "<tr>";
            echo "<td>" . $id . $isCurrentUser . $isContact . "</td>";
            echo "<td>" . $name . "</td>";
            echo "<td>" . $phone . "</td>";
            echo "<td>" . $email . "</td>";
            echo "<td>" . $status . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
} else {
    echo "<p style='color: red;'>❌ Fichier users.xml non trouvé</p>";
}

// Tester l'API contacts.php
echo "<h2>🔌 Test de l'API contacts.php</h2>";
echo "<p>Test de l'action get_contacts :</p>";

$apiUrl = 'php/contacts.php?action=get_contacts';
$context = stream_context_create([
    'http' => [
        'method' => 'GET',
        'header' => 'Cookie: ' . http_build_query($_COOKIE, '', '; ')
    ]
]);

$response = file_get_contents($apiUrl, false, $context);
if ($response === false) {
    echo "<p style='color: red;'>❌ Erreur lors de l'appel API</p>";
} else {
    echo "<p style='color: green;'>✅ Réponse API reçue</p>";
    
    $data = json_decode($response, true);
    if ($data === null) {
        echo "<p style='color: red;'>❌ Erreur de décodage JSON</p>";
        echo "<pre>" . htmlspecialchars($response) . "</pre>";
    } else {
        echo "<p style='color: green;'>✅ JSON décodé avec succès</p>";
        
        if (isset($data['success'])) {
            if ($data['success']) {
                echo "<p style='color: green;'>✅ API retourne success: true</p>";
                if (isset($data['contacts'])) {
                    echo "<p style='color: green;'>✅ " . count($data['contacts']) . " contact(s) retourné(s) par l'API</p>";
                    
                    if (count($data['contacts']) > 0) {
                        echo "<h3>📋 Contacts retournés par l'API :</h3>";
                        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
                        echo "<tr><th>ID</th><th>Contact ID</th><th>Nom</th><th>Nickname</th><th>Téléphone</th><th>Favorite</th></tr>";
                        
                        foreach ($data['contacts'] as $contact) {
                            echo "<tr>";
                            echo "<td>" . $contact['id'] . "</td>";
                            echo "<td>" . $contact['contact_id'] . "</td>";
                            echo "<td>" . $contact['name'] . "</td>";
                            echo "<td>" . $contact['nickname'] . "</td>";
                            echo "<td>" . $contact['phone'] . "</td>";
                            echo "<td>" . ($contact['favorite'] ? 'Oui' : 'Non') . "</td>";
                            echo "</tr>";
                        }
                        echo "</table>";
                    } else {
                        echo "<p style='color: orange;'>⚠️ Aucun contact retourné par l'API</p>";
                    }
                } else {
                    echo "<p style='color: red;'>❌ Pas de clé 'contacts' dans la réponse</p>";
                }
            } else {
                echo "<p style='color: red;'>❌ API retourne success: false</p>";
                if (isset($data['message'])) {
                    echo "<p style='color: red;'>Message d'erreur: " . $data['message'] . "</p>";
                }
            }
        } else {
            echo "<p style='color: red;'>❌ Pas de clé 'success' dans la réponse</p>";
        }
    }
}

// Instructions pour résoudre le problème
echo "<h2>🔧 Instructions de résolution</h2>";
echo "<div style='background: #f0f0f0; padding: 15px; border-radius: 5px;'>";
echo "<h3>Si les contacts ne s'affichent pas :</h3>";
echo "<ol>";
echo "<li>Vérifiez que vous êtes bien connecté (session active)</li>";
echo "<li>Vérifiez que des contacts existent dans contacts.xml pour votre utilisateur</li>";
echo "<li>Vérifiez que l'API contacts.php fonctionne correctement</li>";
echo "<li>Ouvrez la console du navigateur (F12) pour voir les erreurs JavaScript</li>";
echo "<li>Vérifiez que le fichier js/app.js charge bien les contacts</li>";
echo "</ol>";
echo "</div>";

echo "<h2>🧪 Test manuel</h2>";
echo "<p>Pour tester manuellement l'ajout d'un contact :</p>";
echo "<a href='test_contacts_ajoutes.html' target='_blank' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Ouvrir le test des contacts</a>";
?> 