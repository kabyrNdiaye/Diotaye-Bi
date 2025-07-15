// Variables globales
let currentUser = null;
let currentChat = null;
let contacts = [];
let chats = [];
let groups = [];

// Initialisation de l'application
document.addEventListener('DOMContentLoaded', function() {
    checkSession();
    initializeEventListeners();
});

// Vérifier la session utilisateur
function checkSession() {
    fetch('php/check_session.php')
        .then(response => response.json())
        .then(data => {
            if (data.logged_in) {
                currentUser = data.user;
                loadUserInfo();
                loadChats();
                loadContacts();
                loadGroups();
            } else {
                window.location.href = 'login.html';
            }
        })
        .catch(error => {
            console.error('Erreur de session:', error);
            window.location.href = 'login.html';
        });
}

// Charger les informations de l'utilisateur
function loadUserInfo() {
    if (currentUser) {
        document.getElementById('currentUserName').textContent = currentUser.name;
        document.getElementById('currentUserAvatar').textContent = currentUser.name.charAt(0).toUpperCase();
    }
}

// Initialiser les écouteurs d'événements
function initializeEventListeners() {
    // Navigation tabs
    document.querySelectorAll('.nav-tab').forEach(tab => {
        tab.addEventListener('click', function() {
            switchTab(this.dataset.tab);
        });
    });

    // Recherche
    document.getElementById('searchBar').addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase();
        filterItems(searchTerm);
    });

    // Envoi de messages
    document.getElementById('sendBtn').addEventListener('click', sendMessage);
    document.getElementById('messageInput').addEventListener('keypress', function(e) {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            sendMessage();
        }
    });

    // Input message
    document.getElementById('messageInput').addEventListener('input', function() {
        const sendBtn = document.getElementById('sendBtn');
        sendBtn.disabled = this.value.trim() === '';
    });

    // Upload de fichiers
    document.getElementById('uploadBtn').addEventListener('click', function() {
        document.getElementById('fileInput').click();
    });

    document.getElementById('fileInput').addEventListener('change', handleFileUpload);

    // Modals
    document.querySelectorAll('.close-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const modal = this.closest('.modal');
            modal.style.display = 'none';
        });
    });

    // Fermer modals en cliquant à l'extérieur
    document.querySelectorAll('.modal').forEach(modal => {
        modal.addEventListener('click', function(e) {
            if (e.target === this) {
                this.style.display = 'none';
            }
        });
    });
}

// Changer d'onglet
function switchTab(tabName) {
    // Masquer toutes les listes
    document.getElementById('discussionsList').style.display = 'none';
    document.getElementById('contactsList').style.display = 'none';
    document.getElementById('groupsList').style.display = 'none';
    document.getElementById('filesList').style.display = 'none';
    // Désactiver tous les onglets
    document.querySelectorAll('.nav-tab').forEach(tab => tab.classList.remove('active'));
    // Afficher la bonne section
    if (tabName === 'discussions') {
        document.getElementById('discussionsList').style.display = '';
        document.querySelector('.nav-tab[data-tab="discussions"]').classList.add('active');
        displayDiscussions(contacts);
    } else if (tabName === 'contacts') {
        document.getElementById('contactsList').style.display = '';
        document.querySelector('.nav-tab[data-tab="contacts"]').classList.add('active');
        displayContacts(contacts);
        // Réinitialiser les événements du formulaire
        setTimeout(() => {
            initializeContactFormEvents();
        }, 100);
    } else if (tabName === 'groups') {
        document.getElementById('groupsList').style.display = '';
        document.querySelector('.nav-tab[data-tab="groups"]').classList.add('active');
        displayGroups(groups);
    } else if (tabName === 'files') {
        document.getElementById('filesList').style.display = '';
        document.querySelector('.nav-tab[data-tab="files"]').classList.add('active');
        displayFiles(files);
    }
}

// Filtrer les éléments
function filterItems(searchTerm) {
    const activeTab = document.querySelector('.nav-tab.active').dataset.tab;
    
    if (activeTab === 'chats') {
        filterChats(searchTerm);
    } else if (activeTab === 'contacts') {
        filterContacts(searchTerm);
    } else if (activeTab === 'groups') {
        filterGroups(searchTerm);
    } else if (activeTab === 'files') {
        filterFiles(searchTerm);
    }
}

// Charger les chats
function loadChats() {
    fetch('php/chats.php?action=get_chats')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                chats = data.chats;
                displayChats(chats);
            }
        })
        .catch(error => {
            console.error('Erreur chargement chats:', error);
            const chatsContainer = document.getElementById('discussionsList');
            if (chatsContainer) {
                chatsContainer.innerHTML = '<div class="error">Erreur de chargement</div>';
            }
        });
}

// Afficher les chats
function displayChats(chatsList) {
    const container = document.getElementById('discussionsList');
    container.innerHTML = '';

    if (chatsList.length === 0) {
        container.innerHTML = '<div class="no-items">Aucun chat récent</div>';
        return;
    }

    chatsList.forEach(chat => {
        const chatElement = createChatElement(chat);
        container.appendChild(chatElement);
    });
}

