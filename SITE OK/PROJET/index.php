<!DOCTYPE html>
<?php
session_start(); // Démarrer la session pour récupérer les informations de l'utilisateur
?>
<html> 
    <head>
        <!-- gestion accent -->
        <meta charset="UTF-8">
        <title>Login</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <!-- lien fichier css -->
        <link rel="stylesheet" type="text/css" href="../CSS/styles.css">
        
        <script>
            function togglePasswordVisibility() {
                const passwordInput = document.getElementById('password');
                const toggleCheckbox = document.getElementById('show-password');
                passwordInput.type = toggleCheckbox.checked ? 'text' : 'password';
            }

            function updateClock() {
                const clockElement = document.getElementById('current-time');
                const now = new Date();
                const timeString = now.toLocaleTimeString();
                clockElement.textContent = timeString;
            }

            // Mettre à jour l'heure toutes les secondes
            setInterval(updateClock, 1000);

            // Initialiser l'heure au chargement de la page
            document.addEventListener('DOMContentLoaded', updateClock);
        </script>
    </head>
    <body> 
        <!-- Section pour afficher l heure actuelle -->
        <div id="current-time-container" style="text-align: center; margin-top: 10px;">
            <font face="arial" size="5" color="WHITE">
                <span id="current-time"></span>
            </font>
        </div>

        <div class="center-content">
            <font face="arial" size="7" color="WHITE">
                <b>GESTION DU PORTAIL</b>
            </font>
            
            <font face="arial" size="6" color="WHITE">
                <b>
                <br>
                CONNECTION
                </b>
            </font>
        </div>

        <!-- Formulaire de connection -->
        <form method="post" action="login.php">
            <font face="arial" size="5" color="WHITE">
            <br> 
            <p> Nom d'utilisateur : <input type="text" name="username" /></p>
            <br> 
            <p> 
                Mot de passe : 
                <input type="password" name="password" id="password" />
                <label>
                    <input type="checkbox" id="show-password" onclick="togglePasswordVisibility()" />
                    Afficher le mot de passe
                </label>
            </p>
            <p><input type="submit" value="CONNECTION" class="bouton"/></p>
        </form>
        
        <div class="center-container">
            <a href="creauser/creauser.php" class="bouton-retour">Créer un compte</a>
        </div>

    </body>
</html>
