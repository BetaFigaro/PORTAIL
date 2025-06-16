import serial
import time
import requests

# Configuration du port série
MODEM_PORT = '/dev/ttyUSB0'  # PORT USB AUQUEL LE MODEM EST CONNECTé
BAUD_RATE = 115200  # Dépend de la configuration de votre modem
TIMEOUT = 1  # Temps d'attente pour lire la réponse du modem

# URL du site pour rediriger le numéro de téléphone
REDIRECT_URL = 'https://projet.betacorps.ovh/veriftel.php'  

# Fonction pour envoyer le numéro de téléphone via POST
def redirect_to_site(phone_number):
    try:
        # Envoyer le numéro de téléphone avec une requête POST
        print(f"Envoi du numéro : {phone_number} vers le site {REDIRECT_URL}")
        response = requests.post(REDIRECT_URL, data={'number': phone_number})
        if response.status_code == 200:
            print(f"Le numéro {phone_number} a été redirigé vers {REDIRECT_URL}.")
        else:
            print(f"Erreur lors de la redirection du numéro. Status Code: {response.status_code}")
    except Exception as e:
        print(f"Erreur lors de la demande HTTP: {e}")

# Fonction pour récupérer et afficher le numéro de téléphone
def extract_and_display_number():
    modem = serial.Serial(MODEM_PORT, BAUD_RATE, timeout=TIMEOUT)
    modem.flush()

    while True:
        response = modem.readline().decode('utf-8').strip()

        # Ignorer les lignes inutiles comme ATH, AT, RING, etc.
        if response.startswith(('ATH', 'AT', 'RING')):
            continue

        # Si la réponse commence par +CLIP, c'est un appel entrant avec le numéro
        if response.startswith('+CLIP'):
            # Extraire le numéro de téléphone
            phone_number = response.split(',')[0].split(':')[1].strip().strip('"')

            # Retirer le +33 au début du numéro
            phone_number = phone_number[3:]  # Retirer le +33

            print(f"Numéro de téléphone : {phone_number}")

            # Rediriger le numéro vers le site web
            redirect_to_site(phone_number)

        time.sleep(1)

# Lancer l'extraction et l'envoi du numéro
if __name__ == '__main__':
    extract_and_display_number()
