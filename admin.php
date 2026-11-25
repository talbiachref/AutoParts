<?php

session_start();
define('ADMIN_PASSWORD', 'admin1234');

$adminError = '';

if (isset($_POST['admin_login'])) {
    if ($_POST['admin_password'] === ADMIN_PASSWORD) {
        $_SESSION['is_admin'] = true;
    } else {
        $adminError = 'Mot de passe incorrect.';
    }
}

if (isset($_GET['admin_logout'])) {
    unset($_SESSION['is_admin']);
    header("Location: admin.php");
    exit();
}


if (!isset($_SESSION['is_admin'])) {
    showAdminLogin($adminError);
    exit();
}


include('connect.php');

$actionMsg = '';
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $id   = (int) $_GET['delete'];
    $stmt = $con->prepare("DELETE FROM users WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $actionMsg = $stmt->affected_rows > 0 ? "success:Utilisateur supprimé avec succès." : "error:Erreur lors de la suppression.";
    $stmt->close();
}

$totalUsers    = $con->query("SELECT COUNT(*) as n FROM users")->fetch_assoc()['n'];
$todayUsers    = $con->query("SELECT COUNT(*) as n FROM users WHERE DATE(created_at) = CURDATE()")->fetch_assoc()['n'];
$thisMonthUsers= $con->query("SELECT COUNT(*) as n FROM users WHERE YEAR(created_at)=YEAR(NOW()) AND MONTH(created_at)=MONTH(NOW())")->fetch_assoc()['n'];

$search    = trim($_GET['search'] ?? '');
$searchSql = '';
if ($search) {
    $s = $con->real_escape_string($search);
    $searchSql = "WHERE nom LIKE '%$s%' OR prenom LIKE '%$s%' OR email LIKE '%$s%' OR telephone LIKE '%$s%'";
}

$sortCol = $_GET['sort'] ?? 'created_at';
$sortDir = strtoupper($_GET['dir'] ?? 'DESC');
$allowed = ['id','nom','prenom','email','telephone','created_at'];
if (!in_array($sortCol, $allowed)) $sortCol = 'created_at';
if (!in_array($sortDir, ['ASC','DESC'])) $sortDir = 'DESC';

$perPage    = 10;
$page       = max(1, (int)($_GET['page'] ?? 1));
$offset     = ($page - 1) * $perPage;
$totalCount = $con->query("SELECT COUNT(*) as n FROM users $searchSql")->fetch_assoc()['n'];
$totalPages = max(1, ceil($totalCount / $perPage));

$result = $con->query("SELECT id, nom, prenom, email, telephone, created_at FROM users $searchSql ORDER BY $sortCol $sortDir LIMIT $perPage OFFSET $offset");

$con->close();

function sortUrl($col) {
    $cur    = $_GET['sort'] ?? 'created_at';
    $curDir = strtoupper($_GET['dir'] ?? 'DESC');
    $newDir = ($cur === $col && $curDir === 'ASC') ? 'DESC' : 'ASC';
    $params = array_merge($_GET, ['sort' => $col, 'dir' => $newDir, 'page' => 1]);
    return '?' . http_build_query($params);
}
function sortIcon($col) {
    $cur    = $_GET['sort'] ?? 'created_at';
    $curDir = strtoupper($_GET['dir'] ?? 'DESC');
    if ($cur !== $col) return '<span class="sort-icon neutral">⇅</span>';
    return $curDir === 'ASC'
        ? '<span class="sort-icon active">↑</span>'
        : '<span class="sort-icon active">↓</span>';
}
function pageUrl($p) {
    $params = array_merge($_GET, ['page' => $p]);
    return '?' . http_build_query($params);
}
function initials($prenom, $nom) {
    return strtoupper(substr($prenom,0,1) . substr($nom,0,1));
}
function avatarColor($id) {
    $colors = ['#ff6600','#5691ff','#28c76f','#ff9f43','#ea5455','#9c68e0','#00cfe8','#ff6ca9'];
    return $colors[$id % count($colors)];
}


