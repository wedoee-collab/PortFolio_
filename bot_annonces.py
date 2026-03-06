import discord
import os
import requests
import random
from discord.ext import commands, tasks
from dotenv import load_dotenv
from pathlib import Path
from urllib.parse import urlparse, parse_qs, urlencode

# Charge le .env situé dans le même dossier que ce script
env_path = Path(__file__).parent / ".env"
load_dotenv(dotenv_path=env_path)

token = os.getenv("DISCORD_TOKEN")
if not token:
    print("❌ ERREUR : Token introuvable. Vérifie que le fichier .env est bien rempli et sauvegardé.")
    exit()

intents = discord.Intents.default()
intents.message_content = True  # Obligatoire pour lire "!vinted"

bot = commands.Bot(command_prefix='!', intents=intents)

# Variables pour le mode automatique
auto_search_query = None
auto_channel_id = None
last_seen_id = None

# Mappings pour les tailles et états (chargés au démarrage)
VINTED_SIZE_MAP = {}
VINTED_STATUS_MAP = {}

def load_vinted_data():
    global VINTED_SIZE_MAP, VINTED_STATUS_MAP
    # Réinitialisation pour éviter les doublons en cas de reconnexion
    VINTED_SIZE_MAP = {}
    VINTED_STATUS_MAP = {}
    print("🔄 Chargement des données Vinted (Tailles & États)...")
    headers = {
        "User-Agent": "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/122.0.0.0 Safari/537.36"
    }
    session = requests.Session()
    session.headers.update(headers)
    
    try:
        session.get("https://www.vinted.fr/")
        # Chargement des états
        r = session.get("https://www.vinted.fr/api/v2/statuses")
        if r.status_code == 200:
            for s in r.json().get("statuses", []):
                VINTED_STATUS_MAP[s["title"].lower()] = s["id"]
        
        # Chargement des tailles
        r = session.get("https://www.vinted.fr/api/v2/sizes")
        if r.status_code == 200:
            def map_sizes(items):
                for item in items:
                    if item.get("children"):
                        map_sizes(item["children"])
                    # Découpage amélioré : gère "M / 38", "M (38)", "M, 38"
                    clean_title = item["title"].replace("(", "/").replace(")", "/").replace(",", "/")
                    titles = [t.strip().lower() for t in clean_title.split("/") if t.strip()]
                    for title in titles:
                        if title not in VINTED_SIZE_MAP:
                            VINTED_SIZE_MAP[title] = []
                        VINTED_SIZE_MAP[title].append(item["id"])
            map_sizes(r.json().get("sizes", []))
        print(f"✅ Données Vinted chargées : {len(VINTED_STATUS_MAP)} états, {len(VINTED_SIZE_MAP)} tailles.")
    except Exception as e:
        print(f"❌ Erreur chargement données Vinted: {e}")

@bot.event
async def on_ready():
    print(f'✅ Bot opérationnel : {bot.user.name}')
    bot.loop.run_in_executor(None, load_vinted_data)

# Fonction pour récupérer l'ID d'une marque sur Vinted
def get_brand_id(brand_name, session):
    url = f"https://www.vinted.fr/api/v2/brands?keyword={brand_name}"
    try:
        response = session.get(url)
        if response.status_code == 200:
            brands = response.json().get("brands", [])
            if brands:
                # 1. Recherche exacte (ex: "Nike" == "Nike")
                for brand in brands:
                    if brand["title"].lower() == brand_name.lower():
                        return brand["id"]
                # 2. Recherche partielle intelligente (ex: "Arte" -> "Arte Antwerp")
                for brand in brands:
                    if brand["title"].lower().startswith(brand_name.lower()):
                        return brand["id"]
                # 3. Sinon, on prend le premier résultat
                return brands[0]["id"] # On retourne l'ID de la première marque trouvée
    except:
        pass
    return None

