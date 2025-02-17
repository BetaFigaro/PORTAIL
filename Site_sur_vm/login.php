<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();

$message = ""; // Variable pour stocker les messages d'erreur

// Récupérer les données du formulaire
$username = $_POST['username'] ?? '';
$password = $_POST['password'] ?? '';

// Vérifier si les champs sont remplis
if (empty($username) || empty($password)) {
    $message = "Veuillez remplir tous les champs.";
}

// Si aucun message d'erreur n'est défini, poursuivre le traitement
if (empty($message)) {
    // Connexion à la base de données
    $host = 'localhost';       // Adresse du serveur MySQL
    $dbname = 'PORTAIL';       // Nom de la base de données
    $dbuser = 'Raffle0793';    // Nom d'utilisateur MySQL
    $dbpass = 'CtOnZ3R#MBK';    // Mot de passe MySQL

    try {
        $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $dbuser, $dbpass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (PDOException $e) {
        $message = "Erreur de connexion à la base de données : " . $e->getMessage();
    }
}

if (empty($message)) {
    // Vérification dans la table USER_ADM (administrateur)
    $sql = "SELECT Nom, password FROM USER_ADM WHERE Nom = :username";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['username' => $username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        if (password_verify($password, $user['password'])) {
            $_SESSION['username'] = $username;
            $_SESSION['Nom'] = $user['Nom'];
            $_SESSION['is_admin'] = true;  // Définir l'accès administrateur
            header('Location: pageadmin.php');
            exit;
        } else {
            $message = "Mot de passe incorrect.";
        }
    } else {
        // Vérification dans la table USER_NORM (utilisateur normal)
        $sql = "SELECT Nom, password, Heure_Debut, Heure_Fin FROM USER_NORM WHERE Nom = :username";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['username' => $username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            if (password_verify($password, $user['password'])) {
                // Vérifier la période de connexion (uniquement pour USER_NORM)
                $currentDateTime = new DateTime();
                $startDateTime = new DateTime($user['Heure_Debut']);
                $endDateTime = new DateTime($user['Heure_Fin']);

                if ($currentDateTime >= $startDateTime && $currentDateTime <= $endDateTime) {
                    $_SESSION['username'] = $username;
                    $_SESSION['Nom'] = $user['Nom'];
                    header('Location: pageuser.php');
                    exit;
                } else {
                    $message = "Vous n'avez pas le droit de vous connecter à cette heure-ci.";
                }
            } else {
                $message = "Mot de passe incorrect.";
            }
        } else {
            $message = "Nom d'utilisateur non trouvé.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Lien vers le fichier CSS -->
    <link rel="stylesheet" type="text/css" href="../CSS/styles.css">
    <title>Page de Connexion</title>    
</head>
<body>
    <!-- La popup d'erreur s'affiche en bas de la page si un message est défini -->
    <?php if (!empty($message)) { ?>
        <div id="popup" class="popup">
            <p><?php echo $message; ?></p>
            <button onclick="redirectToPage()">OK</button>
        </div>
    <?php } ?>

    <script>
        // Affiche la popup dès que la page est chargée si un message est présent
        window.onload = function() {
            var popup = document.getElementById('popup');
            if (popup) {
                popup.style.display = 'block';
            }
        };

        // Fonction pour rediriger l'utilisateur vers la page d'accueil (index.php)
        function redirectToPage() {
            window.location.href = 'index.php';
        }
    </script>
</body>
</html>
