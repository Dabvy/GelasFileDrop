# GelasFileDrop

In veel organisaties moeten bestanden veilig worden uitgewisseld tussen systemen. Denk aan bedrijfsdocumenten, software builds of gevoelige gegevens. Wanneer bestandsoverdracht niet goed beveiligd is kan dit leiden tot datalekken, manipulatie van bestanden of ongeautoriseerde toegang. In dit project ontwikkelen we een veilig bestandstransfersysteem waarmee bestanden veilig kunnen worden verzonden tussen systemen. Het systeem moet rekening houden met vertrouwelijkheid (confidentiality), integriteit (integrity) en authenticatie (authentication).

# Achtergrond en Probleemstelling

In moderne organisaties is het heel belangrijk om digitale bestanden veilig uit te wisselen tussen verschillende systemen. Denk hierbij aan het overdragen van gevoelige bedrijfsdocumenten, software builds of persoonsgegevens.

Wanneer deze bestanden via onveilige kanalen of oude protocollen worden overgedragen, ontstaan er grote risico's. Dit kan leiden tot:

Datalekken: gevoelige informatie die op straat komt te liggen.

Bestandsmanipulatie: kwaadwillenden die data ongemerkt aanpassen tijdens het transport.

Ongeautoriseerde toegang: personen of systemen die toegang krijgen tot bestanden waar zij geen rechten voor hebben.

Om deze risico's te minimaliseren, is er behoefte aan een centraal, robuust en veilig bestandstransfersysteem.

# Projectdoelstelling

Het doel van dit project is om een Secure File Transfer System te ontwerpen, ontwikkelen en implementeren. Dit systeem zorgt ervoor dat bestanden tussen systemen worden uitgewisseld met strikte naleving van de drie belangrijkste principes van informatiebeveiliging: vertrouwelijkheid, integriteit en authenticatie.

# Functionele en Technische Vereisten

Het systeem zal worden gebouwd rondom de volgende drie belangrijkste onderdelen:

Vertrouwelijkheid

Alle bestanden moeten tijdens het transport versleuteld zijn met moderne, sterke encryptieprotocollen.

Bestanden die tijdelijk of permanent op de server worden opgeslagen, dienen te worden versleuteld.

Integriteit

Het systeem moet bij verzending en ontvangst automatisch cryptografische hashes berekenen en vergelijken. Hiermee wordt gegarandeerd dat het bestand onderweg niet is aangepast of beschadigd.

Indien een hash niet overeenkomt, wordt de overdracht afgebroken en krijgt de beheerder een melding.

Authenticatie en Autorisatie

Systemen die bestanden verzenden of ontvangen moeten zich verplicht authenticeren.

Er wordt een strikt rechtensysteem toegepast, zodat systemen alleen toegang hebben tot de specifieke mappen en bestanden die voor hen bedoeld zijn.

# Aanvullende Systeemeisen

Het systeem houdt een onwijzigbaar logboek bij van alle transacties.

Het systeem moet via een API of CLI-aansturing eenvoudig te integreren zijn in bestaande geautomatiseerde workflows.

# Beoogde Resultaten

Een document met de architectuur- en beveiligingsontwerp.

Een functionerend bestandstransfersysteem dat aan de veiligheidseisen voldoet.

Een testrapport met de resultaten van penetratietesten en integriteitscontroles.

Documentatie voor de installatie, configuratie en API-integratie.
