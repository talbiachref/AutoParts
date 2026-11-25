<?php
session_start();
include('connect.php');

if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$mode    = $_GET['mode'] ?? 'signin'; 
$error   = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'signin') {

    $email    = trim($_POST['email']);
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        $error = "Veuillez remplir tous les champs.";
    } else {
        // Requête préparée — protection contre les injections SQL
        $sql  = "SELECT id, nom, prenom, email, password FROM users WHERE email = ?";
        $stmt = $con->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            // Vérification du mot de passe hashé
            if (password_verify($password, $user['password'])) {
                // Connexion réussie → créer la session
                $_SESSION['user_id']    = $user['id'];
                $_SESSION['user_nom']   = $user['nom'];
                $_SESSION['user_prenom']= $user['prenom'];
                $_SESSION['user_email'] = $user['email'];
                $stmt->close();
                $con->close();
                header("Location: index.php?welcome=1");
                exit();
            } else {
                $error = "Mot de passe incorrect.";
            }
        } else {
            $error = "Aucun compte trouvé avec cet email.";
        }
        $stmt->close();
    }
    $mode = 'signin';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'signup') {

    $nom       = trim($_POST['nom']);
    $prenom    = trim($_POST['prenom']);
    $email     = trim($_POST['email']);
    $telephone = trim($_POST['telephone']);
    $password  = $_POST['password'];
    $confirm   = $_POST['confirm_password'];

    if (empty($nom) || empty($prenom) || empty($email) || empty($password)) {
        $error = "Veuillez remplir tous les champs obligatoires (*).";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Adresse email invalide.";
    } elseif (strlen($password) < 6) {
        $error = "Le mot de passe doit contenir au moins 6 caractères.";
    } elseif ($password !== $confirm) {
        $error = "Les mots de passe ne correspondent pas.";
    } else {
        
        $check = $con->prepare("SELECT id FROM users WHERE email = ?");
        $check->bind_param("s", $email);
        $check->execute();
        $check->store_result();

        if ($check->num_rows > 0) {
            $error = "Un compte existe déjà avec cet email.";
        } else {
            
            $hashed = password_hash($password, PASSWORD_DEFAULT);

            $sql  = "INSERT INTO users (nom, prenom, email, password, telephone) VALUES (?, ?, ?, ?, ?)";
            $stmt = $con->prepare($sql);
            $stmt->bind_param("sssss", $nom, $prenom, $email, $hashed, $telephone);

            if ($stmt->execute()) {
                $success = "Compte créé avec succès ! Vous pouvez maintenant vous connecter.";
                $mode    = 'signin';
            } else {
                $error = "Erreur lors de la création du compte : " . $stmt->error;
            }
            $stmt->close();
        }
        $check->close();
    }
    if (empty($success)) $mode = 'signup';
}

