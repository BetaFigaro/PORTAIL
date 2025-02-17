<?php
header("Content-Type: text/html; charset=UTF-8");

// Scanner les réseaux AVANT d'afficher la liste
shell_exec("nmcli device wifi rescan");
sleep(5); // Laisser le temps au scan de terminer

// Exécuter la commande pour lister les réseaux
$wifi_scan = shell_exec("nmcli -t -f SSID,SIGNAL dev wifi list");
$lines = explode("\n", trim($wifi_scan));

$networks = [];
foreach ($lines as $line) {
    $parts = explode(":", $line);
    if (count($parts) >= 2) {
        $ssid = trim($parts[0]);
        $signal = trim($parts[1]);

        // Ignorer les entrées vides ou incorrectes
        if (!empty($ssid) && $ssid !== "--" && !in_array($ssid, $networks)) {
            $networks[] = $ssid;
            echo "<option value=\"$ssid\">$ssid (Signal: $signal%)</option>";
        }
    }
}

// Si aucun réseau valide n'est trouvé
if (empty($networks)) {
    echo "<option value=''>? Aucun réseau trouvé</option>";
}
?>
