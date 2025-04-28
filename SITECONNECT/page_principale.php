<?php
// Projet PORTAIL - Codé par Rafael
// Ce fichier permet d'enregistrer un portail une fois que le Raspberry est connecté à Internet.
// Il génère une clé unique persistante, l'envoie au serveur distant avec un nom et une adresse du portail.

//Emplacement où la clé générée est stockée localement
define('CLE_PATH', __DIR__ . '/.pi_key');


//URL du script distant qui gère l'enregistrement du portail
define('REGISTER_URL', 'https://projet.betacorps.ovh/pi/register_portail.php');

// Initialisation des variables de contrôle
$success = false;
$error = false;
$key = '';
$pi_name = gethostname(); // Récupère le nom de l'appareil Raspberry Pi

// Si on a reçu un formulaire en POST avec l'adresse et le nom du portail
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['addresse'], $_POST['nom'])) {

    // Vérifie si une clé existe déjà (clé persistante pour ce Raspberry)
    if (file_exists(CLE_PATH)) {
        $key = trim(file_get_contents(CLE_PATH)); // Récupération
    } else {
        // Génère une nouvelle clé aléatoire de 15 chiffres
        $key = strval(random_int(100000000000000, 999999999999999));
        file_put_contents(CLE_PATH, $key); // Enregistre localement
        file_put_contents('/var/www/SITECONNECT/logs/pi_debug.log', "Clé sauvegardée : $key\n", FILE_APPEND);
    }

    // Prépare les données à envoyer au serveur distant
    $data = [
        'cle'      => $key,
        'pi'       => $pi_name,
        'addresse' => $_POST['addresse'],
        'nom'      => $_POST['nom']
    ];

    // Préparation de la requête HTTP POST
    $options = [
        'http' => [
            'method'  => 'POST',
            'header'  => "Content-Type: application/x-www-form-urlencoded",
            'content' => http_build_query($data)
        ]
    ];
    $context = stream_context_create($options);

    // Envoi des données
    $result = @file_get_contents(REGISTER_URL, false, $context);

    // Vérifie la réponse du serveur
    if ($result && strpos($result, 'OK') !== false) {
        $success = true;
    } else {
        $error = true;
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Enregistrement du portail</title>
    <style>
        /* --- Design général et responsive --- */
        body {
            margin: 0;
            font-family: 'Segoe UI Emoji', 'Noto Color Emoji', 'Apple Color Emoji', sans-serif;
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
            max-width: 450px;
            width: 90%;
        }

        h1 {
            margin-top: 0;
            color: #2c3e50;
        }

        input[type="text"] {
            width: 100%;
            padding: 12px;
            margin: 10px 0 20px;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-size: 15px;
        }

        button {
            background-color: #27ae60;
            color: white;
            padding: 12px 25px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            cursor: pointer;
            margin-top: 10px;
        }

        button:hover {
            background-color: #219150;
        }

        .success {
            background-color: #e1fbe1;
            color: #2e7d32;
            padding: 10px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .error {
            background-color: #fdecea;
            color: #c0392b;
            padding: 10px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .next-button {
            display: inline-block;
            margin-top: 20px;
            text-decoration: none;
            background-color: #3498db;
            color: white;
            padding: 10px 20px;
            border-radius: 8px;
            font-weight: bold;
        }

        .next-button:hover {
            background-color: #2980b9;
        }
    </style>
</head>
<body>
    <div class="card">
        <h1>Enregistrement du portail</h1>

        <!-- Si tout s’est bien passé -->
        <?php if ($success): ?>
            <div class="success">Portail enregistré avec succès.</div>
            <a href="creer_compte.php" class="next-button">Continuer</a>
        
        <!-- Sinon, afficher le formulaire ou une erreur -->
        <?php else: ?>
            <?php if ($error): ?>
                <div class="error">Une erreur est survenue lors de l'enregistrement. Veuillez réessayer.</div>
            <?php endif; ?>

            <form method="POST">
                <input type="text" name="addresse" placeholder="Adresse du portail" required>
                <input type="text" name="nom" placeholder="Nom du portail" required>
                <button type="submit">Enregistrer</button>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>
