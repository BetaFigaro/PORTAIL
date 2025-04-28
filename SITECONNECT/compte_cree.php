<?php
// Projet PORTAIL - Codé par Rafael
// Page de confirmation après la tentative de création de compte utilisateur client

// Vérifie dans l'URL si la création s'est bien passée (GET ?success=1)
$success = isset($_GET['success']);

// Vérifie si une erreur a été signalée (GET ?error=1)
$error = isset($_GET['error']);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Création de compte</title>
    <style>
        /* Style général */
        body {
            font-family: 'Segoe UI', Tahoma, sans-serif;
            background: linear-gradient(to right, #74ebd5, #9face6); /* dégradé bleu/rose */
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }

        /* Conteneur principal */
        .card {
            background-color: white;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 8px 20px rgba(0,0,0,0.1);
            max-width: 450px;
            width: 100%;
            text-align: center;
        }

        /* Titre */
        h2 {
            color: #2c3e50;
        }

        /* Message en cas de succès */
        .success {
            background-color: #e1fbe1;
            color: #2e7d32;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        /* Message en cas d'erreur */
        .error {
            background-color: #fdecea;
            color: #c0392b;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        /* Boutons de navigation */
        a.button {
            display: inline-block;
            padding: 12px 25px;
            background-color: #3498db;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: bold;
        }

        /* Hover bouton */
        a.button:hover {
            background-color: #2980b9;
        }
    </style>
</head>
<body>
    <div class="card">
        <h2>Création du compte</h2>

        <?php if ($success): ?>
            <!-- Affichage si création réussie -->
            <div class="success">Votre compte a bien été créé !</div>
            <a class="button" href="https://projet.betacorps.ovh/index.php">Aller à la page de connexion</a>

        <?php elseif ($error): ?>
            <!-- Affichage en cas d'erreur -->
            <div class="error">Une erreur est survenue lors de la création du compte.</div>
            <a class="button" href="creer_compte.php">Retour au formulaire</a>

        <?php else: ?>
            <!-- Cas où aucun paramètre n'est passé -->
            <div class="error">Aucune action détectée.</div>
        <?php endif; ?>
    </div>
</body>
</html>