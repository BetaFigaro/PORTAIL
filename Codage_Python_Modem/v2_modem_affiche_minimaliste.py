import serial
import time

# Configuration du port série
MODEM_PORT = '/dev/ttyUSB0'  # PORT USB AUQUEL LE MODEM EST CONNECTé
BAUD_RATE = 115200  # Dépend de la configuration de votre modem
TIMEOUT = 1  # Temps d'attente pour lire la réponse du modem

def extract_and_display_number():
    modem = serial.Serial(MODEM_PORT, BAUD_RATE, timeout=TIMEOUT)
    modem.flush()

    while True:
        response = modem.readline().decode('utf-8').strip()

        # Ignorer les lignes inutiles (ATH, AT, RING, etc.)
        if response.startswith(('ATH', 'AT', 'RING')):
            continue

        if response.startswith('+CLIP'):
            # Extraire et afficher le numéro
            phone_number = response.split(',')[0].split(':')[1].strip().strip('"')
            if phone_number.startswith('+33'):
                phone_number = '0' + phone_number[3:]
            print(phone_number)

        time.sleep(1)

# Lancer l'extraction et l'affichage du numéro
if __name__ == '__main__':
    extract_and_display_number()
