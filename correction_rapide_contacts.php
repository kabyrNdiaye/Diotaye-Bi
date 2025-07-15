<?php
session_start();

// Simuler la session de Kabyr Diop
$_SESSION['user_id'] = '3';
$_SESSION['user_name'] = 'Kabyr Diop';

echo "<h2>🚀 Correction Rapide - Contacts</h2>";

// Étape 1: Vérifier les données
echo "<h3>📊 Étape 1: Vérification des Données</h3>";

$contactsFile = 'xml/contacts.xml';
$usersFile = 'xml/users.xml';

$contactsXml = simplexml_load_file($contactsFile);
$usersXml = simplexml_load_file($usersFile);

$currentUserId = '3';
$userContacts = [];

// Récupérer les contacts de Kabyr Diop
foreach ($contactsXml->contact as $contact) {
    if ((string)$contact->user_id === $currentUserId) {
        $contactUserId = (string)$contact->contact_user_id;
        
        // Trouver l'utilisateur correspondant
        foreach ($usersXml->user as $user) {
            if ((string)$user['id'] === $contactUserId) {
                $userContacts[] = [
                    'id' => $contactUserId,
                    'contact_id' => (string)$contact['id'],
                    'name' => (string)$user->name,
                    'phone' => (string)$user->phone,
                    'email' => (string)$user->email,
                    'status' => (string)$user->status,
                    'online' => (string)$user->status === 'Online',
                    'avatar' => (string)$user->avatar,
                    'bio' => (string)$user->bio,
                    'nickname' => (string)$contact->nickname,
                    'favorite' => (string)$contact->favorite === 'true',
                    'blocked' => (string)$contact->blocked === 'true',
                    'created_at' => (string)$contact->created_at,
                    'last_contact' => (string)$contact->last_contact
                ];
                break;
            }
        }
    }
}

echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
echo "<strong>Contacts trouvés pour Kabyr Diop:</strong> " . count($userContacts) . "<br>";

foreach ($userContacts as $contact) {
    echo "<div style='border: 1px solid #28a745; padding: 10px; margin: 5px 0; background: #d4edda;'>";
    echo "<strong>✅ Contact:</strong> " . $contact['name'] . "<br>";
    echo "<strong>ID:</strong> " . $contact['id'] . "<br>";
    echo "<strong>Contact ID:</strong> " . $contact['contact_id'] . "<br>";
    echo "<strong>Nickname:</strong> " . $contact['nickname'] . "<br>";
    echo "<strong>Téléphone:</strong> " . $contact['phone'] . "<br>";
    echo "<strong>Status:</strong> " . $contact['status'] . "<br>";
    echo "</div>";
}
echo "</div>";

// Étape 2: Corriger l'API
echo "<h3>🔧 Étape 2: Correction de l'API</h3>";

// Créer une version corrigée de l'API
$correctedApi = '<?php
session_start();
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER["REQUEST_METHOD"] === "OPTIONS") {
    exit(0);
}

if (!isset($_SESSION["user_id"])) {
    echo json_encode(["success" => false, "message" => "Non autorisé"]);
    exit;
}

$xmlFile = __DIR__ . "/../xml/contacts.xml";
$usersFile = __DIR__ . "/../xml/users.xml";

if (!file_exists($xmlFile) || !file_exists($usersFile)) {
    echo json_encode(["success" => false, "message" => "Fichiers XML manquants"]);
    exit;
}

$xml = simplexml_load_file($xmlFile);
$usersXml = simplexml_load_file($usersFile);

if ($xml === false || $usersXml === false) {
    echo json_encode(["success" => false, "message" => "Erreur de chargement XML"]);
    exit;
}

if (isset($_GET["action"]) && $_GET["action"] === "get_contacts") {
    $contacts = [];
    $currentUserId = $_SESSION["user_id"];
    
    // Récupérer les contacts de l\'utilisateur courant
    foreach ($xml->contact as $contact) {
        if ((string)$contact->user_id === $currentUserId) {
            $contactUserId = (string)$contact->contact_user_id;
            
            // Trouver l\'utilisateur correspondant
            foreach ($usersXml->user as $user) {
                if ((string)$user["id"] === $contactUserId) {
                    $contacts[] = [
                        "id" => $contactUserId,
                        "contact_id" => (string)$contact["id"],
                        "name" => (string)$user->name,
                        "phone" => (string)$user->phone,
                        "email" => (string)$user->email,
                        "status" => (string)$user->status,
                        "online" => (string)$user->status === "Online",
                        "avatar" => (string)$user->avatar,
                        "bio" => (string)$user->bio,
                        "nickname" => (string)$contact->nickname,
                        "favorite" => (string)$contact->favorite === "true",
                        "blocked" => (string)$contact->blocked === "true",
                        "created_at" => (string)$contact->created_at,
                        "last_contact" => (string)$contact->last_contact
                    ];
                    break;
                }
            }
        }
    }
    
    echo json_encode(["success" => true, "contacts" => $contacts]);
    exit;
}

// Gérer les autres actions...
echo json_encode(["success" => false, "message" => "Action non reconnue"]);
?>';

// Sauvegarder la version corrigée
file_put_contents('php/contacts_corrige.php', $correctedApi);
echo "<div style='color: green;'>✅ API corrigée créée: contacts_corrige.php</div>";

