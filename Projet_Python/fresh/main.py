"""
Interface graphique du système pour faire des réservations de salles.
Maintenant ça utilise la POO avec les classes Eleve et Reservation.
"""

import tkinter as tk
from tkinter import messagebox, ttk

from config import SALLES, JOURS, HEURE_OUVERTURE, HEURE_FERMETURE, ELEVES_DEFAUT
from classes import Reservation
from fonctions import (
    salle_est_disponible, trouver_salles_alternatives, verifier_droit_reservation,
    ajouter_dans_historique, ajouter_penalite, calculer_statistiques,
    sauvegarder_donnees, charger_donnees, les_horaires_se_chevauchent
)


def creer_interface():
    """
    Crée et lance l'interface graphique principale
    """
    # là on charge les données sauvegardées (ou on prend les valeurs par défaut)
    # maintenant ça retourne des objets Eleve et Reservation au lieu de dicts
    reservations, eleves, historique, compteur = charger_donnees(ELEVES_DEFAUT)
    compteur = [compteur]  # on met dans une liste pour pouvoir modifier dans les fonctions

    # ici ça crée la fenêtre principale
    fenetre = tk.Tk()
    fenetre.title("Système de réservation de Salles")
    fenetre.geometry("900x700")
    fenetre.configure(bg="#f0f0f0")

    # ça crée une liste des IDs des élèves pour pouvoir les retrouver facilement
    liste_ids_eleves = []
    for id_eleve in eleves:
        liste_ids_eleves.append(id_eleve)


    def rafraichir_tableau():
        """
        Met à jour le tableau des réservations
        On supprime toutes les lignes puis on les recrée
        """
        # d'abord on recup tous les éléments du tableau
        tous_les_elements = tableau.get_children()

        # ça les supprime un par un avec une boucle
        for element in tous_les_elements:
            tableau.delete(element)

        # ensuite on parcourt toutes les réservations avec une boucle
        # recup d'abord les numéros de réservation
        numeros = []
        for num in reservations:
            numeros.append(num)

        # puis on parcourt chaque numéro
        for i in range(len(numeros)):
            num = numeros[i]
            resa = reservations[num]

            # récupère les infos de la réservation (maintenant c'est des attributs d'objet)
            eleve_id = resa.eleve_id
            salle = resa.salle
            jour = resa.jour

            # herche le nom de l'élève avec la methode nom_complet()
            nom = "Inconnu"
            if eleve_id in eleves:
                nom = eleves[eleve_id].nom_complet()

            # formate l'horaire avec la methode de l'objet
            horaire = resa.horaire_texte()

            # ajoute la ligne dans le tableau
            tableau.insert("", "end", values=(num, salle, jour, horaire, nom))


    def faire_reservation():
        """
        Crée une nouvelle réservation
        On verif tout avant d'enregistrer
        """
        # récupère l'index sélectionné dans les listes déroulantes
        index_eleve = combo_eleve.current()  # ça donne -1 si rien n'est sélectionné
        index_jour = combo_jour.current()

        # verif qu'un élève et un jour sont sélectionnés
        if index_eleve < 0:
            messagebox.showerror("Erreur", "Sélectionnez un élève.")
            return

        if index_jour < 0:
            messagebox.showerror("Erreur", "Sélectionnez un jour.")
            return

        # essaie de convertir les heures et la salle en nombres
        texte_debut = entree_debut.get()
        texte_fin = entree_fin.get()
        texte_salle = entree_salle.get()

        # verif que ce sont bien des nombres
        h_debut = 0
        h_fin = 0
        salle = 0

        # pour l'heure de début
        est_valide = True
        for caractere in texte_debut:
            if caractere < "0" or caractere > "9":
                est_valide = False

        if est_valide and len(texte_debut) > 0:
            h_debut = int(texte_debut)
        else:
            messagebox.showerror("Erreur", "L'heure de début doit être un nombre.")
            return

        # pour l'heure de fin
        est_valide = True
        for caractere in texte_fin:
            if caractere < "0" or caractere > "9":
                est_valide = False

        if est_valide and len(texte_fin) > 0:
            h_fin = int(texte_fin)
        else:
            messagebox.showerror("Erreur", "L'heure de fin doit être un nombre.")
            return

        # pour la salle
        est_valide = True
        for caractere in texte_salle:
            if caractere < "0" or caractere > "9":
                est_valide = False

        if est_valide and len(texte_salle) > 0:
            salle = int(texte_salle)
        else:
            messagebox.showerror("Erreur", "La salle doit être un nombre.")
            return

        # verif que l'heure de début est valide
        if h_debut < HEURE_OUVERTURE or h_debut >= HEURE_FERMETURE:
            message = "Heure de début entre " + str(HEURE_OUVERTURE) + " et " + str(HEURE_FERMETURE - 1)
            messagebox.showerror("Erreur", message)
            return

        # verif que l'heure de fin est valide
        if h_fin <= h_debut:
            messagebox.showerror("Erreur", "L'heure de fin doit être après l'heure de début.")
            return

        if h_fin > HEURE_FERMETURE:
            messagebox.showerror("Erreur", "L'heure de fin ne peut pas dépasser " + str(HEURE_FERMETURE) + "h.")
            return

        # verif que la salle existe
        salle_existe = False
        for s in SALLES:
            if s == salle:
                salle_existe = True

        if not salle_existe:
            messagebox.showerror("Erreur", "Salle invalide. Choisir 101-105 ou 201-205.")
            return

        # recup l'ID de l'élève à partir de l'index
        eleve_id = liste_ids_eleves[index_eleve]

        # recup le jour à partir de l'index
        jour = JOURS[index_jour]

        # verif que l'élève a le droit de réserver (pas trop de pénalités)
        if not verifier_droit_reservation(eleve_id, eleves):
            messagebox.showerror("Erreur", "Cet élève est bloqué (trop de pénalités).")
            return

        # verif que la salle est disponible
        if salle_est_disponible(salle, jour, h_debut, h_fin, reservations):
            # la salle est libre, on crée la réservation
            compteur[0] = compteur[0] + 1  # incrémente le numéro
            numero_resa = compteur[0]

            # crée la réservation en tant qu'objet Reservation maintenant
            nouvelle_resa = Reservation(numero_resa, salle, jour, h_debut, h_fin, eleve_id)
            reservations[numero_resa] = nouvelle_resa

            # ajoute dans l'historique
            details = {
                "salle": salle,
                "jour": jour,
                "heure_debut": h_debut,
                "heure_fin": h_fin,
                "eleve_id": eleve_id
            }
            ajouter_dans_historique("creation", details, historique)

            # sauvegarde les données
            sauvegarder_donnees(reservations, eleves, historique, compteur[0])

            # affiche un message de succès
            messagebox.showinfo("Succès", "Réservation #" + str(numero_resa) + " confirmée !")

            # rafraîchit le tableau
            rafraichir_tableau()
        else:
            # la salle est occupée, on cherche des alternatives
            alternatives = trouver_salles_alternatives(jour, h_debut, h_fin, salle, reservations)

            # compte combien d'alternatives il y a
            nb_alternatives = 0
            for alt in alternatives:
                nb_alternatives = nb_alternatives + 1

            if nb_alternatives > 0:
                # construit la liste des alternatives
                liste_texte = ""
                for i in range(nb_alternatives):
                    if i > 0:
                        liste_texte = liste_texte + ", "
                    liste_texte = liste_texte + str(alternatives[i])

                message = "Salle " + str(salle) + " occupée.\nSalles disponibles : " + liste_texte
                messagebox.showwarning("Occupée", message)
            else:
                messagebox.showerror("Erreur", "Aucune salle disponible pour ce créneau.")


    def annuler_reservation():
        """
        Annule la réservation sélectionnée dans le tableau
        """
        # recup la sélection
        selection = tableau.selection()

        # verif qu'une ligne est sélectionnée
        if len(selection) == 0:
            messagebox.showerror("Erreur", "Sélectionnez une réservation à annuler.")
            return

        # recup le numéro de la réservation (première colonne)
        element = selection[0]
        valeurs = tableau.item(element)["values"]
        num = valeurs[0]

        # verif que la réservation existe
        resa_existe = False
        for cle in reservations:
            if cle == num:
                resa_existe = True

        if resa_existe:
            # recup les infos de la réservation (maintenant c'est un objet)
            resa = reservations[num]

            # ajoute dans l'historique
            details = {
                "numero_reservation": num,
                "salle": resa.salle,
                "jour": resa.jour
            }
            ajouter_dans_historique("annulation", details, historique)

            # supprime la réservation
            del reservations[num]

            # sauvegarde
            sauvegarder_donnees(reservations, eleves, historique, compteur[0])

            # affiche un message
            messagebox.showinfo("Succès", "Réservation #" + str(num) + " annulée.")

            # rafraîchit le tableau
            rafraichir_tableau()


    def afficher_statistiques():
        """
        Affiche les statistiques dans une nouvelle fenêtre
        """
        # calcule les statistiques
        stats = calculer_statistiques(reservations, eleves, SALLES, JOURS)

        # crée une nouvelle fenêtre
        fen = tk.Toplevel(fenetre)
        fen.title("Statistiques")
        fen.geometry("500x400")

        # crée une zone de texte
        texte = tk.Text(fen, width=60, height=25)
        texte.pack(padx=10, pady=10)

        # construit le contenu
        contenu = "========== STATISTIQUES ==========\n\n"

        # total des réservations
        total = stats["total_reservations"]
        contenu = contenu + "Total : " + str(total) + " réservations\n"

        # salle la plus populaire
        salle_pop = stats["salle_plus_populaire"]
        if salle_pop == None:
            contenu = contenu + "Salle populaire : Aucune\n"
        else:
            contenu = contenu + "Salle populaire : " + str(salle_pop) + "\n"

        # jour le plus chargé
        jour_charge = stats["jour_plus_charge"]
        if jour_charge == None:
            contenu = contenu + "Jour chargé : Aucun\n\n"
        else:
            contenu = contenu + "Jour chargé : " + str(jour_charge) + "\n\n"

        # réservations par salle
        contenu = contenu + "--- Par salle ---\n"
        resa_par_salle = stats["reservations_par_salle"]
        for i in range(len(SALLES)):
            salle = SALLES[i]
            nb = resa_par_salle[salle]
            contenu = contenu + "  Salle " + str(salle) + " : " + str(nb) + "\n"

        # réservations par jour
        contenu = contenu + "\n--- Par jour ---\n"
        resa_par_jour = stats["reservations_par_jour"]
        for i in range(len(JOURS)):
            jour = JOURS[i]
            nb = resa_par_jour[jour]
            contenu = contenu + "  " + jour + " : " + str(nb) + "\n"

        # réservations par élève (on utilise nom_complet() de l'objet Eleve)
        contenu = contenu + "\n--- Par élève ---\n"
        resa_par_eleve = stats["reservations_par_eleve"]
        for id_eleve in liste_ids_eleves:
            nb = resa_par_eleve[id_eleve]
            nom_complet = eleves[id_eleve].nom_complet()
            contenu = contenu + "  " + nom_complet + " : " + str(nb) + "\n"

        # affiche le contenu
        texte.insert("1.0", contenu)
        texte.config(state="disabled")  # désactive pour pas qu'on puisse modifier


    def afficher_historique():
        """
        Affiche l'historique des actions dans une nouvelle fenêtre
        """
        # crée une nouvelle fenêtre
        fen = tk.Toplevel(fenetre)
        fen.title("Historique")
        fen.geometry("600x400")

        # crée une zone de texte
        texte = tk.Text(fen, width=70, height=25)
        texte.pack(padx=10, pady=10)

        # verif s'il y a de l'historique
        nb_entrees = len(historique)

        if nb_entrees == 0:
            contenu = "Aucun historique."
        else:
            contenu = "========== HISTORIQUE ==========\n\n"

            # parcourt l'historique avec une boucle
            for i in range(nb_entrees):
                entree = historique[i]
                numero = i + 1

                # ajoute l'en tête de l'action
                contenu = contenu + "Action #" + str(numero) + " - " + entree["date_heure"] + "\n"

                # ajoute le type en majuscules
                type_action = entree["type"]
                type_maj = ""
                for c in type_action:
                    # convertit en majuscules manuellement
                    if c >= "a" and c <= "z":
                        # soustrait 32 pour passer en majuscule (ASCII)
                        type_maj = type_maj + chr(ord(c) - 32)
                    else:
                        type_maj = type_maj + c
                contenu = contenu + "  Type : " + type_maj + "\n"

                # affiche les détails selon le type
                d = entree["details"]

                if type_action == "creation":
                    # ça cherche le nom de l'élève avec nom_complet()
                    id_el = d["eleve_id"]
                    nom_complet = "? ?"
                    if id_el in eleves:
                        nom_complet = eleves[id_el].nom_complet()

                    contenu = contenu + "  Salle " + str(d["salle"]) + " - "
                    contenu = contenu + d["jour"] + " - "
                    contenu = contenu + str(d["heure_debut"]) + "h-" + str(d["heure_fin"]) + "h\n"
                    contenu = contenu + "  Eleve : " + nom_complet + "\n"

                elif type_action == "annulation":
                    contenu = contenu + "  Reservation #" + str(d["numero_reservation"]) + " annulee\n"

                elif type_action == "penalite":
                    # cherche le nom de l'élève avec nom_complet()
                    id_el = d["eleve_id"]
                    nom_complet = "? ?"
                    if id_el in eleves:
                        nom_complet = eleves[id_el].nom_complet()

                    contenu = contenu + "  Eleve : " + nom_complet + " - +" + str(d["points"]) + " pts\n"

                contenu = contenu + "---\n"

        # affiche le contenu
        texte.insert("1.0", contenu)
        texte.config(state="disabled")  # désactive pour pas qu'on puisse modifier


    def afficher_penalites():
        """
        Affiche les pénalités de tous les élèves
        """
        # crée une nouvelle fenêtre
        fen = tk.Toplevel(fenetre)
        fen.title("Pénalités")
        fen.geometry("400x300")

        # crée une zone de texte
        texte = tk.Text(fen, width=45, height=15)
        texte.pack(padx=10, pady=10)

        # construit le contenu
        contenu = "========== PÉNALITÉS ==========\n\n"

        # parcourt tous les élèves avec une boucle (on utilise les methodes de l'objet)
        for i in range(len(liste_ids_eleves)):
            id_eleve = liste_ids_eleves[i]
            eleve = eleves[id_eleve]

            nom_complet = eleve.nom_complet()
            pts = eleve.penalites

            contenu = contenu + nom_complet + " : " + str(pts) + " pts"

            # verif si l'élève est bloqué avec la methode est_bloque()
            if eleve.est_bloque():
                contenu = contenu + " (BLOQUE)"

            contenu = contenu + "\n"

        # ça affiche le contenu
        texte.insert("1.0", contenu)
        texte.config(state="disabled")  # on désactive pour pas qu'on puisse modifier


    def fenetre_ajout_penalite():
        """
        Ouvre une fenêtre pour ajouter une pénalité à un élève
        """
        # ça crée une nouvelle fenêtre
        fen = tk.Toplevel(fenetre)
        fen.title("Ajouter pénalité")
        fen.geometry("400x250")

        # ça crée la liste des noms pour le menu déroulant (avec nom_complet())
        noms = []
        for i in range(len(liste_ids_eleves)):
            id_eleve = liste_ids_eleves[i]
            nom_complet = eleves[id_eleve].nom_complet()
            noms.append(nom_complet)

        # label et menu déroulant pour l'élève
        tk.Label(fen, text="Élève :").pack(pady=5)
        combo = ttk.Combobox(fen, values=noms, state="readonly", width=30)
        combo.pack(pady=5)

        # label et champ pour les points
        tk.Label(fen, text="Points :").pack(pady=5)
        entree_pts = tk.Entry(fen, width=10)
        entree_pts.pack(pady=5)

        # label et champ pour la raison
        tk.Label(fen, text="Raison :").pack(pady=5)
        entree_raison = tk.Entry(fen, width=40)
        entree_raison.pack(pady=5)

        def valider():
            """
            Valide et enregistre la pénalité
            """
            # verif qu'un élève est sélectionné
            index = combo.current()
            if index < 0:
                messagebox.showerror("Erreur", "Sélectionnez un élève.")
                return

            # verif que les points sont valides
            texte_pts = entree_pts.get()
            est_valide = True
            for c in texte_pts:
                if c < "0" or c > "9":
                    est_valide = False

            if not est_valide or len(texte_pts) == 0:
                messagebox.showerror("Erreur", "Points invalides.")
                return

            pts = int(texte_pts)

            # récupère la raison
            raison = entree_raison.get()
            if len(raison) == 0:
                raison = "Non spécifiée"

            # récupère l'ID de l'élève
            id_eleve = liste_ids_eleves[index]

            # ajoute la pénalité (la fonction utilise la methode de l'objet Eleve)
            ajouter_penalite(id_eleve, pts, raison, eleves, historique)

            # sauvegarde
            sauvegarder_donnees(reservations, eleves, historique, compteur[0])

            # affiche un message
            messagebox.showinfo("Succès", "Pénalité ajoutée.")

            # ferme la fenêtre
            fen.destroy()

        # bouton valider
        tk.Button(fen, text="Valider", command=valider).pack(pady=15)


    def executer_tests():
        """
        Exécute les tests automatisés pour verif que tout fonctionne
        """
        # ça crée une nouvelle fenêtre
        fen = tk.Toplevel(fenetre)
        fen.title("Tests")
        fen.geometry("600x500")

        # ça crée une zone de texte
        texte = tk.Text(fen, width=70, height=30)
        texte.pack(padx=10, pady=10)

        # compteurs pour les tests
        reussis = 0
        echoues = 0
        contenu = "========== TESTS AUTOMATISÉS ==========\n\n"

    # TEST 1 : chevauchement des horaires
        contenu = contenu + "TEST 1 : Chevauchement d'horaires\n"

        # test n1 : pas de chevauchement (8h-10h et 14h-16h)
        resultat = les_horaires_se_chevauchent(8, 10, 14, 16)
        if resultat == False:
            contenu = contenu + "  [OK] 8h-10h et 14h-16h : pas de chevauchement\n"
            reussis = reussis + 1
        else:
            contenu = contenu + "  [ECHEC] 8h-10h et 14h-16h\n"
            echoues = echoues + 1

        # test n2 : chevauchement (8h-12h et 10h-14h)
        resultat = les_horaires_se_chevauchent(8, 12, 10, 14)
        if resultat == True:
            contenu = contenu + "  [OK] 8h-12h et 10h-14h : chevauchement détecté\n"
            reussis = reussis + 1
        else:
            contenu = contenu + "  [ECHEC] 8h-12h et 10h-14h\n"
            echoues = echoues + 1

        # test n3 : heures collées (8h-10h et 10h-12h) -> pas de chevauchement
        resultat = les_horaires_se_chevauchent(8, 10, 10, 12)
        if resultat == False:
            contenu = contenu + "  [OK] 8h-10h et 10h-12h : heures collées, pas de chevauchement\n"
            reussis = reussis + 1
        else:
            contenu = contenu + "  [ECHEC] 8h-10h et 10h-12h\n"
            echoues = echoues + 1

    # TEST 2 : disponibilité des salles (on utilise des objets Reservation maintenant)
        contenu = contenu + "\nTEST 2 : Disponibilité des salles\n"

        # ça crée des réservations de test avec des objets
        test_resa = {}
        test_resa[1] = Reservation(1, 101, "Lundi", 8, 10, 1)

        # test n1 : salle 101 lundi 8h-10h doit être occupée
        resultat = salle_est_disponible(101, "Lundi", 8, 10, test_resa)
        if resultat == False:
            contenu = contenu + "  [OK] Salle 101 Lundi 8h-10h occupée\n"
            reussis = reussis + 1
        else:
            contenu = contenu + "  [ECHEC] Salle 101 Lundi 8h-10h\n"
            echoues = echoues + 1

        # test n2 : salle 101 lundi 14h-16h doit être libre
        resultat = salle_est_disponible(101, "Lundi", 14, 16, test_resa)
        if resultat == True:
            contenu = contenu + "  [OK] Salle 101 Lundi 14h-16h libre\n"
            reussis = reussis + 1
        else:
            contenu = contenu + "  [ECHEC] Salle 101 Lundi 14h-16h\n"
            echoues = echoues + 1

        # test n3 : salle 102 lundi 8h-10h doit être libre (autre salle)
        resultat = salle_est_disponible(102, "Lundi", 8, 10, test_resa)
        if resultat == True:
            contenu = contenu + "  [OK] Salle 102 Lundi 8h-10h libre (autre salle)\n"
            reussis = reussis + 1
        else:
            contenu = contenu + "  [ECHEC] Salle 102 Lundi 8h-10h\n"
            echoues = echoues + 1

    # TEST 3 : salles alternatives
        contenu = contenu + "\nTEST 3 : Recherche d'alternatives\n"

        # cherche les alternatives pour lundi 8h-10h en excluant salle 101
        alt = trouver_salles_alternatives("Lundi", 8, 10, 101, test_resa)

        # compte le nombre d'alternatives
        nb_alt = 0
        for a in alt:
            nb_alt = nb_alt + 1

        # test n1 : il doit y avoir 9 alternatives (10 salles - 1 exclue)
        if nb_alt == 9:
            contenu = contenu + "  [OK] 9 alternatives trouvées\n"
            reussis = reussis + 1
        else:
            contenu = contenu + "  [ECHEC] Trouvé : " + str(nb_alt) + " alternatives\n"
            echoues = echoues + 1

        # test n2 : la salle 101 ne doit pas être dans les alternatives
        salle_101_presente = False
        for a in alt:
            if a == 101:
                salle_101_presente = True

        if salle_101_presente == False:
            contenu = contenu + "  [OK] Salle 101 correctement exclue\n"
            reussis = reussis + 1
        else:
            contenu = contenu + "  [ECHEC] Salle 101 ne devrait pas être dans les alternatives\n"
            echoues = echoues + 1

    # TEST 4 : pénalités (on utilise des objets Eleve maintenant)

        contenu = contenu + "\nTEST 4 : Système de pénalités\n"

        # crée des élèves de test avec des objets Eleve
        from classes import Eleve
        test_eleves = {}
        test_eleves[1] = Eleve(1, "Test", "Zero", 0)
        test_eleves[2] = Eleve(2, "Test", "Bloque", 50)

        # test n1 : élève avec 0 pts peut réserver
        resultat = verifier_droit_reservation(1, test_eleves)
        if resultat == True:
            contenu = contenu + "  [OK] Élève avec 0 pts peut réserver\n"
            reussis = reussis + 1
        else:
            contenu = contenu + "  [ECHEC] Élève avec 0 pts\n"
            echoues = echoues + 1

        # test n2 : élève avec 50 pts est bloqué
        resultat = verifier_droit_reservation(2, test_eleves)
        if resultat == False:
            contenu = contenu + "  [OK] Élève avec 50 pts bloqué\n"
            reussis = reussis + 1
        else:
            contenu = contenu + "  [ECHEC] Élève avec 50 pts\n"
            echoues = echoues + 1

    # TEST 5 : résumé

        contenu = contenu + "\n========== RÉSUMÉ ==========\n"
        contenu = contenu + "Réussis : " + str(reussis) + " / Échoués : " + str(echoues) + "\n"

        if echoues == 0:
            contenu = contenu + "TOUS LES TESTS PASSÉS !\n"
        else:
            contenu = contenu + "Certains tests ont échoué.\n"

        # affichage du contenu
        texte.insert("1.0", contenu)
        texte.config(state="disabled")  # on désactive encore une fois pour pas qu'on puisse modifier


