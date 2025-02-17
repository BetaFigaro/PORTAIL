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

// Vérifier si un ID est fourni
if (isset($_GET['ID'])) {
    $id = intval($_GET['ID']); // Sécuriser l'entrée utilisateur
    $sql = "DELETE FROM USER_NORM WHERE ID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        echo "Utilisateur supprimé avec succès.";
    } else {
        echo "Erreur lors de la suppression de l'utilisateur.";
    }
    
    $stmt->close();
} else {
    echo "Aucun ID spécifié.";
}

$conn->close();

// Rediriger vers la page précédente
header('Location: indexadmin.php');
exit;
?>
