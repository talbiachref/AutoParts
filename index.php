<?php
session_start();

$isLoggedIn  = isset($_SESSION['user_id']);
$userNom     = $isLoggedIn ? htmlspecialchars($_SESSION['user_nom'])    : '';
$userPrenom  = $isLoggedIn ? htmlspecialchars($_SESSION['user_prenom']) : '';
$userEmail   = $isLoggedIn ? htmlspecialchars($_SESSION['user_email'])  : '';

$welcome = isset($_GET['welcome']) && $_GET['welcome'] == '1';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AutoParts Pro - Pièces Automobiles</title>
    <link href="https://fonts.googleapis.com/css2?family=Oswald:wght@400;600;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #ff6600;
            --primary-dark: #cc4f00;
            --bg-dark: #111111;
            --bg-card: #1c1c1c;
            --bg-input: #242424;
            --text-main: #f0f0f0;
            --text-muted: #888;
            --border: #333;
            --success: #28c76f;
            --danger: #ea5455;
            --warning: #ff9f43;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }
        html { scroll-behavior: smooth; }
        body { font-family: 'Inter', sans-serif; background: var(--bg-dark); color: var(--text-main); line-height: 1.6; }

        #toast-container { position: fixed; top: 20px; right: 20px; z-index: 9999; display: flex; flex-direction: column; gap: 10px; }
        .toast { background: var(--bg-card); border-left: 4px solid var(--primary); padding: 14px 20px; border-radius: 8px; min-width: 260px; font-size: 14px; color: var(--text-main); animation: slideIn 0.3s ease, fadeOut 0.4s ease 3.6s forwards; box-shadow: 0 4px 20px rgba(0,0,0,0.5); }
        .toast.success { border-color: var(--success); }
        .toast.danger  { border-color: var(--danger); }
        .toast.warning { border-color: var(--warning); }
        @keyframes slideIn { from { opacity:0; transform:translateX(40px); } to { opacity:1; transform:translateX(0); } }
        @keyframes fadeOut { to { opacity:0; transform:translateX(40px); } }

        header { background: var(--bg-card); border-bottom: 2px solid var(--primary); position: sticky; top: 0; z-index: 100; }
        .header-top { display: flex; align-items: center; justify-content: space-between; padding: 14px 30px; }
        .logo { font-family: 'Oswald', sans-serif; font-size: 28px; font-weight: 700; color: var(--primary); letter-spacing: 2px; }
        .logo span { color: var(--text-main); }
        .header-actions { display: flex; align-items: center; gap: 14px; }
        .search-bar { display: flex; align-items: center; background: var(--bg-input); border: 1px solid var(--border); border-radius: 6px; overflow: hidden; }
        .search-bar input { background: transparent; border: none; outline: none; color: var(--text-main); padding: 8px 14px; font-size: 14px; width: 220px; }
        .search-bar button { background: var(--primary); border: none; color: white; padding: 8px 14px; cursor: pointer; font-size: 14px; }
        #cart-btn { background: var(--primary); color: white; border: none; padding: 8px 18px; border-radius: 6px; cursor: pointer; font-weight: 600; font-size: 14px; display: flex; align-items: center; gap: 8px; transition: background 0.2s; }
        #cart-btn:hover { background: var(--primary-dark); }
        #cart-count { background: white; color: var(--primary); border-radius: 50%; width: 20px; height: 20px; display: flex; align-items: center; justify-content: center; font-size: 11px; font-weight: 700; }

        .auth-area { display: flex; align-items: center; gap: 10px; }

        .btn-signin {
            background: none;
            border: 1px solid var(--primary);
            color: var(--primary);
            padding: 7px 16px;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.2s;
            white-space: nowrap;
        }
        .btn-signin:hover { background: var(--primary); color: white; }

        .user-menu { position: relative; }
        .user-btn {
            display: flex; align-items: center; gap: 8px;
            background: rgba(255,102,0,0.1);
            border: 1px solid var(--primary);
            color: var(--text-main);
            padding: 7px 14px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 13px;
            font-weight: 500;
            transition: background 0.2s;
        }
        .user-btn:hover { background: rgba(255,102,0,0.2); }
        .user-avatar {
            width: 28px; height: 28px;
            background: var(--primary);
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: 12px; font-weight: 700; color: white;
        }
        .user-dropdown {
            display: none;
            position: absolute; top: calc(100% + 8px); right: 0;
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: 10px;
            min-width: 220px;
            box-shadow: 0 8px 32px rgba(0,0,0,0.5);
            z-index: 200;
            overflow: hidden;
        }
        .user-menu:hover .user-dropdown,
        .user-menu:focus-within .user-dropdown { display: block; }
        .dropdown-header {
            padding: 14px 16px;
            border-bottom: 1px solid var(--border);
            background: rgba(255,102,0,0.05);
        }
        .dropdown-header .d-name { font-weight: 600; font-size: 14px; color: white; }
        .dropdown-header .d-email { font-size: 12px; color: var(--text-muted); margin-top: 2px; }
        .dropdown-item {
            display: flex; align-items: center; gap: 10px;
            padding: 11px 16px;
            color: var(--text-muted);
            font-size: 14px;
            text-decoration: none;
            transition: background 0.15s, color 0.15s;
        }
        .dropdown-item:hover { background: rgba(255,102,0,0.08); color: var(--text-main); }
        .dropdown-item.logout { color: var(--danger); }
        .dropdown-item.logout:hover { background: rgba(234,84,85,0.08); }

        nav { background: #161616; border-top: 1px solid var(--border); }
        nav ul { list-style: none; display: flex; padding: 0 20px; }
        nav li a { display: block; padding: 12px 20px; color: var(--text-muted); text-decoration: none; font-size: 14px; font-weight: 500; letter-spacing: 0.5px; transition: color 0.2s; text-transform: uppercase; }
        nav li a:hover, nav li a.active { color: var(--primary); }
        nav li a.active { border-bottom: 2px solid var(--primary); }

        #hero { background: linear-gradient(135deg, #1a0a00 0%, #111 60%); padding: 80px 40px; text-align: center; border-bottom: 1px solid var(--border); position: relative; overflow: hidden; }
        #hero::before { content: ''; position: absolute; inset: 0; background: radial-gradient(ellipse at 50% 0%, rgba(255,102,0,0.12) 0%, transparent 70%); }
        #hero h1 { font-family: 'Oswald', sans-serif; font-size: 58px; font-weight: 700; color: white; line-height: 1.1; margin-bottom: 16px; position: relative; }
        #hero h1 span { color: var(--primary); }
        #hero p { font-size: 18px; color: var(--text-muted); margin-bottom: 30px; position: relative; }
        .hero-stats { display: flex; justify-content: center; gap: 40px; margin-top: 40px; position: relative; }
        .stat-box { text-align: center; padding: 16px 28px; background: rgba(255,255,255,0.04); border: 1px solid var(--border); border-radius: 10px; }
        .stat-box .num { font-family: 'Oswald', sans-serif; font-size: 32px; color: var(--primary); font-weight: 700; }
        .stat-box .lbl { font-size: 12px; color: var(--text-muted); text-transform: uppercase; letter-spacing: 1px; }

        .welcome-banner {
            background: rgba(40,199,111,0.1);
            border: 1px solid rgba(40,199,111,0.3);
            border-radius: 10px;
            padding: 14px 20px;
            margin: 20px 40px 0;
            display: flex; align-items: center; gap: 12px;
            font-size: 14px; color: #6ee8a3;
            position: relative; z-index: 1;
        }
        .welcome-banner strong { color: white; }
        .close-banner { margin-left: auto; background: none; border: none; color: var(--text-muted); cursor: pointer; font-size: 16px; }

        main { max-width: 1200px; margin: 0 auto; padding: 40px 20px; }
        section { margin-bottom: 60px; }
        h2 { font-family: 'Oswald', sans-serif; font-size: 30px; font-weight: 600; color: white; margin-bottom: 24px; padding-bottom: 10px; border-bottom: 2px solid var(--primary); display: flex; align-items: center; gap: 10px; }
        h3 { font-size: 16px; font-weight: 600; color: var(--text-main); margin: 20px 0 12px; }

        .catalogue-controls { display: flex; gap: 12px; flex-wrap: wrap; margin-bottom: 24px; align-items: center; }
        .filter-btn { background: var(--bg-card); border: 1px solid var(--border); color: var(--text-muted); padding: 7px 16px; border-radius: 20px; cursor: pointer; font-size: 13px; transition: all 0.2s; }
        .filter-btn:hover, .filter-btn.active { background: var(--primary); color: white; border-color: var(--primary); }
        .sort-select { margin-left: auto; background: var(--bg-input); border: 1px solid var(--border); color: var(--text-main); padding: 7px 12px; border-radius: 6px; font-size: 13px; cursor: pointer; }

        .products-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 16px; }
        .product-card { background: var(--bg-card); border: 1px solid var(--border); border-radius: 10px; padding: 20px; transition: border-color 0.2s, transform 0.2s; display: flex; flex-direction: column; }
        .product-card:hover { border-color: var(--primary); transform: translateY(-3px); }
        .product-card .badge { font-size: 10px; padding: 3px 8px; border-radius: 4px; font-weight: 600; text-transform: uppercase; display: inline-block; margin-bottom: 10px; }
        .badge-turbo       { background: rgba(255,102,0,0.15);  color: var(--primary); }
        .badge-echappement { background: rgba(234,84,85,0.15);  color: var(--danger); }
        .badge-suspension  { background: rgba(40,199,111,0.15); color: var(--success); }
        .badge-freinage    { background: rgba(255,159,67,0.15); color: var(--warning); }
        .badge-admission   { background: rgba(86,145,255,0.15); color: #5691ff; }
        .product-card .ref   { font-size: 11px; color: var(--text-muted); margin-bottom: 6px; }
        .product-card .name  { font-weight: 600; font-size: 15px; margin-bottom: 8px; color: white; }
        .product-card .price { font-family: 'Oswald', sans-serif; font-size: 22px; color: var(--primary); margin-bottom: 4px; }
        .product-card .stock { font-size: 12px; color: var(--text-muted); margin-bottom: 14px; flex: 1; }
        .stock-ok  { color: var(--success) !important; }
        .stock-low { color: var(--warning) !important; }
        .stock-out { color: var(--danger)  !important; }
        .add-cart-btn { background: var(--primary); color: white; border: none; padding: 9px; border-radius: 6px; cursor: pointer; font-weight: 600; font-size: 13px; width: 100%; transition: background 0.2s; }
        .add-cart-btn:hover { background: var(--primary-dark); }
        .add-cart-btn:disabled { background: var(--border); color: var(--text-muted); cursor: not-allowed; }

        #cart-overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.6); z-index: 500; }
        #cart-panel { position: fixed; right: -420px; top: 0; bottom: 0; width: 420px; background: var(--bg-card); border-left: 1px solid var(--border); z-index: 600; transition: right 0.3s ease; display: flex; flex-direction: column; }
        #cart-panel.open { right: 0; }
        .cart-header { padding: 20px; border-bottom: 1px solid var(--border); display: flex; align-items: center; justify-content: space-between; }
        .cart-header h3 { font-family: 'Oswald', sans-serif; font-size: 20px; color: var(--primary); }
        #close-cart { background: none; border: none; color: var(--text-muted); font-size: 22px; cursor: pointer; }
        #cart-items { flex: 1; overflow-y: auto; padding: 16px; }
        .cart-item { display: flex; align-items: center; gap: 12px; padding: 12px; background: var(--bg-input); border-radius: 8px; margin-bottom: 10px; }
        .cart-item .item-info { flex: 1; }
        .cart-item .item-name { font-size: 14px; font-weight: 500; }
        .cart-item .item-ref { font-size: 11px; color: var(--text-muted); }
        .cart-item .item-price { font-family: 'Oswald', sans-serif; color: var(--primary); font-size: 16px; }
        .qty-ctrl { display: flex; align-items: center; gap: 6px; }
        .qty-ctrl button { background: var(--border); color: white; border: none; width: 26px; height: 26px; border-radius: 4px; cursor: pointer; font-size: 16px; font-weight: 600; }
        .qty-ctrl span { font-size: 14px; min-width: 20px; text-align: center; }
        .remove-item { background: none; border: none; color: var(--danger); cursor: pointer; font-size: 16px; }
        .cart-footer { padding: 16px; border-top: 1px solid var(--border); }
        .cart-total { display: flex; justify-content: space-between; font-size: 18px; font-weight: 600; margin-bottom: 14px; }
        .cart-total span:last-child { color: var(--primary); font-family: 'Oswald', sans-serif; font-size: 22px; }
        #checkout-btn { width: 100%; background: var(--primary); color: white; border: none; padding: 14px; border-radius: 8px; font-size: 15px; font-weight: 700; cursor: pointer; transition: background 0.2s; }
        #checkout-btn:hover { background: var(--primary-dark); }
        #clear-cart { width: 100%; background: none; border: 1px solid var(--border); color: var(--text-muted); padding: 9px; border-radius: 8px; font-size: 13px; cursor: pointer; margin-top: 8px; }
        .empty-cart { text-align: center; padding: 40px; color: var(--text-muted); }

        .calculator { background: var(--bg-card); border: 1px solid var(--border); border-radius: 12px; padding: 28px; max-width: 560px; }
        .calc-row { display: flex; align-items: center; gap: 12px; margin-bottom: 16px; }
        .calc-row label { font-size: 14px; color: var(--text-muted); min-width: 140px; }
        .calc-row select, .calc-row input { flex: 1; background: var(--bg-input); border: 1px solid var(--border); color: var(--text-main); padding: 8px 12px; border-radius: 6px; font-size: 14px; }
        .calc-result { background: var(--bg-input); border-radius: 10px; padding: 16px 20px; margin-top: 10px; }
        .calc-result p { font-size: 14px; color: var(--text-muted); margin-bottom: 6px; }
        .calc-result .big { font-family: 'Oswald', sans-serif; font-size: 32px; color: var(--primary); }

        #commande { background: var(--bg-card); border: 1px solid var(--border); border-radius: 12px; padding: 32px; }
        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 24px; }
        .form-group { display: flex; flex-direction: column; gap: 6px; }
        .form-group.full { grid-column: 1/-1; }
        .form-group label { font-size: 13px; font-weight: 500; color: var(--text-muted); }
        .form-group input, .form-group select, .form-group textarea { background: var(--bg-input); border: 1px solid var(--border); color: var(--text-main); padding: 10px 14px; border-radius: 7px; font-size: 14px; outline: none; transition: border-color 0.2s; }
        .form-group input:focus, .form-group select:focus, .form-group textarea:focus { border-color: var(--primary); }
        .form-group input.valid   { border-color: var(--success); }
        .form-group input.invalid { border-color: var(--danger); }
        .form-error { font-size: 12px; color: var(--danger); min-height: 16px; }
        .form-group textarea { resize: vertical; min-height: 100px; }
        .step-title { font-family: 'Oswald', sans-serif; font-size: 18px; color: var(--primary); margin: 28px 0 16px; display: flex; align-items: center; gap: 10px; }
        .step-num { background: var(--primary); color: white; width: 26px; height: 26px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 13px; font-weight: 700; }
        .checkbox-group { display: flex; flex-wrap: wrap; gap: 10px; margin: 10px 0; }
        .checkbox-item { display: flex; align-items: center; gap: 7px; background: var(--bg-input); border: 1px solid var(--border); border-radius: 6px; padding: 8px 14px; cursor: pointer; transition: border-color 0.2s; }
        .checkbox-item:hover { border-color: var(--primary); }
        .checkbox-item input[type=checkbox] { accent-color: var(--primary); width: 14px; height: 14px; }
        .checkbox-item span { font-size: 13px; }
        .radio-group { display: flex; flex-direction: column; gap: 10px; margin: 10px 0; }
        .radio-item { display: flex; align-items: center; gap: 10px; background: var(--bg-input); border: 1px solid var(--border); border-radius: 8px; padding: 12px 16px; cursor: pointer; transition: border-color 0.2s; }
        .radio-item:hover { border-color: var(--primary); }
        .radio-item input[type=radio] { accent-color: var(--primary); }
        .radio-item .radio-desc { flex: 1; }
        .radio-item .radio-title { font-size: 14px; font-weight: 500; }
        .radio-item .radio-sub { font-size: 12px; color: var(--text-muted); }
        .radio-item .radio-price { font-family: 'Oswald', sans-serif; color: var(--primary); font-size: 15px; }
        .progress-bar-wrap { background: var(--bg-input); border-radius: 8px; overflow: hidden; height: 6px; margin-bottom: 20px; }
        .progress-bar { height: 100%; background: var(--primary); transition: width 0.4s ease; }
        .form-btns { display: flex; gap: 10px; margin-top: 24px; }
        .btn-submit { flex: 1; background: var(--primary); color: white; border: none; padding: 14px; border-radius: 8px; font-size: 15px; font-weight: 700; cursor: pointer; transition: background 0.2s; }
        .btn-submit:hover { background: var(--primary-dark); }
        .btn-reset { background: none; border: 1px solid var(--border); color: var(--text-muted); padding: 14px 22px; border-radius: 8px; font-size: 14px; cursor: pointer; }

        .contact-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 24px; }
        .contact-card { background: var(--bg-card); border: 1px solid var(--border); border-radius: 12px; padding: 24px; }
        .info-row { display: flex; gap: 12px; align-items: flex-start; margin-bottom: 16px; }
        .info-icon { width: 36px; height: 36px; background: rgba(255,102,0,0.1); border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 16px; flex-shrink: 0; }
        .info-text .lbl { font-size: 11px; color: var(--text-muted); text-transform: uppercase; letter-spacing: 1px; }
        .info-text .val { font-size: 14px; color: var(--text-main); }
        .table-wrap { overflow-x: auto; border-radius: 10px; border: 1px solid var(--border); }
        table { width: 100%; border-collapse: collapse; }
        thead th { background: rgba(255,102,0,0.1); color: var(--primary); padding: 12px 16px; text-align: left; font-size: 12px; text-transform: uppercase; letter-spacing: 1px; }
        tbody tr { border-top: 1px solid var(--border); transition: background 0.1s; }
        tbody tr:hover { background: rgba(255,102,0,0.05); }
        tbody td { padding: 12px 16px; font-size: 14px; }

        footer { background: var(--bg-card); border-top: 1px solid var(--border); padding: 40px; text-align: center; margin-top: 40px; }
        .footer-links a { color: var(--text-muted); text-decoration: none; margin: 0 12px; font-size: 14px; transition: color 0.2s; }
        .footer-links a:hover { color: var(--primary); }
        .footer-copy { margin-top: 16px; font-size: 12px; color: var(--text-muted); }

        #search-results { position: absolute; top: 100%; left: 0; right: 0; background: var(--bg-card); border: 1px solid var(--border); border-radius: 0 0 8px 8px; z-index: 200; display: none; }
        .search-wrap { position: relative; }
        .search-result-item { padding: 10px 14px; cursor: pointer; font-size: 14px; border-bottom: 1px solid var(--border); transition: background 0.1s; }
        .search-result-item:hover { background: rgba(255,102,0,0.08); }
        .search-result-item span { color: var(--text-muted); font-size: 12px; margin-left: 6px; }

        hr { border: none; border-top: 1px solid var(--border); margin: 40px 0; }
        a { color: var(--primary); }

        @media (max-width: 768px) {
            .form-grid { grid-template-columns: 1fr; }
            .contact-grid { grid-template-columns: 1fr; }
            .hero-stats { flex-wrap: wrap; gap: 12px; }
            #cart-panel { width: 100%; }
        }
    </style>
