<?php
// Projet PORTAIL - Codé par Rafael
// Fichier : delete.php
// Ce script permet à un administrateur de supprimer un utilisateur normal de la base de données,
// après vérification des droits, de la session et de l'activité. Il redirige ensuite vers l'interface admin.

session_start(); // Démarre la session pour vérifier les droits

// Vérifie que l’utilisateur est connecté
if (!isset($_SESSION['username'])) {
    header('Location: ..\index.php');
    exit;
}

// Vérifie que l’utilisateur est administrateur
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header('Location: ..\pageuser.php');
    exit;
}

// Déconnexion après 15 minutes d’inactivité
$timeout_duration = 900;
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $timeout_duration) {
    session_unset();
    session_destroy();
    header("Location: ..\index.php");
    exit;
}
$_SESSION['last_activity'] = time(); // Mise à jour de l'activité

// Connexion à la base de données
$host = 'localhost';
$dbname = 'PORTAIL';
$dbuser = 'Raffle0793';
$dbpass = 'CtOnZ3R#MBK';

$conn = new mysqli($host, $dbuser, $dbpass, $dbname);
if ($conn->connect_error) {
    die("Connexion échouée : " . $conn->connect_error);
}

// Vérifie que l’ID à supprimer a bien été fourni
if (isset($_GET['ID'])) {
    $id = intval($_GET['ID']); // Sécurisation du paramètre ID (entier uniquement)

    // Prépare et exécute la requête de suppression
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

// Redirige vers la page d’administration
header('Location: indexadmin.php');
exit;
?>
