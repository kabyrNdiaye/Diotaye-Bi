<?php
session_start();
header('Content-Type: application/json');

echo "<h2>Test de session et contacts</h2>";

// Afficher les informations de session
echo "<h3>1. Informations de session</h3>";
if (isset($_SESSION['user_id'])) {
    echo "✅ Session active<br>";
    echo "User ID: " . $_SESSION['user_id'] . "<br>";
    echo "User Name: " . ($_SESSION['user_name'] ?? 'Non défini') . "<br>";
    echo "User Phone: " . ($_SESSION['user_phone'] ?? 'Non défini') . "<br>";
} else {
    echo "❌ Aucune session active<br>";
}

// Simuler une session pour le test
if (!isset($_SESSION['user_id'])) {
    $_SESSION['user_id'] = '1';
    $_SESSION['user_name'] = 'Narou Sagne';
    echo "✅ Session simulée créée<br>";
}

// Test des contacts
echo "<h3>2. Test des contacts</h3>";
$currentUserId = $_SESSION['user_id'];
echo "Recherche des contacts pour l'utilisateur ID: $currentUserId<br><br>";

$contactsFile = 'xml/contacts.xml';
$usersFile = 'xml/users.xml';

if (file_exists($contactsFile) && file_exists($usersFile)) {
    $contactsXml = simplexml_load_file($contactsFile);
    $usersXml = simplexml_load_file($usersFile);
    
    $contacts = [];
    $totalContacts = 0;
    
    foreach ($contactsXml->contact as $contact) {
        $totalContacts++;
        $contactUserId = (string)$contact->user_id;
        $contactContactId = (string)$contact->contact_user_id;
        
        echo "Contact $totalContacts: User ID = $contactUserId, Contact User ID = $contactContactId<br>";
        
        if ($contactUserId === $currentUserId) {
            echo "✅ Ce contact appartient à l'utilisateur actuel<br>";
            
            // Chercher l'utilisateur contact
            $contactUser = null;
            foreach ($usersXml->user as $user) {
                if ((string)$user['id'] === $contactContactId) {
                    $contactUser = $user;
                    break;
                }
            }
            
            if ($contactUser) {
                echo "✅ Utilisateur contact trouvé: " . $contactUser->name . "<br>";
                $contacts[] = [
                    'id' => (string)$contact['id'],
                    'user_id' => $contactContactId,
                    'name' => (string)$contactUser->name,
                    'phone' => (string)$contactUser->phone,
                    'email' => (string)$contactUser->email,
                    'status' => (string)$contactUser->status,
                    'online' => (string)$contactUser->status === 'Online',
                    'nickname' => (string)$contact->nickname,
                    'favorite' => (string)$contact->favorite === 'true',
                    'last_contact' => (string)$contact->last_contact
                ];
            } else {
                echo "❌ Utilisateur contact non trouvé pour ID: $contactContactId<br>";
            }
        } else {
            echo "❌ Ce contact n'appartient pas à l'utilisateur actuel<br>";
        }
        echo "<br>";
    }
    
    echo "<h4>Résumé :</h4>";
    echo "Total de contacts dans le fichier: $totalContacts<br>";
    echo "Contacts trouvés pour l'utilisateur $currentUserId: " . count($contacts) . "<br>";
    
    if (count($contacts) > 0) {
        echo "<h4>Contacts trouvés :</h4>";
        echo "<pre>" . json_encode($contacts, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "</pre>";
    } else {
        echo "<p style='color: red;'>❌ Aucun contact trouvé pour cet utilisateur</p>";
    }
} else {
    echo "❌ Fichiers XML non trouvés<br>";
}

// Test de l'API
echo "<h3>3. Test de l'API contacts</h3>";
$_GET['action'] = 'get_contacts';

ob_start();
include 'php/contacts.php';
$output = ob_get_clean();

echo "<h4>Réponse de l'API :</h4>";
echo "<pre>" . htmlspecialchars($output) . "</pre>";

$data = json_decode($output, true);
if ($data && isset($data['contacts'])) {
    echo "<h4>Contacts retournés par l'API : " . count($data['contacts']) . "</h4>";
    if (count($data['contacts']) > 0) {
        echo "<pre>" . json_encode($data['contacts'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "</pre>";
    }
}
?> 