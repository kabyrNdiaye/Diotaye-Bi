<?php
session_start();

// Simuler une session utilisateur pour le test
$_SESSION['user_id'] = '1';

echo "<!DOCTYPE html><html><head><title>Test Contacts</title></head><body>";
echo "<h2>Test de modification des contacts</h2>";

// Test 1: Vérifier la structure des contacts
echo "<h3>1. Structure des contacts pour l'utilisateur 1</h3>";

$contactsFile = __DIR__ . '/xml/contacts.xml';
$usersFile = __DIR__ . '/xml/users.xml';

echo "<p>Contacts file: $contactsFile</p>";
echo "<p>Users file: $usersFile</p>";

if (!file_exists($contactsFile)) {
    echo "<p style='color: red;'>❌ Fichier contacts.xml manquant</p>";
} else {
    echo "<p style='color: green;'>✅ Fichier contacts.xml trouvé</p>";
}

if (!file_exists($usersFile)) {
    echo "<p style='color: red;'>❌ Fichier users.xml manquant</p>";
} else {
    echo "<p style='color: green;'>✅ Fichier users.xml trouvé</p>";
}

if (!file_exists($contactsFile) || !file_exists($usersFile)) {
    echo "</body></html>";
    exit;
}

$contactsXml = simplexml_load_file($contactsFile);
$usersXml = simplexml_load_file($usersFile);

if ($contactsXml === false) {
    echo "<p style='color: red;'>❌ Erreur lors du chargement de contacts.xml</p>";
    echo "</body></html>";
    exit;
}

if ($usersXml === false) {
    echo "<p style='color: red;'>❌ Erreur lors du chargement de users.xml</p>";
    echo "</body></html>";
    exit;
}

$currentUserId = '1';
$userContacts = [];

// Récupérer les contacts de l'utilisateur courant
foreach ($contactsXml->contact as $contact) {
    if ((string)$contact->user_id === $currentUserId) {
        $contactUserId = (string)$contact->contact_user_id;
        $userContacts[$contactUserId] = [
            'contact_id' => (string)$contact['id'],
            'nickname' => (string)$contact->nickname,
            'favorite' => (string)$contact->favorite === 'true',
            'blocked' => (string)$contact->blocked === 'true'
        ];
    }
}

echo "<h4>Contacts dans contacts.xml pour l'utilisateur 1:</h4>";
if (empty($userContacts)) {
    echo "<p>Aucun contact trouvé pour l'utilisateur 1</p>";
} else {
    foreach ($userContacts as $userId => $contactInfo) {
        echo "<div style='border: 1px solid #ddd; padding: 10px; margin: 5px 0;'>";
        echo "<strong>Contact ID:</strong> " . $contactInfo['contact_id'] . "<br>";
        echo "<strong>User ID:</strong> " . $userId . "<br>";
        echo "<strong>Nickname:</strong> " . $contactInfo['nickname'] . "<br>";
        echo "<strong>Favorite:</strong> " . ($contactInfo['favorite'] ? 'Oui' : 'Non') . "<br>";
        echo "</div>";
    }
}

// Test 2: Simuler l'API get_contacts
echo "<h3>2. Simulation de l'API get_contacts</h3>";

$contacts = [];
foreach ($usersXml->user as $user) {
    $id = (string)$user['id'];
    if ($id !== $currentUserId) {
        $contactInfo = $userContacts[$id] ?? null;
        
        $contacts[] = [
            'id' => $id,
            'contact_id' => $contactInfo ? $contactInfo['contact_id'] : null,
            'name' => (string)$user->name,
            'phone' => (string)$user->phone,
            'email' => (string)$user->email,
            'status' => (string)$user->status,
            'online' => (string)$user->status === 'Online',
            'nickname' => $contactInfo ? $contactInfo['nickname'] : null,
            'favorite' => $contactInfo ? $contactInfo['favorite'] : false
        ];
    }
}

echo "<h4>Contacts retournés par l'API:</h4>";
if (empty($contacts)) {
    echo "<p>Aucun contact retourné</p>";
} else {
    foreach ($contacts as $contact) {
        echo "<div style='border: 1px solid #ddd; padding: 10px; margin: 5px 0;'>";
        echo "<strong>User ID:</strong> " . $contact['id'] . "<br>";
        echo "<strong>Contact ID:</strong> " . ($contact['contact_id'] ?: 'Non défini') . "<br>";
        echo "<strong>Nom:</strong> " . $contact['name'] . "<br>";
        echo "<strong>Surnom:</strong> " . ($contact['nickname'] ?: 'Non défini') . "<br>";
        echo "<strong>Favorite:</strong> " . ($contact['favorite'] ? 'Oui' : 'Non') . "<br>";
        echo "</div>";
    }
}

// Test 3: Test de modification d'un contact
echo "<h3>3. Test de modification d'un contact</h3>";

if (isset($_POST['test_update'])) {
    $contactId = $_POST['contact_id'];
    $newNickname = $_POST['new_nickname'];
    
    // Trouver le contact
    $contact = $contactsXml->xpath("//contact[@id='$contactId' and user_id='$currentUserId']")[0] ?? null;
    
    if ($contact) {
        $oldNickname = (string)$contact->nickname;
        $contact->nickname = $newNickname;
        
        if ($contactsXml->asXML($contactsFile)) {
            echo "<p style='color: green;'>✅ Contact modifié avec succès</p>";
            echo "<p><strong>Ancien surnom:</strong> $oldNickname</p>";
            echo "<p><strong>Nouveau surnom:</strong> $newNickname</p>";
        } else {
            echo "<p style='color: red;'>❌ Erreur lors de la sauvegarde</p>";
        }
    } else {
        echo "<p style='color: red;'>❌ Contact non trouvé</p>";
    }
}

// Formulaire de test
echo "<form method='post'>";
echo "<h4>Modifier un contact:</h4>";
echo "<label>Contact ID: <input type='text' name='contact_id' value='1'></label><br>";
echo "<label>Nouveau surnom: <input type='text' name='new_nickname' value='Nouveau Surnom'></label><br>";
echo "<input type='submit' name='test_update' value='Tester la modification'>";
echo "</form>";

echo "<hr>";
echo "<p><strong>Note:</strong> Ce test vérifie que la structure des données est correcte et que les modifications fonctionnent.</p>";
echo "</body></html>";
?> 