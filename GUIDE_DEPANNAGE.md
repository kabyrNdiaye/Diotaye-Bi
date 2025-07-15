# 🔧 Guide de Dépannage - Onglet Contact

## Problème : "Aucune modification n'est visible"

Si vous ne voyez pas les modifications de l'onglet contact, suivez ces étapes :

## 1. Vérifier que le serveur fonctionne

```bash
# Démarrer le serveur PHP
cd /c/xampp/htdocs/chat_platform
php -S localhost:8000
```

## 2. Tester l'API directement

Ouvrez votre navigateur et allez sur :
- `http://localhost:8000/test_onglet_contact.html` - Test complet de l'API
- `http://localhost:8000/test_contacts.html` - Test simple

## 3. Vérifier la console du navigateur

1. Ouvrez `http://localhost:8000/index.html`
2. Appuyez sur F12 pour ouvrir les outils de développement
3. Allez dans l'onglet "Console"
4. Cliquez sur l'onglet "Contacts"
5. Vérifiez s'il y a des erreurs

## 4. Vérifier les fichiers modifiés

### Fichiers qui doivent être présents :
- ✅ `index.html` - Modifié avec le nouveau formulaire
- ✅ `js/app.js` - Modifié avec les nouvelles fonctions
- ✅ `css/styles.css` - Modifié avec les nouveaux styles
- ✅ `php/contacts.php` - API existante (non modifiée)

### Vérifier le contenu de `index.html` :

Recherchez cette section dans `index.html` :

```html
<!-- Contacts List (gestion) -->
<div class="contact-list" id="contactsList" style="display: none;">
    <div class="contacts-header">
        <button class="add-contact-btn" id="showAddContactFormBtn" type="button">
            <i class="fas fa-plus"></i> Ajouter un contact
        </button>
    </div>
    
    <!-- Formulaire d'ajout de contact -->
    <div id="addContactForm" style="display:none; background: #F0F2F5; padding: 15px; border-radius: 8px; margin: 10px 0;">
        <h4 style="margin: 0 0 10px 0; color: #128C7E;">Rechercher un utilisateur</h4>
        <div style="display: flex; gap: 8px; margin-bottom: 10px;">
            <input type="text" id="searchContactName" placeholder="Nom de l'utilisateur" style="flex: 1;">
            <span style="align-self: center; color: #666;">ou</span>
            <input type="text" id="searchContactPhone" placeholder="Numéro de téléphone" style="flex: 1;">
        </div>
        <div style="display: flex; gap: 8px;">
            <button type="button" class="btn btn-primary" id="searchContactBtn">
                <i class="fas fa-search"></i> Rechercher
            </button>
            <button type="button" class="btn btn-secondary" id="cancelAddContactBtn">
                <i class="fas fa-times"></i> Annuler
            </button>
        </div>
        <div id="searchContactResults" style="margin-top: 10px;"></div>
    </div>
    
    <div class="loading">
        <div class="spinner"></div>
        Chargement des contacts...
    </div>
</div>
```

## 5. Test étape par étape

### Étape 1 : Tester l'API
1. Ouvrez `http://localhost:8000/test_onglet_contact.html`
2. Cliquez sur "📋 Récupérer les contacts"
3. Vous devriez voir la liste des utilisateurs

### Étape 2 : Tester la recherche
1. Dans le même fichier, entrez "Narou Sagne" dans le champ nom
2. Cliquez sur "🔍 Rechercher"
3. Vous devriez voir "Utilisateur trouvé !"

### Étape 3 : Tester l'ajout
1. Entrez "2" dans le champ ID utilisateur
2. Cliquez sur "➕ Ajouter"
3. Vous devriez voir "Contact ajouté avec succès !"

## 6. Problèmes courants

### Problème : "Erreur de session"
**Solution :** Connectez-vous d'abord sur `http://localhost:8000/login.html`

### Problème : "Fichier users.xml non trouvé"
**Solution :** Vérifiez que le fichier `xml/users.xml` existe

### Problème : "Extension XML non chargée"
**Solution :** Activez l'extension XML dans votre configuration PHP

### Problème : Boutons non cliquables
**Solution :** Vérifiez que les événements JavaScript sont bien attachés

## 7. Vérification finale

Si tout fonctionne dans les tests mais pas dans l'interface principale :

1. **Videz le cache du navigateur** (Ctrl+F5)
2. **Vérifiez que vous êtes connecté** dans l'application principale
3. **Cliquez sur l'onglet "Contacts"** dans la navigation
4. **Vérifiez que le bouton "Ajouter un contact" apparaît**

## 8. Debug avancé

Ajoutez ces lignes dans la console du navigateur pour debugger :

```javascript
// Vérifier si les éléments existent
console.log('Bouton ajouter:', document.getElementById('showAddContactFormBtn'));
console.log('Formulaire:', document.getElementById('addContactForm'));
console.log('Liste contacts:', document.getElementById('contactsList'));

// Vérifier les contacts chargés
console.log('Contacts:', contacts);

// Tester la fonction de recherche
searchContactUser();
```

## 9. Contact

Si le problème persiste, vérifiez :
- Les logs d'erreur PHP dans la console
- Les erreurs JavaScript dans la console du navigateur
- Les permissions des fichiers XML
- La configuration PHP (extensions XML activées) 