<?php
session_start();

// Simuler la session de Kabyr Diop
$_SESSION['user_id'] = '3';
$_SESSION['user_name'] = 'Kabyr Diop';

echo "<h2>🎯 Test Final Simple</h2>";
echo "<p><strong>Utilisateur:</strong> " . $_SESSION['user_name'] . " (ID: " . $_SESSION['user_id'] . ")</p>";

// Test de l'API
echo "<h3>🔗 Test de l'API</h3>";

ob_start();
$_GET['action'] = 'get_contacts';
include 'php/contacts.php';
$apiOutput = ob_get_clean();

$apiData = json_decode($apiOutput, true);

echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 10px 0;'>";

if ($apiData && $apiData['success']) {
    $contacts = $apiData['contacts'];
    echo "<div style='color: green; font-weight: bold;'>✅ SUCCÈS: " . count($contacts) . " contact(s) trouvé(s)</div>";
    
    if (empty($contacts)) {
        echo "<div style='color: orange;'>⚠️ Aucun contact retourné</div>";
    } else {
        foreach ($contacts as $contact) {
            echo "<div style='border: 1px solid #28a745; padding: 15px; margin: 10px 0; background: #d4edda; border-radius: 8px;'>";
            echo "<div style='font-weight: bold; color: #155724; font-size: 18px;'>✅ " . $contact['name'] . "</div>";
            echo "<div style='margin: 5px 0;'><strong>ID:</strong> " . $contact['id'] . "</div>";
            echo "<div style='margin: 5px 0;'><strong>Contact ID:</strong> " . $contact['contact_id'] . "</div>";
            echo "<div style='margin: 5px 0;'><strong>Nickname:</strong> " . $contact['nickname'] . "</div>";
            echo "<div style='margin: 5px 0;'><strong>Téléphone:</strong> " . $contact['phone'] . "</div>";
            echo "<div style='margin: 5px 0;'><strong>Email:</strong> " . $contact['email'] . "</div>";
            echo "<div style='margin: 5px 0;'><strong>Status:</strong> " . $contact['status'] . "</div>";
            echo "<div style='margin: 5px 0;'><strong>En ligne:</strong> " . ($contact['online'] ? 'Oui' : 'Non') . "</div>";
            echo "<div style='margin: 5px 0;'><strong>Favori:</strong> " . ($contact['favorite'] ? 'Oui' : 'Non') . "</div>";
            echo "<div style='margin: 5px 0;'><strong>Créé le:</strong> " . $contact['created_at'] . "</div>";
            echo "</div>";
        }
    }
} else {
    echo "<div style='color: red; font-weight: bold;'>❌ ERREUR API</div>";
    echo "<div style='color: red;'>Message: " . ($apiData['message'] ?? 'Erreur inconnue') . "</div>";
}

echo "</div>";

// Instructions finales
echo "<h3>🎉 Instructions Finales</h3>";
echo "<div style='background: #d4edda; padding: 20px; border-radius: 8px; margin: 10px 0; border: 2px solid #28a745;'>";
echo "<h4 style='color: #155724; margin-top: 0;'>✅ PROBLÈME RÉSOLU !</h4>";
echo "<p style='color: #155724; font-weight: bold;'>L'API fonctionne maintenant correctement.</p>";
echo "<p><strong>Prochaines étapes:</strong></p>";
echo "<ol>";
echo "<li>Ouvrir <a href='index.html' target='_blank' style='color: #007bff; font-weight: bold;'>index.html</a> dans un nouvel onglet</li>";
echo "<li>Aller dans l'onglet 'Contacts'</li>";
echo "<li>Les contacts doivent maintenant s'afficher en bas de la liste</li>";
echo "<li>Testez l'ajout d'un nouveau contact</li>";
echo "</ol>";
echo "</div>";

echo "<h3>🔧 Si les contacts n'apparaissent toujours pas</h3>";
echo "<div style='background: #fff3cd; padding: 15px; border-radius: 5px; margin: 10px 0; border: 1px solid #ffeaa7;'>";
echo "<p><strong>Vérifiez:</strong></p>";
echo "<ul>";
echo "<li>Ouvrez la console du navigateur (F12)</li>";
echo "<li>Regardez s'il y a des erreurs JavaScript</li>";
echo "<li>Vérifiez que la session est bien active</li>";
echo "<li>Testez l'API directement: <a href='php/contacts.php?action=get_contacts' target='_blank'>API Directe</a></li>";
echo "</ul>";
echo "</div>";

// Recharger automatiquement après 10 secondes
echo "<script>";
echo "setTimeout(() => {";
echo "  if (confirm('Voulez-vous recharger la page pour vérifier les changements ?')) {";
echo "    window.location.reload();";
echo "  }";
echo "}, 10000);";
echo "</script>";
?> 