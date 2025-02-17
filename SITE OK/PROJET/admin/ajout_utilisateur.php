<!DOCTYPE html>
<?php
session_start(); // Démarrer la session pour récupérer les informations de l'utilisateur

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['username'])) {
    // Si l'utilisateur n'est pas connecté, rediriger vers la page de connexion
    header('Location: ..\index.php');
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
$host = 'localhost'; // Adresse du serveur MySQL
$dbname = 'PORTAIL'; // Nom de la base de données
$dbuser = 'Raffle0793'; // Nom d'utilisateur MySQL
$dbpass = 'CtOnZ3R#MBK'; // Mot de passe MySQL

$conn = new mysqli($host, $dbuser, $dbpass, $dbname);
if ($conn->connect_error) {
    die("Connexion échouée : " . $conn->connect_error);
}

$sql = "SELECT * FROM USER_NORM";
$result = $conn->query($sql);
?>
<html>
<head>
<div class="center-content">
    <title>Ajout d'un utilisateur</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" type="text/css" href="../CSS/styles.css"> 
</head>
<body>
    <!-- formulaire d'ajout d'un utilisateur-->
    <div class="right-container">
    <h1>Ajout d'un utilisateur</h1>
    <form method="POST" action="traitement_ajout.php">
        <label for="Nom">Nom :</label>
        <input type="text" id="Nom" name="Nom" required>
        
        <br>
        <label for="Prenom">Prénom :</label>
        <input type="text" id="Prenom" name="Prenom" required>
        
        <br>
        <label for="email">Email :</label>
        <input type="email" id="email" name="email" required>
        
        <br><br>
        <label for="tel">Téléphone :</label>
        <input type="text" id="tel" name="tel" required>
        
        <br>
        <label for="password">Mot de passe :</label>
        <input type="password" id="password" name="password" required>
        
        <br>
        <label for="Heure_Debut">Heure de debut :</label>
        <input type="time" id="Heure_Debut" name="Heure_Debut" required>
        
        <br><br>
        <label for="Heure_Fin">Heure de fin :</label>
        <input type="time" id="Heure_Fin" name="Heure_Fin" required>
        
        <br><br>
        <button type="submit">Ajouter</button>
    </form>
    </div>
</div>
<!-- Bouton bas de page, déconnexion et retour arriere -->
<div class="footer">
<a href="indexadmin.php">
    <br>
    <button class="bouton-retour">Retour</button>
</a>
</div>
</body>
</html>