</head>
<body>

<div id="toast-container"></div>

<div id="cart-overlay" onclick="closeCart()"></div>
<div id="cart-panel">
    <div class="cart-header">
        <h3>🛒 Mon Panier</h3>
        <button id="close-cart" onclick="closeCart()">✕</button>
    </div>
    <div id="cart-items"></div>
    <div class="cart-footer">
        <div class="cart-total">
            <span>Total</span>
            <span id="cart-total-price">0 €</span>
        </div>
        <button id="checkout-btn" onclick="checkout()">✅ CONFIRMER LA COMMANDE</button>
        <button id="clear-cart" onclick="clearCart()">🗑 Vider le panier</button>
    </div>
</div>

<header>
    <div class="header-top">
        <div class="logo">AUTO<span>PARTS</span> PRO</div>
        <div class="header-actions">
            <div class="search-wrap">
                <div class="search-bar">
                    <input type="text" id="search-input" placeholder="Rechercher un produit..." autocomplete="off">
                    <button onclick="doSearch()">🔍</button>
                </div>
                <div id="search-results"></div>
            </div>
            <button id="cart-btn" onclick="openCart()">
                🛒 Panier
                <div id="cart-count">0</div>
            </button>

            <div class="auth-area">
                <?php if ($isLoggedIn): ?>
                    <!-- Utilisateur connecté → menu dropdown -->
                    <div class="user-menu" tabindex="0">
                        <div class="user-btn">
                            <div class="user-avatar">
                                <?= strtoupper(substr($userPrenom, 0, 1)) . strtoupper(substr($userNom, 0, 1)) ?>
                            </div>
                            <?= $userPrenom ?> <?= $userNom ?>
                            ▾
                        </div>
                        <div class="user-dropdown">
                            <div class="dropdown-header">
                                <div class="d-name">👤 <?= $userPrenom ?> <?= $userNom ?></div>
                                <div class="d-email"><?= $userEmail ?></div>
                            </div>
                            <a href="#commande" class="dropdown-item">🛒 Passer une commande</a>
                            <a href="#contact"  class="dropdown-item">📞 Nous contacter</a>
                            <a href="logout.php" class="dropdown-item logout">🚪 Se déconnecter</a>
                        </div>
                    </div>
                <?php else: ?>
                    <!-- Utilisateur non connecté → bouton Sign In -->
                    <a href="auth.php" class="btn-signin">🔑 Se connecter</a>
                <?php endif; ?>
            </div>

        </div>
    </div>
    <nav>
        <ul>
            <li><a href="#hero"      class="nav-link active">Accueil</a></li>
            <li><a href="#catalogue" class="nav-link">Catalogue</a></li>
            <li><a href="#commande"  class="nav-link">Commander</a></li>
            <li><a href="#contact"   class="nav-link">Contact</a></li>
        </ul>
    </nav>