function showAdminLogin($error) {
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin — AutoParts Pro</title>
    <link href="https://fonts.googleapis.com/css2?family=Oswald:wght@600;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        *{box-sizing:border-box;margin:0;padding:0}
        body{font-family:'Inter',sans-serif;background:#0a0a0a;color:#f0f0f0;min-height:100vh;display:flex;align-items:center;justify-content:center;}
        .login-card{background:#1c1c1c;border:1px solid #2a2a2a;border-radius:16px;padding:40px;width:360px;text-align:center;}
        .shield{font-size:48px;margin-bottom:16px;}
        h1{font-family:'Oswald',sans-serif;font-size:24px;color:#ff6600;margin-bottom:6px;}
        p{font-size:13px;color:#666;margin-bottom:28px;}
        input{width:100%;background:#242424;border:1px solid #333;color:#f0f0f0;padding:11px 14px;border-radius:7px;font-size:14px;outline:none;margin-bottom:14px;}
        input:focus{border-color:#ff6600;}
        button{width:100%;background:#ff6600;color:white;border:none;padding:13px;border-radius:8px;font-size:15px;font-weight:700;cursor:pointer;}
        button:hover{background:#cc4f00;}
        .err{background:rgba(234,84,85,.12);border-left:3px solid #ea5455;padding:10px 14px;border-radius:6px;font-size:13px;color:#ff8a8a;margin-bottom:16px;text-align:left;}
        .back{display:block;margin-top:18px;font-size:13px;color:#666;text-decoration:none;}
        .back:hover{color:#ff6600;}
    </style>
</head>
<body>
<div class="login-card">
    <div class="shield">🛡️</div>
    <h1>ESPACE ADMIN</h1>
    <p>AutoParts Pro — Accès restreint</p>
    <?php if ($error): ?>
    <div class="err">⚠ <?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <form method="POST">
        <input type="password" name="admin_password" placeholder="Mot de passe admin" autofocus required>
        <button type="submit" name="admin_login">🔐 Accéder au panel</button>
    </form>
    <a href="index.php" class="back">← Retour au site</a>
</div>
</body>
</html>
<?php
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard — AutoParts Pro</title>
    <link href="https://fonts.googleapis.com/css2?family=Oswald:wght@500;600;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary:      #ff6600;
            --primary-dark: #cc4f00;
            --bg:           #0d0d0d;
            --bg-card:      #161616;
            --bg-card2:     #1c1c1c;
            --bg-input:     #222;
            --text:         #f0f0f0;
            --muted:        #666;
            --border:       #2a2a2a;
            --success:      #28c76f;
            --danger:       #ea5455;
            --warning:      #ff9f43;
            --blue:         #5691ff;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Inter', sans-serif; background: var(--bg); color: var(--text); min-height: 100vh; }

        
        .sidebar {
            position: fixed; left: 0; top: 0; bottom: 0; width: 220px;
            background: var(--bg-card);
            border-right: 1px solid var(--border);
            display: flex; flex-direction: column;
            z-index: 50;
        }
        .sidebar-logo {
            padding: 22px 20px 18px;
            border-bottom: 1px solid var(--border);
            font-family: 'Oswald', sans-serif;
            font-size: 20px; font-weight: 700;
            color: var(--primary); letter-spacing: 1px;
        }
        .sidebar-logo span { color: var(--text); font-size: 11px; display: block; font-family: 'Inter', sans-serif; font-weight: 400; color: var(--muted); margin-top: 2px; letter-spacing: 0; }
        .sidebar-nav { flex: 1; padding: 16px 0; }
        .nav-item {
            display: flex; align-items: center; gap: 10px;
            padding: 11px 20px; font-size: 14px; font-weight: 500;
            color: var(--muted); text-decoration: none;
            transition: all 0.15s; cursor: pointer;
            border-left: 3px solid transparent;
        }
        .nav-item:hover { color: var(--text); background: rgba(255,102,0,0.05); }
        .nav-item.active { color: var(--primary); border-left-color: var(--primary); background: rgba(255,102,0,0.07); }
        .nav-item .icon { font-size: 16px; width: 20px; text-align: center; }
        .sidebar-footer { padding: 16px 20px; border-top: 1px solid var(--border); }
        .sidebar-footer a { font-size: 13px; color: var(--muted); text-decoration: none; display: flex; align-items: center; gap: 8px; }
        .sidebar-footer a:hover { color: var(--danger); }

        .main { margin-left: 220px; padding: 32px; }

        
        .topbar {
            display: flex; align-items: center; justify-content: space-between;
            margin-bottom: 32px;
        }
        .topbar h1 {
            font-family: 'Oswald', sans-serif;
            font-size: 28px; font-weight: 700; color: var(--text);
        }
        .topbar h1 span { color: var(--primary); }
        .topbar-right { display: flex; align-items: center; gap: 12px; }
        .admin-badge {
            display: flex; align-items: center; gap: 8px;
            background: rgba(255,102,0,0.1); border: 1px solid rgba(255,102,0,0.3);
            border-radius: 8px; padding: 7px 14px;
            font-size: 13px; color: var(--primary); font-weight: 600;
        }
        .btn-logout {
            background: none; border: 1px solid var(--border);
            color: var(--muted); padding: 7px 14px;
            border-radius: 8px; font-size: 13px; cursor: pointer;
            text-decoration: none; transition: all 0.15s;
        }
        .btn-logout:hover { border-color: var(--danger); color: var(--danger); }

       
        .stats-row { display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; margin-bottom: 28px; }
        .stat-card {
            background: var(--bg-card2); border: 1px solid var(--border);
            border-radius: 12px; padding: 20px 22px;
            position: relative; overflow: hidden;
        }
        .stat-card::before {
            content: '';
            position: absolute; top: 0; left: 0; right: 0; height: 3px;
        }
        .stat-card.orange::before { background: var(--primary); }
        .stat-card.green::before  { background: var(--success); }
        .stat-card.blue::before   { background: var(--blue); }
        .stat-label { font-size: 12px; color: var(--muted); text-transform: uppercase; letter-spacing: 1px; margin-bottom: 8px; }
        .stat-value { font-family: 'Oswald', sans-serif; font-size: 36px; font-weight: 700; line-height: 1; }
        .stat-card.orange .stat-value { color: var(--primary); }
        .stat-card.green  .stat-value { color: var(--success); }
        .stat-card.blue   .stat-value { color: var(--blue); }
        .stat-sub { font-size: 12px; color: var(--muted); margin-top: 6px; }
        .stat-icon { position: absolute; right: 18px; top: 50%; transform: translateY(-50%); font-size: 36px; opacity: 0.12; }

        
        .table-section { background: var(--bg-card2); border: 1px solid var(--border); border-radius: 14px; overflow: hidden; }
        .table-header {
            padding: 20px 24px; border-bottom: 1px solid var(--border);
            display: flex; align-items: center; justify-content: space-between; gap: 16px; flex-wrap: wrap;
        }
        .table-header h2 { font-family: 'Oswald', sans-serif; font-size: 20px; color: var(--text); }
        .table-header h2 span { color: var(--primary); }

       
        .search-form { display: flex; gap: 8px; align-items: center; }
        .search-form input {
            background: var(--bg-input); border: 1px solid var(--border);
            color: var(--text); padding: 8px 14px; border-radius: 7px;
            font-size: 13px; outline: none; width: 240px;
        }
        .search-form input:focus { border-color: var(--primary); }
        .btn-search {
            background: var(--primary); color: white; border: none;
            padding: 8px 16px; border-radius: 7px; font-size: 13px;
            cursor: pointer; font-weight: 600;
        }
        .btn-clear {
            background: none; border: 1px solid var(--border); color: var(--muted);
            padding: 8px 12px; border-radius: 7px; font-size: 13px; cursor: pointer;
            text-decoration: none;
        }
        .btn-clear:hover { border-color: var(--primary); color: var(--primary); }

        
        table { width: 100%; border-collapse: collapse; }
        thead th {
            padding: 12px 16px; text-align: left;
            background: rgba(255,102,0,0.06);
            font-size: 11px; font-weight: 600;
            text-transform: uppercase; letter-spacing: 1px; color: var(--muted);
            border-bottom: 1px solid var(--border);
            white-space: nowrap;
        }
        thead th a { color: inherit; text-decoration: none; display: flex; align-items: center; gap: 6px; }
        thead th a:hover { color: var(--primary); }
        .sort-icon { font-size: 11px; }
        .sort-icon.active { color: var(--primary); }
        .sort-icon.neutral { opacity: 0.3; }
        tbody tr {
            border-bottom: 1px solid var(--border);
            transition: background 0.1s;
        }
        tbody tr:last-child { border-bottom: none; }
        tbody tr:hover { background: rgba(255,102,0,0.04); }
        tbody td { padding: 13px 16px; font-size: 14px; vertical-align: middle; }

        
        .user-cell { display: flex; align-items: center; gap: 10px; }
        .avatar {
            width: 36px; height: 36px; border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: 12px; font-weight: 700; color: white; flex-shrink: 0;
        }
        .user-name { font-weight: 600; font-size: 14px; }
        .user-id   { font-size: 11px; color: var(--muted); margin-top: 1px; }

        .email-cell { color: var(--muted); font-size: 13px; }
        .phone-cell { font-size: 13px; font-family: monospace; color: var(--text); }
        .phone-empty { color: var(--muted); font-style: italic; font-size: 12px; }

        .date-cell { font-size: 12px; color: var(--muted); }
        .date-cell strong { color: var(--text); font-size: 13px; display: block; }

        
        .badge-new {
            background: rgba(40,199,111,0.12); color: var(--success);
            font-size: 10px; padding: 2px 7px; border-radius: 4px;
            font-weight: 600; text-transform: uppercase; letter-spacing: .5px;
        }

        
        .btn-delete {
            background: none; border: 1px solid transparent;
            color: var(--muted); padding: 5px 10px; border-radius: 6px;
            font-size: 13px; cursor: pointer; transition: all 0.15s;
        }
        .btn-delete:hover { border-color: var(--danger); color: var(--danger); background: rgba(234,84,85,0.08); }

       
        .no-results { text-align: center; padding: 60px 20px; color: var(--muted); }
        .no-results .icon { font-size: 40px; margin-bottom: 12px; }

        
        .action-msg {
            padding: 12px 20px; border-radius: 8px; font-size: 14px;
            margin-bottom: 20px; display: flex; align-items: center; gap: 8px;
        }
        .action-msg.success { background: rgba(40,199,111,0.1); border-left: 3px solid var(--success); color: #6ee8a3; }
        .action-msg.error   { background: rgba(234,84,85,0.1);  border-left: 3px solid var(--danger);  color: #ff8a8a; }

       
        .pagination {
            padding: 16px 24px; border-top: 1px solid var(--border);
            display: flex; align-items: center; justify-content: space-between;
            font-size: 13px; color: var(--muted);
        }
        .page-links { display: flex; gap: 4px; }
        .page-link {
            padding: 5px 11px; border-radius: 6px; border: 1px solid var(--border);
            color: var(--muted); text-decoration: none; font-size: 13px; transition: all 0.15s;
        }
        .page-link:hover { border-color: var(--primary); color: var(--primary); }
        .page-link.active { background: var(--primary); border-color: var(--primary); color: white; font-weight: 600; }
        .page-link.disabled { opacity: 0.3; pointer-events: none; }

       
        .export-row {
            padding: 12px 24px; border-top: 1px solid var(--border);
            background: rgba(255,102,0,0.03);
            display: flex; align-items: center; gap: 10px; flex-wrap: wrap;
        }
        .btn-export {
            background: none; border: 1px solid var(--border); color: var(--muted);
            padding: 6px 14px; border-radius: 6px; font-size: 12px; cursor: pointer;
            transition: all 0.15s;
        }
        .btn-export:hover { border-color: var(--primary); color: var(--primary); }
        .export-label { font-size: 12px; color: var(--muted); margin-right: 4px; }

        
        #confirm-overlay {
            display: none; position: fixed; inset: 0;
            background: rgba(0,0,0,0.7); z-index: 500;
            align-items: center; justify-content: center;
        }
        #confirm-overlay.show { display: flex; }
        .confirm-card {
            background: var(--bg-card2); border: 1px solid var(--border);
            border-radius: 14px; padding: 32px; max-width: 380px; width: 90%;
            text-align: center;
        }
        .confirm-card .icon { font-size: 40px; margin-bottom: 12px; }
        .confirm-card h3 { font-size: 18px; margin-bottom: 8px; }
        .confirm-card p { font-size: 14px; color: var(--muted); margin-bottom: 24px; }
        .confirm-btns { display: flex; gap: 10px; }
        .btn-cancel { flex: 1; background: none; border: 1px solid var(--border); color: var(--muted); padding: 11px; border-radius: 8px; cursor: pointer; font-size: 14px; }
        .btn-confirm-del { flex: 1; background: var(--danger); color: white; border: none; padding: 11px; border-radius: 8px; cursor: pointer; font-size: 14px; font-weight: 700; }
    </style>
</head>
<body>


<div class="sidebar">
    <div class="sidebar-logo">
        🔧 AUTOPARTS
        <span>Panel Administrateur</span>
    </div>
    <nav class="sidebar-nav">
        <a class="nav-item active">
            <span class="icon">👥</span> Utilisateurs
        </a>
        <a href="index.php" class="nav-item">
            <span class="icon">🌐</span> Voir le site
        </a>
    </nav>
    <div class="sidebar-footer">
        <a href="?admin_logout=1">🚪 Déconnexion admin</a>
    </div>
</div>


<div class="main">

    
    <div class="topbar">
        <h1>Dashboard <span>Admin</span></h1>
        <div class="topbar-right">
            <div class="admin-badge"> Administrateur</div>
            <a href="?admin_logout=1" class="btn-logout">Déconnexion</a>
        </div>
    </div>

    
    <?php if ($actionMsg):
        [$type, $msg] = explode(':', $actionMsg, 2); ?>
    <div class="action-msg <?= $type ?>">
        <?= $type === 'success' ? '✅' : '❌' ?> <?= htmlspecialchars($msg) ?>
    </div>
    <?php endif; ?>

   
    <div class="stats-row">
        <div class="stat-card orange">
            <div class="stat-label">Total Comptes</div>
            <div class="stat-value"><?= $totalUsers ?></div>
            <div class="stat-sub">utilisateurs inscrits</div>
            <div class="stat-icon">👥</div>
        </div>
        <div class="stat-card green">
            <div class="stat-label">Ce mois-ci</div>
            <div class="stat-value"><?= $thisMonthUsers ?></div>
            <div class="stat-sub">nouvelles inscriptions</div>
            <div class="stat-icon">📅</div>
        </div>
        <div class="stat-card blue">
            <div class="stat-label">Aujourd'hui</div>
            <div class="stat-value"><?= $todayUsers ?></div>
            <div class="stat-sub">inscriptions du jour</div>
            <div class="stat-icon">⚡</div>
        </div>
    </div>

    
    <div class="table-section">
        <div class="table-header">
            <h2>Liste des <span>Utilisateurs</span>
                <?php if ($search): ?>
                <span style="font-size:14px;color:var(--muted);font-family:'Inter';font-weight:400;">
                    — <?= $totalCount ?> résultat(s) pour "<?= htmlspecialchars($search) ?>"
                </span>
                <?php endif; ?>
            </h2>
            <form class="search-form" method="GET" action="admin.php">
                <input type="text" name="search"
                       placeholder="🔍 Rechercher par nom, email..."
                       value="<?= htmlspecialchars($search) ?>">
                <button type="submit" class="btn-search">Chercher</button>
                <?php if ($search): ?>
                <a href="admin.php" class="btn-clear">✕ Effacer</a>
                <?php endif; ?>
            </form>
        </div>

        <?php if ($result && $result->num_rows > 0): ?>
        <div style="overflow-x:auto;">
        <table>
            <thead>
                <tr>
                    <th><a href="<?= sortUrl('id') ?>">ID <?= sortIcon('id') ?></a></th>
                    <th><a href="<?= sortUrl('nom') ?>">Utilisateur <?= sortIcon('nom') ?></a></th>
                    <th><a href="<?= sortUrl('email') ?>">Email <?= sortIcon('email') ?></a></th>
                    <th><a href="<?= sortUrl('telephone') ?>">Téléphone <?= sortIcon('telephone') ?></a></th>
                    <th><a href="<?= sortUrl('created_at') ?>">Inscription <?= sortIcon('created_at') ?></a></th>
                    <th style="text-align:center;">Action</th>
                </tr>
            </thead>
            <tbody>
            <?php
           
            $today = date('Y-m-d');
            while ($row = $result->fetch_assoc()):
                $color    = avatarColor($row['id']);
                $initials = initials($row['prenom'], $row['nom']);
                $dateObj  = new DateTime($row['created_at']);
                $isToday  = $dateObj->format('Y-m-d') === $today;
            ?>
            <tr>
                <td style="color:var(--muted);font-size:13px;font-family:monospace;">#<?= $row['id'] ?></td>
                <td>
                    <div class="user-cell">
                        <div class="avatar" style="background:<?= $color ?>;">
                            <?= $initials ?>
                        </div>
                        <div>
                            <div class="user-name"><?= htmlspecialchars($row['prenom'].' '.$row['nom']) ?></div>
                            <div class="user-id">ID <?= $row['id'] ?>
                                <?php if ($isToday): ?>
                                <span class="badge-new">Nouveau</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </td>
                <td class="email-cell">
                    <a href="mailto:<?= htmlspecialchars($row['email']) ?>"
                       style="color:var(--muted);text-decoration:none;"
                       onmouseover="this.style.color='var(--primary)'"
                       onmouseout="this.style.color='var(--muted)'">
                        <?= htmlspecialchars($row['email']) ?>
                    </a>
                </td>
                <td>
                    <?php if ($row['telephone']): ?>
                        <span class="phone-cell"><?= htmlspecialchars($row['telephone']) ?></span>
                    <?php else: ?>
                        <span class="phone-empty">— non renseigné</span>
                    <?php endif; ?>
                </td>
                <td class="date-cell">
                    <strong><?= $dateObj->format('d/m/Y') ?></strong>
                    <?= $dateObj->format('H:i') ?>
                </td>
                <td style="text-align:center;">
                    <button class="btn-delete"
                            onclick="confirmDelete(<?= $row['id'] ?>, '<?= htmlspecialchars($row['prenom'].' '.$row['nom'], ENT_QUOTES) ?>')">
                        🗑 Supprimer
                    </button>
                </td>
            </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
        </div>

        <div class="pagination">
            <span>
                Affichage <strong><?= ($offset+1) ?>–<?= min($offset+$perPage, $totalCount) ?></strong>
                sur <strong><?= $totalCount ?></strong> utilisateurs
            </span>
            <div class="page-links">
                <a href="<?= pageUrl(1) ?>"       class="page-link <?= $page<=1?'disabled':'' ?>">«</a>
                <a href="<?= pageUrl($page-1) ?>" class="page-link <?= $page<=1?'disabled':'' ?>">‹</a>
                <?php for ($i = max(1,$page-2); $i <= min($totalPages,$page+2); $i++): ?>
                <a href="<?= pageUrl($i) ?>" class="page-link <?= $i===$page?'active':'' ?>"><?= $i ?></a>
                <?php endfor; ?>
                <a href="<?= pageUrl($page+1) ?>" class="page-link <?= $page>=$totalPages?'disabled':'' ?>">›</a>
                <a href="<?= pageUrl($totalPages) ?>" class="page-link <?= $page>=$totalPages?'disabled':'' ?>">»</a>
            </div>
        </div>

        <div class="export-row">
            <span class="export-label">Exporter :</span>
            <button class="btn-export" onclick="exportCSV()">📄 CSV</button>
            <button class="btn-export" onclick="window.print()">🖨 Imprimer</button>
        </div>

        <?php else: ?>
        <div class="no-results">
            <div class="icon">🔍</div>
            <p><?= $search ? 'Aucun utilisateur trouvé pour "'.htmlspecialchars($search).'".' : 'Aucun utilisateur enregistré pour le moment.' ?></p>
        </div>
        <?php endif; ?>
    </div>

</div>

<div id="confirm-overlay">
    <div class="confirm-card">
        <div class="icon">⚠️</div>
        <h3>Confirmer la suppression</h3>
        <p id="confirm-text">Êtes-vous sûr de vouloir supprimer cet utilisateur ?<br>Cette action est irréversible.</p>
        <div class="confirm-btns">
            <button class="btn-cancel" onclick="closeConfirm()">Annuler</button>
            <a id="confirm-link" href="#" class="btn-confirm-del">Supprimer</a>
        </div>
    </div>
</div>

<script>
function confirmDelete(id, name) {
    document.getElementById('confirm-text').innerHTML =
        `Supprimer <strong>${name}</strong> (ID #${id}) ?<br><span style="color:#666;font-size:12px;">Cette action est irréversible.</span>`;
    document.getElementById('confirm-link').href = `admin.php?delete=${id}<?= $search ? '&search='.urlencode($search) : '' ?>`;
    document.getElementById('confirm-overlay').classList.add('show');
}
function closeConfirm() {
    document.getElementById('confirm-overlay').classList.remove('show');
}
document.getElementById('confirm-overlay').addEventListener('click', function(e) {
    if (e.target === this) closeConfirm();
});

function exportCSV() {
    const rows = [['ID','Prénom','Nom','Email','Téléphone','Date inscription']];
    document.querySelectorAll('tbody tr').forEach(tr => {
        const cells = tr.querySelectorAll('td');
        const id    = cells[0].textContent.trim().replace('#','');
        const name  = cells[1].querySelector('.user-name').textContent.trim().split(' ');
        const prenom= name[0] || '';
        const nom   = name.slice(1).join(' ');
        const email = cells[2].textContent.trim();
        const phone = cells[3].textContent.trim();
        const date  = cells[4].textContent.trim().replace(/\s+/g,' ');
        rows.push([id, prenom, nom, email, phone, date]);
    });
    const csv  = rows.map(r => r.map(c => `"${c.replace(/"/g,'""')}"`).join(',')).join('\n');
    const blob = new Blob(['\uFEFF'+csv], {type:'text/csv;charset=utf-8'});
    const a    = document.createElement('a');
    a.href     = URL.createObjectURL(blob);
    a.download = `autoparts_users_<?= date('Y-m-d') ?>.csv`;
    a.click();
}
</script>
</body>
</html>