# Fonction pour chercher sur Vinted (utilisée par !vinted et !auto)
def search_vinted(objet, mode="random"):
    objet = objet.strip() # Enlève les espaces accidentels
    if not objet:
        return None

    headers = {
        "User-Agent": "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/122.0.0.0 Safari/537.36",
        "Accept": "application/json, text/plain, */*",
        "Accept-Language": "fr-FR,fr;q=0.9,en-US;q=0.8,en;q=0.7",
        "Referer": "https://www.vinted.fr/catalog",
        "Sec-Fetch-Dest": "empty",
        "Sec-Fetch-Mode": "cors",
        "Sec-Fetch-Site": "same-origin",
    }
    
    session = requests.Session()
    session.headers.update(headers)
    
    try:
        session.get("https://www.vinted.fr/")
        
        params = {
            "order": "newest_first",
            "per_page": 20 # On récupère plus d'articles pour avoir du choix
        }

        if objet.startswith("https://www.vinted.fr/"):
            # On analyse l'URL pour gérer proprement les filtres et le tri
            parsed_url = urlparse(objet)
            query_params = parse_qs(parsed_url.query)
            
            # On force le tri par nouveauté pour avoir le dernier article
            query_params['order'] = ['newest_first']
            query_params['per_page'] = ['20']
            
            api_url = f"https://www.vinted.fr/api/v2/catalog/items?{urlencode(query_params, doseq=True)}"
        else:
            # Mode "Critères manuels" : on analyse les mots-clés (max:20, marque:Nike...)
            words = objet.split()
            search_keywords = []
            
            for word in words:
                lower_word = word.lower()
                if lower_word.startswith("max:"):
                    params["price_to"] = word.split(":")[1]
                elif lower_word.startswith("min:"):
                    params["price_from"] = word.split(":")[1]
                elif lower_word.startswith("marque:") or lower_word.startswith("brand:"):
                    brand_name = word.split(":")[1]
                    brand_id = get_brand_id(brand_name, session)
                    if brand_id:
                        params["brand_ids[]"] = brand_id
                    else:
                        # Si la marque n'est pas trouvée en ID, on l'ajoute à la recherche texte par sécurité
                        search_keywords.append(brand_name)
                elif lower_word.startswith("taille:") or lower_word.startswith("size:"):
                    parts = word.split(":")
                    if len(parts) > 1:
                        size_val = parts[1].lower()
                        if size_val in VINTED_SIZE_MAP:
                            if "size_ids[]" not in params: params["size_ids[]"] = []
                            params["size_ids[]"].extend(VINTED_SIZE_MAP[size_val])
                        else:
                            # On NE met PAS la taille en recherche texte si elle est inconnue.
                            # Cela évite les faux positifs (ex: "L" trouvé dans "Ralph Lauren")
                            print(f"⚠️ Taille ignorée (non trouvée dans le mapping) : {size_val}")
                elif lower_word.startswith("etat:") or lower_word.startswith("condition:"):
                    etat_val = word.split(":")[1].lower().replace("_", " ")
                    found_ids = []
                    if etat_val in VINTED_STATUS_MAP:
                        found_ids.append(VINTED_STATUS_MAP[etat_val])
                    else:
                        # Recherche partielle (ex: "neuf" -> "neuf avec étiquette")
                        for k, v in VINTED_STATUS_MAP.items():
                            if etat_val in k: found_ids.append(v)
                    if found_ids:
                        if "status_ids[]" not in params: params["status_ids[]"] = []
                        params["status_ids[]"].extend(found_ids)
                else:
                    search_keywords.append(word)
            
            params["search_text"] = " ".join(search_keywords)
            api_url = f"https://www.vinted.fr/api/v2/catalog/items?{urlencode(params, doseq=True)}"
            
        response = session.get(api_url)
        
        if response.status_code != 200:
            return None

        data = response.json()
        items = data.get("items", [])
        
        if not items:
            return None
            
        if mode == "newest":
            return items[0] # Pour l'alerte auto, on veut le tout dernier
        else:
            return random.choice(items[:10]) # Pour la recherche manuelle, on varie parmi les 10 derniers
    except:
        return None

