<?php
header('Content-Type: application/json');

if (!extension_loaded('xml')) {
    echo json_encode(['error' => 'Extension XML non chargée']);
    exit;
}

session_start();

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Utilisateur non connecté']);
    exit;
}

$groupsFile = __DIR__ . '/../xml/groups.xml';
$usersFile = __DIR__ . '/../xml/users.xml';

// Vérifier si le fichier XML existe, sinon le créer
if (!file_exists($groupsFile)) {
    $xmlContent = '<?xml version="1.0" encoding="UTF-8"?><groups></groups>';
    file_put_contents($groupsFile, $xmlContent);
}

// GET - Récupérer les groupes de l'utilisateur
if ($_SERVER['REQUEST_METHOD'] === 'GET' && $_GET['action'] === 'get_user_groups') {
    $userId = $_SESSION['user_id'];
    
    $xml = simplexml_load_file($groupsFile);
    if ($xml === false) {
        echo json_encode(['error' => 'Erreur lors du chargement des groupes']);
        exit;
    }

    $groups = [];
    foreach ($xml->group as $group) {
        $groupId = (string)$group['id'];
        $isMember = false;
        $userRole = '';

        // Vérifier si l'utilisateur est membre du groupe
        foreach ($group->members->member as $member) {
            if ((string)$member['user_id'] === $userId) {
                $isMember = true;
                $userRole = (string)$member['role'];
                break;
            }
        }

        if ($isMember) {
            $groups[] = [
                'id' => $groupId,
                'name' => (string)$group->name,
                'description' => (string)$group->description,
                'created_by' => (string)$group->created_by,
                'created_at' => (string)$group->created_at,
                'avatar' => (string)$group->avatar,
                'member_count' => count($group->members->member),
                'user_role' => $userRole,
                'settings' => [
                    'allow_files' => (string)$group->settings->allow_files === 'true',
                    'max_file_size' => (int)$group->settings->max_file_size,
                    'only_admin_can_add' => (string)$group->settings->only_admin_can_add === 'true'
                ]
            ];
        }
    }

    echo json_encode(['success' => true, 'groups' => $groups]);
}

// GET - Récupérer tous les groupes (pour l'administration)
elseif ($_SERVER['REQUEST_METHOD'] === 'GET' && $_GET['action'] === 'get_all_groups') {
    $xml = simplexml_load_file($groupsFile);
    if ($xml === false) {
        echo json_encode(['error' => 'Erreur lors du chargement des groupes']);
        exit;
    }

    $groups = [];
    foreach ($xml->group as $group) {
        $groups[] = [
            'id' => (string)$group['id'],
            'name' => (string)$group->name,
            'description' => (string)$group->description,
            'created_by' => (string)$group->created_by,
            'created_at' => (string)$group->created_at,
            'avatar' => (string)$group->avatar,
            'member_count' => count($group->members->member)
        ];
    }

    echo json_encode(['success' => true, 'groups' => $groups]);
}

// GET - Récupérer les détails d'un groupe
elseif ($_SERVER['REQUEST_METHOD'] === 'GET' && $_GET['action'] === 'get_group_details') {
    $groupId = $_GET['group_id'];
    $userId = $_SESSION['user_id'];
    
    $xml = simplexml_load_file($groupsFile);
    if ($xml === false) {
        echo json_encode(['error' => 'Erreur lors du chargement des groupes']);
        exit;
    }

    $group = null;
    $isMember = false;
    $userRole = '';

    foreach ($xml->group as $g) {
        if ((string)$g['id'] === $groupId) {
            // Vérifier si l'utilisateur est membre
            foreach ($g->members->member as $member) {
                if ((string)$member['user_id'] === $userId) {
                    $isMember = true;
                    $userRole = (string)$member['role'];
                    break;
                }
            }

            $group = [
                'id' => (string)$g['id'],
                'name' => (string)$g->name,
                'description' => (string)$g->description,
                'created_by' => (string)$g->created_by,
                'created_at' => (string)$g->created_at,
                'avatar' => (string)$g->avatar,
                'is_member' => $isMember,
                'user_role' => $userRole,
                'settings' => [
                    'allow_files' => (string)$g->settings->allow_files === 'true',
                    'max_file_size' => (int)$g->settings->max_file_size,
                    'only_admin_can_add' => (string)$g->settings->only_admin_can_add === 'true'
                ]
            ];

            // Récupérer les membres
            $members = [];
            $usersXml = simplexml_load_file($usersFile);
            
            foreach ($g->members->member as $member) {
                $memberUserId = (string)$member['user_id'];
                $memberRole = (string)$member['role'];
                $joinedAt = (string)$member['joined_at'];

                // Récupérer les informations de l'utilisateur
                foreach ($usersXml->user as $user) {
                    if ((string)$user['id'] === $memberUserId) {
                        $members[] = [
                            'id' => $memberUserId,
                            'name' => (string)$user->name,
                            'phone' => (string)$user->phone,
                            'status' => (string)$user->status,
                            'avatar' => (string)$user->avatar,
                            'role' => $memberRole,
                            'joined_at' => $joinedAt
                        ];
                        break;
                    }
                }
            }

            $group['members'] = $members;
            break;
        }
    }

    if ($group) {
        echo json_encode(['success' => true, 'group' => $group]);
    } else {
        echo json_encode(['error' => 'Groupe non trouvé']);
    }
}

