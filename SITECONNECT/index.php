<?php
// Projet PORTAIL - Codé par Rafael
// Page d'accueil du Raspberry Pi en mode hotspot
// Permet à l'utilisateur de se connecter à son réseau Wi-Fi personnel (via SSID + mot de passe)
// Affiche un message d'erreur si la tentative précédente a échoué

// Récupération du code d'erreur dans l'URL (ex: ?error=1)
$erreur = isset($_GET['error']) ? intval($_GET['error']) : 0;

// Définition des messages associés aux erreurs
$messages = [
    1 => "❌ Échec de la connexion au Wi-Fi. Vérifiez le mot de passe ou la portée du réseau.",
    2 => "⚠️ Impossible de détecter une connexion Wi-Fi. Veuillez réessayer.",
];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Connexion Wi-Fi</title>
    <style>
        /* Style général du fond en dégradé */
        body {
            margin: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* Carte blanche centrale */
        .card {
            background-color: white;
            padding: 40px 50px;
            border-radius: 15px;
            text-align: center;
            box-shadow: 0 8px 25px rgba(0,0,0,0.2);
            max-width: 400px;
            width: 90%;
        }

        h2 {
            color: #333;
            margin-bottom: 20px;
        }

        /* Style des champs SSID et mot de passe */
        input[type="text"],
        input[type="password"] {
            width: 100%;
            padding: 12px;
            margin: 10px 0 20px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 16px;
        }

        /* Bouton de soumission */
        button {
            background-color: #3498db;
            color: white;
            padding: 12px 20px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            cursor: pointer;
            transition: background 0.3s;
        }

        /* Changement de couleur au survol du bouton */
        button:hover {
            background-color: #2980b9;
        }

        /* Boîte de message d'erreur */
        .message {
            background-color: #ffe6e6;
            border: 1px solid #ff4d4d;
            padding: 12px;
            border-radius: 8px;
            color: #cc0000;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="card">
        <h2>Connexion à votre réseau Wi-Fi</h2>

        <!-- Affiche un message d'erreur si présent dans l'URL -->
        <?php if ($erreur && isset($messages[$erreur])): ?>
            <div class="message">
                <?= htmlspecialchars($messages[$erreur]) ?>
            </div>
        <?php endif; ?>

        <!-- Formulaire de connexion Wi-Fi -->
        <form method="POST" action="connect.php">
            <input type="text" name="ssid" placeholder="Nom du réseau (SSID)" required>
            <input type="password" name="password" placeholder="Mot de passe Wi-Fi" required>
            <button type="submit">Se connecter</button>
        </form>
    </div>

    <script>
        // Lors de l'envoi du formulaire, attendre 3 secondes puis rediriger
        const form = document.querySelector("form");
        form.addEventListener("submit", function () {
            setTimeout(function () {
                window.location.href = "http://portailpi.local/success.php";
            }, 3000);
        });
    </script>
</body>
</html>
