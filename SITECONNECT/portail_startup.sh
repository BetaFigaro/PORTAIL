#!/bin/bash

KEY_PATH="/var/www/SITECONNECT/.pi_key"

echo "[PORTAIL STARTUP] Démarrage du Raspberry"

if [ -f "$KEY_PATH" ]; then
    echo "[PORTAIL STARTUP] Clé détectée. Lancement des services du portail."
    systemctl start mqtt-reset.service
    systemctl start portail_modem.service
    systemctl start portail_mqtt.service
else
    echo "[PORTAIL STARTUP] Aucune clé détectée. Lancement du hotspot."
    bash /var/www/SITECONNECT/start_hotspot.sh
fi

