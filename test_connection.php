<?php
// Script de test pour diagnostiquer les problèmes de connexion
session_start();

echo "<h2>Test de connexion - Chat Platform</h2>";

// Test 1: Vérifier les extensions PHP
echo "<h3>1. Extensions PHP</h3>";
echo "XML extension: " . (extension_loaded('xml') ? '✅ Chargée' : '❌ Non chargée') . "<br>";
echo "Session extension: " . (extension_loaded('session') ? '✅ Chargée' : '❌ Non chargée') . "<br>";

// Test 2: Vérifier les fichiers XML
echo "<h3>2. Fichiers XML</h3>";
$xmlFiles = [
    'xml/users.xml',
    'xml/messages.xml',
    'xml/contacts.xml',
    'xml/groups.xml',
    'xml/files.xml'
];

foreach ($xmlFiles as $file) {
    if (file_exists($file)) {
        echo "✅ $file existe<br>";
        if (is_readable($file)) {
            echo "✅ $file est lisible<br>";
        } else {
            echo "❌ $file n'est pas lisible<br>";
        }
    } else {
        echo "❌ $file n'existe pas<br>";
    }
}

// Test 3: Vérifier la session
echo "<h3>3. Session actuelle</h3>";
if (isset($_SESSION['user_id'])) {
    echo "✅ Session active pour l'utilisateur ID: " . $_SESSION['user_id'] . "<br>";
    echo "Nom: " . ($_SESSION['user_name'] ?? 'Non défini') . "<br>";
    echo "Téléphone: " . ($_SESSION['user_phone'] ?? 'Non défini') . "<br>";
} else {
    echo "❌ Aucune session active<br>";
}

// Test 4: Test de connexion directe
echo "<h3>4. Test de connexion</h3>";
if (isset($_POST['test_login'])) {
    $phone = $_POST['phone'];
    $password = $_POST['password'];
    
    $xmlFile = 'xml/users.xml';
    if (file_exists($xmlFile)) {
        $xml = simplexml_load_file($xmlFile);
        $userFound = false;
        
        foreach ($xml->user as $user) {
            if ((string)$user->phone === $phone) {
                $userFound = true;
                if (password_verify($password, (string)$user->password)) {
                    echo "✅ Connexion réussie pour " . $user->name . "<br>";
                    
                    // Démarrer la session
                    session_start();
                    $_SESSION['user_id'] = (string)$user['id'];
                    $_SESSION['user_name'] = (string)$user->name;
                    $_SESSION['user_phone'] = (string)$user->phone;
                    
                    echo "✅ Session créée<br>";
                } else {
                    echo "❌ Mot de passe incorrect<br>";
                }
                break;
            }
        }
        
        if (!$userFound) {
            echo "❌ Utilisateur non trouvé<br>";
        }
    } else {
        echo "❌ Fichier users.xml non trouvé<br>";
    }
}

// Formulaire de test
echo "<h3>5. Test de connexion manuel</h3>";
echo "<form method='post'>";
echo "<label>Téléphone: <input type='text' name='phone' placeholder='774123456'></label><br>";
echo "<label>Mot de passe: <input type='password' name='password' placeholder='password'></label><br>";
echo "<input type='submit' name='test_login' value='Tester la connexion'>";
echo "</form>";

// Test 6: Informations de débogage
echo "<h3>6. Informations de débogage</h3>";
echo "PHP Version: " . phpversion() . "<br>";
echo "Document Root: " . $_SERVER['DOCUMENT_ROOT'] . "<br>";
echo "Script Path: " . __FILE__ . "<br>";
echo "Current Directory: " . getcwd() . "<br>";

// Test 7: Vérifier les permissions
echo "<h3>7. Permissions des dossiers</h3>";
$dirs = ['xml', 'uploads', 'uploads/avatars', 'uploads/files'];
foreach ($dirs as $dir) {
    if (is_dir($dir)) {
        echo "✅ $dir existe et est un dossier<br>";
        if (is_writable($dir)) {
            echo "✅ $dir est accessible en écriture<br>";
        } else {
            echo "❌ $dir n'est pas accessible en écriture<br>";
        }
    } else {
        echo "❌ $dir n'existe pas<br>";
    }
}
?> 