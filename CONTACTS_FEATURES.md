# Fonctionnalités de l'Onglet Contact

## Vue d'ensemble

L'onglet contact a été entièrement refait pour permettre une gestion complète des contacts avec les fonctionnalités suivantes :

## Fonctionnalités principales

### 1. Ajout de contacts
- **Bouton "Ajouter un contact"** : Affiche un formulaire de recherche
- **Recherche par nom OU numéro de téléphone** : L'utilisateur peut entrer soit le nom, soit le numéro de téléphone
- **Vérification d'existence** : Le système vérifie que l'utilisateur existe dans `users.xml` avant d'autoriser l'ajout
- **Interface intuitive** : Formulaire avec deux champs séparés par "ou" pour clarifier l'option

### 2. Gestion des contacts existants
Chaque contact affiché dans la liste dispose de trois boutons d'action :

#### Bouton Modifier (✏️)
- Permet de modifier uniquement le **surnom** du contact
- Ouvre un modal avec le champ de modification
- Sauvegarde automatique dans `contacts.xml`

#### Bouton Favori (⭐)
- Ajoute/retire le contact des favoris
- Changement visuel immédiat (étoile pleine/vide)
- Sauvegarde automatique dans `contacts.xml`

#### Bouton Supprimer (🗑️)
- Supprime définitivement le contact de la liste
- Demande de confirmation avant suppression
- Suppression de l'entrée dans `contacts.xml`

### 3. Affichage des informations
Chaque contact affiche :
- **Avatar** avec initiale du nom/surnom
- **Nom ou surnom** (priorité au surnom s'il existe)
- **Statut en ligne/hors ligne** avec indicateur visuel
- **Numéro de téléphone** (si disponible)
- **Email** (si disponible)
- **Surnom** (si différent du nom)

## Structure technique

### Fichiers modifiés

#### `index.html`
- Nouveau formulaire de recherche dans l'onglet contacts
- Interface améliorée avec boutons d'action

#### `js/app.js`
- Nouvelles fonctions :
  - `searchContactUser()` : Recherche d'utilisateurs
  - `addContactFromSearch()` : Ajout depuis la recherche
  - Amélioration des fonctions existantes pour la gestion des IDs

#### `css/styles.css`
- Nouveaux styles pour les boutons d'action
- Amélioration de l'apparence des contacts
- Style pour le bouton de succès

#### `php/contacts.php`
- Fonction `check_user` : Vérifie l'existence d'un utilisateur par nom ET numéro
- Toutes les fonctions CRUD existantes conservées

### API Endpoints

#### POST `/php/contacts.php`
- `action: 'check_user'` : Vérifie l'existence d'un utilisateur
  - Paramètres : `name`, `phone`
  - Retour : `{success: true, user_id: "..."}` ou `{success: false, message: "..."}`

- `action: 'add_contact'` : Ajoute un contact
  - Paramètres : `user_id`
  - Retour : `{success: true, contact_id: "..."}`

- `action: 'update_contact'` : Modifie le surnom
  - Paramètres : `contact_id`, `nickname`
  - Retour : `{success: true}`

- `action: 'toggle_favorite'` : Bascule le statut favori
  - Paramètres : `contact_id`
  - Retour : `{success: true}`

- `action: 'delete_contact'` : Supprime un contact
  - Paramètres : `contact_id`
  - Retour : `{success: true}`

#### GET `/php/contacts.php?action=get_contacts`
- Retourne la liste complète des contacts avec toutes les informations

## Utilisation

### Pour ajouter un contact :
1. Cliquer sur "Ajouter un contact"
2. Entrer le nom OU le numéro de téléphone de l'utilisateur
3. Cliquer sur "Rechercher"
4. Si l'utilisateur est trouvé, cliquer sur "Ajouter aux contacts"

### Pour modifier un contact :
1. Cliquer sur le bouton ✏️ à côté du contact
2. Modifier le surnom dans le modal
3. Cliquer sur "Sauvegarder"

### Pour gérer les favoris :
1. Cliquer sur le bouton ⭐ à côté du contact
2. Le statut favori bascule automatiquement

### Pour supprimer un contact :
1. Cliquer sur le bouton 🗑️ à côté du contact
2. Confirmer la suppression

## Tests

Un fichier `test_contacts.html` est fourni pour tester toutes les fonctionnalités de l'API contacts.

## Notes importantes

- L'ajout de contacts nécessite que l'utilisateur soit connecté
- La recherche vérifie que l'utilisateur existe dans `users.xml`
- Les modifications sont sauvegardées automatiquement dans `contacts.xml`
- L'interface est responsive et compatible mobile 