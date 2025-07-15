<?php
session_start();
header('Content-Type: text/html; charset=utf-8');

echo "<!DOCTYPE html>
<html lang='fr'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Vérification Installation - Onglet Contact</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 0 auto; padding: 20px; }
        .check { margin: 10px 0; padding: 10px; border-radius: 5px; }
        .success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .warning { background: #fff3cd; color: #856404; border: 1px solid #ffeaa7; }
        .info { background: #d1ecf1; color: #0c5460; border: 1px solid #bee5eb; }
        .file-check { display: flex; justify-content: space-between; align-items: center; }
        .status { font-weight: bold; }
        .status.ok { color: #28a745; }
        .status.error { color: #dc3545; }
        .status.warning { color: #ffc107; }
    </style>
</head>
<body>
    <h1>🔍 Vérification de l'Installation - Onglet Contact</h1>";

// Vérifier les fichiers
$files_to_check = [
    'index.html' => 'Interface principale avec le formulaire de contact',
    'js/app.js' => 'JavaScript avec les nouvelles fonctions',
    'css/styles.css' => 'Styles CSS pour les boutons d\'action',
    'php/contacts.php' => 'API PHP pour la gestion des contacts',
    'xml/users.xml' => 'Fichier des utilisateurs',
    'xml/contacts.xml' => 'Fichier des contacts',
    'test_onglet_contact.html' => 'Fichier de test complet',
    'test_contacts.html' => 'Fichier de test simple',
    'GUIDE_DEPANNAGE.md' => 'Guide de dépannage'
];

echo "<h2>📁 Vérification des fichiers</h2>";

foreach ($files_to_check as $file => $description) {
    $exists = file_exists($file);
    $status = $exists ? 'ok' : 'error';
    $status_text = $exists ? '✅ Présent' : '❌ Manquant';
    
    echo "<div class='check {$status}'>
        <div class='file-check'>
            <div>
                <strong>{$file}</strong><br>
                <small>{$description}</small>
            </div>
            <div class='status {$status}'>{$status_text}</div>
        </div>
    </div>";
}

// Vérifier l'extension XML
echo "<h2>🔧 Vérification de l'environnement PHP</h2>";

$xml_loaded = extension_loaded('xml');
$xml_status = $xml_loaded ? 'ok' : 'error';
$xml_text = $xml_loaded ? '✅ Extension XML chargée' : '❌ Extension XML manquante';

echo "<div class='check " . ($xml_loaded ? 'success' : 'error') . "'>
    <div class='file-check'>
        <div>
            <strong>Extension XML</strong><br>
            <small>Nécessaire pour lire les fichiers XML</small>
        </div>
        <div class='status {$xml_status}'>{$xml_text}</div>
    </div>
</div>";

// Vérifier la session
$session_active = isset($_SESSION['user_id']);
$session_status = $session_active ? 'ok' : 'warning';
$session_text = $session_active ? '✅ Session active (ID: ' . $_SESSION['user_id'] . ')' : '⚠️ Aucune session active';

echo "<div class='check " . ($session_active ? 'success' : 'warning') . "'>
    <div class='file-check'>
        <div>
            <strong>Session utilisateur</strong><br>
            <small>Nécessaire pour tester les fonctionnalités</small>
        </div>
        <div class='status {$session_status}'>{$session_text}</div>
    </div>
</div>";

// Tester l'API contacts
echo "<h2>🔌 Test de l'API Contacts</h2>";

try {
    $xmlFile = __DIR__ . '/xml/users.xml';
    if (file_exists($xmlFile)) {
        $xml = simplexml_load_file($xmlFile);
        if ($xml !== false) {
            $userCount = count($xml->user);
            echo "<div class='check success'>
                <div class='file-check'>
                    <div>
                        <strong>Fichier users.xml</strong><br>
                        <small>Nombre d'utilisateurs: {$userCount}</small>
                    </div>
                    <div class='status ok'>✅ Valide</div>
                </div>
            </div>";
        } else {
            echo "<div class='check error'>
                <div class='file-check'>
                    <div>
                        <strong>Fichier users.xml</strong><br>
                        <small>Erreur lors du chargement</small>
                    </div>
                    <div class='status error'>❌ Erreur</div>
                </div>
            </div>";
        }
    } else {
        echo "<div class='check error'>
            <div class='file-check'>
                <div>
                    <strong>Fichier users.xml</strong><br>
                    <small>Fichier non trouvé</small>
                </div>
                <div class='status error'>❌ Manquant</div>
            </div>
        </div>";
    }
} catch (Exception $e) {
    echo "<div class='check error'>
        <div class='file-check'>
            <div>
                <strong>Test XML</strong><br>
                <small>Exception: " . $e->getMessage() . "</small>
            </div>
            <div class='status error'>❌ Erreur</div>
        </div>
    </div>";
}

// Vérifier le contenu des fichiers clés
echo "<h2>📝 Vérification du contenu</h2>";

// Vérifier index.html
if (file_exists('index.html')) {
    $content = file_get_contents('index.html');
    $has_contact_form = strpos($content, 'showAddContactFormBtn') !== false;
    $has_search_form = strpos($content, 'searchContactName') !== false;
    
    echo "<div class='check " . ($has_contact_form ? 'success' : 'error') . "'>
        <div class='file-check'>
            <div>
                <strong>index.html - Formulaire de contact</strong><br>
                <small>Bouton d'ajout de contact</small>
            </div>
            <div class='status " . ($has_contact_form ? 'ok' : 'error') . "'>" . ($has_contact_form ? '✅ Présent' : '❌ Manquant') . "</div>
        </div>
    </div>";
    
    echo "<div class='check " . ($has_search_form ? 'success' : 'error') . "'>
        <div class='file-check'>
            <div>
                <strong>index.html - Formulaire de recherche</strong><br>
                <small>Champs de recherche nom/téléphone</small>
            </div>
            <div class='status " . ($has_search_form ? 'ok' : 'error') . "'>" . ($has_search_form ? '✅ Présent' : '❌ Manquant') . "</div>
        </div>
    </div>";
}

// Vérifier app.js
if (file_exists('js/app.js')) {
    $content = file_get_contents('js/app.js');
    $has_search_function = strpos($content, 'searchContactUser') !== false;
    $has_add_function = strpos($content, 'addContactFromSearch') !== false;
    
    echo "<div class='check " . ($has_search_function ? 'success' : 'error') . "'>
        <div class='file-check'>
            <div>
                <strong>app.js - Fonction de recherche</strong><br>
                <small>Fonction searchContactUser</small>
            </div>
            <div class='status " . ($has_search_function ? 'ok' : 'error') . "'>" . ($has_search_function ? '✅ Présent' : '❌ Manquant') . "</div>
        </div>
    </div>";
    
    echo "<div class='check " . ($has_add_function ? 'success' : 'error') . "'>
        <div class='file-check'>
            <div>
                <strong>app.js - Fonction d'ajout</strong><br>
                <small>Fonction addContactFromSearch</small>
            </div>
            <div class='status " . ($has_add_function ? 'ok' : 'error') . "'>" . ($has_add_function ? '✅ Présent' : '❌ Manquant') . "</div>
        </div>
    </div>";
}

// Instructions de test
echo "<h2>🧪 Instructions de test</h2>";

echo "<div class='check info'>
    <h3>Pour tester l'onglet contact :</h3>
    <ol>
        <li><strong>Démarrer le serveur :</strong> <code>php -S localhost:8000</code></li>
        <li><strong>Ouvrir :</strong> <a href='http://localhost:8000/test_onglet_contact.html' target='_blank'>http://localhost:8000/test_onglet_contact.html</a></li>
        <li><strong>Tester la recherche :</strong> Entrez 'Narou Sagne' et cliquez sur Rechercher</li>
        <li><strong>Tester l'ajout :</strong> Entrez '2' et cliquez sur Ajouter</li>
        <li><strong>Vérifier l'interface :</strong> <a href='http://localhost:8000/index.html' target='_blank'>http://localhost:8000/index.html</a></li>
    </ol>
</div>";

// Résumé
$all_files_exist = true;
foreach ($files_to_check as $file => $description) {
    if (!file_exists($file)) {
        $all_files_exist = false;
        break;
    }
}

$all_ok = $all_files_exist && $xml_loaded;

echo "<h2>📊 Résumé</h2>";

if ($all_ok) {
    echo "<div class='check success'>
        <h3>🎉 Installation réussie !</h3>
        <p>Tous les fichiers sont présents et l'environnement est correctement configuré.</p>
        <p>Vous pouvez maintenant tester l'onglet contact.</p>
    </div>";
} else {
    echo "<div class='check error'>
        <h3>⚠️ Problèmes détectés</h3>
        <p>Certains fichiers sont manquants ou l'environnement n'est pas correctement configuré.</p>
        <p>Consultez le <a href='GUIDE_DEPANNAGE.md'>Guide de dépannage</a> pour résoudre les problèmes.</p>
    </div>";
}

echo "</body></html>";
?> 