// Étape 3: Tester l'API corrigée
echo "<h3>🧪 Étape 3: Test de l'API Corrigée</h3>";

ob_start();
$_GET['action'] = 'get_contacts';
include 'php/contacts_corrige.php';
$apiOutput = ob_get_clean();

$apiData = json_decode($apiOutput, true);

echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
echo "<strong>Réponse de l'API corrigée:</strong><br>";
echo "<pre style='background: white; padding: 10px; border-radius: 5px; margin: 10px 0;'>";
echo htmlspecialchars($apiOutput);
echo "</pre>";

if ($apiData && $apiData['success']) {
    $contacts = $apiData['contacts'];
    echo "<div style='color: green;'>✅ API corrigée fonctionne: " . count($contacts) . " contact(s) retourné(s)</div>";
} else {
    echo "<div style='color: red;'>❌ Erreur API corrigée</div>";
}
echo "</div>";

// Étape 4: Créer une page de test avec l'API corrigée
echo "<h3>🎯 Étape 4: Page de Test avec API Corrigée</h3>";

$testPage = '<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Contacts - API Corrigée</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .contact-item { 
            border: 1px solid #ddd; 
            padding: 15px; 
            margin: 10px 0; 
            border-radius: 8px; 
            background: #f9f9f9; 
        }
        .contact-name { font-weight: bold; color: #128C7E; }
        .contact-phone { color: #666; margin: 5px 0; }
        .contact-email { color: #666; margin: 5px 0; }
        .contact-status { color: #28a745; }
        .error { color: red; background: #ffe6e6; padding: 10px; border-radius: 5px; }
        .success { color: green; background: #e6ffe6; padding: 10px; border-radius: 5px; }
        .loading { color: blue; }
    </style>
</head>
<body>
    <h1>🧪 Test Contacts - API Corrigée</h1>
    <div id="status" class="loading">Chargement des contacts...</div>
    <div id="contacts"></div>

    <script>
        // Simuler la session
        fetch("php/contacts_corrige.php?action=get_contacts")
            .then(response => response.json())
            .then(data => {
                const statusDiv = document.getElementById("status");
                const contactsDiv = document.getElementById("contacts");
                
                if (data.success) {
                    const contacts = data.contacts;
                    statusDiv.className = "success";
                    statusDiv.innerHTML = `✅ ${contacts.length} contact(s) chargé(s) avec succès`;
                    
                    if (contacts.length === 0) {
                        contactsDiv.innerHTML = "<div class=\"error\">Aucun contact trouvé</div>";
                    } else {
                        contacts.forEach(contact => {
                            const contactDiv = document.createElement("div");
                            contactDiv.className = "contact-item";
                            contactDiv.innerHTML = `
                                <div class="contact-name">${contact.name}</div>
                                <div class="contact-phone">📱 ${contact.phone}</div>
                                <div class="contact-email">📧 ${contact.email}</div>
                                <div class="contact-status">${contact.online ? "🟢 En ligne" : "🔴 Hors ligne"}</div>
                                <div>💬 Surnom: ${contact.nickname}</div>
                                <div>⭐ Favori: ${contact.favorite ? "Oui" : "Non"}</div>
                                <div>📅 Créé le: ${contact.created_at}</div>
                            `;
                            contactsDiv.appendChild(contactDiv);
                        });
                    }
                } else {
                    statusDiv.className = "error";
                    statusDiv.innerHTML = `❌ Erreur: ${data.message}`;
                }
            })
            .catch(error => {
                document.getElementById("status").className = "error";
                document.getElementById("status").innerHTML = `❌ Erreur de connexion: ${error.message}`;
            });
    </script>
</body>
</html>';

file_put_contents('test_contacts_api_corrigee.html', $testPage);
echo "<div style='color: green;'>✅ Page de test créée: test_contacts_api_corrigee.html</div>";

// Étape 5: Instructions finales
echo "<h3>🎉 Instructions Finales</h3>";
echo "<div style='background: #e7f3ff; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
echo "<strong>Pour corriger définitivement le problème:</strong><br><br>";
echo "1. <strong>Remplacer l'API:</strong> Copier le contenu de php/contacts_corrige.php vers php/contacts.php<br>";
echo "2. <strong>Tester:</strong> Ouvrir <a href='test_contacts_api_corrigee.html' target='_blank'>test_contacts_api_corrigee.html</a><br>";
echo "3. <strong>Vérifier:</strong> Si le test fonctionne, ouvrir index.html et aller dans l'onglet Contacts<br>";
echo "4. <strong>Résultat:</strong> Les contacts doivent maintenant s'afficher correctement<br>";
echo "</div>";

echo "<h3>🔧 Action Immédiate</h3>";
echo "<div style='background: #fff3cd; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
echo "<strong>Cliquez sur ce lien pour tester immédiatement:</strong><br>";
echo "<a href='test_contacts_api_corrigee.html' target='_blank' style='color: #007bff; font-weight: bold;'>🧪 TESTER L'API CORRIGÉE</a><br><br>";
echo "Si le test fonctionne, les contacts s'afficheront dans l'application principale.";
echo "</div>";
?> 