// Créer un élément chat
function createChatElement(chat) {
    // Chercher le contact à jour pour le nickname
    let upToDateContact = contacts.find(c => c.id === chat.id || c.contact_id === chat.id);
    let displayName = (upToDateContact && upToDateContact.nickname) ? upToDateContact.nickname : chat.name;
    const div = document.createElement('div');
    div.className = 'contact-item';
    div.onclick = () => openChat(chat);

    const lastMessage = chat.last_message || 'Aucun message';
    const time = chat.last_time ? formatTime(chat.last_time) : '';

    div.innerHTML = `
        <div class="contact-avatar">
            ${displayName.charAt(0).toUpperCase()}
            ${chat.online ? '<div class="online-indicator"></div>' : ''}
        </div>
        <div class="contact-info">
            <div class="contact-name">${displayName}</div>
            <div class="contact-status">${lastMessage}</div>
        </div>
        <div class="contact-time">${time}</div>
    `;

    return div;
}

// Filtrer les chats
function filterChats(searchTerm) {
    const filtered = chats.filter(chat => 
        chat.name.toLowerCase().includes(searchTerm)
    );
    displayChats(filtered);
}

// Charger les contacts
function loadContacts() {
    console.log('Chargement des contacts...');
    console.log('Utilisateur actuel:', currentUser);
    
    fetch('php/contacts.php?action=get_contacts')
        .then(response => response.json())
        .then(data => {
            console.log('Réponse API contacts:', data);
            if (data.success) {
                contacts = data.contacts;
                console.log('Contacts chargés:', contacts);
                displayContacts(contacts);
            } else {
                console.error('Erreur API contacts:', data.message);
                document.getElementById('contactsList').innerHTML = '<div class="error">Erreur: ' + data.message + '</div>';
            }
        })
        .catch(error => {
            console.error('Erreur chargement contacts:', error);
            document.getElementById('contactsList').innerHTML = '<div class="error">Erreur de chargement</div>';
        });
}

// Afficher les contacts
function displayContacts(contactsList) {
    console.log('Affichage des contacts:', contactsList);
    const container = document.getElementById('contactsList');
    
    // Vérifier si le header existe déjà, sinon le créer
    let header = container.querySelector('.contacts-header');
    if (!header) {
        header = document.createElement('div');
        header.className = 'contacts-header';
        header.innerHTML = `
            <button class="add-contact-btn" id="showAddContactFormBtn" type="button">
                <i class="fas fa-plus"></i> Ajouter un contact
            </button>
        `;
    }
    
    // Vérifier si le formulaire existe déjà, sinon le créer
    let addForm = container.querySelector('#addContactForm');
    if (!addForm) {
        addForm = document.createElement('div');
        addForm.id = 'addContactForm';
        addForm.style.display = 'none';
        addForm.style.background = '#F0F2F5';
        addForm.style.padding = '15px';
        addForm.style.borderRadius = '8px';
        addForm.style.margin = '10px 0';
        addForm.innerHTML = `
            <h4 style="margin: 0 0 10px 0; color: #128C7E;">Rechercher un utilisateur</h4>
            <div style="margin-bottom: 10px;">
                <input type="text" id="searchContactPhone" placeholder="Numéro de téléphone" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
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
        `;
    }
    
    // Créer le conteneur des contacts
    const contactsContainer = document.createElement('div');
    contactsContainer.className = 'contacts-container';
    contactsContainer.style.marginTop = '20px';
    contactsContainer.id = 'contactsListContainer';
    
    if (contactsList.length === 0) {
        contactsContainer.innerHTML = '<div class="no-items">📭 Aucun contact ajouté</div>';
    } else {
        // Ajouter un titre pour la liste des contacts
        const titleDiv = document.createElement('div');
        titleDiv.style.cssText = 'font-weight: bold; color: #128C7E; margin-bottom: 15px; font-size: 16px;';
        titleDiv.innerHTML = `📞 Vos contacts (${contactsList.length})`;
        contactsContainer.appendChild(titleDiv);
        
        // Trier les contacts : favoris en premier, puis par date de création (plus récents en bas)
        const sortedContacts = [...contactsList].sort((a, b) => {
            // D'abord par statut favori
            if (a.favorite && !b.favorite) return -1;
            if (!a.favorite && b.favorite) return 1;
            
            // Puis par date de création (plus récents en bas)
            const dateA = new Date(a.created_at || 0);
            const dateB = new Date(b.created_at || 0);
            return dateA - dateB;
        });
        
        console.log('Contacts triés:', sortedContacts);
        
        // Créer les éléments de contact avec animation progressive
        sortedContacts.forEach((contact, index) => {
            const contactElement = createContactElement(contact);
            
            // Animation d'apparition progressive
            contactElement.style.opacity = '0';
            contactElement.style.transform = 'translateY(20px)';
            contactElement.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
            
            contactsContainer.appendChild(contactElement);
            
            // Déclencher l'animation avec un délai
            setTimeout(() => {
                contactElement.style.opacity = '1';
                contactElement.style.transform = 'translateY(0)';
            }, index * 50); // 50ms de délai entre chaque contact
        });
    }
    
    // Reconstruire le contenu en s'assurant que le header et le formulaire sont présents
    container.innerHTML = '';
    container.appendChild(header);
    container.appendChild(addForm);
    container.appendChild(contactsContainer);
    
    // Réinitialiser les événements après reconstruction
    setTimeout(() => {
        initializeContactFormEvents();
    }, 10);
    
    // Faire défiler vers le bas pour voir les nouveaux contacts
    if (contactsList.length > 0) {
        setTimeout(() => {
            const lastContact = contactsContainer.querySelector('.contact-item:last-child');
            if (lastContact) {
                lastContact.scrollIntoView({ behavior: 'smooth', block: 'end' });
            }
        }, 200);
    }
}

