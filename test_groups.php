<?php
session_start();

// Simuler la session de Kabyr Diop
$_SESSION['user_id'] = '3';
$_SESSION['user_name'] = 'Kabyr Diop';

echo "<h2>🧪 Test Gestion des Groupes</h2>";
echo "<p><strong>Utilisateur:</strong> " . $_SESSION['user_name'] . " (ID: " . $_SESSION['user_id'] . ")</p>";

// Test 1: Vérifier les groupes existants
echo "<h3>📊 Test 1: Groupes Existants</h3>";

ob_start();
$_GET['action'] = 'get_user_groups';
include 'php/groups.php';
$apiOutput = ob_get_clean();

$apiData = json_decode($apiOutput, true);

echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
echo "<strong>Groupes de l'utilisateur:</strong><br>";

if ($apiData && $apiData['success']) {
    $groups = $apiData['groups'];
    if (empty($groups)) {
        echo "<div style='color: orange;'>⚠️ Aucun groupe trouvé</div>";
    } else {
        echo "<div style='color: green;'>✅ " . count($groups) . " groupe(s) trouvé(s)</div>";
        foreach ($groups as $group) {
            echo "<div style='border: 1px solid #28a745; padding: 15px; margin: 10px 0; background: #d4edda; border-radius: 8px;'>";
            echo "<div style='font-weight: bold; color: #155724; font-size: 18px;'>👥 " . $group['name'] . "</div>";
            echo "<div style='margin: 5px 0;'><strong>ID:</strong> " . $group['id'] . "</div>";
            echo "<div style='margin: 5px 0;'><strong>Description:</strong> " . $group['description'] . "</div>";
            echo "<div style='margin: 5px 0;'><strong>Membres:</strong> " . $group['member_count'] . "</div>";
            echo "<div style='margin: 5px 0;'><strong>Rôle:</strong> " . $group['user_role'] . "</div>";
            echo "<div style='margin: 5px 0;'><strong>Admin:</strong> " . ($group['is_admin'] ? 'Oui' : 'Non') . "</div>";
            echo "<div style='margin: 5px 0;'><strong>Créé le:</strong> " . $group['created_at'] . "</div>";
            echo "</div>";
        }
    }
} else {
    echo "<div style='color: red;'>❌ Erreur API: " . ($apiData['message'] ?? 'Erreur inconnue') . "</div>";
}
echo "</div>";

// Test 2: Vérifier les contacts disponibles
echo "<h3>👥 Test 2: Contacts Disponibles</h3>";

ob_start();
$_GET['action'] = 'get_contacts';
include 'php/contacts.php';
$contactsOutput = ob_get_clean();

$contactsData = json_decode($contactsOutput, true);

echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
echo "<strong>Contacts de l'utilisateur:</strong><br>";

if ($contactsData && $contactsData['success']) {
    $contacts = $contactsData['contacts'];
    if (empty($contacts)) {
        echo "<div style='color: orange;'>⚠️ Aucun contact trouvé</div>";
    } else {
        echo "<div style='color: green;'>✅ " . count($contacts) . " contact(s) trouvé(s)</div>";
        foreach ($contacts as $contact) {
            echo "<div style='border: 1px solid #007bff; padding: 10px; margin: 5px 0; background: #cce7ff; border-radius: 5px;'>";
            echo "<strong>📞 " . $contact['name'] . "</strong> (ID: " . $contact['id'] . ")<br>";
            echo "Téléphone: " . $contact['phone'] . "<br>";
            echo "Email: " . $contact['email'] . "<br>";
            echo "Status: " . $contact['status'] . "<br>";
            echo "</div>";
        }
    }
} else {
    echo "<div style='color: red;'>❌ Erreur API contacts: " . ($contactsData['message'] ?? 'Erreur inconnue') . "</div>";
}
echo "</div>";

// Test 3: Créer un nouveau groupe
echo "<h3>➕ Test 3: Création de Groupe</h3>";

echo "<form method='post' style='background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
echo "<h4>Créer un nouveau groupe</h4>";
echo "<div style='margin-bottom: 10px;'>";
echo "<label>Nom du groupe: <input type='text' name='group_name' value='Groupe Test' style='padding: 5px; margin: 5px; width: 200px;'></label><br>";
echo "</div>";
echo "<div style='margin-bottom: 10px;'>";
echo "<label>Description: <input type='text' name='group_description' value='Groupe de test pour vérification' style='padding: 5px; margin: 5px; width: 300px;'></label><br>";
echo "</div>";
echo "<div style='margin-bottom: 10px;'>";
echo "<label>Contacts à ajouter (IDs séparés par des virgules): <input type='text' name='member_ids' value='1,2' style='padding: 5px; margin: 5px; width: 200px;'></label><br>";
echo "</div>";
echo "<input type='submit' name='create_group' value='Créer le groupe' style='background: #28a745; color: white; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer;'>";
echo "</form>";

