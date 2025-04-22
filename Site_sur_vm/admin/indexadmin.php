<?php
// Projet PORTAIL - Codé par Rafael
// Fichier : indexadmin.php
// Cette page est l'interface d'administration principale. Elle affiche les utilisateurs normaux
// associés à l'administrateur connecté, permet de modifier, supprimer, ajouter un utilisateur,
// d'accéder aux logs, et de consulter les 5 dernières actions. Elle intègre aussi la gestion de session,
// des droits, de l'inactivité et du logging.

session_start();
require_once '../utils.php'; // Fonctions utilitaires (getPDO, log_action, etc.)

$pdo = getPDO(); // Connexion à la BDD avec PDO pour les logs

$username = $_SESSION['username'] ?? 'Inconnu';
// Journalise l'accès à cette page
log_action($pdo, $username, "Accès à l'interface admin");

// Vérifie si l'utilisateur est connecté
if (!isset($_SESSION['username'])) {
    header('Location: ..\index.php');
    exit;
}

// Vérifie que l'utilisateur est admin
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header('Location: ..\pageuser.php');
    exit;
}

// Déconnexion manuelle
if (isset($_POST['logout'])) {
    $username = $_SESSION['username'] ?? 'Inconnu';
    log_action($pdo, $username, "Déconnexion");
    session_unset();
    session_destroy();
    header('Location: ../../index.php');
    exit;
}

// Gestion de l'inactivité (15 minutes)
$timeout_duration = 900;
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $timeout_duration) {
    session_unset();
    session_destroy();
    header("Location: index.php");
    exit();
}
$_SESSION['last_activity'] = time(); // Mise à jour de l'activité

// Connexion à la base avec mysqli pour afficher les utilisateurs
$host = 'localhost';
$dbname = 'PORTAIL';
$dbuser = 'Rasping5939';
$dbpass = 'Puy1520PW5EJ';
$conn = new mysqli($host, $dbuser, $dbpass, $dbname);
if ($conn->connect_error) {
    die("Connexion échouée : " . $conn->connect_error);
}

// Récupère l’ID de l’admin connecté
$adminUsername = $_SESSION['username'];
$queryAdmin = "SELECT ID FROM USER_ADM WHERE Nom = ?";
$stmtAdmin = $conn->prepare($queryAdmin);
$stmtAdmin->bind_param("s", $adminUsername);
$stmtAdmin->execute();
$resultAdmin = $stmtAdmin->get_result();
if ($resultAdmin->num_rows > 0) {
    $rowAdmin = $resultAdmin->fetch_assoc();
    $adminId = $rowAdmin['ID'];
} else {
    die("Erreur : Admin non trouvé dans la BDD.");
}
$stmtAdmin->close();

// Sélectionne uniquement les utilisateurs créés par cet admin
$sql = "SELECT * FROM USER_NORM WHERE Admin = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $adminId);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Interface Administrateur</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" type="text/css" href="../CSS/styles.css"> 
    <style>
        /* Mise en page générale */
        body {
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            height: 100vh;
            margin: 0;
        }
    </style>
    <script>
        // Confirmation de suppression avec redirection si confirmé
        function confirmDelete(id) {
            if (confirm("Voulez-vous vraiment supprimer cet utilisateur ?")) {
                window.location.href = `delete.php?ID=${id}`;
            }
        }
    </script>
</head>
<body>
<div class="header">
    <font face="arial" size="6" color="WHITE"><b>INTERFACE ADMINISTRATEUR</b></font>
</div>

<div class="content">
    <!-- Affichage des utilisateurs -->
    <div class="table-container">
        <h2>Utilisateur :</h2>
        <table>
            <thead>
                <tr>
                    <th>Nom</th>
                    <th>Prénom</th>
                    <th>Email</th>
                    <th>Téléphone</th>
                    <th>Heure debut</th>
                    <th>Heure fin</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['Nom']); ?></td>
                            <td><?= htmlspecialchars($row['Prenom']); ?></td>
                            <td><?= htmlspecialchars($row['email']); ?></td>
                            <td><?= htmlspecialchars($row['tel']); ?></td>
                            <td><?= htmlspecialchars($row['Heure_Debut']); ?></td>
                            <td><?= htmlspecialchars($row['Heure_Fin']); ?></td>
                            <td>
                                <!-- Supprimer -->
                                <button class="delete-button" onclick="confirmDelete('<?= $row['ID']; ?>')">
                                    <img src="..\images\corbeille.png" alt="Corbeille" width="30">
                                </button>
                                <!-- Modifier -->
                                <a href="modification_utilisateur.php?ID=<?= $row['ID']; ?>" class="modify-button">
                                    <img src="..\images\crayon.png" alt="Modifier" width="30">
                                </a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="8">Aucune donnée trouvée</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Zone de gestion -->
    <div class="right-container">
        <h2>Options</h2>
        <br>
        <div class="button-group">
            <button class="bouton-retour" onclick="window.location.href='ajout_utilisateur.php'">Ajout utilisateur</button>
        </div>
        <div class="button-group">
            <button class="bouton-retour" onclick="window.location.href='logs.php'">Voir les logs</button>
        </div>
        <div class="admin-danger-zone" style="margin-top: 30px; text-align: center;">
          <form method="get" action="supprimer_portail.php" onsubmit="return confirm('⚠️ Cette action est irréversible. Êtes-vous sûr ?');">
              <button type="submit" class="bouton" style="background-color: #e53935; color: white; padding: 10px 20px; font-size: 18px;">
                  Supprimer mon portail
              </button>
          </form>
        </div>

    </div>

    <!-- Logs récents -->
    <?php
    // Récupérer la clé du portail de l'admin connecté
    $stmtCle = $pdo->prepare("SELECT portail FROM USER_ADM WHERE Nom = ?");
    $stmtCle->execute([$username]);
    $clePortail = $stmtCle->fetchColumn();
    
    // Afficher les 5 derniers logs liés à ce portail
    $stmtLogs = $pdo->prepare("SELECT * FROM LOGS WHERE portail_cle = ? ORDER BY timestamp DESC LIMIT 5");
    $stmtLogs->execute([$clePortail]);
    $logsRecents = $stmtLogs->fetchAll(PDO::FETCH_ASSOC);
    ?>
    <div class="log-container full-width">
        <h2>Dernières actions</h2>
        <table>
            <thead>
                <tr>
                    <th>Heure</th>
                    <th>Utilisateur</th>
                    <th>IP</th>
                    <th>Action</th>
                    <th>Client</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($logsRecents as $log): ?>
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
</div>

<!-- Pied de page avec déconnexion -->
<div class="footer">
    <a href="../pageadmin.php">
        <br>
        <button class="bouton-retour">Retour</button>
    </a>
    <form method="post" action="">
        <br>
        <button type="submit" name="logout" class="bouton-deco">Déconnexion</button> 
    </form>
</div>
</body>
</html>