// Créer un élément contact (pour la gestion, sans ouverture de chat)
function createContactElement(contact) {
    const div = document.createElement('div');
    div.className = 'contact-item';
    
    // Afficher le statut en ligne/hors ligne
    const statusText = contact.online ? 'En ligne' : 'Hors ligne';
    const statusClass = contact.online ? 'online' : 'offline';
    
    // Afficher le téléphone si disponible
    const phoneInfo = contact.phone ? `<div class="contact-phone">📱 ${contact.phone}</div>` : '';
    
    // Afficher l'email si disponible
    const emailInfo = contact.email ? `<div class="contact-email">📧 ${contact.email}</div>` : '';
    
    // Afficher le surnom si différent du nom
    const nicknameInfo = contact.nickname && contact.nickname !== contact.name ? 
        `<div class="contact-nickname">💬 ${contact.nickname}</div>` : '';
    
    // Utiliser le surnom s'il existe, sinon le nom
    const displayName = contact.nickname || contact.name;
    
    // Déterminer l'ID correct pour les actions
    const contactId = contact.contact_id || contact.id;
    
    div.innerHTML = `
        <div class="contact-avatar">
            ${displayName.charAt(0).toUpperCase()}
            ${contact.online ? '<div class="online-indicator"></div>' : ''}
        </div>
        <div class="contact-info">
            <div class="contact-name">${displayName}</div>
            <div class="contact-status ${statusClass}">${statusText}</div>
            ${phoneInfo}
            ${emailInfo}
            ${nicknameInfo}
        </div>
        <div class="contact-actions">
            <button class="contact-action-btn edit" onclick="editContact('${contactId}', event)" title="Modifier le surnom">
                <i class="fas fa-edit"></i>
            </button>
            <button class="contact-action-btn ${contact.favorite ? 'unfavorite' : 'favorite'}" 
                    onclick="toggleFavorite('${contactId}', event)" 
                    title="${contact.favorite ? 'Retirer des favoris' : 'Ajouter aux favoris'}">
                <i class="fas fa-star"></i>
            </button>
            <button class="contact-action-btn delete" onclick="deleteContact('${contactId}', event)" title="Supprimer le contact">
                <i class="fas fa-trash"></i>
            </button>
        </div>
    `;
    return div;
}

// Filtrer les contacts
function filterContacts(searchTerm) {
    const filtered = contacts.filter(contact => 
        contact.name.toLowerCase().includes(searchTerm)
    );
    displayContacts(filtered);
}

// Charger les groupes
function loadGroups() {
    fetch('php/groups.php?action=get_groups')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                groups = data.groups;
                displayGroups(groups);
            }
        })
        .catch(error => {
            console.error('Erreur chargement groupes:', error);
            document.getElementById('groupsList').innerHTML = '<div class="error">Erreur de chargement</div>';
        });
}

// Afficher les groupes
function displayGroups(groupsList) {
    const container = document.getElementById('groupsList');
    container.innerHTML = '';

    if (groupsList.length === 0) {
        container.innerHTML = '<div class="no-items">Aucun groupe</div>';
        return;
    }

    groupsList.forEach(group => {
        const groupElement = createGroupElement(group);
        container.appendChild(groupElement);
    });
}

// Créer un élément groupe
function createGroupElement(group) {
    const div = document.createElement('div');
    div.className = 'group-item';
    div.onclick = () => openChat(group);
    
    const lastMessage = group.last_message || 'Aucun message';
    const time = group.last_time ? formatTime(group.last_time) : '';
    
    div.innerHTML = `
        <div class="group-avatar">
            ${group.name.charAt(0).toUpperCase()}
        </div>
        <div class="group-info">
            <div class="group-name">${group.name}</div>
            <div class="group-last-message">${lastMessage}</div>
        </div>
        <div class="contact-time">${time}</div>
    `;
    
    return div;
}

// Filtrer les groupes
function filterGroups(searchTerm) {
    const filtered = groups.filter(group => 
        group.name.toLowerCase().includes(searchTerm)
    );
    displayGroups(filtered);
}

// Ouvrir un chat
function openChat(chat) {
    currentChat = chat;
    // Chercher le contact à jour dans la liste (par id ou contact_id)
    let upToDateContact = contacts.find(c => c.id === chat.id || c.contact_id === chat.id);
    // Si c'est un contact, utiliser le nickname, sinon le nom du groupe
    let displayName = (upToDateContact && upToDateContact.nickname) ? upToDateContact.nickname : chat.name;
    document.getElementById('chatHeader').style.display = 'flex';
    document.getElementById('chatContactName').textContent = displayName;
    document.getElementById('chatContactAvatar').textContent = displayName.charAt(0).toUpperCase();
    document.getElementById('chatContactStatus').textContent = chat.online ? 'En ligne' : 'Hors ligne';
    document.getElementById('welcomeMessage').style.display = 'none';
    document.getElementById('messagesArea').style.display = 'block';
    document.getElementById('messageInputContainer').style.display = 'flex';
    loadMessages(chat.id, chat.type || 'contact');
    document.querySelectorAll('.contact-item, .group-item').forEach(item => {
        item.classList.remove('active');
    });
    if (event && event.currentTarget) {
        event.currentTarget.classList.add('active');
    }
}