</header>

<?php if ($isLoggedIn && $welcome): ?>
<div class="welcome-banner" id="welcome-banner">
    🎉 Bienvenue <strong><?= $userPrenom ?> <?= $userNom ?></strong> ! Vous êtes connecté à votre espace AutoParts Pro.
    <button class="close-banner" onclick="document.getElementById('welcome-banner').remove()">✕</button>
</div>
<?php endif; ?>

<section id="hero">
    <h1>Votre spécialiste<br><span>pièces aftermarket</span></h1>
    <p>Performance, fiabilité, livraison rapide partout en Tunisie</p>
    <div class="hero-stats">
        <div class="stat-box"><div class="num" data-count="500">0</div><div class="lbl">Produits</div></div>
        <div class="stat-box"><div class="num" data-count="1201">0</div><div class="lbl">Clients satisfaits</div></div>
        <div class="stat-box"><div class="num" data-count="8">0</div><div class="lbl">Années d'expérience</div></div>
        <div class="stat-box"><div class="num" data-count="24">0</div><div class="lbl">Livraison Express (h)</div></div>
    </div>
</section>

<main>
    <section id="catalogue">
        <h2>📦 Notre Catalogue</h2>
        <div class="catalogue-controls">
            <button class="filter-btn active" data-filter="all">Tous</button>
            <button class="filter-btn" data-filter="turbo">Turbo</button>
            <button class="filter-btn" data-filter="echappement">Échappement</button>
            <button class="filter-btn" data-filter="suspension">Suspension</button>
            <button class="filter-btn" data-filter="freinage">Freinage</button>
            <button class="filter-btn" data-filter="admission">Admission</button>
            <select class="sort-select" id="sort-select">
                <option value="default">Trier par défaut</option>
                <option value="price-asc">Prix croissant</option>
                <option value="price-desc">Prix décroissant</option>
                <option value="stock">Disponibilité</option>
            </select>
        </div>
        <div class="products-grid" id="products-grid"></div>
        <br>
        <h3>Comparatif Turbos</h3>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Marque</th><th>Modèle</th><th>Référence</th>
                        <th>Puissance Max</th><th>Prix</th><th>Action</th>
                    </tr>
                </thead>
                <tbody id="turbo-table"></tbody>
            </table>
        </div>
    </section>

    <hr>

    <section id="calculateur">
        <h2>💰 Calculateur de Budget</h2>
        <div class="calculator">
            <div class="calc-row"><label>Produit 1</label><select id="calc-p1" onchange="updateCalc()"><option value="0">-- Sélectionner --</option></select></div>
            <div class="calc-row"><label>Produit 2</label><select id="calc-p2" onchange="updateCalc()"><option value="0">-- Sélectionner --</option></select></div>
            <div class="calc-row"><label>Produit 3</label><select id="calc-p3" onchange="updateCalc()"><option value="0">-- Sélectionner --</option></select></div>
            <div class="calc-row">
                <label>Livraison</label>
                <select id="calc-livraison" onchange="updateCalc()">
                    <option value="0">Standard (Gratuite)</option>
                    <option value="15">Express 2-3j (+15€)</option>
                    <option value="35">Urgente 24h (+35€)</option>
                </select>
            </div>
            <div class="calc-row"><label>Code promo</label><input type="text" id="promo-code" placeholder="Ex: PROMO10" oninput="updateCalc()"></div>
            <div class="calc-result">
                <p>Total estimé (TVA incluse)</p>
                <div class="big" id="calc-total">0 €</div>
                <div id="promo-msg" style="font-size:12px;color:var(--success);margin-top:6px;"></div>
            </div>
        </div>
    </section>

    <hr>

    <section id="commande">
        <h2>🛒 Passer une Commande</h2>
        <?php if (!$isLoggedIn): ?>
        <div style="background:rgba(255,102,0,0.08);border:1px solid rgba(255,102,0,0.3);border-radius:10px;padding:14px 20px;margin-bottom:20px;font-size:14px;color:var(--text-muted);">
            💡 <a href="auth.php" style="color:var(--primary);font-weight:600;">Connectez-vous</a> pour pré-remplir automatiquement vos informations de commande.
        </div>
        <?php endif; ?>

        <div class="progress-bar-wrap"><div class="progress-bar" id="form-progress" style="width:0%"></div></div>

        <form id="order-form" novalidate>
            <div class="step-title"><span class="step-num">1</span> Informations Client</div>
            <div class="form-grid">
                <div class="form-group">
                    <label>Nom *</label>
                    <input type="text" id="nom" name="nom" placeholder="Votre nom"
                           value="<?= $isLoggedIn ? $userNom : '' ?>" required>
                    <div class="form-error" id="err-nom"></div>
                </div>
                <div class="form-group">
                    <label>Prénom *</label>
                    <input type="text" id="prenom" name="prenom" placeholder="Votre prénom"
                           value="<?= $isLoggedIn ? $userPrenom : '' ?>" required>
                    <div class="form-error" id="err-prenom"></div>
                </div>
                <div class="form-group">
                    <label>Email *</label>
                    <input type="email" id="email" name="email" placeholder="exemple@email.com"
                           value="<?= $isLoggedIn ? $userEmail : '' ?>" required>
                    <div class="form-error" id="err-email"></div>
                </div>
                <div class="form-group">
                    <label>Téléphone</label>
                    <input type="tel" id="telephone" name="telephone" placeholder="+216 XX XXX XXX">
                    <div class="form-error" id="err-telephone"></div>
                </div>
                <div class="form-group full">
                    <label>Adresse</label>
                    <textarea id="adresse" name="adresse" placeholder="Votre adresse complète"></textarea>
                </div>
            </div>

            <div class="step-title"><span class="step-num">2</span> Informations Véhicule</div>
            <div class="form-grid">
                <div class="form-group">
                    <label>Marque</label>
                    <select id="marque" name="marque" onchange="updateModeles()">
                        <option value="">-- Choisir --</option>
                        <option value="bmw">BMW</option><option value="audi">Audi</option>
                        <option value="mercedes">Mercedes</option><option value="volkswagen">Volkswagen</option>
                        <option value="honda">Honda</option><option value="toyota">Toyota</option>
                        <option value="nissan">Nissan</option><option value="mazda">Mazda</option>
                        <option value="subaru">Subaru</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Modèle</label>
                    <select id="modele" name="modele"><option value="">-- Choisir la marque d'abord --</option></select>
                </div>
                <div class="form-group">
                    <label>Année</label>
                    <input type="number" id="annee" name="annee" min="1980" max="2026" placeholder="2020">
                    <div class="form-error" id="err-annee"></div>
                </div>
                <div class="form-group">
                    <label>Cylindrée (L)</label>
                    <input type="text" id="cylindree" name="cylindree" placeholder="Ex: 2.0, 3.5">
                </div>
            </div>

            <div class="step-title"><span class="step-num">3</span> Catégories souhaitées</div>
            <div class="checkbox-group">
                <label class="checkbox-item"><input type="checkbox" name="categories" value="turbo"><span>🔥 Turbo / Compresseur</span></label>
                <label class="checkbox-item"><input type="checkbox" name="categories" value="echappement"><span>💨 Échappement</span></label>
                <label class="checkbox-item"><input type="checkbox" name="categories" value="suspension"><span>🔧 Suspension</span></label>
                <label class="checkbox-item"><input type="checkbox" name="categories" value="freinage"><span>🛑 Freinage</span></label>
                <label class="checkbox-item"><input type="checkbox" name="categories" value="admission"><span>🌀 Admission d'air</span></label>
                <label class="checkbox-item"><input type="checkbox" name="categories" value="accessoires"><span>⚡ Accessoires</span></label>
            </div>

            <div class="step-title"><span class="step-num">4</span> Type de livraison</div>
            <div class="radio-group">
                <label class="radio-item">
                    <input type="radio" name="livraison" value="standard" checked>
                    <div class="radio-desc"><div class="radio-title">Standard</div><div class="radio-sub">5-7 jours ouvrés</div></div>
                    <div class="radio-price">Gratuit</div>
                </label>
                <label class="radio-item">
                    <input type="radio" name="livraison" value="express">
                    <div class="radio-desc"><div class="radio-title">Express</div><div class="radio-sub">2-3 jours ouvrés</div></div>
                    <div class="radio-price">+15€</div>
                </label>
                <label class="radio-item">
                    <input type="radio" name="livraison" value="urgente">
                    <div class="radio-desc"><div class="radio-title">Urgente</div><div class="radio-sub">Livraison en 24h</div></div>
                    <div class="radio-price">+35€</div>
                </label>
            </div>

            <div class="step-title"><span class="step-num">5</span> Commentaires</div>
            <div class="form-group">
                <textarea id="commentaires" name="commentaires" placeholder="Questions, demandes spéciales..."></textarea>
                <div style="font-size:12px;color:var(--text-muted);margin-top:4px;" id="char-count">0 / 500 caractères</div>
            </div>

            <div class="step-title"><span class="step-num">6</span> Conditions</div>
            <label class="checkbox-item" style="margin-bottom:10px;">
                <input type="checkbox" id="conditions" name="conditions" required>
                <span>J'accepte les <a href="#">conditions générales de vente</a> *</span>
            </label>
            <br>
            <label class="checkbox-item">
                <input type="checkbox" id="newsletter" name="newsletter">
                <span>Je souhaite recevoir la newsletter et les offres spéciales</span>
            </label>

            <div class="form-btns">
                <button type="submit" class="btn-submit">✅ VALIDER LA COMMANDE</button>
                <button type="button" class="btn-reset" onclick="resetForm()">🔄 Réinitialiser</button>
            </div>
        </form>
    </section>

    <hr>

    <section id="contact">
        <h2>📞 Nous Contacter</h2>
        <div class="contact-grid">
            <div class="contact-card">
                <h3 style="margin-bottom:20px;">Coordonnées</h3>
                <div class="info-row"><div class="info-icon">📍</div><div class="info-text"><div class="lbl">Adresse</div><div class="val">126 Avenue Habib Bourgiba, 3220 Ghomrassen, Tunisie</div></div></div>
                <div class="info-row"><div class="info-icon">📞</div><div class="info-text"><div class="lbl">Téléphone</div><div class="val"><a href="tel:+21625525554">+216 25 525 554</a></div></div></div>
                <div class="info-row"><div class="info-icon">✉️</div><div class="info-text"><div class="lbl">Email</div><div class="val"><a href="mailto:achraftalbi609@gmail.com">achraftalbi609@gmail.com</a></div></div></div>
                <div class="info-row"><div class="info-icon">🕐</div><div class="info-text"><div class="lbl">Horaires</div><div class="val">Lun–Ven: 9h00–18h00<br>Sam: 10h00–16h00<br>Dim: Fermé</div></div></div>
            </div>
            <div class="contact-card">
                <h3 style="margin-bottom:20px;">Message Rapide</h3>
                <form id="contact-form" novalidate>
                    <div class="form-group" style="margin-bottom:12px;"><label>Nom</label><input type="text" id="c-nom" placeholder="Votre nom" value="<?= $isLoggedIn ? "$userPrenom $userNom" : '' ?>" required></div>
                    <div class="form-group" style="margin-bottom:12px;"><label>Email</label><input type="email" id="c-email" placeholder="exemple@email.com" value="<?= $isLoggedIn ? $userEmail : '' ?>" required></div>
                    <div class="form-group" style="margin-bottom:12px;"><label>Sujet</label><select id="c-sujet"><option value="info">Demande d'information</option><option value="commande">Suivi de commande</option><option value="technique">Question technique</option><option value="reclamation">Réclamation</option></select></div>
                    <div class="form-group" style="margin-bottom:16px;"><label>Message</label><textarea id="c-message" rows="4" placeholder="Votre message..." required></textarea></div>
                    <button type="submit" class="btn-submit" style="width:100%;">📧 ENVOYER</button>
                </form>
            </div>
        </div>
    </section>
