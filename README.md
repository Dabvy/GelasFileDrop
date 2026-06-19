# GelasFileDrop

In veel organisaties is het nodig om bestanden veilig uit te wisselen tussen systemen. Dit omvat bedrijfsdocumenten, software builds en gevoelige gegevens. Onvoldoende beveiligde bestandsoverdracht kan leiden tot datalekken, manipulatie van bestanden of ongeautoriseerde toegang. In dit project ontwikkelen we een veilig bestandstransfersysteem waarmee bestanden veilig kunnen worden verzonden tussen systemen. Het systeem moet rekening houden met vertrouwelijkheid, integriteit en authenticatie.

# Achtergrond en Probleemstelling

In moderne organisaties is het belangrijk om digitale bestanden veilig uit te wisselen tussen verschillende systemen. Dit geldt voor het overdragen van gevoelige bedrijfsdocumenten, software builds en persoonsgegevens.

Als deze bestanden via onveilige kanalen of verouderde protocollen worden overgedragen, kunnen er aanzienlijke risico's ontstaan. Dit kan leiden tot:

Datalekken: gevoelige informatie die openbaar wordt.

Bestandsmanipulatie: kwaadwillenden die tijdens het transport data ongemerkt aanpassen.

Ongeautoriseerde toegang: personen of systemen die toegang krijgen tot bestanden waar zij geen rechten voor hebben.

Om deze risico's te verminderen, is er behoefte aan een centraal, sterk en veilig bestandstransfersysteem.

# Projectdoelstelling

Dit project heeft als doel om een Secure File Transfer System te ontwerpen, ontwikkelen en implementeren. Dit systeem zorgt ervoor dat bestanden tussen systemen worden uitgewisseld met strikte naleving van de drie belangrijkste principes van informatiebeveiliging: vertrouwelijkheid, integriteit en authenticatie.

# Functionele en Technische Vereisten

Het systeem zal worden opgebouwd rond de volgende drie belangrijkste onderdelen:

Vertrouwelijkheid:

Alle bestanden moeten tijdens het transport worden versleuteld met sterke encryptieprotocollen.

Bestanden die tijdelijk of permanent op de server worden opgeslagen, moeten ook worden versleuteld.

Integriteit:

Het systeem moet bij verzending en ontvangst automatisch cryptografische hashes berekenen en vergelijken. Dit garandeert dat het bestand onderweg niet is gewijzigd of beschadigd.

Als een hash niet overeenkomt, wordt de overdracht afgebroken en krijgt de beheerder een melding.

Authenticatie en Autorisatie:

Systemen die bestanden verzenden of ontvangen moeten zich verplicht identificeren.

Er wordt een strikt rechtensysteem toegepast, zodat systemen alleen toegang hebben tot specifieke mappen en bestanden die voor hen bedoeld zijn.

# Beoogde Resultaten

Een document met het architectuur- en beveiligingsontwerp.

Een functionerend bestandstransfersysteem dat aan de veiligheidseisen voldoet.

---

## Functionaliteiten in de code (Code Functions Overview)

Het script `indexPHP.php` voert de volgende kernfuncties uit op basis van de serveraanvragen (GET/POST):

1. **Sessiebeheer**
   - Start een actieve gebruikerssessie via `session_start()` om tijdelijke data, zoals de gegenereerde downloadlink, veilig door te geven na een pagina-omleiding.

2. **Database Verbinding**
   - Initialiseert een MySQLi-verbinding (`new mysqli()`) met de lokale database (`filedrop`) onder de gebruiker 'root'. Het script bevat foutafhandeling (`connect_error`) die het stopt als de database niet bereikbaar is.

3. **GHTTPS-omleiding**
   - Controleert via de servervariabelen (`$_SERVER["HTTPS"]`) of de verbinding beveiligd is. Als dat niet zo is, wordt de bezoeker automatisch doorgestuurd naar de beveiligde `https://` versie van de huidige pagina met een HTTP 301-statuscode.

4. **Bestandsoverdracht & Download**
   - Wordt geactiveerd wanneer de URL de parameters `?id=[HASH]&action=download` bevat.
   - Haalt de oorspronkelijke bestandsnaam, het MIME-type en de binaire BLOB-gegevens op uit de database via een veilig *prepared statement*.
   - Stuurt de juiste HTTP-headers naar de browser om het bestand als bijlage (`Content-Disposition: attachment`) te downloaden.

5. **Landingspagina / Bestandscontrole **
   - Wordt geactiveerd wanneer een gebruiker de speciale link bezoekt (`?id=[HASH]`), zonder de downloadactie.
   - Controleert in de database of het ID bestaat en haalt de bestandsnaam op om deze op het scherm te tonen ter controle voor de downloader.

6. **Bestandsvalidatie & Upload (File Validation & Upload Function)**
   - Verwerkt het formulier zodra er een bestand wordt geüpload via een POST-aanroep (`$_FILES["filename"]`).
   - **Extensiecontrole:** Staat alleen `.jpg`, `.jpeg`, `.png` en `.gif` bestanden toe.
   - **Groottecontrole:** Weigert bestanden die groter zijn dan 500 KB (500.000 bytes).
   - **Unieke ID-generatie:** Maakt een unieke MD5-hash aan met `md5(uniqid())` om als veilige identificatiecode in de database te dienen.

7. **Dynamische HTML-Weergave**
   - Schakelt de interface automatisch tussen twee modi:
     - **Modus A (Downloadpagina):** Toont de bestandsnaam en een downloadknop als er een geldig `id` in de URL staat.
     - **Modus B (Uploadpagina):** Toont het standaard uploadformulier en, indien net geüpload, de gegenereerde, kopieerbare HTTPS-downloadlink voor de gebruiker.
    
---

## Bekende Problemen / Nog niet af 
Nog niet af. Dit moet in de toekomst dynamischer worden gemaakt door `$_SERVER['SCRIPT_NAME']` te gebruiken in plaats van een vaste bestandsnaam, zodat de link altijd automatisch klopt, ongeacht de mapstructuur.