// Charger les messages
function loadMessages(chatId, chatType) {
    let url = '';
    if (chatType === 'group') {
        url = `php/messages.php?action=get_group_messages&group_id=${chatId}`;
    } else {
        url = `php/messages.php?action=get_private_messages&user_id=${currentUser.id}&contact_id=${chatId}`;
    }
    fetch(url)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayMessages(data.messages);
            }
        })
        .catch(error => {
            console.error('Erreur chargement messages:', error);
        });
}

// Afficher les messages
function displayMessages(messages) {
    const container = document.getElementById('messagesArea');
    container.innerHTML = '';

    messages.forEach(message => {
        const messageElement = createMessageElement(message);
        container.appendChild(messageElement);
    });

    // Scroll vers le bas
    container.scrollTop = container.scrollHeight;
}

// Créer un élément message
function createMessageElement(message) {
    const div = document.createElement('div');
    div.className = `message ${message.sender_id == currentUser.id ? 'sent' : 'received'}`;
    
    const time = formatTime(message.timestamp);
    const status = message.sender_id == currentUser.id ? getMessageStatus(message.status) : '';
    
    div.innerHTML = `
        <div class="message-bubble">
            <div class="message-content">${message.content}</div>
            <div class="message-time">
                ${time}
                ${status}
            </div>
        </div>
    `;
    
    return div;
}

// Obtenir le statut du message
function getMessageStatus(status) {
    const statusMap = {
        'sent': '<span class="status-icon status-sent"><i class="fas fa-check"></i></span>',
        'delivered': '<span class="status-icon status-delivered"><i class="fas fa-check-double"></i></span>',
        'read': '<span class="status-icon status-read"><i class="fas fa-check-double"></i></span>'
    };
    return statusMap[status] || '';
}

// Envoyer un message
function sendMessage() {
    const input = document.getElementById('messageInput');
    const content = input.value.trim();
    
    if (!content || !currentChat) return;

    // Préparer les données POST attendues par messages.php
    const formData = new URLSearchParams();
    formData.append('action', 'send_message');
    formData.append('sender_id', currentUser.id);
    if (currentChat.type === 'group') {
        formData.append('group_id', currentChat.id);
        formData.append('receiver_id', '');
    } else {
        formData.append('receiver_id', currentChat.id);
        formData.append('group_id', '');
    }
    formData.append('content', content);

    fetch('php/messages.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: formData.toString()
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            input.value = '';
            document.getElementById('sendBtn').disabled = true;
            
            // Ajouter le message à l'affichage
            const message = {
                id: data.id || data.message_id,
                content: content,
                sender_id: currentUser.id,
                timestamp: new Date().toISOString(),
                status: 'sent'
            };
            
            const messageElement = createMessageElement(message);
            document.getElementById('messagesArea').appendChild(messageElement);
            
            // Scroll vers le bas
            const messagesArea = document.getElementById('messagesArea');
            messagesArea.scrollTop = messagesArea.scrollHeight;
        } else {
            alert(data.error || data.message || 'Erreur lors de l\'envoi du message');
        }
    })
    .catch(error => {
        console.error('Erreur envoi message:', error);
        alert('Erreur lors de l\'envoi du message');
    });
}

// Démarrer un chat avec un contact
function startChat(contact) {
    // Toujours prendre le contact à jour depuis la liste
    const upToDateContact = contacts.find(c => c.id === contact.id);
    openChat(upToDateContact || contact);
}

// Gérer l'upload de fichiers
function handleFileUpload(event) {
    const files = event.target.files;
    if (!files.length) return;
    
    if (!currentChat) {
        alert('Veuillez sélectionner un chat pour envoyer des fichiers');
        return;
    }
    
    Array.from(files).forEach(file => {
        uploadFile(file);
    });
    
    // Réinitialiser l'input
    event.target.value = '';
}

// Uploader un fichier
function uploadFile(file) {
    const formData = new FormData();
    formData.append('file', file);
    formData.append('action', 'upload_file');
    formData.append('chat_id', currentChat.id);
    formData.append('chat_type', currentChat.type);
    
    fetch('php/message.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Recharger les messages pour afficher le nouveau fichier
            loadMessages(currentChat.id, currentChat.type);
            // Recharger la liste des fichiers
            if (document.querySelector('.nav-tab.active').dataset.tab === 'files') {
                loadFiles();
            }
        } else {
            alert('Erreur upload: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Erreur upload fichier:', error);
        alert('Erreur lors de l\'upload du fichier');
    });
}

// Formater l'heure
function formatTime(timestamp) {
    const date = new Date(timestamp);
    const now = new Date();
    const diff = now - date;
    
    if (diff < 24 * 60 * 60 * 1000) {
        // Aujourd'hui
        return date.toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit' });
    } else if (diff < 7 * 24 * 60 * 60 * 1000) {
        // Cette semaine
        return date.toLocaleDateString('fr-FR', { weekday: 'short' });
    } else {
        // Plus ancien
        return date.toLocaleDateString('fr-FR', { day: '2-digit', month: '2-digit' });
    }
}

// Fonctions pour les modals
function showModal(modalId) {
    document.getElementById(modalId).style.display = 'block';
}

function closeModal(modalId) {
    document.getElementById(modalId).style.display = 'none';
}

