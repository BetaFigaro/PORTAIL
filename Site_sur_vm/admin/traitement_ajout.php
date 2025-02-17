<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['username'])) {
    header('Location: ..\login.php');
    exit;
}

// Vérifier si l'utilisateur est administrateur
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header('Location: ..\pageuser.php');
    exit;
}

// Timeout de 15 minutes
$timeout_duration = 900;
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $timeout_duration) {
    session_unset();
    session_destroy();
    header("Location: ..\index.php");
    exit();
}
$_SESSION['last_activity'] = time();

$host   = 'localhost';
$dbname = 'PORTAIL';
$dbuser = 'Raffle0793';
$dbpass = 'CtOnZ3R#MBK';

$conn = new mysqli($host, $dbuser, $dbpass, $dbname);
if ($conn->connect_error) {
    die("Connexion échouée : " . $conn->connect_error);
}

$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom     = $_POST['Nom'];
    $prenom  = $_POST['Prenom'];
    $email   = $_POST['email'];
    $tel     = $_POST['tel'];
    $password = $_POST['password'];
    $debut   = $_POST['Heure_Debut']; 
    $fin     = $_POST['Heure_Fin'];

    // Hachage du mot de passe
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    
    // Récupérer l'ID de l'admin depuis la BDD à partir du username stocké dans la session
    $adminUsername = $_SESSION['username'];
    $queryAdmin = "SELECT ID FROM USER_ADM WHERE Nom = ?";
    $stmtAdmin = $conn->prepare($queryAdmin);
    $stmtAdmin->bind_param("s", $adminUsername);
    $stmtAdmin->execute();
    $resultAdmin = $stmtAdmin->get_result();
    if ($resultAdmin->num_rows > 0) {
         $rowAdmin = $resultAdmin->fetch_assoc();
         $adminId = $rowAdmin['ID'];
    } else {
         $message = "Erreur : Admin non trouvé dans la BDD.";
         $stmtAdmin->close();
         $conn->close();
         exit();
    }
    $stmtAdmin->close();

    // Préparation de la requête d'insertion en utilisant "i" pour le paramètre entier (adminId)
    $sql = "INSERT INTO USER_NORM (Nom, Prenom, email, tel, password, Heure_Debut, Heure_Fin, Admin) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssssi", $nom, $prenom, $email, $tel, $hashedPassword, $debut, $fin, $adminId);

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
    <link rel="stylesheet" type="text/css" href="../CSS/styles.css">
    <title>Ajout d'utilisateur</title>    
</head>
<body>
    <?php if (!empty($message)) { ?>
        <div id="popup" class="popup">
            <p><?php echo $message; ?></p>
            <button onclick="redirectToPage()">OK</button>
        </div>
    <?php } ?>
    <script>
        window.onload = function() {
            var message = "<?php echo $message; ?>";
            var popup = document.getElementById('popup');
            if (message !== "") {
                popup.style.display = 'block';
            }
        };
        function redirectToPage() {
            window.location.href = 'indexadmin.php';
        }
    </script>
</body>
</html>