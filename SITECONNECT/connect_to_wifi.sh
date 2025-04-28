#!/bin/bash
# Projet PORTAIL - Codé par Rafael
# Ce script se connecte à un réseau Wi-Fi donné (SSID + mot de passe),
# coupe le hotspot s’il est actif, tente une reconnexion, et relance le hotspot si échec.

SSID="$1"           # Récupère le 1er argument (nom du réseau)
PASSWORD="$2"       # Récupère le 2e argument (mot de passe du réseau)
LOGFILE="/var/www/SITECONNECT/logs/wifi_connect_debug.log"  # Fichier log principal

# Petit log séparé pour vérifier que le script s’est bien lancé (debug)
echo "Script lancé à $(date)" >> /var/www/SITECONNECT/logs/test_lancement.log

# Initialisation du fichier log principal
echo "======== Nouvelle tentative à $(date) ========" > $LOGFILE
echo "SSID: $SSID" >> $LOGFILE
echo "Mot de passe: $PASSWORD" >> $LOGFILE  # ⚠️ À commenter en prod pour éviter d’exposer le mot de passe

# Petite pause pour laisser le temps à la page PHP de se charger proprement
sleep 5

# Vérifie que l’interface Wi-Fi (wlan0) existe
echo "[INFO] Vérification de l'interface wlan0..." >> $LOGFILE
if ! ip link show wlan0 > /dev/null; then
    echo "[ERROR] Interface wlan0 non trouvée !" >> $LOGFILE
    exit 1
fi

# Coupe le hotspot (créé par create_ap si actif)
echo "[INFO] Arrêt du hotspot..." >> $LOGFILE
sudo pkill create_ap >> $LOGFILE 2>&1
sleep 5

# Force un scan des réseaux Wi-Fi visibles
echo "[INFO] Forcer un scan Wi-Fi..." >> $LOGFILE
sudo nmcli dev wifi rescan ifname wlan0 >> $LOGFILE 2>&1
sleep 2

# Redémarre le NetworkManager pour s’assurer qu’il est bien fonctionnel
echo "[INFO] Redémarrage de NetworkManager..." >> $LOGFILE
sudo systemctl restart NetworkManager >> $LOGFILE 2>&1
sleep 2

# Log les infos de tentative de connexion
echo "[INFO] Tentative de connexion à $SSID avec mot de passe : $PASSWORD" >> $LOGFILE
echo "[INFO] Exécution de la commande de connexion : nmcli dev wifi connect '$SSID' password '$PASSWORD' ifname wlan0" >> $LOGFILE

# Tente 3 fois de se connecter au réseau Wi-Fi spécifié
for i in {1..3}
do
    echo "[INFO] Tentative de connexion #$i..." >> $LOGFILE
    sudo nmcli dev wifi connect "$SSID" password "$PASSWORD" ifname wlan0 >> $LOGFILE 2>&1
    sleep 2

    # Vérifie si la connexion est active (présente dans les connexions actives)
    if nmcli connection show --active | grep -q "$SSID"; then
        echo "[SUCCESS] Connexion réussie à $SSID !" >> $LOGFILE
        exit 0  # Succès → on quitte avec code 0
    fi
done

# Si aucune des 3 tentatives n’a réussi → relance le hotspot
echo "[ERROR] Connexion échouée après 3 tentatives. Relancement du hotspot..." >> $LOGFILE
sudo /var/www/SITECONNECT/start_hotspot.sh >> $LOGFILE 2>&1

exit 1  # On quitte avec code erreur pour prévenir PHP