// Rechercher des utilisateurs pour ajouter un contact
function searchUser() {
    const searchTerm = document.getElementById('searchUser').value.trim();
    if (!searchTerm) return;
    
    fetch(`php/user.php?action=get_all_users`)
        .then(response => response.json())
        .then(data => {
            const filtered = data.filter(user => 
                user.name.toLowerCase().includes(searchTerm.toLowerCase()) ||
                user.email.toLowerCase().includes(searchTerm.toLowerCase())
            );
            displaySearchResults(filtered);
        })
        .catch(error => {
            console.error('Erreur recherche:', error);
        });
}

// Afficher les résultats de recherche
function displaySearchResults(users) {
    const container = document.getElementById('searchResults');
    container.innerHTML = '';
    
    if (users.length === 0) {
        container.innerHTML = '<p>Aucun utilisateur trouvé</p>';
        return;
    }
    
    users.forEach(user => {
        const div = document.createElement('div');
        div.className = 'search-result-item';
        div.innerHTML = `
            <div class="user-info">
                <div class="user-avatar">${user.name.charAt(0).toUpperCase()}</div>
                <div>
                    <div class="user-name">${user.name}</div>
                    <div class="user-email">${user.email}</div>
                    <div class="user-phone">📱 ${user.phone || 'N/A'}</div>
                </div>
            </div>
            <button class="btn btn-primary" onclick="addContact('${user.id}')">Ajouter</button>
        `;
        container.appendChild(div);
    });
}

// Ajouter un contact
function addContact(userId) {
    fetch('php/contacts.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            action: 'add_contact',
            user_id: userId
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Contact ajouté avec succès');
            closeModal('addContactModal');
            loadContacts();
        } else {
            alert('Erreur: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Erreur ajout contact:', error);
    });
}

// Rechercher des membres pour créer un groupe
function searchGroupMembers() {
    const searchTerm = document.getElementById('searchGroupMembers').value.trim();
    if (!searchTerm) return;
    
    fetch(`php/user.php?action=get_all_users`)
        .then(response => response.json())
        .then(data => {
            const filtered = data.filter(user => 
                user.name.toLowerCase().includes(searchTerm.toLowerCase()) ||
                user.email.toLowerCase().includes(searchTerm.toLowerCase())
            );
            displayGroupMembersResults(filtered);
        })
        .catch(error => {
            console.error('Erreur recherche:', error);
        });
}

// Afficher les résultats de recherche pour les membres de groupe
function displayGroupMembersResults(users) {
    const container = document.getElementById('groupMembersResults');
    container.innerHTML = '';
    
    if (users.length === 0) {
        container.innerHTML = '<p>Aucun utilisateur trouvé</p>';
        return;
    }
    
    users.forEach(user => {
        const div = document.createElement('div');
        div.className = 'search-result-item';
        div.innerHTML = `
            <div class="user-info">
                <div class="user-avatar">${user.name.charAt(0).toUpperCase()}</div>
                <div>
                    <div class="user-name">${user.name}</div>
                    <div class="user-email">${user.email}</div>
                </div>
            </div>
            <input type="checkbox" value="${user.email}" class="member-checkbox">
        `;
        container.appendChild(div);
    });
}

// Créer un groupe
function createGroup() {
    const name = document.getElementById('groupName').value.trim();
    const description = document.getElementById('groupDescription').value.trim();
    const members = Array.from(document.querySelectorAll('.member-checkbox:checked')).map(cb => cb.value);
    
    if (!name) {
        alert('Veuillez entrer un nom pour le groupe');
        return;
    }
    
    const groupData = {
        action: 'create_group',
        name: name,
        description: description,
        members: members
    };
    
    fetch('php/groups.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(groupData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Groupe créé avec succès');
            closeModal('createGroupModal');
            loadGroups();
        } else {
            alert('Erreur: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Erreur création groupe:', error);
    });
}

// Variables globales pour les fichiers
let files = [];

// Charger les fichiers
function loadFiles() {
    fetch('php/files.php?action=get_files')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                files = data.files;
                displayFiles(files);
            }
        })
        .catch(error => {
            console.error('Erreur chargement fichiers:', error);
            document.getElementById('filesList').innerHTML = '<div class="error">Erreur de chargement</div>';
        });
}

// Afficher les fichiers
function displayFiles(filesList) {
    const container = document.getElementById('filesList');
    container.innerHTML = '';

    if (filesList.length === 0) {
        container.innerHTML = '<div class="no-items">Aucun fichier partagé</div>';
        return;
    }

    filesList.forEach(file => {
        const fileElement = createFileElement(file);
        container.appendChild(fileElement);
    });
}

// Créer un élément fichier
function createFileElement(file) {
    const div = document.createElement('div');
    div.className = 'file-item';
    
    const fileIcon = getFileIcon(file.mime_type);
    const fileSize = formatFileSize(file.file_size);
    const uploadDate = formatTime(file.uploaded_at);
    const recipient = file.receiver_name || file.group_name || 'Moi';
    
    div.innerHTML = `
        <div class="file-icon ${fileIcon.class}">
            <i class="${fileIcon.icon}"></i>
        </div>
        <div class="file-info">
            <div class="file-name">${file.original_name}</div>
            <div class="file-details">
                <div class="file-meta">
                    <span class="file-size">${fileSize}</span>
                    <span class="file-date">${uploadDate}</span>
                    <span class="file-downloads">${file.downloads} téléchargement(s)</span>
                </div>
                <div class="file-sender">Envoyé par: ${file.sender_name}</div>
                <div class="file-recipient">À: ${recipient}</div>
            </div>
        </div>
        <div class="file-actions">
            <button class="file-action-btn download" onclick="downloadFile('${file.id}')" title="Télécharger">
                <i class="fas fa-download"></i>
            </button>
            ${file.sender_id == currentUser.id ? 
                `<button class="file-action-btn delete" onclick="deleteFile('${file.id}')" title="Supprimer">
                    <i class="fas fa-trash"></i>
                </button>` : ''
            }
        </div>
    `;
    
    return div;
}

