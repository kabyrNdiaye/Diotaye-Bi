# 📱 Nouvelles Fonctionnalités - Recherche par Téléphone

## 🎯 Changements apportés

### ✅ Avant (ancien système)
- Recherche par **nom ET numéro de téléphone**
- Le surnom était automatiquement le nom de l'utilisateur dans `users.xml`
- Interface avec deux champs (nom + téléphone)

### ✅ Maintenant (nouveau système)
- Recherche **uniquement par numéro de téléphone**
- Possibilité d'ajouter un **surnom personnalisé** différent du nom dans `users.xml`
- Interface simplifiée avec un seul champ (téléphone)

## 🔧 Fonctionnement détaillé

### 1. Recherche d'utilisateur
- **Entrée** : Numéro de téléphone uniquement
- **Vérification** : Le numéro doit exister dans `users.xml`
- **Retour** : Informations complètes de l'utilisateur (nom, téléphone, email)

### 2. Ajout de contact
- **Surnom personnalisé** : Optionnel, peut être différent du nom dans `users.xml`
- **Si surnom fourni** : Utilise le surnom personnalisé
- **Si pas de surnom** : Utilise le nom de l'utilisateur dans `users.xml`

## 📝 Exemples d'utilisation

### Exemple 1 : Ajout sans surnom personnalisé
1. Entrez le numéro : `774123456`
2. Recherche trouve : "Narou Sagne"
3. Laissez le champ surnom vide
4. Résultat : Contact ajouté avec le nom "Narou Sagne"

### Exemple 2 : Ajout avec surnom personnalisé
1. Entrez le numéro : `774123456`
2. Recherche trouve : "Narou Sagne"
3. Entrez le surnom : "Mon Ami"
4. Résultat : Contact ajouté avec le surnom "Mon Ami"

## 🔄 API Modifiée

### POST `/php/contacts.php` - Action `check_user`

**Avant :**
```json
{
    "action": "check_user",
    "name": "Narou Sagne",
    "phone": "774123456"
}
```

**Maintenant :**
```json
{
    "action": "check_user",
    "phone": "774123456"
}
```

**Réponse :**
```json
{
    "success": true,
    "user_id": "1",
    "user_name": "Narou Sagne",
    "user_phone": "774123456",
    "user_email": "narou.sagne@example.com"
}
```

### POST `/php/contacts.php` - Action `add_contact`

**Avant :**
```json
{
    "action": "add_contact",
    "user_id": "1"
}
```

**Maintenant :**
```json
{
    "action": "add_contact",
    "user_id": "1",
    "nickname": "Mon Ami"  // Optionnel
}
```

## 🧪 Tests

### Fichier de test principal
- `test_recherche_telephone.html` - Test complet de la nouvelle fonctionnalité

### Tests disponibles
1. **Recherche par téléphone** : Entrez un numéro et vérifiez qu'un utilisateur est trouvé
2. **Ajout avec surnom** : Testez l'ajout avec et sans surnom personnalisé
3. **Liste des utilisateurs** : Voir tous les utilisateurs disponibles

## 🎨 Interface utilisateur

### Formulaire de recherche
- **Un seul champ** : Numéro de téléphone
- **Bouton recherche** : Lance la recherche
- **Résultats** : Affiche les informations de l'utilisateur trouvé

### Formulaire d'ajout
- **Informations utilisateur** : Nom, téléphone, email (en lecture seule)
- **Champ surnom** : Saisie libre pour un surnom personnalisé
- **Boutons** : Ajouter / Annuler

## ✅ Avantages du nouveau système

1. **Plus simple** : Un seul champ à remplir pour la recherche
2. **Plus flexible** : Surnom personnalisé possible
3. **Plus précis** : Recherche uniquement par numéro (plus fiable)
4. **Plus intuitif** : Interface plus claire

## 🔍 Cas d'usage

### Cas 1 : Ajouter un ami
- Recherche par son numéro
- Ajoute avec son vrai nom ou un surnom

### Cas 2 : Ajouter un contact professionnel
- Recherche par numéro de l'entreprise
- Ajoute avec un surnom comme "Bureau" ou "Travail"

### Cas 3 : Ajouter un membre de famille
- Recherche par numéro
- Ajoute avec un surnom comme "Maman", "Papa", "Frère"

## 🚀 Utilisation

1. **Cliquez sur "Ajouter un contact"**
2. **Entrez le numéro de téléphone**
3. **Cliquez sur "Rechercher"**
4. **Si trouvé** : Entrez un surnom personnalisé (optionnel)
5. **Cliquez sur "Ajouter aux contacts"**

Le contact sera ajouté avec le surnom personnalisé ou le nom original selon votre choix ! 