// POST - Créer un nouveau groupe
elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['action'] === 'create_group') {
    $name = trim($_POST['name']);
    $description = trim($_POST['description'] ?? '');
    $createdBy = $_SESSION['user_id'];
    
    if (empty($name)) {
        echo json_encode(['error' => 'Le nom du groupe est requis']);
        exit;
    }

    if (strlen($name) > 100) {
        echo json_encode(['error' => 'Le nom du groupe est trop long (maximum 100 caractères)']);
        exit;
    }

    $xml = simplexml_load_file($groupsFile);
    if ($xml === false) {
        echo json_encode(['error' => 'Erreur lors du chargement des groupes']);
        exit;
    }

    // Générer un nouvel ID
    $maxId = 0;
    foreach ($xml->group as $group) {
        $groupId = (int)$group['id'];
        if ($groupId > $maxId) {
            $maxId = $groupId;
        }
    }
    $newId = $maxId + 1;

    // Créer le nouveau groupe
    $newGroup = $xml->addChild('group');
    $newGroup->addAttribute('id', $newId);
    $newGroup->addChild('name', htmlspecialchars($name));
    $newGroup->addChild('description', htmlspecialchars($description));
    $newGroup->addChild('created_by', $createdBy);
    $newGroup->addChild('created_at', date('c'));
    $newGroup->addChild('avatar', 'uploads/groups/default.jpg');

    // Ajouter les membres
    $members = $newGroup->addChild('members');
    $creatorMember = $members->addChild('member');
    $creatorMember->addAttribute('user_id', $createdBy);
    $creatorMember->addAttribute('role', 'admin');
    $creatorMember->addAttribute('joined_at', date('c'));

    // Ajouter les paramètres par défaut
    $settings = $newGroup->addChild('settings');
    $settings->addChild('allow_files', 'true');
    $settings->addChild('max_file_size', '10485760'); // 10MB
    $settings->addChild('only_admin_can_add', 'false');

    // Sauvegarder le fichier XML
    if ($xml->asXML($groupsFile)) {
        echo json_encode([
            'success' => true, 
            'message' => 'Groupe créé avec succès',
            'group_id' => $newId
        ]);
    } else {
        echo json_encode(['error' => 'Erreur lors de la sauvegarde du groupe']);
    }
}

// POST - Ajouter un membre au groupe
elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['action'] === 'add_member') {
    $groupId = $_POST['group_id'];
    $userId = $_POST['user_id'];
    $addedBy = $_SESSION['user_id'];
    
    $xml = simplexml_load_file($groupsFile);
    if ($xml === false) {
        echo json_encode(['error' => 'Erreur lors du chargement des groupes']);
        exit;
    }

    $group = null;
    $isAdmin = false;

    // Trouver le groupe et vérifier les permissions
    foreach ($xml->group as $g) {
        if ((string)$g['id'] === $groupId) {
            $group = $g;
            
            // Vérifier si l'utilisateur qui ajoute est admin
            foreach ($g->members->member as $member) {
                if ((string)$member['user_id'] === $addedBy && (string)$member['role'] === 'admin') {
                    $isAdmin = true;
                    break;
                }
            }
            break;
        }
    }

    if (!$group) {
        echo json_encode(['error' => 'Groupe non trouvé']);
        exit;
    }

    if (!$isAdmin) {
        echo json_encode(['error' => 'Vous devez être administrateur pour ajouter des membres']);
        exit;
    }

    // Vérifier si l'utilisateur est déjà membre
    foreach ($group->members->member as $member) {
        if ((string)$member['user_id'] === $userId) {
            echo json_encode(['error' => 'L\'utilisateur est déjà membre du groupe']);
            exit;
        }
    }

    // Ajouter le membre
    $newMember = $group->members->addChild('member');
    $newMember->addAttribute('user_id', $userId);
    $newMember->addAttribute('role', 'member');
    $newMember->addAttribute('joined_at', date('c'));

    if ($xml->asXML($groupsFile)) {
        echo json_encode(['success' => true, 'message' => 'Membre ajouté avec succès']);
    } else {
        echo json_encode(['error' => 'Erreur lors de la sauvegarde']);
    }
}

// POST - Retirer un membre du groupe
elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['action'] === 'remove_member') {
    $groupId = $_POST['group_id'];
    $userId = $_POST['user_id'];
    $removedBy = $_SESSION['user_id'];
    
    $xml = simplexml_load_file($groupsFile);
    if ($xml === false) {
        echo json_encode(['error' => 'Erreur lors du chargement des groupes']);
        exit;
    }

    $group = null;
    $isAdmin = false;

    // Trouver le groupe et vérifier les permissions
    foreach ($xml->group as $g) {
        if ((string)$g['id'] === $groupId) {
            $group = $g;
            
            // Vérifier si l'utilisateur qui retire est admin
            foreach ($g->members->member as $member) {
                if ((string)$member['user_id'] === $removedBy && (string)$member['role'] === 'admin') {
                    $isAdmin = true;
                    break;
                }
            }
            break;
        }
    }

    if (!$group) {
        echo json_encode(['error' => 'Groupe non trouvé']);
        exit;
    }

    if (!$isAdmin) {
        echo json_encode(['error' => 'Vous devez être administrateur pour retirer des membres']);
        exit;
    }

    // Retirer le membre
    $memberFound = false;
    foreach ($group->members->member as $member) {
        if ((string)$member['user_id'] === $userId) {
            unset($member[0]);
            $memberFound = true;
            break;
        }
    }

    if ($memberFound) {
        if ($xml->asXML($groupsFile)) {
            echo json_encode(['success' => true, 'message' => 'Membre retiré avec succès']);
        } else {
            echo json_encode(['error' => 'Erreur lors de la sauvegarde']);
        }
    } else {
        echo json_encode(['error' => 'Membre non trouvé']);
    }
}

else {
    echo json_encode(['error' => 'Action non reconnue']);
}
?> 