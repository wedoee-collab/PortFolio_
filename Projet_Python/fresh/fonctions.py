import json
import os
from datetime import datetime
from config import SALLES, FICHIER_SAUVEGARDE
from classes import Salle, Eleve, Reservation


# FONCTIONS DE VERIFICATION


def les_horaires_se_chevauchent(debut1, fin1, debut2, fin2):
    """
    ça verif si deux créneaux se chevauchent
    Retourne True si chevauchement et False sinon
    """
    return debut1 < fin2 and debut2 < fin1


def salle_est_disponible(salle, jour, heure_debut, heure_fin, reservations):
    """
    ça verif si une salle est libre pour un créneau donné
    maintenant on utilise les methodes de l'objet Reservation
    """
    for resa in reservations.values():
        if resa.meme_salle_et_jour(salle, jour):
            if resa.chevauche(heure_debut, heure_fin):
                return False
    return True


def trouver_salles_alternatives(jour, heure_debut, heure_fin, salle_exclue, reservations):
    """
    trouve les salles disponibles pour un créneau (sauf celle demandée)
    """
    salles_libres = []
    for salle in SALLES:
        if salle != salle_exclue:
            if salle_est_disponible(salle, jour, heure_debut, heure_fin, reservations):
                salles_libres.append(salle)
    return salles_libres


def verifier_droit_reservation(eleve_id, eleves):
    """
    ça verif si un élève a le droit de réserver (moins de 50 pénalités)
    on utilise la methode peut_reserver() de l'objet Eleve
    """
    if eleve_id in eleves:
        return eleves[eleve_id].peut_reserver()
    return False


# FONCTIONS HISTORIQUE ET PENALITÉS


def ajouter_dans_historique(type_action, details, historique):
    """
    ajoute une action dans l'historique avec date/heure
    """
    entree = {
        "type": type_action,
        "date_heure": datetime.now().strftime("%d/%m/%Y %H:%M:%S"),
        "details": details
    }
    historique.append(entree)


def ajouter_penalite(eleve_id, points, raison, eleves, historique):
    """
    ajoute des points de pénalité à un élève
    on utilise la methode ajouter_penalite() de l'objet Eleve
    """
    if eleve_id in eleves:
        eleves[eleve_id].ajouter_penalite(points)
        details = {"eleve_id": eleve_id, "points": points, "raison": raison}
        ajouter_dans_historique("penalite", details, historique)
        return True
    return False


# FONCTIONS STATISTIQUES


def calculer_statistiques(reservations, eleves, salles, jours):
    """
    Calcule les statistiques d'utilisation.
    """
    stats = {
        "total_reservations": len(reservations),
        "reservations_par_salle": {},
        "reservations_par_jour": {},
        "reservations_par_eleve": {}
    }

    # init les compteurs a 0
    for s in salles:
        stats["reservations_par_salle"][s] = 0
    for j in jours:
        stats["reservations_par_jour"][j] = 0
    for e in eleves:
        stats["reservations_par_eleve"][e] = 0

    # comptage en utilisant les attributs des objets Reservation
    for resa in reservations.values():
        salle = resa.salle
        jour = resa.jour
        eleve_id = resa.eleve_id

        if salle in stats["reservations_par_salle"]:
            stats["reservations_par_salle"][salle] = stats["reservations_par_salle"][salle] + 1
        if jour in stats["reservations_par_jour"]:
            stats["reservations_par_jour"][jour] = stats["reservations_par_jour"][jour] + 1
        if eleve_id in stats["reservations_par_eleve"]:
            stats["reservations_par_eleve"][eleve_id] = stats["reservations_par_eleve"][eleve_id] + 1

    # trouver les plus populaires
    stats["salle_plus_populaire"] = None
    stats["jour_plus_charge"] = None

    max_salle = 0
    for salle, count in stats["reservations_par_salle"].items():
        if count > max_salle:
            max_salle = count
            stats["salle_plus_populaire"] = salle

    max_jour = 0
    for jour, count in stats["reservations_par_jour"].items():
        if count > max_jour:
            max_jour = count
            stats["jour_plus_charge"] = jour

    return stats


# FONCTIONS DE CREATION D'OBJETS


def creer_eleve(id_eleve, donnees):
    """
    ça crée un objet Eleve a partir d'un dict
    """
    return Eleve(
        id_eleve,
        donnees["Nom"],
        donnees["Prenom"],
        donnees.get("penalites", 0)
    )


def creer_reservation(numero, donnees):
    """
    ça crée un objet Reservation a partir d'un dict aussi
    """
    return Reservation(
        numero,
        donnees["salle"],
        donnees["jour"],
        donnees["heure_debut"],
        donnees["heure_fin"],
        donnees["eleve_id"]
    )


# FONCTIONS SAUVEGARDE


def sauvegarder_donnees(reservations, eleves, historique, compteur):
    """
    sauvegarde toutes les données dans un fichier json
    on convertit les objets en dicts avec vers_dict()
    """
    # convertit les reservations (objets) en dicts
    resa_dict = {}
    for num, resa in reservations.items():
        resa_dict[str(num)] = resa.vers_dict()

    # convertit les eleves (objets) en dicts
    eleves_dict = {}
    for id_el, eleve in eleves.items():
        eleves_dict[str(id_el)] = eleve.vers_dict()

    donnees = {
        "reservations": resa_dict,
        "eleves": eleves_dict,
        "historique": historique,
        "numero_reservation": compteur
    }

    try:
        with open(FICHIER_SAUVEGARDE, "w", encoding="utf-8") as f:
            json.dump(donnees, f, indent=4, ensure_ascii=False)
        return True
    except:
        return False


def charger_donnees(eleves_defaut):
    """
    charge les données depuis le fichier json
    retourne (reservations, eleves, historique, compteur)
    maintenant ça crée des objets Eleve et Reservation au lieu de dicts
    """
    if not os.path.exists(FICHIER_SAUVEGARDE):
        # pas de fichier, on crée les objets Eleve a partir des dicts par defaut
        eleves_obj = {}
        for id_el, donnees in eleves_defaut.items():
            eleves_obj[id_el] = creer_eleve(id_el, donnees)
        return {}, eleves_obj, [], 0

    try:
        with open(FICHIER_SAUVEGARDE, "r", encoding="utf-8") as f:
            donnees = json.load(f)

        # crée les objets Reservation a partir des dicts
        reservations = {}
        for num_str, resa_dict in donnees["reservations"].items():
            num = int(num_str)
            reservations[num] = creer_reservation(num, resa_dict)

        # crée les objets Eleve a partir des dicts
        eleves = {}
        for id_str, eleve_dict in donnees["eleves"].items():
            id_el = int(id_str)
            eleves[id_el] = creer_eleve(id_el, eleve_dict)

        return reservations, eleves, donnees["historique"], donnees["numero_reservation"]
    except:
        # si ça plante on repart avec les valeurs par defaut
        eleves_obj = {}
        for id_el, donnees in eleves_defaut.items():
            eleves_obj[id_el] = creer_eleve(id_el, donnees)
        return {}, eleves_obj, [], 0
