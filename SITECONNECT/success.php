<?php
// Projet PORTAIL - Codé par Rafael
// Ce fichier est affiché après une tentative de connexion au Wi-Fi.
// Il vérifie que le Raspberry est bien connecté et redirige si besoin vers son IP locale pour continuer la configuration.

/**
 * Vérifie si l'interface wlan0 est bien connectée à un réseau Wi-Fi.
 * Utilise la commande `nmcli` pour lire l'état de l'interface réseau.
 */
function isWifiConnected(): bool {
    $etat = shell_exec("nmcli -t -f DEVICE,STATE dev | grep '^wlan0:connected'");
    return !empty($etat);
}

/**
 * Récupère l’adresse IP locale assignée à l’interface Wi-Fi (wlan0).
 * Utilise la commande `ip` pour extraire l’adresse IPv4.
 */
function getWifiIP(): ?string {
    $output = null;
    exec("ip -4 addr show wlan0 | grep -oP '(?<=inet\\s)\\d+(\\.\\d+){3}'", $output);
    return $output[0] ?? null;
}

// Si le Wi-Fi est bien connecté et que l’utilisateur est encore sur "portailpi.local"
if (isWifiConnected()) {
    $currentHost = $_SERVER['HTTP_HOST'] ?? '';
    if ($currentHost === 'portailpi.local') {
        $ip = getWifiIP(); // On récupère l'IP réelle du Raspberry Pi
        if ($ip && filter_var($ip, FILTER_VALIDATE_IP)) {
            // Redirection vers cette IP locale (ex: http://192.168.1.42/success.php)
            header("Location: http://$ip/success.php");
            exit;
        }
    }
} else {
    // Si pas connecté au Wi-Fi, on renvoie vers index.php avec erreur
    header("Location: index.php?error=1");
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Connexion réussie</title>
    <style>
        /* Style visuel moderne et responsive */
        body {
            margin: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .card {
            background-color: white;
            padding: 40px 50px;
            border-radius: 15px;
            text-align: center;
            box-shadow: 0 8px 25px rgba(0,0,0,0.2);
            max-width: 400px;
            width: 90%;
        }

        h1 {
            color: #2ecc71;
            font-size: 2em;
            margin-bottom: 10px;
        }

        p {
            font-size: 1.1em;
            color: #333;
            margin-bottom: 30px;
        }

        a.button {
            background-color: #2ecc71;
            color: white;
            padding: 12px 25px;
            border: none;
            border-radius: 8px;
            text-decoration: none;
            font-weight: bold;
            transition: background 0.3s;
        }

        a.button:hover {
            background-color: #27ae60;
        }
    </style>
</head>
<body>
    <div class="card">
        <h1>✅ Connexion réussie !</h1>
        <p>Le Raspberry Pi est maintenant connecté au réseau Wi-Fi.</p>
        <a href="page_principale.php" class="button">Continuer</a>
    </div>
</body>
</html>