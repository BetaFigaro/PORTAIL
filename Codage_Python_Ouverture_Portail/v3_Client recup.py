import paho.mqtt.client as mqtt
import time

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
while(True):
    pass
client1.loop_stop()