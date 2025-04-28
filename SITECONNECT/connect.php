<?php
// Projet PORTAIL - Codé par Rafael
// Ce fichier reçoit les données du formulaire (SSID + mot de passe),
// exécute un script Bash pour connecter le Raspberry Pi à un réseau Wi-Fi,
// et redirige selon le résultat.

// Vérifie que la requête est bien en POST (sécurité)
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    // Si ce fichier est accédé directement via GET, on redirige vers l'accueil
    header('Location: index.php');
    exit;
}

// Récupération sécurisée des données du formulaire (SSID et mot de passe)
// escapeshellarg() protège contre les injections dans la ligne de commande
$ssid = escapeshellarg($_POST['ssid']);
$password = escapeshellarg($_POST['password']);

// Pour déboguer : récupère l'identité de l'utilisateur exécutant ce script PHP
$output = [];
exec("whoami", $output);

// Écrit le résultat dans un fichier log pour vérifier les droits
file_put_contents('/var/www/SITECONNECT/logs/whoami_php.txt', implode("\n", $output));

// Appel du script Bash pour tenter une connexion Wi-Fi
// Le script `connect_to_wifi.sh` est lancé avec le SSID et mot de passe en argument
// Le résultat de l'exécution est stocké dans $code
exec("sudo /var/www/SITECONNECT/connect_to_wifi.sh $ssid $password", $output, $code);

// Vérifie le code retour du script
if ($code === 0) {
    // Si le script retourne 0 = succès → on redirige vers la page de confirmation
    header('Location: success.php');
} else {
    // Sinon, on renvoie vers l'index avec un code d'erreur visible dans l'URL
    header('Location: index.php?error=1');
}
exit;
