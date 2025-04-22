<?php
// Projet PORTAIL - Codé par Rafael
// Fichier : traitement_ajout.php
// Ce script traite l'ajout d'un nouvel utilisateur normal via un formulaire.
// Il est réservé aux administrateurs connectés, vérifie les droits, insère les données
// dans la base, et affiche un retour utilisateur en popup.

ini_set('display_errors', 1);                // Active l'affichage des erreurs
ini_set('display_startup_errors', 1);        // Active les erreurs au démarrage
error_reporting(E_ALL);                      // Affiche tous les types d'erreurs

session_start(); // Démarrage de la session

// Vérifie si l'utilisateur est connecté
if (!isset($_SESSION['username'])) {
    header('Location: ..\index.php');
    exit;
}

// Vérifie que l'utilisateur est bien administrateur
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header('Location: ..\pageuser.php');
    exit;
}

// Déconnexion automatique après 15 minutes d'inactivité
$timeout_duration = 900;
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $timeout_duration) {
    session_unset();
    session_destroy();
    header("Location: ..\index.php");
    exit();
}
$_SESSION['last_activity'] = time(); // Mise à jour de l'activité

// Connexion à la base de données via mysqli
$host   = 'localhost';
$dbname = 'PORTAIL';
$dbuser = 'Raffle0793';
$dbpass = 'CtOnZ3R#MBK';

$conn = new mysqli($host, $dbuser, $dbpass, $dbname);
if ($conn->connect_error) {
    die("Connexion échouée : " . $conn->connect_error);
}

$message = ""; // Message à afficher à l’utilisateur (succès ou erreur)

// Si la requête est de type POST (soumission du formulaire)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupération des champs du formulaire
    $nom     = $_POST['Nom'];
    $prenom  = $_POST['Prenom'];
    $email   = $_POST['email'];
    $tel     = $_POST['tel'];
    $password = $_POST['password'];
    $debut   = $_POST['Heure_Debut']; 
    $fin     = $_POST['Heure_Fin'];

    // Hachage du mot de passe avant enregistrement
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    
    // Récupération de l'ID de l'administrateur ayant effectué l'ajout
    $adminUsername = $_SESSION['username'];
    $queryAdmin = "SELECT ID FROM USER_ADM WHERE Nom = ?";
    $stmtAdmin = $conn->prepare($queryAdmin);
    $stmtAdmin->bind_param("s", $adminUsername);
    $stmtAdmin->execute();
    $resultAdmin = $stmtAdmin->get_result();

    // Vérifie que l'admin existe bien
    if ($resultAdmin->num_rows > 0) {
        $rowAdmin = $resultAdmin->fetch_assoc();
        $adminId = $rowAdmin['ID'];
    } else {
        $message = "Erreur : Admin non trouvé dans la BDD.";
        $stmtAdmin->close();
        $conn->close();
        exit(); // Arrêt du script si admin introuvable
    }
    $stmtAdmin->close();

    // Insertion de l'utilisateur normal avec liaison à l'admin
    $sql = "INSERT INTO USER_NORM (Nom, Prenom, email, tel, password, Heure_Debut, Heure_Fin, Admin)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssssi", $nom, $prenom, $email, $tel, $hashedPassword, $debut, $fin, $adminId);

    if ($stmt->execute()) {
        $message = "Utilisateur ajouté avec succès.";
    } else {
        $message = "Erreur : " . $conn->error;
    }

    // Fermeture des connexions
    $stmt->close();
    $conn->close();
}
?>

<!-- Partie HTML : affichage du message dans un popup -->
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" type="text/css" href="../CSS/styles.css">
    <title>Ajout d'utilisateur</title>    
</head>
<body>
    <script>
        // JS : affiche une alerte si un message est défini, puis redirige vers indexadmin.php
        window.onload = function() {
            var message = "<?php echo $message; ?>";
            if (message !== "") {
                alert(message);
                window.location.href = "indexadmin.php";
            }
        };


        // Redirection vers la page admin une fois le message confirmé
        function redirectToPage() {
            window.location.href = 'indexadmin.php';
        }
    </script>
</body>
</html>