// Obtenir l'icône du fichier selon son type
function getFileIcon(mimeType) {
    if (mimeType.includes('pdf')) {
        return { class: 'pdf', icon: 'fas fa-file-pdf' };
    } else if (mimeType.includes('image')) {
        return { class: 'image', icon: 'fas fa-file-image' };
    } else if (mimeType.includes('text') || mimeType.includes('document')) {
        return { class: 'document', icon: 'fas fa-file-alt' };
    } else {
        return { class: 'other', icon: 'fas fa-file' };
    }
}

// Formater la taille du fichier
function formatFileSize(bytes) {
    if (bytes === 0) return '0 Bytes';
    
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
}

// Filtrer les fichiers
function filterFiles(searchTerm) {
    const filtered = files.filter(file => 
        file.original_name.toLowerCase().includes(searchTerm) ||
        file.sender_name.toLowerCase().includes(searchTerm)
    );
    displayFiles(filtered);
}

// Télécharger un fichier
function downloadFile(fileId) {
    window.open(`php/files.php?action=download_file&file_id=${fileId}`, '_blank');
}

// Supprimer un fichier
function deleteFile(fileId) {
    if (!confirm('Êtes-vous sûr de vouloir supprimer ce fichier ?')) {
        return;
    }
    
    const formData = new FormData();
    formData.append('file_id', fileId);
    
    fetch('php/files.php?action=delete_file', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Fichier supprimé avec succès');
            loadFiles();
        } else {
            alert('Erreur: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Erreur suppression fichier:', error);
    });
}

// Ouvrir les paramètres
function openSettings() {
    window.location.href = 'settings.html';
}

// Afficher le modal d'ajout de contact
function showAddContactModal() {
    showModal('addContactModal');
}

// Modifier un contact
function editContact(contactId, event) {
    event.stopPropagation();
    
    // Chercher le contact par contact_id ou id
    const contact = contacts.find(c => c.contact_id === contactId || c.id === contactId);
    
    if (contact) {
        // Remplir le modal avec les informations du contact
        document.getElementById('editContactId').value = contact.contact_id || contactId;
        document.getElementById('editContactName').value = contact.nickname || contact.name;
        showModal('editContactModal');
    } else {
        alert('Contact non trouvé');
    }
}

// Basculer le statut favori
function toggleFavorite(contactId, event) {
    event.stopPropagation();
    
    // Chercher le contact par contact_id ou id
    const contact = contacts.find(c => c.contact_id === contactId || c.id === contactId);
    const actualContactId = contact ? contact.contact_id : contactId;
    
    fetch('php/contacts.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            action: 'toggle_favorite',
            contact_id: actualContactId
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            loadContacts(); // Recharger la liste
        } else {
            alert('Erreur: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Erreur toggle favori:', error);
    });
}

// Supprimer un contact
function deleteContact(contactId, event) {
    event.stopPropagation();
    
    // Chercher le contact par contact_id ou id
    const contact = contacts.find(c => c.contact_id === contactId || c.id === contactId);
    const actualContactId = contact ? contact.contact_id : contactId;
    
    if (!confirm('Êtes-vous sûr de vouloir supprimer ce contact ?')) {
        return;
    }
    
    fetch('php/contacts.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            action: 'delete_contact',
            contact_id: actualContactId
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            loadContacts(); // Recharger la liste
        } else {
            alert('Erreur: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Erreur suppression contact:', error);
    });
}

// Sauvegarder les modifications d'un contact
function saveContactEdit() {
    const contactId = document.getElementById('editContactId').value;
    const nickname = document.getElementById('editContactName').value.trim();
    // On cherche le contact par contact_id
    const contact = contacts.find(c => c.contact_id === contactId);

    if (!nickname) {
        alert('Veuillez entrer un nom');
        return;
    }

    if (contact && contact.contact_id) {
        // Le contact existe déjà, on modifie directement
        updateContactNickname(contact.contact_id, nickname);
    } else {
        // Le contact n'existe pas, on l'ajoute d'abord
        // On doit trouver l'id utilisateur cible (c.id)
        const userId = contacts.find(c => c.id === contactId)?.id || contactId;
        fetch('php/contacts.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                action: 'add_contact',
                user_id: userId
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success && data.contact_id) {
                updateContactNickname(data.contact_id, nickname);
            } else {
                alert(data.message || 'Erreur lors de l\'ajout du contact');
            }
        })
        .catch(error => {
            console.error('Erreur ajout contact:', error);
            alert('Erreur lors de l\'ajout du contact');
        });
    }
}