</main>

<footer>
    <div class="footer-links">
        <a href="https://www.facebook.com/achref.talbi.7330" target="_blank">Facebook</a>
        <a href="https://instagram.com" target="_blank">Instagram</a>
        <a href="https://youtube.com" target="_blank">YouTube</a>
        <a href="https://twitter.com" target="_blank">Twitter</a>
    </div>
    <div class="footer-copy">
        © <?= date('Y') ?> AutoParts Pro — Tous droits réservés
        <?php if ($isLoggedIn): ?>
        | Connecté en tant que <strong style="color:var(--primary);"><?= $userPrenom ?> <?= $userNom ?></strong>
        | <a href="logout.php" style="color:var(--danger);">Déconnexion</a>
        <?php endif; ?>
    </div>
</footer>
<script>
<?php if ($isLoggedIn && $welcome): ?>
window.addEventListener('load', () => {
    showToast('🎉 Bienvenue <?= $userPrenom ?> ! Vous êtes connecté.', 'success');
});
<?php endif; ?>

const PRODUCTS = [
    { ref:'TRB001', name:'Turbo GT3582R',            cat:'turbo',        price:1299, stock:5  },
    { ref:'GTX001', name:'Turbo Garrett GTX3076R',   cat:'turbo',        price:1599, stock:2  },
    { ref:'EFR001', name:'BorgWarner EFR 7670',      cat:'turbo',        price:1749, stock:3  },
    { ref:'PT6001', name:'Precision PT6266',          cat:'turbo',        price:1899, stock:1  },
    { ref:'EXH002', name:'Ligne Akrapovic',           cat:'echappement',  price:2499, stock:3  },
    { ref:'MAG001', name:'Silencieux Magnaflow',      cat:'echappement',  price:799,  stock:7  },
    { ref:'DWN001', name:'Downpipe Inox 3"',          cat:'echappement',  price:349,  stock:10 },
    { ref:'SUS003', name:'Coilovers BC Racing',       cat:'suspension',   price:899,  stock:8  },
    { ref:'ROT001', name:'Kit Compresseur Rotrex',   cat:'turbo',        price:2200, stock:2  },
    { ref:'BRK004', name:'Kit Brembo 6 pistons',     cat:'freinage',     price:3499, stock:2  },
    { ref:'FIL005', name:'Filtre K&N Sport',          cat:'admission',    price:89,   stock:25 },
    { ref:'AEM001', name:'Kit Admission AEM',         cat:'admission',    price:249,  stock:12 },
];
const TURBOS_COMPARE = [
    { marque:'Garrett',    modele:'GTX3076R',  ref:'856801-5004S', hp:600, price:1599 },
    { marque:'Precision',  modele:'PT6266',    ref:'CEA-62',       hp:750, price:1899 },
    { marque:'BorgWarner', modele:'EFR 7670',  ref:'179391',       hp:650, price:1749 },
];
const MODELES = {
    bmw:['M3 E46','M3 E92','M5 F10','135i E82','335i F30'],
    audi:['S3 8V','RS3 8Y','TT RS 8S','A4 B8 TFSI','RS5 B9'],
    mercedes:['C63 AMG W204','A45 AMG W176','E55 AMG W211','CLA 45 AMG'],
    volkswagen:['Golf GTI','Golf R','Scirocco','Polo GTI','Passat CC'],
    honda:['Civic Type R FK8','S2000 AP1','Accord 2.2 VTEC','Integra DC5'],
    toyota:['Supra A90','GR Yaris','86 ZN6','Corolla AE86'],
    nissan:['GT-R R35','370Z Z34','350Z Z33','Silvia S15','Skyline R34'],
    mazda:['RX-7 FD3S','RX-8 SE3P','MX-5 ND','Speed3'],
    subaru:['WRX STI','Impreza WRX','BRZ ZC6','Legacy GT'],
};
const PROMO_CODES = { 'PROMO10':10, 'AUTOPARTS20':20, 'SUMMER15':15 };

