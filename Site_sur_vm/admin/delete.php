<?php
// Projet PORTAIL - Cod� par Rafael
// Fichier : delete.php
// Ce script permet � un administrateur de supprimer un utilisateur normal de la base de donn�es,
// apr�s v�rification des droits, de la session et de l'activit�. Il redirige ensuite vers l'interface admin.

session_start(); // D�marre la session pour v�rifier les droits

// V�rifie que l�utilisateur est connect�
if (!isset($_SESSION['username'])) {
    header('Location: ..\index.php');
    exit;
}

// V�rifie que l�utilisateur est administrateur
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header('Location: ..\pageuser.php');
    exit;
}

// D�connexion apr�s 15 minutes d�inactivit�
$timeout_duration = 900;
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $timeout_duration) {
    session_unset();
    session_destroy();
    header("Location: ..\index.php");
    exit;
}
$_SESSION['last_activity'] = time(); // Mise � jour de l'activit�

// Connexion � la base de donn�es
$host = 'localhost';
$dbname = 'PORTAIL';
$dbuser = 'Raffle0793';
$dbpass = 'CtOnZ3R#MBK';

$conn = new mysqli($host, $dbuser, $dbpass, $dbname);
if ($conn->connect_error) {
    die("Connexion �chou�e : " . $conn->connect_error);
}

// V�rifie que l�ID � supprimer a bien �t� fourni
if (isset($_GET['ID'])) {
    $id = intval($_GET['ID']); // S�curisation du param�tre ID (entier uniquement)

    // Pr�pare et ex�cute la requ�te de suppression
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

// Redirige vers la page d�administration
header('Location: indexadmin.php');
exit;
?>