function updateContactNickname(contactId, nickname) {
    fetch('php/contacts.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            action: 'update_contact',
            contact_id: contactId,
            nickname: nickname
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            closeModal('editContactModal');
            loadContacts(); // Recharger la liste
        } else {
            alert('Erreur: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Erreur modification contact:', error);
        alert('Erreur lors de la modification du contact');
    });
} 

// Afficher la liste des discussions (tous les contacts)
function displayDiscussions(contactsList) {
    const container = document.getElementById('discussionsList');
    container.innerHTML = '';
    if (contactsList.length === 0) {
        container.innerHTML = '<div class="no-items">Aucune discussion</div>';
        return;
    }
    contactsList.forEach(contact => {
        const discussionElement = createDiscussionElement(contact);
        container.appendChild(discussionElement);
    });
}

// Créer un élément discussion (pour chatter)
function createDiscussionElement(contact) {
    const div = document.createElement('div');
    div.className = 'contact-item';
    div.onclick = () => startChat(contact);
    const displayName = contact.nickname || contact.name;
    const statusText = contact.online ? 'En ligne' : 'Hors ligne';
    const statusClass = contact.online ? 'online' : 'offline';
    div.innerHTML = `
        <div class="contact-avatar">
            ${displayName.charAt(0).toUpperCase()}
            ${contact.online ? '<div class="online-indicator"></div>' : ''}
        </div>
        <div class="contact-info">
            <div class="contact-name">${displayName}</div>
            <div class="contact-status ${statusClass}">${statusText}</div>
        </div>
    `;
    return div;
} 

// Fonction pour initialiser les événements du formulaire de contact
function initializeContactFormEvents() {
    const showBtn = document.getElementById('showAddContactFormBtn');
    const addContactForm = document.getElementById('addContactForm');
    const cancelBtn = document.getElementById('cancelAddContactBtn');
    const searchBtn = document.getElementById('searchContactBtn');
    
    if (showBtn && addContactForm) {
        // Supprimer les anciens événements en clonant l'élément
        const newShowBtn = showBtn.cloneNode(true);
        showBtn.parentNode.replaceChild(newShowBtn, showBtn);
        
        newShowBtn.addEventListener('click', function() {
            addContactForm.style.display = 'block';
            newShowBtn.style.display = 'none';
            // Vider les champs
            const phoneInput = document.getElementById('searchContactPhone');
            const resultsDiv = document.getElementById('searchContactResults');
            if (phoneInput) phoneInput.value = '';
            if (resultsDiv) resultsDiv.innerHTML = '';
        });
    }
    
    if (cancelBtn && addContactForm) {
        // Supprimer les anciens événements en clonant l'élément
        const newCancelBtn = cancelBtn.cloneNode(true);
        cancelBtn.parentNode.replaceChild(newCancelBtn, cancelBtn);
        
        newCancelBtn.addEventListener('click', function() {
            addContactForm.style.display = 'none';
            const showBtn = document.getElementById('showAddContactFormBtn');
            if (showBtn) showBtn.style.display = 'block';
        });
    }
    
    if (searchBtn) {
        // Supprimer les anciens événements en clonant l'élément
        const newSearchBtn = searchBtn.cloneNode(true);
        searchBtn.parentNode.replaceChild(newSearchBtn, searchBtn);
        
        newSearchBtn.addEventListener('click', function() {
            searchContactUser();
        });
    }
    
    // Permettre la recherche avec Entrée
    const phoneInput = document.getElementById('searchContactPhone');
    
    if (phoneInput) {
        // Supprimer les anciens événements
        const newPhoneInput = phoneInput.cloneNode(true);
        phoneInput.parentNode.replaceChild(newPhoneInput, phoneInput);
        
        newPhoneInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                searchContactUser();
            }
        });
    }
}

// Gestion du formulaire d'ajout de contact dans l'onglet Contacts
window.addEventListener('DOMContentLoaded', function() {
    initializeContactFormEvents();
});

// Fonction pour rechercher un utilisateur à ajouter comme contact
function searchContactUser() {
    const phone = document.getElementById('searchContactPhone').value.trim();
    const resultsDiv = document.getElementById('searchContactResults');
    
    if (!phone) {
        resultsDiv.innerHTML = '<div style="color: #e74c3c; padding: 10px;">Veuillez entrer un numéro de téléphone</div>';
        return;
    }
    
    resultsDiv.innerHTML = '<div style="text-align: center; padding: 10px;"><i class="fas fa-spinner fa-spin"></i> Recherche en cours...</div>';
    
    fetch('php/contacts.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            action: 'check_user',
            phone: phone
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Utilisateur trouvé, afficher le formulaire d'ajout avec surnom personnalisé
            resultsDiv.innerHTML = `
                <div style="background: #d4edda; border: 1px solid #c3e6cb; border-radius: 5px; padding: 10px; margin-top: 10px;">
                    <div style="font-weight: bold; color: #155724;">Utilisateur trouvé !</div>
                    <div style="margin: 5px 0;">Nom: ${data.user_name}</div>
                    <div style="margin: 5px 0;">Téléphone: ${data.user_phone}</div>
                    <div style="margin: 5px 0;">Email: ${data.user_email}</div>
                    
                    <div style="margin-top: 15px;">
                        <label style="display: block; margin-bottom: 5px; font-weight: bold;">Surnom personnalisé (optionnel):</label>
                        <input type="text" id="customNickname" placeholder="Entrez un surnom personnalisé" 
                               style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; margin-bottom: 10px;">
                        <div style="display: flex; gap: 8px;">
                            <button type="button" class="btn btn-success" onclick="addContactFromSearch('${data.user_id}')">
                                <i class="fas fa-plus"></i> Ajouter aux contacts
                            </button>
                            <button type="button" class="btn btn-secondary" onclick="cancelAddContact()">
                                <i class="fas fa-times"></i> Annuler
                            </button>
                        </div>
                    </div>
                </div>
            `;
        } else {
            resultsDiv.innerHTML = `<div style="color: #e74c3c; padding: 10px;">${data.message}</div>`;
        }
    })
    .catch(error => {
        console.error('Erreur recherche utilisateur:', error);
        resultsDiv.innerHTML = '<div style="color: #e74c3c; padding: 10px;">Erreur lors de la recherche</div>';
    });
}

