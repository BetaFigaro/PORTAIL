<?php
// Projet PORTAIL - Codé par Rafael
// Fichier : ajout_utilisateur.php
// Cette page permet à un administrateur d’ajouter un utilisateur normal.
// Elle affiche un formulaire avec les champs nécessaires et redirige le POST vers traitement_ajout.php.
// Elle intègre une vérification de session, d'inactivité et de droits admin.

session_start(); // Démarrage de la session

// Vérifie que l'utilisateur est bien connecté
if (!isset($_SESSION['username'])) {
    header('Location: ..\index.php');
    exit;
}

// Vérifie que l'utilisateur est administrateur
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header('Location: ..\pageuser.php');
    exit;
}

// Gestion de l’inactivité (déconnexion après 15 minutes)
$timeout_duration = 900;
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $timeout_duration) {
    session_unset();
    session_destroy();
    header("Location: ..\index.php");
    exit();
}
$_SESSION['last_activity'] = time(); // Mise à jour du timestamp

// Connexion à la base de données
$host = 'localhost';
$dbname = 'PORTAIL';
$dbuser = 'Raffle0793';
$dbpass = 'CtOnZ3R#MBK';

$conn = new mysqli($host, $dbuser, $dbpass, $dbname);
if ($conn->connect_error) {
    die("Connexion échouée : " . $conn->connect_error);
}

// (optionnel ici, tu ne fais rien avec le résultat dans cette page)
$sql = "SELECT * FROM USER_NORM";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Ajout d'un utilisateur</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" type="text/css" href="../CSS/styles.css">
</head>
<body>

<!-- Formulaire d'ajout d'utilisateur -->
<div class="center-content">
  <div class="right-container">
      <h1 style="color: black;">Ajout d'un utilisateur</h1>
  
      <!-- Formulaire POST vers traitement_ajout.php -->
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
          <label for="Heure_Debut">Heure de début :</label>
          <input type="time" id="Heure_Debut" name="Heure_Debut" required>
  
          <br><br>
          <label for="Heure_Fin">Heure de fin :</label>
          <input type="time" id="Heure_Fin" name="Heure_Fin" required>
  
          <br><br>
          <button type="submit">Ajouter</button>
      </form>
  </div>
</div>

<!-- Bouton de retour -->
<div class="footer">
    <a href="indexadmin.php">
        <br>
        <button class="bouton-retour">Retour</button>
    </a>
</div>
</body>
</html>
