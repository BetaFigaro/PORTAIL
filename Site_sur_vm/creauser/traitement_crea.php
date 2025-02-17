<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start(); // Démarrer la session pour récupérer les informations de l'utilisateur
// Met à jour l'heure de dernière activité
$_SESSION['last_activity'] = time();

$host = 'localhost';
$dbname = 'PORTAIL';
$dbuser = 'Raffle0793';
$dbpass = 'CtOnZ3R#MBK';

$conn = new mysqli($host, $dbuser, $dbpass, $dbname);
if ($conn->connect_error) {
    die("Connexion échouée : " . $conn->connect_error);
}

$message = ""; // Variable pour stocker le message

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = $_POST['nom'];
    $prenom = $_POST['prenom'];
    $email = $_POST['email'];
    $tel = $_POST['tel'];
    $password = $_POST['password'];
    $portail = isset($_POST['IDP']) ? intval($_POST['IDP']) : 0;

    // Hachage du mot de passe
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Préparation de la requête d'insertion
    $sql = "INSERT INTO USER_ADM (nom, prenom, email, tel, password, portail) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssi", $nom, $prenom, $email, $tel, $hashedPassword, $portail);

    if ($stmt->execute()) {
        $message = "Utilisateur ajouté avec succès.";
    } else {
        $message = "Erreur : " . $conn->error;
    }

    $stmt->close();
    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- lien fichier css -->
    <link rel="stylesheet" type="text/css" href="../CSS/styles.css">
    <title>Page de Connexion</title>    
</head>
<body>

    <!-- Si un message est défini, afficher la popup -->
    <?php if (!empty($message)) { ?>
        <div id="popup" class="popup">
            <p><?php echo $message; ?></p>
            <button onclick="redirectToPage()">OK</button>
        </div>
    <?php } ?>

    <script>
        // Affiche la popup
        window.onload = function() {
            var message = "<?php echo $message; ?>";
            var popup = document.getElementById('popup');
            if (message !== "") {
                popup.style.display = 'block';  // Afficher la popup
            }
        };

        // Fonction pour rediriger l'utilisateur vers une autre page
        function redirectToPage() {
            window.location.href = '../index.php';
        }
    </script>

</body>
</html>
