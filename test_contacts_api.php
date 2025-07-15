<?php
// Test simple de l'API contacts
session_start();

// Simuler une session utilisateur
$_SESSION['user_id'] = '1';
$_SESSION['user_name'] = 'Narou Sagne';

echo "<h2>Test de l'API Contacts</h2>";

// Test direct de l'API
echo "<h3>Résultat de l'API :</h3>";

// Capturer la sortie de l'API
ob_start();
include 'php/contacts.php?action=get_contacts';
$output = ob_get_clean();

echo "<pre>" . htmlspecialchars($output) . "</pre>";

// Test de décodage JSON
$data = json_decode($output, true);
if ($data === null) {
    echo "<p style='color: red;'>❌ Erreur de décodage JSON</p>";
    echo "<p>Erreur JSON : " . json_last_error_msg() . "</p>";
} else {
    echo "<p style='color: green;'>✅ JSON valide</p>";
    echo "<pre>" . json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "</pre>";
}
?> 