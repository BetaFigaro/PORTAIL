#!/bin/bash

KEY_PATH="/var/www/SITECONNECT/.pi_key"

echo "[PORTAIL STARTUP] D�marrage du Raspberry"

if [ -f "$KEY_PATH" ]; then
    echo "[PORTAIL STARTUP] Cl� d�tect�e. Lancement des services du portail."
    systemctl start mqtt-reset.service
    systemctl start portail_modem.service
    systemctl start portail_mqtt.service
else
    echo "[PORTAIL STARTUP] Aucune cl� d�tect�e. Lancement du hotspot."
    bash /var/www/SITECONNECT/start_hotspot.sh
fi

