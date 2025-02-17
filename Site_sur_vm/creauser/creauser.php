<?php
session_start(); // Démarrer la session pour récupérer les informations de l'utilisateur

// Temps d'inactivité maximal (en secondes)
$timeout_duration = 900; // 15 minutes = 900 secondes

// Connexion à la base de données
$host = 'localhost'; // Adresse du serveur MySQL
$dbname = 'PORTAIL'; // Nom de la base de données
$dbuser = 'Raffle0793'; // Nom d'utilisateur MySQL
$dbpass = 'CtOnZ3R#MBK'; // Mot de passe MySQL

$conn = new mysqli($host, $dbuser, $dbpass, $dbname);
if ($conn->connect_error) {
    die("Connexion échouée : " . $conn->connect_error);
}

$sql = "SELECT * FROM USER_ADM";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Création de votre compte</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" type="text/css" href="../CSS/styles.css"> 
</head>
<body>
    <div class="center-content">
    <!-- formulaire d'ajout d'un utilisateur-->
    <div class="right-container">
    <h1>Création de votre compte</h1>
    <form method="POST" action="traitement_crea.php">
        <label for="nom">Nom :</label>
        <input type="text" id="nom" name="nom" required>
        
        <br>
        <label for="prenom">Prénom :</label>
        <input type="text" id="prenom" name="prenom" required>
        
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
        <label for="PORTAIL">ID générer par le portail :</label>
        <input type="text" id="IDP" name="IDP" required>
        
        <br><br>
        <button type="submit">Ajouter</button>
    </form>
    </div>
    </div>
<!-- Bouton bas de page, déconnexion et retour arriere -->
<div class="footer">
<a href="..\index.php">
    <br>
    <button class="bouton-retour">Retour</button>
</a>
</div>
</body>
</html>
