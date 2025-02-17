# -*- coding: utf-8 -*-

# import GPIO and time
import RPi.GPIO as GPIO
import automationhat
import time

# set GPIO numbering mode and define output pins
GPIO.setmode(GPIO.BCM)
GPIO.setup(19,GPIO.OUT)

# cycle those relays
GPIO.output(19,True)
time.sleep(1)
GPIO.output(19,False)

# cleanup the GPIO before finishing :)
GPIO.cleanup()
    
