<?php
// Projet PORTAIL - Codé par Rafael
// Enregistrement d'un utilisateur client dans la table USER_ADM

// --- CONFIGURATION BASE DE DONNÉES ---
$host = 'localhost';
$dbname = 'PORTAIL';
$user = 'Raffle0793';
$pass = 'CtOnZ3R#MBK';

// Vérification des données reçues
$champs = ['Nom', 'Prenom', 'tel', 'email', 'password', 'portail'];
foreach ($champs as $champ) {
    if (empty($_POST[$champ])) {
        http_response_code(400);
        echo "Champ manquant : $champ";
        exit;
    }
}

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $pdo->prepare("INSERT INTO USER_ADM (Nom, Prenom, tel, email, password, portail) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([
        $_POST['Nom'],
        $_POST['Prenom'],
        $_POST['tel'],
        $_POST['email'],
        $_POST['password'],
        $_POST['portail']
    ]);

    echo "OK";
} catch (PDOException $e) {
    http_response_code(500);
    echo "Erreur BDD : " . $e->getMessage();
}
