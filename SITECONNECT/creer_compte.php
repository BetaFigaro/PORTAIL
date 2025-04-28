<?php
// Projet PORTAIL - Codé par Rafael
// Ce fichier permet à l'utilisateur de créer un compte client associé à un portail.

// URL du script distant d'enregistrement d'utilisateur
define('REGISTER_URL', 'https://projet.betacorps.ovh/pi/register_user.php');

// Fichier contenant la clé unique du portail (générée dans page_principale.php)
define('CLE_PATH', __DIR__ . '/.pi_key');

// Récupération de la clé existante
$cle = file_exists(CLE_PATH) ? trim(file_get_contents(CLE_PATH)) : null;

// Traitement du formulaire lorsque la requête est de type POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Prépare les données à envoyer au serveur
    $data = [
        'Nom'      => $_POST['nom'] ?? '',
        'Prenom'   => $_POST['prenom'] ?? '',
        'tel'      => $_POST['telephone'] ?? '',
        'email'    => $_POST['email'] ?? '',
        'password' => password_hash($_POST['password'], PASSWORD_DEFAULT), // hash sécurisé
        'portail'  => $cle // clé du portail associée à ce compte client
    ];

    // Création du contexte HTTP pour faire la requête POST
    $options = [
        'http' => [
            'method'  => 'POST',
            'header'  => 'Content-Type: application/x-www-form-urlencoded',
            'content' => http_build_query($data)
        ]
    ];

    $context = stream_context_create($options);
    $response = @file_get_contents(REGISTER_URL, false, $context);

    // Redirige selon le succès ou l'échec de la requête
    if ($response && strpos($response, 'OK') !== false) {
        header('Location: compte_cree.php?success=1');
        exit;
    } else {
        header('Location: compte_cree.php?error=1');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Création du compte client</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, sans-serif;
            background: linear-gradient(to right, #74ebd5, #9face6);
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }

        .form-container {
            background-color: white;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 8px 20px rgba(0,0,0,0.1);
            max-width: 450px;
            width: 100%;
        }

        h2 {
            margin-bottom: 20px;
            color: #2c3e50;
            text-align: center;
        }

        input {
            width: 100%;
            padding: 12px;
            margin-bottom: 15px;
            border-radius: 8px;
            border: 1px solid #ccc;
            font-size: 14px;
        }

        button {
            width: 100%;
            padding: 12px;
            background-color: #27ae60;
            color: white;
            font-size: 16px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
        }

        button:hover {
            background-color: #219150;
        }

        .success, .error {
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 15px;
            text-align: center;
            font-weight: bold;
        }

        .success {
            background-color: #e1fbe1;
            color: #2e7d32;
        }

        .error {
            background-color: #fdecea;
            color: #c0392b;
        }
    </style>
</head>
<body>
    <div class="form-container">
        <h2>Créer un compte client</h2>

        <!-- Le formulaire POST va envoyer les données au même fichier -->
        <form method="POST">
            <input type="text" name="nom" placeholder="Nom" required>
            <input type="text" name="prenom" placeholder="Prénom" required>
            <input type="tel" name="telephone" placeholder="Téléphone" required>
            <input type="email" name="email" placeholder="Adresse e-mail" required>
            <input type="password" name="password" placeholder="Mot de passe" required>
            <button type="submit">Créer le compte</button>
        </form>
    </div>
</body>
</html>