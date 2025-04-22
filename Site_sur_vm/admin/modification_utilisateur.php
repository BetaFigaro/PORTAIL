<?php
// Projet PORTAIL - Codé par Rafael
// Fichier : modification_utilisateur.php
// Cette page permet à un administrateur de modifier les informations d’un utilisateur existant.
// Elle affiche un formulaire pré-rempli avec les données de l’utilisateur, récupérées depuis la BDD.
// Elle vérifie les droits, la session, et redirige en cas de problème d’accès.

session_start(); // Démarre la session pour accéder aux données utilisateur

// Vérifie que l'utilisateur est connecté
if (!isset($_SESSION['username'])) {
    header('Location: ..\index.php');
    exit;
}

// Vérifie que l'utilisateur a les droits administrateur
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header('Location: ..\pageuser.php');
    exit;
}

// ⏱️ Vérification d'inactivité (15 minutes)
$timeout_duration = 900;
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $timeout_duration) {
    session_unset();
    session_destroy();
    header("Location: ..\index.php");
    exit();
}

// Mise à jour du temps de dernière activité
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

// Récupération des données de l’utilisateur sélectionné pour modification
$id = intval($_GET['ID']); // Sécurise l'ID récupéré depuis l'URL (conversion en entier)
$sql = "SELECT * FROM USER_NORM WHERE ID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

// Si aucun utilisateur trouvé avec cet ID
if ($result->num_rows === 0) {
    die("Utilisateur non trouvé.");
}

$user = $result->fetch_assoc(); // Données de l'utilisateur à modifier
?>
<!DOCTYPE html>
<html>
<head>
    <title>Modifier l'utilisateur</title>
    <meta charset="UTF-8">
    <link rel="stylesheet" type="text/css" href="../CSS/styles.css">
</head>
<body>
    <div class="center-content">
      <!-- Conteneur principal -->
      <div class="right-container">
          <h1 style="color: black;">Modifier l'utilisateur</h1>
  
          <!-- Formulaire pré-rempli pour la mise à jour -->
          <form method="POST" action="update_utilisateur.php">
              <!-- Champ caché contenant l'ID de l'utilisateur -->
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
    </div>
    <!-- Pied de page avec bouton retour -->
    <div class="footer">
        <a href="indexadmin.php">
            <br>
            <button class="bouton-retour">Retour</button>
        </a>
    </div>
</body>
</html>
