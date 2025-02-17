# -*- coding: utf-8 -*-
import RPi.GPIO as GPIO
import paho.mqtt.client as mqtt
import time
import automationhat

# set GPIO numbering mode and define output pins
GPIO.setmode(GPIO.BCM)
GPIO.setup(19,GPIO.OUT)

#fonction pour le callback
def on_message(client, userdata, message):
    mess = str(message.payload.decode("utf-8"))
    print("message received ", str(message.payload.decode("utf-8")))
    print("message topic=", message.topic)
            
client1 = mqtt.Client()
client1.username_pw_set("portail", "Cu2kscd#bpF")
client1.connect("192.168.1.226")
client1.subscribe("portail/status")
client1.on_message = on_message
client1.loop_start()
time.sleep(40)
client1.loop_stop()


if(client1.subscribe("portail/status" == "OUVERTURE")):
    # cycle those relays
    GPIO.output(19,True)
    time.sleep(1)
    GPIO.output(19,False)
    client1.publish("portail/status", "FERMER")
    time.sleep(1)

client1.disconnect()
# cleanup the GPIO before finishing :)
GPIO.cleanup()

print("Le programme est bel et bien fonctionnel")




    