$con->close();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AutoParts Pro — <?= $mode === 'signin' ? 'Connexion' : 'Créer un compte' ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Oswald:wght@400;600;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary:      #ff6600;
            --primary-dark: #cc4f00;
            --bg-dark:      #111111;
            --bg-card:      #1c1c1c;
            --bg-input:     #242424;
            --text-main:    #f0f0f0;
            --text-muted:   #888;
            --border:       #333;
            --success:      #28c76f;
            --danger:       #ea5455;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Inter', sans-serif;
            background: var(--bg-dark);
            color: var(--text-main);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        header {
            background: var(--bg-card);
            border-bottom: 2px solid var(--primary);
            padding: 14px 30px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .logo {
            font-family: 'Oswald', sans-serif;
            font-size: 26px; font-weight: 700;
            color: var(--primary); letter-spacing: 2px;
            text-decoration: none;
        }
        .logo span { color: var(--text-main); }
        .back-link {
            color: var(--text-muted); font-size: 14px;
            text-decoration: none; transition: color 0.2s;
        }
        .back-link:hover { color: var(--primary); }

        main {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 20px;
            position: relative;
        }
        main::before {
            content: '';
            position: absolute; inset: 0;
            background: radial-gradient(ellipse at 50% 0%, rgba(255,102,0,0.08) 0%, transparent 65%);
            pointer-events: none;
        }

        .auth-card {
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: 16px;
            width: 100%;
            max-width: 460px;
            padding: 40px 36px;
            position: relative;
            z-index: 1;
        }
        .auth-card h1 {
            font-family: 'Oswald', sans-serif;
            font-size: 28px; font-weight: 700;
            color: white; margin-bottom: 6px;
        }
        .auth-card h1 span { color: var(--primary); }
        .auth-subtitle {
            font-size: 14px; color: var(--text-muted);
            margin-bottom: 30px;
        }

        .tabs {
            display: flex;
            background: var(--bg-input);
            border-radius: 8px;
            padding: 4px;
            margin-bottom: 28px;
        }
        .tab-btn {
            flex: 1; padding: 9px;
            background: none; border: none;
            color: var(--text-muted); font-size: 14px;
            font-weight: 600; cursor: pointer;
            border-radius: 6px; transition: all 0.2s;
            font-family: 'Inter', sans-serif;
        }
        .tab-btn.active {
            background: var(--primary);
            color: white;
        }

        .form-group {
            display: flex; flex-direction: column;
            gap: 6px; margin-bottom: 16px;
        }
        .form-group label {
            font-size: 13px; font-weight: 500;
            color: var(--text-muted);
        }
        .form-group input {
            background: var(--bg-input);
            border: 1px solid var(--border);
            color: var(--text-main);
            padding: 11px 14px;
            border-radius: 7px; font-size: 14px;
            outline: none;
            transition: border-color 0.2s;
            font-family: 'Inter', sans-serif;
        }
        .form-group input:focus { border-color: var(--primary); }
        .form-grid-2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
        }

        .input-wrap { position: relative; }
        .input-wrap input { width: 100%; padding-right: 42px; }
        .toggle-pw {
            position: absolute; right: 12px; top: 50%;
            transform: translateY(-50%);
            background: none; border: none;
            color: var(--text-muted); cursor: pointer;
            font-size: 16px; padding: 0;
        }
        .toggle-pw:hover { color: var(--primary); }

        .msg {
            padding: 12px 16px; border-radius: 8px;
            font-size: 14px; margin-bottom: 18px;
            display: flex; align-items: center; gap: 8px;
        }
        .msg.error   { background: rgba(234,84,85,0.12);  border-left: 3px solid var(--danger);  color: #ff8a8a; }
        .msg.success { background: rgba(40,199,111,0.12); border-left: 3px solid var(--success); color: #6ee8a3; }

        .btn-submit {
            width: 100%;
            background: var(--primary); color: white;
            border: none; padding: 13px;
            border-radius: 8px; font-size: 15px;
            font-weight: 700; cursor: pointer;
            transition: background 0.2s;
            font-family: 'Inter', sans-serif;
            margin-top: 6px;
        }
        .btn-submit:hover { background: var(--primary-dark); }

        .divider {
            text-align: center; margin: 20px 0;
            color: var(--text-muted); font-size: 13px;
            position: relative;
        }
        .divider::before, .divider::after {
            content: '';
            position: absolute; top: 50%;
            width: 40%; height: 1px;
            background: var(--border);
        }
        .divider::before { left: 0; }
        .divider::after  { right: 0; }

        .switch-link {
            text-align: center; font-size: 14px;
            color: var(--text-muted); margin-top: 20px;
        }
        .switch-link a {
            color: var(--primary); text-decoration: none;
            font-weight: 600;
        }
        .switch-link a:hover { text-decoration: underline; }

        .pw-strength { margin-top: 6px; }
        .pw-bar {
            height: 4px; border-radius: 4px;
            background: var(--border); overflow: hidden;
        }
        .pw-bar-fill {
            height: 100%; width: 0%;
            transition: width 0.3s, background 0.3s;
            border-radius: 4px;
        }
        .pw-label { font-size: 11px; color: var(--text-muted); margin-top: 4px; }

        footer {
            text-align: center; padding: 20px;
            font-size: 12px; color: var(--text-muted);
            border-top: 1px solid var(--border);
        }

        @media (max-width: 480px) {
            .auth-card { padding: 28px 20px; }
            .form-grid-2 { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>

<header>
    <a href="index.php" class="logo">AUTO<span>PARTS</span> PRO</a>
    <a href="index.php" class="back-link">← Retour au site</a>
</header>

<main>
    <div class="auth-card">

        <h1>AUTO<span>PARTS</span> PRO</h1>
        <p class="auth-subtitle">Votre spécialiste pièces aftermarket en Tunisie</p>

        <div class="tabs">
            <button class="tab-btn <?= $mode === 'signin' ? 'active' : '' ?>"
                    onclick="switchTab('signin')">
                🔑 Se connecter
            </button>
            <button class="tab-btn <?= $mode === 'signup' ? 'active' : '' ?>"
                    onclick="switchTab('signup')">
                ✨ Créer un compte
            </button>
        </div>

        <?php if ($error): ?>
            <div class="msg error">⚠ <?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="msg success">✅ <?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <div id="form-signin" style="display:<?= $mode === 'signin' ? 'block' : 'none' ?>;">
            <form method="POST" action="auth.php">
                <input type="hidden" name="action" value="signin">

                <div class="form-group">
                    <label>Adresse email *</label>
                    <input type="email" name="email" placeholder="exemple@email.com"
                           value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
                </div>

                <div class="form-group">
                    <label>Mot de passe *</label>
                    <div class="input-wrap">
                        <input type="password" name="password" id="pw-signin"
                               placeholder="Votre mot de passe" required>
                        <button type="button" class="toggle-pw"
                                onclick="togglePw('pw-signin', this)">👁</button>
                    </div>
                </div>

                <button type="submit" class="btn-submit">🔑 Se connecter</button>
            </form>

            <div class="divider">ou</div>
            <div class="switch-link">
                Pas encore de compte ?
                <a href="#" onclick="switchTab('signup')">Créer un compte gratuit</a>
            </div>
        </div>

        <div id="form-signup" style="display:<?= $mode === 'signup' ? 'block' : 'none' ?>;">
            <form method="POST" action="auth.php">
                <input type="hidden" name="action" value="signup">

                <div class="form-grid-2">
                    <div class="form-group">
                        <label>Nom *</label>
                        <input type="text" name="nom" placeholder="Ben Salem"
                               value="<?= htmlspecialchars($_POST['nom'] ?? '') ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Prénom *</label>
                        <input type="text" name="prenom" placeholder="Mohamed"
                               value="<?= htmlspecialchars($_POST['prenom'] ?? '') ?>" required>
                    </div>
                </div>

                <div class="form-group">
                    <label>Adresse email *</label>
                    <input type="email" name="email" placeholder="exemple@email.com"
                           value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
                </div>

                <div class="form-group">
                    <label>Téléphone</label>
                    <input type="tel" name="telephone" placeholder="+216 XX XXX XXX"
                           value="<?= htmlspecialchars($_POST['telephone'] ?? '') ?>">
                </div>

                <div class="form-group">
                    <label>Mot de passe * (min. 6 caractères)</label>
                    <div class="input-wrap">
                        <input type="password" name="password" id="pw-signup"
                               placeholder="Choisir un mot de passe"
                               oninput="checkStrength(this.value)" required>
                        <button type="button" class="toggle-pw"
                                onclick="togglePw('pw-signup', this)">👁</button>
                    </div>
                    
                    <div class="pw-strength">
                        <div class="pw-bar"><div class="pw-bar-fill" id="pw-bar-fill"></div></div>
                        <div class="pw-label" id="pw-label"></div>
                    </div>
                </div>

                <div class="form-group">
                    <label>Confirmer le mot de passe *</label>
                    <div class="input-wrap">
                        <input type="password" name="confirm_password" id="pw-confirm"
                               placeholder="Répéter le mot de passe" required>
                        <button type="button" class="toggle-pw"
                                onclick="togglePw('pw-confirm', this)">👁</button>
                    </div>
                </div>

                <button type="submit" class="btn-submit">✨ Créer mon compte</button>
            </form>

            <div class="switch-link" style="margin-top:20px;">
                Déjà inscrit ?
                <a href="#" onclick="switchTab('signin')">Se connecter</a>
            </div>
        </div>

    </div>
</main>

<footer>
    &copy; <?= date('Y') ?> AutoParts Pro — Tous droits réservés
</footer>

<script>

function switchTab(mode) {
    const signin = document.getElementById('form-signin');
    const signup = document.getElementById('form-signup');
    const tabs   = document.querySelectorAll('.tab-btn');

    if (mode === 'signin') {
        signin.style.display = 'block';
        signup.style.display = 'none';
        tabs[0].classList.add('active');
        tabs[1].classList.remove('active');
    } else {
        signin.style.display = 'none';
        signup.style.display = 'block';
        tabs[0].classList.remove('active');
        tabs[1].classList.add('active');
    }
}

function togglePw(inputId, btn) {
    const input = document.getElementById(inputId);
    if (input.type === 'password') {
        input.type = 'text';
    } else {
        input.type = 'password';
        btn.textContent = '👁';
    }
}

function checkStrength(value) {
    const fill  = document.getElementById('pw-bar-fill');
    const label = document.getElementById('pw-label');
    let score   = 0;

    if (value.length >= 6)  score++;
    if (value.length >= 10) score++;
    if (/[A-Z]/.test(value)) score++;
    if (/[0-9]/.test(value)) score++;
    if (/[^A-Za-z0-9]/.test(value)) score++;

    const levels = [
        { w: '0%',   color: 'transparent',      txt: '' },
        { w: '25%',  color: '#ea5455',           txt: '🔴 Très faible' },
        { w: '50%',  color: '#ff9f43',           txt: '🟠 Faible' },
        { w: '75%',  color: '#ffd700',           txt: '🟡 Moyen' },
        { w: '90%',  color: '#28c76f',           txt: '🟢 Fort' },
        { w: '100%', color: '#00e5a0',           txt: '✅ Très fort' },
    ];

    const lvl   = Math.min(score, 5);
    fill.style.width      = levels[lvl].w;
    fill.style.background = levels[lvl].color;
    label.textContent     = levels[lvl].txt;
}
</script>
</body>
</html>
