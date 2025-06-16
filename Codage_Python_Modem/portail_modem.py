import serial
import time
import requests
import ssl

# Constantes
MODEM_PORT, BAUD_RATE, TIMEOUT = '/dev/ttyUSB0', 115200, 1
REDIRECT_URL = 'https://projet.betacorps.ovh/veriftel.php'

# Fonction pour envoyer le numéro au serveur
def redirect_to_site(phone_number):
    try:
        session = requests.Session()
        context = ssl.create_default_context()
        # Assurez-vous que le certificat est accessible et spécifié correctement
        context.load_verify_locations("/etc/ssl/certs/ca-certificates.crt")
        
        # Envoi de la requête sans afficher le code HTTP
        response = session.post(REDIRECT_URL, data={'tel': phone_number}, verify="/etc/ssl/certs/ca-certificates.crt")
        
        # Affichage seulement de la réponse du serveur, sans le code 200
        if "message publié" in response.text:
            print(f"Numéro {phone_number} publié avec succès.")
        else:
            print(f"Problème avec le numéro {phone_number}.")
            
        # Optionnel: Affichage partiel de la réponse du serveur pour vérification
        print("Réponse du serveur: ", response.text[:100])  # Afficher seulement les 100 premiers caractères de la réponse

    except Exception as e:
        print(f"Erreur: {e}")

# Fonction pour répondre et raccrocher l'appel
def handle_call(modem):
    modem.write(b'ATA\r')  # Répondre
    time.sleep(1)
    modem.write(b'AT+CHUP\r')  # Raccrocher
    time.sleep(1)

# Fonction pour extraire le numéro et traiter l'appel
def extract_and_process_number():
    with serial.Serial(MODEM_PORT, BAUD_RATE, timeout=TIMEOUT) as modem:
        modem.flush()
        while True:
            response = modem.readline().decode('utf-8').strip()
            if response.startswith('+CLIP'):
                phone_number = response.split(',')[0].split(':')[1].strip().strip('"')[3:]
                print(f"Numéro détecté: {phone_number}")
                handle_call(modem)  # Répondre et raccrocher
                redirect_to_site(phone_number)  # Envoi du numéro
            time.sleep(1)

# Démarrer le programme
if __name__ == '__main__':
    extract_and_process_number()