# Fonction pour créer l'affichage (Embed)
def create_embed(item, objet):
    titre = item.get("title") or "Article sans titre"
    
    # Gestion du prix (parfois un dictionnaire, parfois une valeur)
    raw_price = item.get("total_item_price") or item.get("price")
    if isinstance(raw_price, dict):
        prix = raw_price.get("amount", "N/A")
        currency = raw_price.get("currency_code", "€")
    else:
        prix = raw_price or "N/A"
        currency = item.get("currency", "€")

    url_article = item.get("url")
    photo = item.get("photo", {}).get("url")
    
    # Infos supplémentaires
    marque = item.get("brand_title") or "Inconnue"
    taille = item.get("size_title") or "N/A"
    vendeur = item.get("user", {}).get("login") or "Inconnu"
    avatar_vendeur = item.get("user", {}).get("photo", {}).get("url")
    
    embed = discord.Embed(title=f"🛍️ {titre}", url=url_article, color=0x09B1BA)
    if photo:
        embed.set_image(url=photo)
    if avatar_vendeur:
        embed.set_thumbnail(url=avatar_vendeur)
        
    embed.add_field(name="💶 Prix", value=f"**{prix} {currency}**", inline=True)
    embed.add_field(name="📏 Taille", value=taille, inline=True)
    embed.add_field(name="🏷️ Marque", value=marque, inline=True)
    embed.add_field(name="👤 Vendeur", value=vendeur, inline=True)
    
    embed.set_footer(text=f"Recherche : {objet} • Vinted Bot", icon_url="https://upload.wikimedia.org/wikipedia/commons/thumb/2/29/Vinted_logo.png/600px-Vinted_logo.png")
    return embed

@bot.command(name="vinted")
async def vinted(ctx, *, objet: str):
    print(f"📩 Commande reçue pour : {objet}")
    
    # Message d'attente
    wait_msg = await ctx.send(f"🕵️‍♂️ **Recherche en cours** pour : `{objet}`...\n*Je scanne les rayons de Vinted...* ⏳")
    
    # Recherche (via executor pour ne pas bloquer le bot)
    item = await bot.loop.run_in_executor(None, search_vinted, objet, "random")
    
    # Suppression du message d'attente
    await wait_msg.delete()
    
    if item:
        await ctx.send(f"🎉 **Trouvé !** Voici le dernier article correspondant :")
        embed = create_embed(item, objet)
        
        # Ajout du bouton lien
        view = discord.ui.View()
        view.add_item(discord.ui.Button(label="Voir l'annonce", url=item.get("url"), style=discord.ButtonStyle.link, emoji="🔗"))
        await ctx.send(embed=embed, view=view)
    else:
        await ctx.send(f"📉 **Zut !** Je n'ai rien trouvé pour `{objet}`.\n💡 *Essaie de modifier tes filtres (taille, prix...) ou l'orthographe.*")

@tasks.loop(minutes=1)
async def auto_check():
    global last_seen_id
    if not auto_search_query or not auto_channel_id:
        return

    item = search_vinted(auto_search_query, mode="newest")
    if item:
        # Si l'ID est différent du dernier vu, c'est une nouvelle annonce
        if last_seen_id != item['id']:
            last_seen_id = item['id']
            channel = bot.get_channel(auto_channel_id)
            if channel:
                embed = create_embed(item, auto_search_query)
                
                # Ajout du bouton lien pour l'alerte aussi
                view = discord.ui.View()
                view.add_item(discord.ui.Button(label="Voir l'annonce", url=item.get("url"), style=discord.ButtonStyle.link, emoji="🔗"))
                await channel.send("🚨 **Nouvelle annonce détectée !**", embed=embed, view=view)

@bot.command(name="auto")
async def auto(ctx, *, objet: str):
    global auto_search_query, auto_channel_id, last_seen_id
    auto_search_query = objet
    auto_channel_id = ctx.channel.id
    last_seen_id = None # On reset pour qu'il affiche le premier trouvé
    
    if not auto_check.is_running():
        auto_check.start()
    
    await ctx.send(f"👀 Mode automatique activé pour : **{objet}**. Je vérifie toutes les minutes.")

@bot.command(name="stop")
async def stop(ctx):
    if auto_check.is_running():
        auto_check.stop()
    await ctx.send("🛑 Mode automatique arrêté.")

@vinted.error
async def vinted_error(ctx, error):
    if isinstance(error, commands.MissingRequiredArgument):
        await ctx.send("🤔 **Tu cherches quoi ?**\nIl faut me dire ce que tu veux !\n\n👉 **Exemple :** `!vinted pull nike taille:M max:20`")

@bot.command(name="clear")
async def clear(ctx, nombre: int = 5):
    """Supprime les X derniers messages (par défaut 5)"""
    await ctx.channel.purge(limit=nombre + 1) # +1 pour supprimer aussi la commande !clear
    await ctx.send(f"🧹 {nombre} messages supprimés !", delete_after=3)

# Mets bien ton token entre les guillemets
bot.run(token)