let cart = JSON.parse(localStorage.getItem('ap_cart') || '[]');
function saveCart() { localStorage.setItem('ap_cart', JSON.stringify(cart)); }

function addToCart(ref) {
    const p = PRODUCTS.find(x => x.ref === ref);
    if (!p) return;
    const existing = cart.find(x => x.ref === ref);
    if (existing) { existing.qty++; } else { cart.push({ ...p, qty: 1 }); }
    saveCart(); renderCart(); updateCartCount();
    showToast(`"${p.name}" ajouté au panier`, 'success');
}
function removeFromCart(ref) { cart = cart.filter(x => x.ref !== ref); saveCart(); renderCart(); updateCartCount(); }
function changeQty(ref, delta) { const item = cart.find(x => x.ref === ref); if (!item) return; item.qty = Math.max(1, item.qty + delta); saveCart(); renderCart(); updateCartCount(); }
function clearCart() { cart = []; saveCart(); renderCart(); updateCartCount(); showToast('Panier vidé', 'warning'); }
function updateCartCount() { document.getElementById('cart-count').textContent = cart.reduce((s,x) => s+x.qty, 0); }

function renderCart() {
    const el = document.getElementById('cart-items');
    if (!cart.length) { el.innerHTML = '<div class="empty-cart">🛒<br>Votre panier est vide</div>'; document.getElementById('cart-total-price').textContent = '0 €'; return; }
    let html = '', total = 0;
    cart.forEach(item => {
        const sub = item.price * item.qty; total += sub;
        html += `<div class="cart-item"><div class="item-info"><div class="item-name">${item.name}</div><div class="item-ref">${item.ref}</div><div class="item-price">${sub.toLocaleString('fr-FR')} €</div></div><div class="qty-ctrl"><button onclick="changeQty('${item.ref}',-1)">−</button><span>${item.qty}</span><button onclick="changeQty('${item.ref}',1)">+</button></div><button class="remove-item" onclick="removeFromCart('${item.ref}')">🗑</button></div>`;
    });
    el.innerHTML = html;
    document.getElementById('cart-total-price').textContent = total.toLocaleString('fr-FR') + ' €';
}
function openCart()  { document.getElementById('cart-panel').classList.add('open');    document.getElementById('cart-overlay').style.display = 'block'; }
function closeCart() { document.getElementById('cart-panel').classList.remove('open'); document.getElementById('cart-overlay').style.display = 'none'; }
function checkout()  { if (!cart.length) { showToast('Votre panier est vide !', 'danger'); return; } closeCart(); showToast('Commande confirmée ! Merci 🎉', 'success'); cart=[]; saveCart(); renderCart(); updateCartCount(); document.getElementById('order-form').scrollIntoView({behavior:'smooth'}); }

