<?php
session_start();

echo "<h2>Ajout de contacts de test</h2>";

// Simuler une session utilisateur
if (!isset($_SESSION['user_id'])) {
    $_SESSION['user_id'] = '1';
    $_SESSION['user_name'] = 'Narou Sagne';
}

$currentUserId = $_SESSION['user_id'];
echo "Utilisateur actuel: $currentUserId<br><br>";

$contactsFile = 'xml/contacts.xml';
$usersFile = 'xml/users.xml';

if (!file_exists($contactsFile) || !file_exists($usersFile)) {
    echo "❌ Fichiers XML manquants<br>";
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

echo "Contacts existants pour l'utilisateur $currentUserId: " . implode(', ', $existingContacts) . "<br><br>";

// Ajouter des contacts manquants
$contactsToAdd = [];
foreach ($usersXml->user as $user) {
    $userId = (string)$user['id'];
    if ($userId !== $currentUserId && !in_array($userId, $existingContacts)) {
        $contactsToAdd[] = $userId;
    }
}

echo "Contacts à ajouter: " . implode(', ', $contactsToAdd) . "<br><br>";

if (empty($contactsToAdd)) {
    echo "✅ Tous les contacts sont déjà ajoutés<br>";
} else {
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
            
            echo "✅ Contact ajouté: " . $userFound->name . " (ID: $userId)<br>";
        }
    }
    
    // Sauvegarder le fichier
    $contactsXml->asXML($contactsFile);
    echo "<br>✅ Fichier contacts.xml mis à jour<br>";
}

// Afficher les contacts finaux
echo "<h3>Contacts finaux :</h3>";
$finalContacts = [];
foreach ($contactsXml->contact as $contact) {
    if ((string)$contact->user_id === $currentUserId) {
        $contactUserId = (string)$contact->contact_user_id;
        
        // Trouver l'utilisateur
        $contactUser = null;
        foreach ($usersXml->user as $user) {
            if ((string)$user['id'] === $contactUserId) {
                $contactUser = $user;
                break;
            }
        }
        
        if ($contactUser) {
            $finalContacts[] = [
                'id' => (string)$contact['id'],
                'name' => (string)$contactUser->name,
                'phone' => (string)$contactUser->phone,
                'email' => (string)$contactUser->email,
                'status' => (string)$contactUser->status,
                'nickname' => (string)$contact->nickname,
                'favorite' => (string)$contact->favorite === 'true'
            ];
        }
    }
}

if (count($finalContacts) > 0) {
    echo "<div style='border: 1px solid #ccc; padding: 15px; margin: 15px 0; border-radius: 5px;'>";
    echo "<h4>Contacts de l'utilisateur $currentUserId :</h4>";
    foreach ($finalContacts as $contact) {
        $statusColor = $contact['status'] === 'Online' ? 'green' : 'gray';
        echo "<div style='border: 1px solid #ddd; padding: 10px; margin: 10px 0; border-radius: 5px;'>";
        echo "<div style='font-weight: bold;'>" . $contact['name'] . "</div>";
        echo "<div style='color: $statusColor;'>" . $contact['status'] . "</div>";
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
    echo "</div>";
} else {
    echo "<p style='color: red;'>❌ Aucun contact trouvé</p>";
}

echo "<br><a href='index.html' style='background: #007bff; color: white; padding: 10px 15px; text-decoration: none; border-radius: 5px;'>Retour à l'application</a>";
?> 