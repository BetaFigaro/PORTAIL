#!/bin/bash
# Stoppe dnsmasq si actif (évite les conflits avec create_ap)
sudo systemctl stop dnsmasq

# Lance le hotspot avec create_ap (utilise wlan0 pour l'AP, eth0 comme interface source)
sudo create_ap wlan0 eth0 WIFI_PORTAIL1 motdepasse123
