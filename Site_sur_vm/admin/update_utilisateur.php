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

// Vérifiez si les données nécessaires sont envoyées
if (!isset($_POST['Nom'], $_POST['Prenom'], $_POST['email'], $_POST['tel'], $_POST['Heure_Debut'], $_POST['Heure_Fin'])) {
    die("Données incomplètes.");
}

// Connexion à la base de données
$host = 'localhost';
$dbname = 'PORTAIL';
$dbuser = 'Raffle0793';
$dbpass = 'CtOnZ3R#MBK';

$conn = new mysqli($host, $dbuser, $dbpass, $dbname);
if ($conn->connect_error) {
    die("Connexion échouée : " . $conn->connect_error);
}

// Récupération et validation des données
$id = intval($_POST['ID']);
$nom = $_POST['Nom'];
$prenom = $_POST['Prenom'];
$email = $_POST['email'];
$tel = $_POST['tel'];
$debut = $_POST['Heure_Debut'];
$fin = $_POST['Heure_Fin'];

// Gestion du mot de passe
if (!empty($_POST['password'])) {
    // Si un nouveau mot de passe est fourni, on le hache avant de l'enregistrer
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
} else {
    // Si aucun mot de passe n'est fourni, on conserve l'ancien mot de passe
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

// Mise à jour des données dans la base de données
$sql = "UPDATE USER_NORM SET Nom = ?, Prenom = ?, email = ?, tel = ?, password = ?, Heure_Debut = ?, Heure_Fin = ? WHERE ID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("sssssssi", $nom, $prenom, $email, $tel, $password, $debut, $fin, $id);

if ($stmt->execute()) {
    echo "Utilisateur mis à jour avec succès.";
    header("Location: indexadmin.php"); // Redirection vers la liste des utilisateurs
    exit;
} else {
    echo "Erreur : " . $stmt->error;
}

// Fermez les connexions
$stmt->close();
$conn->close();
?>
