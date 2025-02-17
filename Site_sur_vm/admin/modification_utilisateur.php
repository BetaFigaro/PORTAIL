<?php
session_start(); // Démarrer la session pour récupérer les informations de l'utilisateur

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['username'])) {
    // Si l'utilisateur n'est pas connecté, rediriger vers la page de connexion
    header('Location: ..\login.php');
    exit;
}

// Vérifier si l'utilisateur est administrateur via la variable "is_admin"
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    // Si l'utilisateur n'est pas administrateur, rediriger vers la page utilisateur par exemple
    header('Location: ..\pageuser.php');
    exit;
}

// Temps d'inactivité maximal (en secondes)
$timeout_duration = 900; // 15 minutes = 900 secondes

// Vérifier si la variable de session pour l'heure de dernière activité existe
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $timeout_duration) {
    // Si le temps d'inactivité est supérieur au délai, déconnecter l'utilisateur
    session_unset();
    session_destroy();
    header("Location: ..\index.php"); // Rediriger vers la page d'accueil
    exit();
}

// Mettre à jour l'heure de dernière activité
$_SESSION['last_activity'] = time();

// Connexion à la base de données
$host = 'localhost';
$dbname = 'PORTAIL';
$dbuser = 'Raffle0793';
$dbpass = 'CtOnZ3R#MBK';

$conn = new mysqli($host, $dbuser, $dbpass, $dbname);
if ($conn->connect_error) {
    die("Connexion échouée : " . $conn->connect_error);
}

// Récupérez les informations de l'utilisateur sélectionné
$id = intval($_GET['ID']); // Sécurisez l'ID
$sql = "SELECT * FROM USER_NORM WHERE ID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Utilisateur non trouvé.");
}

$user = $result->fetch_assoc();
?>
<!DOCTYPE html>
<html>
<head>
    <div class="center-content">
    <title>Modifier l'utilisateur</title>
    <meta charset="UTF-8">
    <link rel="stylesheet" type="text/css" href="../CSS/styles.css">
</head>
<body>
    <div class="right-container">
    <h1>Modifier l'utilisateur</h1>
    <form method="POST" action="update_utilisateur.php">
        <input type="hidden" name="id" value="<?php echo htmlspecialchars($user['ID']); ?>">
        
        <label for="Nom">Nom :</label>
        <input type="text" id="Nom" name="Nom" value="<?php echo htmlspecialchars($user['Nom']); ?>" required>

        <label for="Prenom">Prénom :</label>
        <input type="text" id="Prenom" name="Prenom" value="<?php echo htmlspecialchars($user['Prenom']); ?>" required>

        <label for="email">Email :</label>
        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>

        <label for="tel">Téléphone :</label>
        <input type="text" id="tel" name="tel" value="<?php echo htmlspecialchars($user['tel']); ?>" required>

        <label for="password">Mot de passe :</label>
        <input type="password" id="password" name="password" placeholder="Laissez vide pour ne pas modifier">

        <label for="Heure_Debut">Heure de début :</label>
        <input type="time" id="Heure_Debut" name="Heure_Debut" value="<?php echo htmlspecialchars($user['Heure_Debut']); ?>" required>
        
        <br><br>
        <label for="Heure_Fin">Heure de fin :</label>
        <input type="time" id="Heure_Fin" name="Heure_Fin" value="<?php echo htmlspecialchars($user['Heure_Fin']); ?>" required>
        <br><br>
        
        <button type="submit">Enregistrer les modifications</button>
    </form>
    </div>
<!-- Bouton bas de page, déconnexion et retour arriere -->
<div class="footer">
<a href="indexadmin.php">
    <br>
    <button class="bouton-retour">Retour</button>
</a>
</div>
</body>
</div>
</html>
