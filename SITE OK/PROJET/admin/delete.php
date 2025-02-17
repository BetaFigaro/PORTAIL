<?php
session_start(); // D�marrer la session pour r�cup�rer les informations de l'utilisateur

// V�rifier si l'utilisateur est connect�
if (!isset($_SESSION['username'])) {
    // Si l'utilisateur n'est pas connect�, rediriger vers la page de connexion
    header('Location: ..\login.php');
    exit;
}

// V�rifier si l'utilisateur est administrateur via la variable "is_admin"
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    // Si l'utilisateur n'est pas administrateur, rediriger vers la page utilisateur par exemple
    header('Location: ..\pageuser.php');
    exit;
}

// Temps d'inactivit� maximal (en secondes)
$timeout_duration = 900; // 15 minutes = 900 secondes

// V�rifier si la variable de session pour l'heure de derni�re activit� existe
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $timeout_duration) {
    // Si le temps d'inactivit� est sup�rieur au d�lai, d�connecter l'utilisateur
    session_unset();
    session_destroy();
    header("Location: ..\index.php"); // Rediriger vers la page d'accueil
    exit();
}

// Mettre � jour l'heure de derni�re activit�
$_SESSION['last_activity'] = time();

// Connexion � la base de donn�es
$host = 'localhost';
$dbname = 'PORTAIL';
$dbuser = 'Raffle0793';
$dbpass = 'CtOnZ3R#MBK';

$conn = new mysqli($host, $dbuser, $dbpass, $dbname);
if ($conn->connect_error) {
    die("Connexion �chou�e : " . $conn->connect_error);
}

// V�rifier si un ID est fourni
if (isset($_GET['ID'])) {
    $id = intval($_GET['ID']); // S�curiser l'entr�e utilisateur
    $sql = "DELETE FROM USER_NORM WHERE ID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        echo "Utilisateur supprim� avec succ�s.";
    } else {
        echo "Erreur lors de la suppression de l'utilisateur.";
    }
    
    $stmt->close();
} else {
    echo "Aucun ID sp�cifi�.";
}

$conn->close();

// Rediriger vers la page pr�c�dente
header('Location: indexadmin.php');
exit;
?>
