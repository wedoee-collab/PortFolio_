"""
Configuration du systeme de reservation.
Contient toutes les constantes et les donnees initiales.
"""

# liste de toutes les salles dispo
SALLES = [101, 102, 103, 104, 105, 201, 202, 203, 204, 205]

# les jours des réservations qu'on peut faire
JOURS = ["Lundi", "Mardi", "Mercredi", "Jeudi", "Vendredi"]

# les horaires d'ouverture
HEURE_OUVERTURE = 8
HEURE_FERMETURE = 18

# pénalité par heure de retard
PENALITE_PAR_HEURE = 5

# fichier de sauvegarde
FICHIER_SAUVEGARDE = "db.json"

# élèves par défaut (on peut en rajouter si on veut)
ELEVES_DEFAUT = {
    1: {"Nom": "Routinha", "Prenom": "Marie", "penalites": 0},
    2: {"Nom": "Ducroix", "Prenom": "Michel", "penalites": 0},
    3: {"Nom": "Dupont", "Prenom": "Georges", "penalites": 0},
    4: {"Nom": "Delacroix", "Prenom": "Bernard", "penalites": 0},
    5: {"Nom": "Doe", "Prenom": "John", "penalites": 0}
}
