import paho.mqtt.client as mqtt
import time


client1 = mqtt.Client()
client1.username_pw_set("portail", "Cu2kscd#bpF")
client1.connect("192.168.1.226")

client1.publish("portail/status", "FERMER")
time.sleep(1)
client1.disconnect()