let currentFilter = 'all', currentSort = 'default';

function renderProducts() {
    let list = PRODUCTS.filter(p => currentFilter === 'all' || p.cat === currentFilter);
    if (currentSort === 'price-asc')  list.sort((a,b) => a.price - b.price);
    else if (currentSort === 'price-desc') list.sort((a,b) => b.price - a.price);
    else if (currentSort === 'stock') list.sort((a,b) => b.stock - a.stock);
    const grid = document.getElementById('products-grid');
    grid.innerHTML = list.map(p => {
        const sc = p.stock===0?'stock-out':p.stock<=3?'stock-low':'stock-ok';
        const sl = p.stock===0?'❌ Rupture de stock':p.stock<=3?`⚠️ Plus que ${p.stock} en stock`:`✅ En stock (${p.stock})`;
        const dis = p.stock===0 ? 'disabled' : '';
        const lbl = p.stock===0 ? 'Indisponible' : '🛒 Ajouter au panier';
        return `<div class="product-card" data-cat="${p.cat}"><div class="badge badge-${p.cat}">${p.cat}</div><div class="ref">${p.ref}</div><div class="name">${p.name}</div><div class="price">${p.price.toLocaleString('fr-FR')} €</div><div class="stock ${sc}">${sl}</div><button class="add-cart-btn" onclick="addToCart('${p.ref}')" ${dis}>${lbl}</button></div>`;
    }).join('');
    if (!list.length) grid.innerHTML = '<p style="color:var(--text-muted);padding:20px;">Aucun produit trouvé.</p>';
}

