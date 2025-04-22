<?php
// Projet PORTAIL - Codé par Rafael
// Fichier : logs.php
// Cette page permet aux administrateurs de consulter l’historique complet des actions enregistrées dans la table LOGS.
// Elle inclut des fonctions de tri dynamique, une recherche en temps réel, et l'affichage des informations utiles :
// utilisateur, IP, action, agent, heure.

session_start();
require_once '../utils.php'; // Accès à la BDD et aux fonctions utilitaires

// Vérifie que l'utilisateur est bien connecté
if (!isset($_SESSION['username'])) {
    header('Location: ..\index.php');
    exit;
}

// Vérifie les droits d'administrateur
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header('Location: ..\pageuser.php');
    exit;
}

// Gestion de l'inactivité
$timeout_duration = 900;
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $timeout_duration) {
    session_unset();
    session_destroy();
    header("Location: index.php");
    exit();
}
$_SESSION['last_activity'] = time(); // Mise à jour de l'activité

// Connexion BDD
$pdo = getPDO();
$username = $_SESSION['username'] ?? 'Inconnu';

// Récupération de la CLE du portail lié à l'admin connecté
$stmtCle = $pdo->prepare("SELECT portail FROM USER_ADM WHERE Nom = ?");
$stmtCle->execute([$username]);
$cle_portail = $stmtCle->fetchColumn();

// Récupération uniquement des logs liés à ce portail
$stmt = $pdo->prepare("SELECT * FROM LOGS WHERE portail_cle = ? ORDER BY timestamp DESC");
$stmt->execute([$cle_portail]);
$logs = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Historique des Logs</title>
    <link rel="stylesheet" href="../CSS/styles.css">
</head>
<body class="log-body">
<div class="log-page-container">
    <h1>Historique complet des actions</h1>

    <!-- Bouton retour vers l'administration -->
    <div style="text-align: center; margin-bottom: 20px;">
        <a href="indexadmin.php">
            <button class="bouton-retour">Retour à la page admin</button>
        </a>
    </div>

    <!-- Zone de recherche dynamique -->
    <input type="text" id="searchInput" class="search-input" placeholder="Rechercher dans les logs...">

    <!-- Tableau d'affichage des logs -->
    <table class="log-table" id="logsTable">
        <thead>
        <tr>
            <!-- Colonnes triables -->
            <th onclick="sortTable(0)">Heure</th>
            <th onclick="sortTable(1)">Utilisateur</th>
            <th onclick="sortTable(2)">IP</th>
            <th onclick="sortTable(3)">Action</th>
            <th onclick="sortTable(4)">Client</th>
        </tr>
        </thead>
        <tbody>
        <!-- Affiche chaque entrée du tableau logs -->
        <?php foreach ($logs as $log): ?>
            <tr>
                <td><?= htmlspecialchars($log['timestamp']) ?></td>
                <td><?= htmlspecialchars($log['username']) ?></td>
                <td><?= htmlspecialchars($log['ip_address']) ?></td>
                <td><?= htmlspecialchars($log['action']) ?></td>
                <td><?= htmlspecialchars($log['user_agent']) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- JS : recherche + tri dynamique -->
<script>
    const searchInput = document.getElementById("searchInput");
    const table = document.getElementById("logsTable");
    const rows = table.getElementsByTagName("tr");

    // Recherche live : filtre les lignes selon la saisie
    searchInput.addEventListener("keyup", function () {
        const filter = searchInput.value.toLowerCase();
        for (let i = 1; i < rows.length; i++) {
            const row = rows[i];
            const text = row.innerText.toLowerCase();
            row.style.display = text.includes(filter) ? "" : "none";
        }
    });

    // Tri dynamique par colonne
    function sortTable(colIndex) {
        const tbody = table.tBodies[0];
        const rowsArray = Array.from(tbody.rows);
        const asc = !tbody.classList.contains("asc");

        rowsArray.sort((a, b) => {
            const cellA = a.cells[colIndex].innerText.toLowerCase();
            const cellB = b.cells[colIndex].innerText.toLowerCase();
            return asc ? cellA.localeCompare(cellB) : cellB.localeCompare(cellA);
        });

        rowsArray.forEach(row => tbody.appendChild(row));
        tbody.classList.toggle("asc", asc);
    }
</script>
</body>
</html>
