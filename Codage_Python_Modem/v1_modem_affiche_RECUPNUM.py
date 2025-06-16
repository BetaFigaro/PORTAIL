import serial
import time

# Configuration du port série
MODEM_PORT = '/dev/ttyUSB0'  # PORT USB AUQUEL LE MODEM EST CONNECTé
BAUD_RATE = 115200  # Dépend de la configuration de votre modem
TIMEOUT = 1  # Temps d'attente pour lire la réponse du modem

# Fonction pour extraire et afficher le numéro de téléphone
def extract_and_display_number():
    # Ouvrir la connexion série avec le modem
    modem = serial.Serial(MODEM_PORT, BAUD_RATE, timeout=TIMEOUT)
    modem.flush()

    while True:
        # Lire la ligne du modem
        response = modem.readline().decode('utf-8').strip()

        # Ignorer les lignes qui ne nous intéressent pas (ATH, AT, AT+CLIP=1, etc.)
        if response.startswith('ATH') or response.startswith('AT') or response.startswith('RING'):
            continue

        # Afficher la réponse brute après AT+CLIP=1
        if response.startswith('+CLIP'):
            print(f"Réponse brute après AT+CLIP=1 : {response}")

            # Exemple de réponse attendue : +CLIP: "+33553775600",145,"",0,,0
            try:
                # Extraire le numéro de téléphone de la réponse
                phone_number = response.split(',')[0].split(':')[1].strip().strip('"')

                # Remplacer +33 par 0 pour obtenir le numéro français
                if phone_number.startswith('+33'):
                    phone_number = '0' + phone_number[3:]

                # Afficher uniquement le numéro de téléphone
                print(f"Numéro appelant : {phone_number}")

            except IndexError:
                print("Erreur lors de l'extraction du numéro.")

        time.sleep(1)  # Attente avant la prochaine lecture

# Lancer l'extraction et l'affichage du numéro
if __name__ == '__main__':
    extract_and_display_number()
