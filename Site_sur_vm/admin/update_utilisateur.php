<?php
// Projet PORTAIL - Codé par Rafael
// Fichier : update_utilisateur.php
// Cette page est appelée lorsqu’un administrateur soumet un formulaire de modification d’utilisateur.
// Elle vérifie les autorisations, la validité des données, met à jour les informations dans la base,
// et gère correctement le mot de passe (ne le modifie que s’il a été renseigné).

session_start(); // Démarre la session PHP pour accéder aux informations utilisateur

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

// Gestion de l'inactivité (déconnexion automatique après 15 minutes)
$timeout_duration = 900;

if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $timeout_duration) {
    session_unset();
    session_destroy();
    header("Location: ..\index.php");
    exit();
}

// Mise à jour de l'heure de dernière activité
$_SESSION['last_activity'] = time();

// Vérifie que toutes les données attendues sont bien envoyées
if (!isset($_POST['Nom'], $_POST['Prenom'], $_POST['email'], $_POST['tel'], $_POST['Heure_Debut'], $_POST['Heure_Fin'])) {
    die("Données incomplètes.");
}

// Connexion à la base de données avec mysqli (au lieu de PDO ici)
$host = 'localhost';
$dbname = 'PORTAIL';
$dbuser = 'Raffle0793';
$dbpass = 'CtOnZ3R#MBK';

$conn = new mysqli($host, $dbuser, $dbpass, $dbname);

// Vérifie que la connexion a réussi
if ($conn->connect_error) {
    die("Connexion échouée : " . $conn->connect_error);
}

// Récupération et sécurisation des données du formulaire
$id     = intval($_POST['id']); // ID utilisateur
$nom    = $_POST['Nom'];
$prenom = $_POST['Prenom'];
$email  = $_POST['email'];
$tel    = $_POST['tel'];
$debut  = $_POST['Heure_Debut'];
$fin    = $_POST['Heure_Fin'];

// Gestion du mot de passe : soit nouveau, soit conservation de l'existant
if (!empty($_POST['password'])) {
    // Hachage du nouveau mot de passe s’il est fourni
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
} else {
    // Sinon, on récupère l'ancien mot de passe dans la BDD
    $query = "SELECT password FROM USER_NORM WHERE ID = ?";
    $stmt_password = $conn->prepare($query);
    $stmt_password->bind_param("i", $id);
    $stmt_password->execute();
    $result_password = $stmt_password->get_result();

    if ($result_password->num_rows === 0) {
        die("Utilisateur introuvable.");
    }

    $row_password = $result_password->fetch_assoc();
    $password = $row_password['password'];
}

// Préparation de la requête SQL de mise à jour
$sql = "UPDATE USER_NORM SET Nom = ?, Prenom = ?, email = ?, tel = ?, password = ?, Heure_Debut = ?, Heure_Fin = ? WHERE ID = ?";
$stmt = $conn->prepare($sql);

// Liaison des variables aux paramètres de la requête
$stmt->bind_param("sssssssi", $nom, $prenom, $email, $tel, $password, $debut, $fin, $id);

// Exécution de la requête et traitement du résultat
if ($stmt->execute()) {
    echo "Utilisateur mis à jour avec succès.";
    header("Location: indexadmin.php"); // Redirection après succès
    exit;
} else {
    echo "Erreur : " . $stmt->error;
}

// Nettoyage et fermeture des ressources
$stmt->close();
$conn->close();
?>
