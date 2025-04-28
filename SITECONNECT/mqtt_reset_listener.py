#!/usr/bin/env python3
# Projet PORTAIL - Codé par Rafael
# Script lancé sur le Raspberry Pi pour écouter les instructions MQTT de type "RESET"
# Objectif : réinitialiser l'appareil (suppression clé, reset Wi-Fi, relancer hotspot) puis s'arrêter

import paho.mqtt.client as mqtt
import os
import ssl
import subprocess
import time
import socket

# === ������ Fonction pour charger la clé locale (.pi_key) ===
def charger_cle():
    try:
        with open("/var/www/SITECONNECT/.pi_key", "r") as f:
            return f.read().strip()
    except FileNotFoundError:
        print("Clé .pi_key introuvable.")
        return None

# === ������ Callback : appelée à la réception d’un message MQTT ===
def on_message(client, userdata, message):
    mess = message.payload.decode("utf-8")
    print(f"Message reçu sur {message.topic} : {mess}")

    if mess == "RESET":
        print("Instruction RESET reçue.")

        # === Étape 1 : Vider le topic MQTT (supprimer le message retain) ===
        try:
            client.publish(TOPIC, payload="", qos=0, retain=True)
            print(f"Topic {TOPIC} vidé (retain supprimé).")
        except Exception as e:
            print(f"Erreur suppression retain topic : {e}")

        # === Étape 2 : Supprimer la clé .pi_key ===
        try:
            os.remove("/var/www/SITECONNECT/.pi_key")
            print("Clé supprimée.")
        except FileNotFoundError:
            print("Clé déjà absente.")
        except Exception as e:
            print(f"Erreur suppression : {e}")

        # === Étape 3 : Vider le fichier de configuration Wi-Fi ===
        try:
            print("Vidage de la config Wi-Fi...")
            if os.path.exists("/etc/wpa_supplicant/wpa_supplicant.conf"):
                with open("/etc/wpa_supplicant/wpa_supplicant.conf", "w") as f:
                    f.write("")
                print("Fichier vidé.")
        except Exception as e:
            print(f"Erreur suppression config wpa_supplicant : {e}")

        # === Étape 4 : Petite pause ===
        time.sleep(5)

        # === Étape 4.1 : Redémarrer l'interface wlan0 ===
        try:
            subprocess.run(["sudo", "ip", "link", "set", "wlan0", "down"], check=True)
            time.sleep(2)
            subprocess.run(["sudo", "ip", "link", "set", "wlan0", "up"], check=True)
            print("Interface wlan0 redémarrée.")
        except Exception as e:
            print(f"Erreur redémarrage interface wlan0 : {e}")

        # === Étape 5 : Relancer le hotspot ===
        try:
            subprocess.run(["bash", "/var/www/SITECONNECT/start_hotspot.sh"], check=True)
            print("Hotspot relancé.")
        except Exception as e:
            print(f"Erreur relance hotspot : {e}")

        # === Étape 6 : Pause courte avant arrêt du service ===
        time.sleep(2)

        # === Étape 7 : Arrêter le service systemd lui-même ===
        try:
            subprocess.run(["sudo", "systemctl", "stop", "mqtt-reset.service"], check=True)
            print("Service mqtt-reset.service stoppé.")
        except Exception as e:
            print(f"Erreur arrêt service : {e}")

# === MAIN ===

CLE = charger_cle()
if not CLE:
    exit(1)

TOPIC = f"portail/{CLE}/action"
print(f"Abonnement au topic : {TOPIC}")

# Configuration du client MQTT
client = mqtt.Client()
client.username_pw_set("portail", "Cu2kscd#bpF")
client.tls_set(
    ca_certs="/etc/ssl/certs/ca-certificates.crt",
    tls_version=ssl.PROTOCOL_TLS
)
client.on_message = on_message

# Protection contre les erreurs réseau (DNS down, etc.)
try:
    socket.gethostbyname("mqtt.projet.betacorps.ovh")
    client.connect("mqtt.projet.betacorps.ovh", port=8884, keepalive=60)
    client.subscribe(TOPIC)
    client.loop_forever()
except socket.gaierror:
    print("Connexion impossible au broker MQTT (DNS ou réseau KO).")
except Exception as e:
    print(f"Erreur connexion MQTT : {e}")
