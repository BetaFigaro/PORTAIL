<?php
// Projet PORTAIL - Codé par Rafael

// === Configuration de la base de données distante ===
$host = 'localhost';
$dbname = 'PORTAIL';
$user = 'Raffle0793';
$pass = 'CtOnZ3R#MBK';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    http_response_code(500);
    echo "Erreur de connexion à la base de données.";
    exit;
}

// === Récupération des données POST ===
$cle     = $_POST['cle']     ?? null;
$pi      = $_POST['pi']      ?? null;
$address = $_POST['addresse'] ?? null;
$nom     = $_POST['nom']     ?? null;

if (!$cle || !$pi) {
    http_response_code(400);
    echo "Clé et nom de la PI obligatoires.";
    exit;
}


try {
    // Vérifier si la clé existe déjà
    $stmt = $pdo->prepare("SELECT ID FROM PORTAIL WHERE CLE = ?");
    $stmt->execute([$cle]);
    $exists = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($exists) {
        // Mise à jour si des infos supplémentaires sont envoyées
        if ($address && $nom) {
            $stmt = $pdo->prepare("UPDATE PORTAIL SET Addresse = ?, Nom = ?, PI = ? WHERE CLE = ?");
            $stmt->execute([$address, $nom, $pi, $cle]);
        }
        echo "OK";
    } else {
        // Insertion
        $stmt = $pdo->prepare("INSERT INTO PORTAIL (Addresse, CLE, Nom, PI) VALUES (?, ?, ?, ?)");
        $stmt->execute([
            $address ?? '',
            $cle,
            $nom ?? '',
            $pi
        ]);
        echo "OK";
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo "Erreur SQL : " . $e->getMessage();
}
