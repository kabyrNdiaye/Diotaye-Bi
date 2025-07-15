<?php
session_start();

// Simuler la session de Kabyr Diop
$_SESSION['user_id'] = '3';
$_SESSION['user_name'] = 'Kabyr Diop';

echo "<h2>🔍 Test API Simple</h2>";
echo "<p><strong>Utilisateur:</strong> " . $_SESSION['user_name'] . " (ID: " . $_SESSION['user_id'] . ")</p>";

// Test 1: Vérifier les fichiers XML
echo "<h3>📁 Test 1: Vérification des Fichiers</h3>";

$contactsFile = 'xml/contacts.xml';
$usersFile = 'xml/users.xml';

if (file_exists($contactsFile)) {
    $contactsXml = simplexml_load_file($contactsFile);
    echo "<p style='color: green;'>✅ contacts.xml chargé</p>";
    
    echo "<h4>Contacts dans le fichier:</h4>";
    foreach ($contactsXml->contact as $contact) {
        echo "<div style='border: 1px solid #ddd; padding: 10px; margin: 5px 0;'>";
        echo "<strong>Contact ID:</strong> " . $contact['id'] . "<br>";
        echo "<strong>User ID:</strong> " . $contact->user_id . "<br>";
        echo "<strong>Contact User ID:</strong> " . $contact->contact_user_id . "<br>";
        echo "<strong>Nickname:</strong> " . $contact->nickname . "<br>";
        echo "</div>";
    }
} else {
    echo "<p style='color: red;'>❌ contacts.xml non trouvé</p>";
}

if (file_exists($usersFile)) {
    $usersXml = simplexml_load_file($usersFile);
    echo "<p style='color: green;'>✅ users.xml chargé</p>";
    
    echo "<h4>Utilisateurs dans le fichier:</h4>";
    foreach ($usersXml->user as $user) {
        echo "<div style='border: 1px solid #ddd; padding: 10px; margin: 5px 0;'>";
        echo "<strong>User ID:</strong> " . $user['id'] . "<br>";
        echo "<strong>Name:</strong> " . $user->name . "<br>";
        echo "<strong>Phone:</strong> " . $user->phone . "<br>";
        echo "</div>";
    }
} else {
    echo "<p style='color: red;'>❌ users.xml non trouvé</p>";
}

// Test 2: Test direct de l'API
echo "<h3>🔗 Test 2: Test Direct de l'API</h3>";

// Capturer la sortie de l'API
ob_start();
$_GET['action'] = 'get_contacts';
include 'php/contacts.php';
$apiOutput = ob_get_clean();

echo "<h4>Réponse brute de l'API:</h4>";
echo "<pre style='background: #f8f9fa; padding: 15px; border-radius: 5px;'>";
echo htmlspecialchars($apiOutput);
echo "</pre>";

// Décoder la réponse
$apiData = json_decode($apiOutput, true);
if ($apiData === null) {
    echo "<p style='color: red;'>❌ Erreur de décodage JSON</p>";
} else {
    echo "<p style='color: green;'>✅ JSON valide</p>";
    
    if ($apiData['success']) {
        $contacts = $apiData['contacts'];
        echo "<h4>Contacts retournés par l'API:</h4>";
        if (empty($contacts)) {
            echo "<p style='color: orange;'>⚠️ Aucun contact retourné</p>";
        } else {
            echo "<p style='color: green;'>✅ " . count($contacts) . " contact(s) retourné(s)</p>";
            foreach ($contacts as $contact) {
                echo "<div style='border: 1px solid #28a745; padding: 10px; margin: 5px 0; background: #d4edda;'>";
                echo "<strong>Nom:</strong> " . $contact['name'] . "<br>";
                echo "<strong>ID:</strong> " . $contact['id'] . "<br>";
                echo "<strong>Contact ID:</strong> " . $contact['contact_id'] . "<br>";
                echo "<strong>Nickname:</strong> " . $contact['nickname'] . "<br>";
                echo "<strong>Téléphone:</strong> " . $contact['phone'] . "<br>";
                echo "</div>";
            }
        }
    } else {
        echo "<p style='color: red;'>❌ Erreur API: " . $apiData['message'] . "</p>";
    }
}

// Test 3: Test manuel de la logique
echo "<h3>🔧 Test 3: Test Manuel de la Logique</h3>";

$currentUserId = '3';
$userContacts = [];

// Récupérer les contacts de l'utilisateur courant
foreach ($contactsXml->contact as $contact) {
    if ((string)$contact->user_id === $currentUserId) {
        $contactUserId = (string)$contact->contact_user_id;
        echo "<p>✅ Contact trouvé pour utilisateur $currentUserId: contact_user_id = $contactUserId</p>";
        
        // Trouver l'utilisateur correspondant
        $userFound = null;
        foreach ($usersXml->user as $user) {
            if ((string)$user['id'] === $contactUserId) {
                $userFound = $user;
                break;
            }
        }
        
        if ($userFound) {
            echo "<p style='color: green;'>✅ Utilisateur contact trouvé: " . $userFound->name . "</p>";
            $userContacts[] = [
                'id' => $contactUserId,
                'name' => (string)$userFound->name,
                'phone' => (string)$userFound->phone,
                'nickname' => (string)$contact->nickname
            ];
        } else {
            echo "<p style='color: red;'>❌ Utilisateur contact non trouvé pour ID: $contactUserId</p>";
        }
    }
}

echo "<h4>Résultat du test manuel:</h4>";
if (empty($userContacts)) {
    echo "<p style='color: red;'>❌ Aucun contact trouvé manuellement</p>";
} else {
    echo "<p style='color: green;'>✅ " . count($userContacts) . " contact(s) trouvé(s) manuellement</p>";
    foreach ($userContacts as $contact) {
        echo "<div style='border: 1px solid #007bff; padding: 10px; margin: 5px 0; background: #cce7ff;'>";
        echo "<strong>Nom:</strong> " . $contact['name'] . "<br>";
        echo "<strong>ID:</strong> " . $contact['id'] . "<br>";
        echo "<strong>Nickname:</strong> " . $contact['nickname'] . "<br>";
        echo "<strong>Téléphone:</strong> " . $contact['phone'] . "<br>";
        echo "</div>";
    }
}

echo "<h3>🎯 Conclusion</h3>";
if (!empty($userContacts)) {
    echo "<p style='color: green; font-weight: bold;'>✅ Le problème est dans l'API, pas dans les données</p>";
    echo "<p>Les contacts existent mais l'API ne les retourne pas correctement.</p>";
} else {
    echo "<p style='color: red; font-weight: bold;'>❌ Le problème est dans les données</p>";
    echo "<p>Aucun contact trouvé pour l'utilisateur $currentUserId.</p>";
}
?> 