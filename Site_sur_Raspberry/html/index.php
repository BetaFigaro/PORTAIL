<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion Wi-Fi</title>
    <script>
        function refreshWiFiList() {
            document.getElementById("wifi-list").innerHTML = "<option>‚è≥ Chargement...</option>";
            fetch("get_wifi_list.php")
                .then(response => response.text())
                .then(data => {
                    document.getElementById("wifi-list").innerHTML = data;
                })
                .catch(error => {
                    console.error("Erreur lors de la r√©cup√©ration des r√©seaux Wi-Fi :", error);
                    document.getElementById("wifi-list").innerHTML = "<option>‚ùå Erreur lors de l‚Äôactualisation</option>";
                });
        }
    </script>
</head>
<body>
    <h2>Connectez votre Raspberry Pi au Wi-Fi</h2>

    <form method="post" action="save_wifi.php">
        <label>SSID :</label>
        <select id="wifi-list" name="ssid" required>
            <option value="">-- Cliquez sur 'Actualiser' --</option>
        </select>
        <button type="button" onclick="refreshWiFiList()">Ì†ΩÌ¥Ñ Actualiser</button>

        <br><br>

        <label>Mot de passe :</label>
        <input type="password" name="password" required><br>

        <button type="submit">Se connecter</button>
    </form>
</body>
</html>
