import serial
import time
import requests
import ssl
from requests.adapters import HTTPAdapter
from urllib3.poolmanager import PoolManager

# Configuration du port série
MODEM_PORT = '/dev/ttyUSB0'  # PORT USB AUQUEL LE MODEM EST CONNECTé
BAUD_RATE = 115200  # Dépend de la configuration de votre modem
TIMEOUT = 1  # Temps d'attente pour lire la réponse du modem

# URL du site pour rediriger le numéro de téléphone
REDIRECT_URL = 'https://projet.betacorps.ovh/veriftel.php'  # Remplace par ton URL

# Fonction de configuration TLS
class TLSAdapter(HTTPAdapter):
    def __init__(self, ssl_context=None, **kwargs):
        self.ssl_context = ssl_context
        super().__init__(**kwargs)

    def init_poolmanager(self, *args, **kwargs):
        context = self.ssl_context or ssl.create_default_context()
        kwargs['ssl_context'] = context
        return super().init_poolmanager(*args, **kwargs)

# Fonction pour envoyer le numéro de téléphone via POST avec TLS (données en URL-encoded)
def redirect_to_site(phone_number):
    try:
        # Créer un contexte SSL/TLS
        context = ssl.create_default_context()
        context.load_verify_locations("/etc/ssl/certs/ca-certificates.crt")  # Spécifie le fichier CA

        # Créer une session de requêtes et ajouter l'adaptateur TLS
        session = requests.Session()
        adapter = TLSAdapter(ssl_context=context)
        session.mount('https://', adapter)

        # Préparer les données à envoyer dans le bon format
        data = {'tel': phone_number}  # Données en format x-www-form-urlencoded
        headers = {'Content-Type': 'application/x-www-form-urlencoded'}  # En-tête de la requête

        # Envoyer le numéro de téléphone avec une requête POST sécurisée
        print(f"Envoi du numéro : {phone_number} vers le site {REDIRECT_URL}")
        response = session.post(REDIRECT_URL, data=data)

        if response.status_code == 200:
            print(f"Le numéro {phone_number} a été redirigé vers {REDIRECT_URL}.")
        else:
            print(f"Erreur lors de la redirection du numéro. Status Code: {response.status_code}")
            print(f"Réponse du serveur : {response.text}")
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

            # Retirer le +33 au début du numéro (si présent)
            phone_number = phone_number[3:]  # Retirer le +33

            print(f"Numéro de téléphone : {phone_number}")

            # Rediriger le numéro vers le site web avec TLS
            redirect_to_site(phone_number)

        time.sleep(1)

# Lancer l'extraction et l'envoi du numéro
if __name__ == '__main__':
    extract_and_display_number()