if (isset($_POST['create_group'])) {
    $name = $_POST['group_name'];
    $description = $_POST['group_description'];
    $memberIds = array_map('trim', explode(',', $_POST['member_ids']));
    
    $groupData = [
        'action' => 'create_group',
        'name' => $name,
        'description' => $description,
        'member_ids' => $memberIds
    ];
    
    // Simuler l'appel API
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'http://localhost/chat_platform/php/groups.php');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($groupData));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    
    $response = curl_exec($ch);
    curl_close($ch);
    
    if ($response) {
        $result = json_decode($response, true);
        if ($result && $result['success']) {
            echo "<div style='color: green; padding: 10px; background: #d4edda; border-radius: 5px; margin: 10px 0;'>✅ Groupe créé avec succès ! ID: " . $result['group_id'] . "</div>";
            echo "<script>setTimeout(() => window.location.reload(), 2000);</script>";
        } else {
            echo "<div style='color: red; padding: 10px; background: #f8d7da; border-radius: 5px; margin: 10px 0;'>❌ Erreur: " . ($result['message'] ?? 'Erreur inconnue') . "</div>";
        }
    } else {
        echo "<div style='color: red; padding: 10px; background: #f8d7da; border-radius: 5px; margin: 10px 0;'>❌ Erreur de connexion à l'API</div>";
    }
}

// Test 4: Détails d'un groupe existant
if (!empty($groups)) {
    echo "<h3>🔍 Test 4: Détails d'un Groupe</h3>";
    
    $firstGroup = $groups[0];
    ob_start();
    $_GET['action'] = 'get_group_details';
    $_GET['group_id'] = $firstGroup['id'];
    include 'php/groups.php';
    $detailsOutput = ob_get_clean();
    
    $detailsData = json_decode($detailsOutput, true);
    
    echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
    echo "<strong>Détails du groupe '" . $firstGroup['name'] . "':</strong><br>";
    
    if ($detailsData && $detailsData['success']) {
        $group = $detailsData['group'];
        echo "<div style='color: green;'>✅ Détails récupérés avec succès</div>";
        echo "<div style='border: 1px solid #28a745; padding: 15px; margin: 10px 0; background: #d4edda; border-radius: 8px;'>";
        echo "<div style='font-weight: bold; color: #155724; font-size: 18px;'>👥 " . $group['name'] . "</div>";
        echo "<div style='margin: 5px 0;'><strong>Description:</strong> " . $group['description'] . "</div>";
        echo "<div style='margin: 5px 0;'><strong>Membres:</strong> " . count($group['members']) . "</div>";
        echo "<div style='margin: 5px 0;'><strong>Rôle:</strong> " . $group['user_role'] . "</div>";
        echo "<div style='margin: 5px 0;'><strong>Admin:</strong> " . ($group['is_admin'] ? 'Oui' : 'Non') . "</div>";
        
        if (!empty($group['members'])) {
            echo "<div style='margin: 10px 0;'><strong>Liste des membres:</strong></div>";
            foreach ($group['members'] as $member) {
                echo "<div style='border: 1px solid #007bff; padding: 8px; margin: 5px 0; background: #cce7ff; border-radius: 5px;'>";
                echo "<strong>👤 " . $member['name'] . "</strong> " . ($member['is_admin'] ? '👑' : '') . "<br>";
                echo "Rôle: " . $member['role'] . " | Status: " . $member['status'] . "<br>";
                echo "Rejoint le: " . $member['joined_at'] . "<br>";
                echo "</div>";
            }
        }
        echo "</div>";
    } else {
        echo "<div style='color: red;'>❌ Erreur: " . ($detailsData['message'] ?? 'Erreur inconnue') . "</div>";
    }
    echo "</div>";
}

// Test 5: Actions sur les groupes
echo "<h3>⚙️ Test 5: Actions sur les Groupes</h3>";

echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
echo "<h4>Actions disponibles:</h4>";
echo "<ul>";
echo "<li><strong>Créer un groupe:</strong> Sélectionner au moins 2 contacts</li>";
echo "<li><strong>Modifier un groupe:</strong> Changer le nom et la description (admin uniquement)</li>";
echo "<li><strong>Ajouter un membre:</strong> Depuis la liste des contacts (admin uniquement)</li>";
echo "<li><strong>Retirer un membre:</strong> Du groupe (admin uniquement)</li>";
echo "<li><strong>Quitter un groupe:</strong> Libre pour tous les membres</li>";
echo "<li><strong>Supprimer un groupe:</strong> Seulement si vide et admin</li>";
echo "</ul>";
echo "</div>";

// Instructions finales
echo "<h3>🎯 Instructions Finales</h3>";
echo "<div style='background: #e7f3ff; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
echo "<strong>Pour tester l'interface des groupes:</strong><br><br>";
echo "1. Ouvrir <a href='index.html' target='_blank'>index.html</a> dans un nouvel onglet<br>";
echo "2. Aller dans l'onglet 'Groupes'<br>";
echo "3. Cliquer sur 'Créer un groupe'<br>";
echo "4. Sélectionner au moins 2 contacts<br>";
echo "5. Créer le groupe et tester les fonctionnalités<br>";
echo "</div>";

echo "<h3>🔧 Fonctionnalités Implémentées</h3>";
echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
echo "<strong>✅ Fonctionnalités complètes:</strong><br>";
echo "• Création de groupe avec sélection de contacts<br>";
echo "• Gestion des rôles (admin/membre)<br>";
echo "• Ajout/retrait de membres<br>";
echo "• Modification des informations du groupe<br>";
echo "• Quitter un groupe<br>";
echo "• Suppression de groupe (si vide)<br>";
echo "• Interface utilisateur complète<br>";
echo "• Animations et styles modernes<br>";
echo "</div>";

// Recharger automatiquement après 15 secondes
echo "<script>";
echo "setTimeout(() => {";
echo "  if (confirm('Voulez-vous recharger la page pour vérifier les changements ?')) {";
echo "    window.location.reload();";
echo "  }";
echo "}, 15000);";
echo "</script>";
?> 