function renderTurboTable() {
    document.getElementById('turbo-table').innerHTML = TURBOS_COMPARE.map(t => {
        const match = PRODUCTS.find(p => p.name.includes(t.marque) || p.name.includes(t.modele));
        const ref = match ? match.ref : 'GTX001';
        return `<tr><td><strong>${t.marque}</strong></td><td>${t.modele}</td><td style="color:var(--text-muted);font-family:monospace;">${t.ref}</td><td><span style="color:var(--primary);font-weight:600;">${t.hp} HP</span></td><td><strong>${t.price.toLocaleString('fr-FR')} €</strong></td><td><button class="add-cart-btn" style="width:auto;padding:6px 14px;" onclick="addToCart('${ref}')">+ Panier</button></td></tr>`;
    }).join('');
}

document.querySelectorAll('.filter-btn').forEach(btn => {
    btn.addEventListener('click', () => {
        document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
        btn.classList.add('active'); currentFilter = btn.dataset.filter; renderProducts();
    });
});
document.getElementById('sort-select').addEventListener('change', e => { currentSort = e.target.value; renderProducts(); });

const searchInput   = document.getElementById('search-input');
const searchResults = document.getElementById('search-results');
searchInput.addEventListener('input', () => {
    const q = searchInput.value.trim().toLowerCase();
    if (!q) { searchResults.style.display = 'none'; return; }
    const matches = PRODUCTS.filter(p => p.name.toLowerCase().includes(q)||p.ref.toLowerCase().includes(q)||p.cat.toLowerCase().includes(q));
    if (!matches.length) { searchResults.style.display = 'none'; return; }
    searchResults.innerHTML = matches.slice(0,6).map(p => `<div class="search-result-item" onclick="selectSearchResult('${p.ref}')">${p.name} <span>${p.ref} — ${p.price}€</span></div>`).join('');
    searchResults.style.display = 'block';
});
document.addEventListener('click', e => { if (!e.target.closest('.search-wrap')) searchResults.style.display = 'none'; });

function doSearch() {
    const q = searchInput.value.trim().toLowerCase();
    if (!q) return;
    currentFilter = 'all';
    document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
    document.querySelector('[data-filter="all"]').classList.add('active');
    renderProducts();
    document.getElementById('catalogue').scrollIntoView({behavior:'smooth'});
    searchResults.style.display = 'none';
    const items = document.getElementById('products-grid').querySelectorAll('.product-card');
    items.forEach(card => {
        const name = card.querySelector('.name').textContent.toLowerCase();
        const ref  = card.querySelector('.ref').textContent.toLowerCase();
        card.style.display = (name.includes(q)||ref.includes(q)) ? '' : 'none';
    });
}

function selectSearchResult(ref) {
    searchResults.style.display = 'none';
    const p = PRODUCTS.find(x => x.ref === ref);
    if (!p) return;
    searchInput.value = p.name;
    currentFilter = 'all'; renderProducts();
    document.getElementById('catalogue').scrollIntoView({behavior:'smooth'});
    setTimeout(() => {
        document.querySelectorAll('.product-card').forEach(c => {
            c.style.display = c.querySelector('.ref').textContent === ref ? '' : 'none';
        });
    }, 100);
}