// Fonction pour ajouter un contact depuis la recherche
function addContactFromSearch(userId) {
    const customNickname = document.getElementById('customNickname')?.value.trim() || '';
    
    console.log('Ajout de contact:', { userId, customNickname });
    
    // Afficher un indicateur de chargement
    const resultsDiv = document.getElementById('searchContactResults');
    resultsDiv.innerHTML = '<div style="text-align: center; padding: 10px;"><i class="fas fa-spinner fa-spin"></i> Ajout du contact en cours...</div>';
    
    fetch('php/contacts.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            action: 'add_contact',
            user_id: userId,
            nickname: customNickname
        })
    })
    .then(response => response.json())
    .then(data => {
        console.log('Réponse ajout contact:', data);
        if (data.success) {
            // Masquer le formulaire et recharger les contacts
            document.getElementById('addContactForm').style.display = 'none';
            document.getElementById('showAddContactFormBtn').style.display = 'block';
            
            // Vider les résultats de recherche
            resultsDiv.innerHTML = '';
            
            // Afficher un message de succès temporaire avec animation
            const nicknameText = customNickname ? ` avec le surnom "${customNickname}"` : '';
            resultsDiv.innerHTML = `
                <div style="background: #d4edda; border: 1px solid #c3e6cb; border-radius: 5px; padding: 15px; color: #155724; animation: fadeIn 0.5s ease;">
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <i class="fas fa-check-circle" style="font-size: 20px; color: #28a745;"></i>
                        <div>
                            <div style="font-weight: bold; margin-bottom: 5px;">✅ Contact ajouté avec succès !</div>
                            <div style="font-size: 14px;">Le contact apparaîtra dans votre liste ci-dessous.</div>
                        </div>
                    </div>
                </div>
            `;
            
            // Recharger immédiatement les contacts pour voir le nouveau contact en bas
            console.log('Rechargement des contacts après ajout...');
            
            // Attendre un peu pour s'assurer que le contact est bien ajouté dans le XML
            setTimeout(() => {
                loadContacts();
                
                // Marquer le nouveau contact après le rechargement
                setTimeout(() => {
                    const contactsContainer = document.getElementById('contactsListContainer');
                    if (contactsContainer) {
                        const lastContact = contactsContainer.querySelector('.contact-item:last-child');
                        if (lastContact) {
                            // Ajouter la classe pour le style spécial
                            lastContact.classList.add('new-contact');
                            
                            // Ajouter une animation de pulsation
                            lastContact.style.animation = 'pulse 2s ease-in-out';
                            
                            // Faire défiler vers le nouveau contact
                            lastContact.scrollIntoView({ behavior: 'smooth', block: 'center' });
                            
                            // Retirer les classes après 3 secondes
                            setTimeout(() => {
                                lastContact.classList.remove('new-contact');
                                lastContact.style.animation = '';
                            }, 3000);
                        }
                    }
                }, 100);
            }, 500);
            
            // Masquer le message de succès après 4 secondes
            setTimeout(() => {
                resultsDiv.innerHTML = '';
            }, 4000);
        } else {
            // Afficher l'erreur avec un style approprié
            resultsDiv.innerHTML = `
                <div style="background: #f8d7da; border: 1px solid #f5c6cb; border-radius: 5px; padding: 15px; color: #721c24; animation: fadeIn 0.5s ease;">
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <i class="fas fa-exclamation-triangle" style="font-size: 20px; color: #dc3545;"></i>
                        <div>
                            <div style="font-weight: bold; margin-bottom: 5px;">❌ Erreur lors de l'ajout</div>
                            <div style="font-size: 14px;">${data.message}</div>
                        </div>
                    </div>
                </div>
            `;
        }
    })
    .catch(error => {
        console.error('Erreur ajout contact:', error);
        resultsDiv.innerHTML = `
            <div style="background: #f8d7da; border: 1px solid #f5c6cb; border-radius: 5px; padding: 15px; color: #721c24; animation: fadeIn 0.5s ease;">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <i class="fas fa-exclamation-triangle" style="font-size: 20px; color: #dc3545;"></i>
                    <div>
                        <div style="font-weight: bold; margin-bottom: 5px;">❌ Erreur de connexion</div>
                        <div style="font-size: 14px;">Impossible de se connecter au serveur. Veuillez réessayer.</div>
                    </div>
                </div>
            </div>
        `;
    });
}

// Fonction pour annuler l'ajout de contact
function cancelAddContact() {
    const resultsDiv = document.getElementById('searchContactResults');
    resultsDiv.innerHTML = '';
}