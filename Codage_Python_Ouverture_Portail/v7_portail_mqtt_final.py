# -*- coding: utf-8 -*- 
import RPi.GPIO as GPIO
import paho.mqtt.client as mqtt
import time
import automationhat

# Définir le mode de numérotation des broches GPIO et spécifier les broches de sortie
GPIO.setmode(GPIO.BCM)
GPIO.setup(19, GPIO.OUT)

# Fonction appelée automatiquement lorsqu'un message est reçu sur le topic MQTT abonné.
def on_message(client, userdata, message):
    mess = str(message.payload.decode("utf-8"))
    print("message reçu:", mess)
    print("message topic=", message.topic)
    
    if mess == "OUVERTURE":
        # cycle les relais
        GPIO.output(19, True)
        time.sleep(1)
        GPIO.output(19, False)
        client.publish("portail/status", "FERMER")  # publier "FERMER" après l'ouverture
        print("Le relais a été activé.")
    elif mess == "FERMER":
        print("Aucune action, statut: FERMER.")

# Initialiser le client MQTT
client = mqtt.Client()
client.username_pw_set("portail", "Cu2kscd#bpF")
client.connect("82.66.89.105")
client.subscribe("portail/status")
client.on_message = on_message

# Démarrer la boucle MQTT
client.loop_start()

# Maintenir le script en fonctionnement infini
try:
    while True:
        # Cette boucle infinie empêche le script de se terminer
        time.sleep(1)  # Pause pour éviter une boucle trop gourmande en CPU
except KeyboardInterrupt:
    # Pour interrompre proprement le script avec Ctrl+C
    print("Interruption du script par l'utilisateur.")
    client.loop_stop()  # Arrêter la boucle MQTT proprement
    GPIO.cleanup()  # Nettoyer les configurations GPIO avant de quitter

