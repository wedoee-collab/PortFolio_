from datetime import datetime


class Salle:
    """
    ça représente une salle qu'on peut réserver
    """
    def __init__(self, numero):
        # le numero de la salle (ex: 101, 202...)
        self.numero = numero

    def __str__(self):
        # pour afficher la salle facilement
        return "Salle " + str(self.numero)


class Eleve:
    """
    ça représente un élève qui peut faire des réservations
    """
    def __init__(self, id_eleve, nom, prenom, penalites=0):
        # les infos de l'élève
        self.id_eleve = id_eleve
        self.nom = nom
        self.prenom = prenom
        self.penalites = penalites

    def nom_complet(self):
        # retourne le prénom et nom
        return self.prenom + " " + self.nom

    def peut_reserver(self):
        # verif si l'élève a le droit de réserver (moins de 50 pénalités)
        return self.penalites < 50

    def ajouter_penalite(self, points):
        # ajoute des points de pénalité
        self.penalites = self.penalites + points

    def est_bloque(self):
        # verif si l'élève est bloqué (50 pts ou plus)
        return self.penalites >= 50

    def vers_dict(self):
        # convertit en dict pour la sauvegarde
        return {
            "Nom": self.nom,
            "Prenom": self.prenom,
            "penalites": self.penalites
        }



class Reservation:
    """
    ça représente une réservation de salle.
    """
    def __init__(self, numero, salle, jour, heure_debut, heure_fin, eleve_id):
        # les infos de la réservation
        self.numero = numero
        self.salle = salle
        self.jour = jour
        self.heure_debut = heure_debut
        self.heure_fin = heure_fin
        self.eleve_id = eleve_id

    def chevauche(self, autre_debut, autre_fin):
        # verif si cette réservation chevauche un autre créneau
        return self.heure_debut < autre_fin and autre_debut < self.heure_fin

    def meme_salle_et_jour(self, salle, jour):
        # verif si c'est la meme salle et le meme jour
        return self.salle == salle and self.jour == jour

    def horaire_texte(self):
        # retourne l'horaire formaté en texte
        return str(self.heure_debut) + "h - " + str(self.heure_fin) + "h"

    def vers_dict(self):
        # convertit en dict pour la sauvegarde
        return {
            "salle": self.salle,
            "jour": self.jour,
            "heure_debut": self.heure_debut,
            "heure_fin": self.heure_fin,
            "eleve_id": self.eleve_id
        }