# CRÉATION DE L'INTERFACE GRAPHIQUE


    # titre principal
    titre = tk.Label(fenetre, text="SYSTÈME DE RÉSERVATION DE SALLES",
                     font=("Arial", 18, "bold"), bg="#f0f0f0")
    titre.pack(pady=10)

    # cadre pour le formulaire de réservation
    frame_form = tk.LabelFrame(fenetre, text="Nouvelle réservation",
                               font=("Arial", 12), padx=15, pady=15)
    frame_form.pack(padx=20, pady=10, fill="x")

    # ligne pour sélectionner l'élève
    frame1 = tk.Frame(frame_form)
    frame1.pack(fill="x", pady=5)
    tk.Label(frame1, text="Élève :", width=15, anchor="e").pack(side="left")

    # ici ça crée la liste des noms pour le menu déroulant (avec nom_complet())
    noms_eleves = []
    for i in range(len(liste_ids_eleves)):
        id_eleve = liste_ids_eleves[i]
        nom_complet = eleves[id_eleve].nom_complet()
        noms_eleves.append(nom_complet)

    combo_eleve = ttk.Combobox(frame1, values=noms_eleves, state="readonly", width=25)
    combo_eleve.pack(side="left", padx=10)

    # ligne pour sélectionner le jour
    frame2 = tk.Frame(frame_form)
    frame2.pack(fill="x", pady=5)
    tk.Label(frame2, text="Jour :", width=15, anchor="e").pack(side="left")
    combo_jour = ttk.Combobox(frame2, values=JOURS, state="readonly", width=25)
    combo_jour.pack(side="left", padx=10)

    # ligne pour les horaires
    frame3 = tk.Frame(frame_form)
    frame3.pack(fill="x", pady=5)
    tk.Label(frame3, text="Horaires :", width=15, anchor="e").pack(side="left")
    entree_debut = tk.Entry(frame3, width=5)
    entree_debut.pack(side="left", padx=5)
    tk.Label(frame3, text="h  à ").pack(side="left")
    entree_fin = tk.Entry(frame3, width=5)
    entree_fin.pack(side="left", padx=5)
    tk.Label(frame3, text="h   (8h-18h)").pack(side="left")

    # ligne pour la salle
    frame4 = tk.Frame(frame_form)
    frame4.pack(fill="x", pady=5)
    tk.Label(frame4, text="Salle :", width=15, anchor="e").pack(side="left")
    entree_salle = tk.Entry(frame4, width=10)
    entree_salle.pack(side="left", padx=10)
    tk.Label(frame4, text="(101-105 ou 201-205)").pack(side="left")

    # bouton pour faire la réservation
    btn_reserver = tk.Button(frame_form, text="Faire la réservation",
                             command=faire_reservation,
                             bg="#4CAF50", fg="white",
                             font=("Arial", 10, "bold"), padx=20, pady=5)
    btn_reserver.pack(pady=10)

    # cadre pour la liste des réservations
    frame_liste = tk.LabelFrame(fenetre, text="Réservations en cours",
                                font=("Arial", 12), padx=15, pady=15)
    frame_liste.pack(padx=20, pady=10, fill="both", expand=True)

    # ça crée le tableau
    colonnes = ("Numero", "Salle", "Jour", "Horaire", "Eleve")
    tableau = ttk.Treeview(frame_liste, columns=colonnes, show="headings", height=8)

    # config les en-têtes du tableau
    for i in range(len(colonnes)):
        col = colonnes[i]
        if col == "Numero":
            tableau.heading(col, text="#")
        else:
            tableau.heading(col, text=col)

    # config les largeurs des colonnes
    tableau.column("Numero", width=50)
    tableau.column("Salle", width=80)
    tableau.column("Jour", width=100)
    tableau.column("Horaire", width=120)
    tableau.column("Eleve", width=150)

    tableau.pack(fill="both", expand=True)

    # bouton pour annuler une réservation
    btn_annuler = tk.Button(frame_liste, text="Annuler la réservation sélectionnée",
                            command=annuler_reservation, bg="#f44336", fg="white")
    btn_annuler.pack(pady=10)

    # cadre pour les boutons bonus en bas
    frame_btns = tk.Frame(fenetre, bg="#f0f0f0")
    frame_btns.pack(pady=10)

    # bouton statistiques
    btn_stats = tk.Button(frame_btns, text="Statistiques", command=afficher_statistiques,
                          bg="#2196F3", fg="white", padx=10)
    btn_stats.pack(side="left", padx=5)

    # bouton historique
    btn_histo = tk.Button(frame_btns, text="Historique", command=afficher_historique,
                          bg="#9C27B0", fg="white", padx=10)
    btn_histo.pack(side="left", padx=5)

    # bouton pénalités
    btn_penal = tk.Button(frame_btns, text="Pénalités", command=afficher_penalites,
                          bg="#FF9800", fg="white", padx=10)
    btn_penal.pack(side="left", padx=5)

    # bouton ajouter pénalité
    btn_ajout_penal = tk.Button(frame_btns, text="Ajouter pénalité",
                                command=fenetre_ajout_penalite,
                                bg="#795548", fg="white", padx=10)
    btn_ajout_penal.pack(side="left", padx=5)

    # bouton tests
    btn_tests = tk.Button(frame_btns, text="Tests", command=executer_tests,
                          bg="#607D8B", fg="white", padx=10)
    btn_tests.pack(side="left", padx=5)

    # ça charge les réservations existantes dans le tableau
    rafraichir_tableau()

    # ça lance la fenêtre
    fenetre.mainloop()


# on lance l'interface
creer_interface()