function populateCalcSelects() {
    ['calc-p1','calc-p2','calc-p3'].forEach(id => {
        const sel = document.getElementById(id);
        PRODUCTS.forEach(p => { const opt = document.createElement('option'); opt.value=p.price; opt.textContent=`${p.name} (${p.price}€)`; sel.appendChild(opt); });
    });
}

function updateCalc() {
    const p1=+document.getElementById('calc-p1').value||0, p2=+document.getElementById('calc-p2').value||0, p3=+document.getElementById('calc-p3').value||0, liv=+document.getElementById('calc-livraison').value||0;
    const code=document.getElementById('promo-code').value.trim().toUpperCase();
    let sub=p1+p2+p3+liv, discount=0;
    const promoMsg=document.getElementById('promo-msg');
    if (code && PROMO_CODES[code]) { discount=Math.round(sub*PROMO_CODES[code]/100); promoMsg.textContent=`✅ Code "${code}" appliqué : -${PROMO_CODES[code]}% (−${discount}€)`; promoMsg.style.color='var(--success)'; }
    else if (code) { promoMsg.textContent='❌ Code promo invalide'; promoMsg.style.color='var(--danger)'; }
    else { promoMsg.textContent=''; }
    document.getElementById('calc-total').textContent = Math.max(0,sub-discount).toLocaleString('fr-FR')+' €';
}

function updateModeles() {
    const marque=document.getElementById('marque').value, sel=document.getElementById('modele');
    sel.innerHTML='<option value="">-- Sélectionner --</option>';
    if (marque && MODELES[marque]) MODELES[marque].forEach(m => { const opt=document.createElement('option'); opt.value=m.toLowerCase().replace(/ /g,'_'); opt.textContent=m; sel.appendChild(opt); });
}

function validateField(id,rule,errId,msg) {
    const el=document.getElementById(id), err=document.getElementById(errId);
    if (!el) return true;
    const val=el.value.trim();
    if (!rule(val)) { el.className='invalid'; if(err) err.textContent=msg; return false; }
    el.className='valid'; if(err) err.textContent=''; return true;
}

function updateProgress() {
    const allInputs=document.querySelectorAll('#order-form input:not([type=radio]):not([type=checkbox]),#order-form select,#order-form textarea');
    let filled=0; allInputs.forEach(el => { if(el.value.trim()) filled++; });
    document.getElementById('form-progress').style.width=Math.min(100,Math.round(filled/allInputs.length*100))+'%';
}

document.getElementById('commentaires').addEventListener('input', function() {
    const len=this.value.length, el=document.getElementById('char-count');
    el.textContent=`${len} / 500 caractères`;
    if(len>450) el.style.color='var(--warning)';
    if(len>=500){this.value=this.value.slice(0,500);el.style.color='var(--danger)';} else el.style.color='var(--text-muted)';
    updateProgress();
});

['nom','prenom','email','telephone','annee'].forEach(id => {
    const el=document.getElementById(id);
    if(el){ el.addEventListener('input',updateProgress); el.addEventListener('blur',()=>liveValidate(id)); }
});

function liveValidate(id) {
    const rules={nom:v=>v.length>=2,prenom:v=>v.length>=2,email:v=>/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v),telephone:v=>!v||/^[\d\s+\-().]{7,}$/.test(v),annee:v=>!v||(+v>=1980&&+v<=2026)};
    const msgs={nom:'Nom trop court (min 2 caractères)',prenom:'Prénom trop court',email:'Email invalide',telephone:'Téléphone invalide',annee:'Année entre 1980 et 2026'};
    if(rules[id]) validateField(id,rules[id],`err-${id}`,msgs[id]);
}

document.getElementById('order-form').addEventListener('submit', e => {
    e.preventDefault();
    let valid=true;
    valid &= validateField('nom',v=>v.length>=2,'err-nom','Nom requis (min 2 caractères)');
    valid &= validateField('prenom',v=>v.length>=2,'err-prenom','Prénom requis');
    valid &= validateField('email',v=>/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v),'err-email','Email invalide');
    const av=document.getElementById('annee').value;
    if(av) valid &= validateField('annee',v=>+v>=1980&&+v<=2026,'err-annee','Année entre 1980 et 2026');
    if(!document.getElementById('conditions').checked){showToast('Veuillez accepter les conditions générales','danger');valid=false;}
    if(!valid){showToast('Veuillez corriger les erreurs','danger');return;}
    showToast('✅ Commande envoyée avec succès ! Vous recevrez une confirmation par email.','success');
    document.getElementById('form-progress').style.width='100%';
    setTimeout(()=>{document.getElementById('order-form').reset();updateProgress();},1500);
});

function resetForm() {
    document.getElementById('order-form').reset();
    document.querySelectorAll('#order-form input').forEach(el=>el.className='');
    document.querySelectorAll('.form-error').forEach(el=>el.textContent='');
    document.getElementById('char-count').textContent='0 / 500 caractères';
    updateProgress(); showToast('Formulaire réinitialisé','warning');
}

document.getElementById('contact-form').addEventListener('submit', e => {
    e.preventDefault();
    const nom=document.getElementById('c-nom').value.trim(), email=document.getElementById('c-email').value.trim(), msg=document.getElementById('c-message').value.trim();
    if(!nom||!email||!msg){showToast('Veuillez remplir tous les champs requis','danger');return;}
    if(!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)){showToast('Email invalide','danger');return;}
    showToast('Message envoyé ! Nous vous répondrons dans les 24h.','success');
    document.getElementById('contact-form').reset();
});

function showToast(msg,type='success') {
    const t=document.createElement('div'); t.className=`toast ${type}`; t.textContent=msg;
    document.getElementById('toast-container').appendChild(t);
    setTimeout(()=>t.remove(),4000);
}

const sections=['hero','catalogue','commande','contact'];
window.addEventListener('scroll',()=>{
    let current='hero';
    sections.forEach(id=>{const el=document.getElementById(id);if(el&&window.scrollY>=el.offsetTop-100) current=id;});
    document.querySelectorAll('.nav-link').forEach(a=>a.classList.toggle('active',a.getAttribute('href')==='#'+current));
});

function animateCounters() {
    document.querySelectorAll('[data-count]').forEach(el=>{
        const target=+el.dataset.count; let current=0; const step=Math.ceil(target/60);
        const timer=setInterval(()=>{current=Math.min(current+step,target);el.textContent=current.toLocaleString('fr-FR');if(current>=target) clearInterval(timer);},16);
    });
}

renderProducts(); renderTurboTable(); renderCart(); updateCartCount(); populateCalcSelects(); updateProgress(); animateCounters();
document.getElementById('order-form').addEventListener('focusin',updateProgress);
</script>
</body>
</html>