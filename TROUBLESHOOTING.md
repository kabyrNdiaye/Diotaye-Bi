# Guide de dépannage - Chat Platform

## Problème : "Failed to fetch" - Impossible de se connecter

### Étape 1 : Vérifier le serveur web

1. **Assurez-vous que XAMPP est démarré**
   - Ouvrez le panneau de contrôle XAMPP
   - Démarrez Apache et MySQL
   - Vérifiez que les services sont en vert

2. **Testez l'accès au serveur**
   - Ouvrez votre navigateur
   - Allez à `http://localhost/chat_platform/test_server.php`
   - Vous devriez voir un message JSON de succès

### Étape 2 : Vérifier les fichiers

1. **Exécutez le script de test**
   - Allez à `http://localhost/chat_platform/test_connection.php`
   - Vérifiez que tous les fichiers XML existent et sont lisibles

2. **Générez un mot de passe de test**
   - Allez à `http://localhost/chat_platform/generate_password.php`
   - Cela mettra à jour le mot de passe de l'utilisateur de test

### Étape 3 : Testez la connexion

1. **Utilisez la page de test client**
   - Allez à `http://localhost/chat_platform/test_client.html`
   - Cliquez sur "Tester la connexion"
   - Utilisez ces identifiants :
     - Téléphone : `774123456`
     - Mot de passe : `password123`

### Étape 4 : Vérifier les erreurs

1. **Console du navigateur**
   - Appuyez sur F12 pour ouvrir les outils de développement
   - Allez dans l'onglet "Console"
   - Regardez s'il y a des erreurs JavaScript

2. **Logs PHP**
   - Vérifiez les logs d'erreur dans `C:\xampp\apache\logs\error.log`
   - Cherchez les erreurs liées à votre projet

### Étape 5 : Solutions courantes

#### Problème : CORS (Cross-Origin Resource Sharing)
**Solution :** Les headers CORS ont été ajoutés aux fichiers PHP. Si le problème persiste :
- Assurez-vous d'accéder au site via `http://localhost` et non `file://`
- Vérifiez que tous les fichiers PHP ont les headers CORS

#### Problème : Sessions PHP
**Solution :** 
- Vérifiez que le dossier `C:\xampp\tmp` existe et est accessible
- Assurez-vous que les sessions PHP fonctionnent

#### Problème : Permissions de fichiers
**Solution :**
- Vérifiez que le dossier `xml` est accessible en lecture
- Vérifiez que le dossier `uploads` est accessible en écriture

### Étape 6 : Test complet

1. **Testez chaque composant individuellement :**
   ```bash
   # Test du serveur
   http://localhost/chat_platform/test_server.php
   
   # Test de la session
   http://localhost/chat_platform/php/check_session.php
   
   # Test de connexion
   http://localhost/chat_platform/test_client.html
   ```

2. **Si tout fonctionne, testez l'application principale :**
   - Allez à `http://localhost/chat_platform/login.html`
   - Connectez-vous avec les identifiants de test

### Identifiants de test

- **Téléphone :** `774123456`
- **Mot de passe :** `password123`
- **Nom :** Narou Sagne

### Si le problème persiste

1. **Vérifiez la version de PHP :**
   - Allez à `http://localhost/chat_platform/test_server.php`
   - Vérifiez que la version est 7.4 ou supérieure

2. **Vérifiez les extensions PHP :**
   - L'extension XML doit être activée
   - L'extension session doit être activée

3. **Redémarrez XAMPP :**
   - Arrêtez Apache et MySQL
   - Redémarrez XAMPP
   - Relancez les services

### Contact

Si vous avez toujours des problèmes après avoir suivi ce guide, vérifiez :
1. Les logs d'erreur Apache
2. La console du navigateur
3. Les permissions des fichiers
4. La configuration de XAMPP 