<?php
// Projet PORTAIL - Codé par Rafael
// Fichier : index.php
// Cette page est la page de connexion principale du portail. Elle permet aux administrateurs ou utilisateurs normaux
// de se connecter. Les droits sont vérifiés ainsi que les plages horaires pour les utilisateurs normaux. Le système
// gère aussi les cookies de session, la mémorisation, les erreurs, et les redirections automatiques suivant le rôle de l'utilisateur.

session_start(); // Démarre une session PHP pour gérer les variables de session
require_once 'utils.php'; // Inclusion des fonctions utiles comme la connexion à la BDD et le logging

// Récupère le message de session en cas d'erreur précédente
$message = $_SESSION['login_message'] ?? '';
// Récupère le nom d'utilisateur précédemment saisi (utile pour le préremplissage)
$username = $_SESSION['login_username'] ?? '';
// Nettoyage des messages pour éviter qu'ils ne persistent
unset($_SESSION['login_message'], $_SESSION['login_username']);

// Traitement du formulaire si une requête POST est détectée
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupération des champs du formulaire
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    // Vérification des champs obligatoires
    if (empty($username) || empty($password)) {
        $_SESSION['login_message'] = "Veuillez remplir tous les champs.";
        $_SESSION['login_username'] = $username;
        header("Location: index.php");
        exit;
    }

    $pdo = getPDO(); // Connexion à la BDD

    // Tentative de connexion en tant qu'administrateur
    $stmt = $pdo->prepare("SELECT Nom, password FROM USER_ADM WHERE Nom = :username");
    $stmt->execute(['username' => $username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {
        // Connexion réussie pour un admin
        $_SESSION['username'] = $username;
        $_SESSION['Nom'] = $user['Nom'];
        $_SESSION['is_admin'] = true;
        // Récupération de la CLE du portail de l'admin
        $stmtCle = $pdo->prepare("SELECT portail FROM USER_ADM WHERE Nom = ?");
        $stmtCle->execute([$username]);
        $adminData = $stmtCle->fetch();
        $_SESSION['cle'] = $adminData['portail'];
        log_action($pdo, $username, 'Connexion réussie (admin)');

        // Gestion du cookie "Se souvenir de moi"
        if (isset($_POST['remember_me'])) {
            setcookie('remembered_user', $username, time() + (86400 * 30), "/"); // Cookie pour 30 jours
        } else {
            setcookie('remembered_user', '', time() - 3600, "/"); // Supprime le cookie si décoché
        }

        header("Location: pageadmin.php"); // Redirection vers l'espace admin
        exit;
    } elseif ($user) {
        // Mauvais mot de passe pour un admin
        $_SESSION['login_message'] = "Mot de passe incorrect.";
        log_action($pdo, $username, "Échec connexion admin : mauvais mot de passe");
        $_SESSION['login_username'] = $username;
        header("Location: index.php");
        exit;
    }

    // Si ce n'est pas un admin, vérifier s'il s'agit d'un utilisateur normal
    $stmt = $pdo->prepare("SELECT Nom, password, Heure_Debut, Heure_Fin FROM USER_NORM WHERE Nom = :username");
    $stmt->execute(['username' => $username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {
        $now = new DateTime();
        $start = new DateTime($user['Heure_Debut']);
        $end = new DateTime($user['Heure_Fin']);

        // Vérifie si la connexion est effectuée dans les plages horaires autorisées
        if ($now >= $start && $now <= $end) {
            $_SESSION['username'] = $username;
            $_SESSION['Nom'] = $user['Nom'];
            // Récupération de la CLE via l'admin lié à l'utilisateur
            $stmtCle = $pdo->prepare("SELECT portail FROM USER_ADM WHERE id = (SELECT Admin FROM USER_NORM WHERE Nom = ?)");
            $stmtCle->execute([$username]);
            $adminData = $stmtCle->fetch();
            $_SESSION['cle'] = $adminData['portail'];
            
            log_action($pdo, $username, 'Connexion réussie (utilisateur normal)');

            if (isset($_POST['remember_me'])) {
                setcookie('remembered_user', $username, time() + (86400 * 30), "/");
            } else {
                setcookie('remembered_user', '', time() - 3600, "/");
            }

            header("Location: pageuser.php"); // Redirige vers interface utilisateur
            exit;
        } else {
            $_SESSION['login_message'] = "Connexion refusée : en dehors des horaires autorisés.";
            log_action($pdo, $username, "Connexion refusée : hors horaires autorisés");
        }
    } else {
        // Soit mauvais mot de passe, soit utilisateur non trouvé
        $_SESSION['login_message'] = $user ? "Mot de passe incorrect." : "Nom d'utilisateur non trouvé.";
        log_action($pdo, $username, $user ? "Échec connexion : mauvais mot de passe" : "Échec connexion : utilisateur introuvable");
    }

    $_SESSION['login_username'] = $username;
    header("Location: index.php"); // Redirection finale en cas d'échec
    exit;
}
?>

<!DOCTYPE html>
<?php session_start(); ?>
<html> 
    <head>
        <meta charset="UTF-8">
        <title>Login</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="stylesheet" type="text/css" href="../CSS/styles.css">
        
        <script>
            // Affiche ou masque le mot de passe
            function togglePasswordVisibility() {
              const input = document.getElementById('password');
              input.type = input.type === 'password' ? 'text' : 'password';
            }

            // Met à jour l'horloge toutes les secondes
            function updateClock() {
                const clockElement = document.getElementById('current-time');
                const now = new Date();
                const timeString = now.toLocaleTimeString();
                clockElement.textContent = timeString;
            }

            setInterval(updateClock, 1000); // Mise à jour régulière
            document.addEventListener('DOMContentLoaded', updateClock); // Initialisation
        </script>
    </head>
    <body>
        <?php if (!empty($message)) {
          // Affichage d'une alerte JS si un message est présent
          echo "<script>alert(" . json_encode($message) . ");</script>";
        } ?>
        
        <!-- Horloge en haut -->
        <div id="current-time-container" style="text-align: center; margin-top: 10px;">
            <font face="arial" size="5" color="WHITE">
                <span id="current-time"></span>
            </font>
        </div>

        <!-- Titre de la page -->
        <div class="center-content">
            <font face="arial" size="7" color="WHITE"><b>GESTION DU PORTAIL</b></font>
            <font face="arial" size="6" color="WHITE"><b><br>CONNECTION</b></font>
        </div>

        <!-- Formulaire de connexion -->
        <form method="post" action="">
            <font face="arial" size="5" color="WHITE">
            <br> 
            <p> Nom :
                <input type="text" name="username" 
                    value="<?= isset($_COOKIE['remembered_user']) ? htmlspecialchars($_COOKIE['remembered_user']) : htmlspecialchars($username ?? '') ?>" />
            </p>
            <br> 
            <p>
              Mot de passe :
              <div class="password-wrapper">
                <input type="password" name="password" id="password" />
                <!-- Icône pour afficher/masquer le mot de passe -->
                <svg class="toggle-password" onclick="togglePasswordVisibility()" xmlns="http://www.w3.org/2000/svg" height="24" viewBox="0 0 24 24" width="24" fill="#555">
                  <path d="M0 0h24v24H0z" fill="none"/>
                  <path d="M12 4.5C7 4.5 2.7 8 1 12c1.7 4 6 7.5 11 7.5s9.3-3.5 11-7.5c-1.7-4-6-7.5-11-7.5zm0 13c-3 0-5.5-2.5-5.5-5.5S9 6.5 12 6.5s5.5 2.5 5.5 5.5S15 17.5 12 17.5zm0-9c-2 0-3.5 1.5-3.5 3.5S10 15.5 12 15.5s3.5-1.5 3.5-3.5S14 8.5 12 8.5z"/>
                </svg>
              </div>
            </p>
        
            <!-- Checkbox pour mémorisation -->
            <p>
                <label>
                    <input type="checkbox" name="remember_me" <?= isset($_COOKIE['remembered_user']) ? 'checked' : '' ?>>
                    Se souvenir de moi
                </label>
            </p>
        
            <p><input type="submit" value="CONNECTION" class="bouton"/></p>
        </form>
    </body>
</html>
