<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!isset($_POST['ssid']) || empty($_POST['ssid'])) {
        echo "❌ Aucun réseau sélectionné. Veuillez actualiser la liste et choisir un SSID.";
        exit;
    }

    $ssid = trim($_POST['ssid']);
    $password = escapeshellarg($_POST['password']);

    echo "<b>������ Recherche de réseaux...</b><br>";

    // Scanner les réseaux AVANT de tenter la connexion
    shell_exec("nmcli device wifi rescan");
    sleep(5);

    // Lister les réseaux disponibles après le scan
    $wifi_scan = shell_exec("nmcli -t -f BSSID,SSID,SIGNAL dev wifi list");
    $lines = explode("\n", trim($wifi_scan));

    echo "<b>������ Réseaux détectés :</b><br>";
    $best_bssid = null;
    $best_signal = -1;

    foreach ($lines as $line) {
        $parts = explode(":", $line);
        if (count($parts) >= 3) {
            $bssid = trim($parts[0]);
            $ssid_name = trim($parts[1]);
            $signal = intval(trim($parts[2]));

            echo "➡️ <b>SSID :</b> $ssid_name | <b>BSSID :</b> $bssid | <b>Signal :</b> $signal%<br>";

            // Vérifie si c'est le SSID sélectionné
            if ($ssid_name === $ssid && $signal > $best_signal) {
                $best_bssid = $bssid;
                $best_signal = $signal;
            }
        }
    }

    if (!$best_bssid) {
        echo "<br>❌ Aucun point d'accès trouvé pour <b>$ssid</b>.";
        exit;
    }

    echo "<br>✅ Meilleur BSSID sélectionné : <b>$best_bssid</b> avec signal $best_signal%<br>";

    // Supprimer d'anciennes connexions avec ce SSID (évite les erreurs)
    exec("nmcli connection delete id $ssid 2>&1", $output_delete, $return_delete);

    // Connexion avec le meilleur BSSID trouvé
    $command = "nmcli device wifi connect $best_bssid password $password";
    exec($command . " 2>&1", $output, $return_var);

    if ($return_var === 0) {
        echo "<br>✅ Connexion réussie à <b>$ssid</b> ($best_bssid) !";
    } else {
        echo "<br>❌ Erreur de connexion à <b>$ssid</b>.";
        echo "<br>Détails : " . implode("<br>", $output);
    }
}
?>
