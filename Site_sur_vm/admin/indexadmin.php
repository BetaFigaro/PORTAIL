<!DOCTYPE html>
<?php
session_start(); // Démarrer la session pour récupérer les infos

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['username'])) {
    header('Location: ..\index.php');
    exit;
}

// Vérifier si l'utilisateur est admin
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header('Location: ..\pageuser.php');
    exit;
}

// Gestion de la déconnexion
if (isset($_POST['logout'])) {
    session_unset();
    session_destroy();
    header('Location: ..\index.php');
    exit;
}

// Temps d'inactivité maximal (15 minutes)
$timeout_duration = 900;
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $timeout_duration) {
    session_unset();
    session_destroy();
    header("Location: index.php");
    exit();
}
$_SESSION['last_activity'] = time();

// Connexion à la BDD
$host = 'localhost';
$dbname = 'PORTAIL';
$dbuser = 'Raffle0793';
$dbpass = 'CtOnZ3R#MBK';

$conn = new mysqli($host, $dbuser, $dbpass, $dbname);
if ($conn->connect_error) {
    die("Connexion échouée : " . $conn->connect_error);
}

// Récupérer l'ID de l'admin connecté via son username stocké en session
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

// Sélectionner uniquement les utilisateurs liés à cet admin (filtre par ID)
$sql = "SELECT * FROM USER_NORM WHERE Admin = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $adminId);
$stmt->execute();
$result = $stmt->get_result();
?>
<html>
<head>
    <title>Interface Administrateur</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" type="text/css" href="../CSS/styles.css"> 
    <style>
        body {
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            height: 100vh;
            margin: 0;
        }
    </style>
    <script>
        function confirmDelete(id) {
            if (confirm("Voulez-vous vraiment supprimer cet utilisateur ?")) {
                window.location.href = `delete.php?ID=${id}`;
            }
        }
    </script>
</head>
<div class="header">
    <font face="arial" size="6" color="WHITE">
        <b>INTERFACE ADMINISTRATEUR</b>
    </font>
</div>

<div class="content">
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
                            <td><?php echo htmlspecialchars($row['Nom']); ?></td>
                            <td><?php echo htmlspecialchars($row['Prenom']); ?></td>
                            <td><?php echo htmlspecialchars($row['email']); ?></td>
                            <td><?php echo htmlspecialchars($row['tel']); ?></td>
                            <td><?php echo htmlspecialchars($row['Heure_Debut']); ?></td>
                            <td><?php echo htmlspecialchars($row['Heure_Fin']); ?></td>
                            <td>
                                <button class="delete-button" onclick="confirmDelete('<?php echo $row['ID']; ?>')">
                                    <img src="..\images\corbeille.png" alt="Corbeille" width="30">
                                </button>
                                <a href="modification_utilisateur.php?ID=<?php echo $row['ID']; ?>" class="modify-button">
                                    <img src="..\images\crayon.png" alt="Modifier" width="30">
                                </a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8">Aucune donnée trouvée</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    
    <div class="right-container">
        <h2>Options</h2>
        <br>
        <div class="button-group">
            <button class="bouton-retour" onclick="window.location.href='ajout_utilisateur.php'">Ajout utilisateur</button>
        </div>
    </div>
</div>

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