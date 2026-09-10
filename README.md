# Shopware 5 EU-GARAN-Label Plugin

Zeigt das EU-GARAN-Label für die freiwillige Haltbarkeitsgarantie des
Herstellers gemäß Richtlinie (EU) 2024/825 (Abschnitt 3 der EU-Leitlinien).

## Features

- Ein Artikel-Freitextfeld genügt: Garantiedauer in Jahren (z.B. 5 oder 2,5)
- Das vollständige Label wird automatisch aus der offiziellen SVG-Vorlage
  erzeugt: Jahre aus dem Freitextfeld, Brand/Trademark aus dem
  Artikel-Hersteller, Modellkennung nach konfigurierbarer Prioritätenliste
- Optionaler Override pro Artikel: eigenes Label-Bild (Medienauswahl),
  z.B. das vom Hersteller gelieferte Original
- Offizielles Nested Label (kompakt, mit Jahreszahl) auf Produktdetailseite,
  Bestellabschluss-Seite und im Listing (nur wenn der Kaufen-Button im
  Listing aktiv ist); erster Klick öffnet das vollständige Label im Modal
- Ohne JavaScript führt der Link zum Your-Europe-Portal (gleiches Ziel wie
  der QR-Code im Label)
- Beim Kauf von Artikeln mit Herstellergarantie wird das ausgefüllte Label
  (bzw. das Override-Bild) automatisch an die Bestellbestätigungs-E-Mail
  angehängt
- Texte in allen 24 EU-Amtssprachen
- Die vorgeschriebene Label-Schriftart "Inter" wird mitgeliefert
  (siehe "Schriftart des Labels")

## Schriftart des Labels

Die EU schreibt für das Label die Schriftart "Inter" vor. Sie wird mit dem
Plugin mitgeliefert und im Shop automatisch geladen – es muss nichts
eingerichtet werden. Die Schrift ist kostenlos nutzbar (SIL Open Font
License, siehe `Resources/frontend/fonts/OFL.txt`).

Öffnet ein Kunde das Label außerhalb des Shops – zum Beispiel den Anhang
der Bestellbestätigungs-E-Mail – steht "Inter" dort unter Umständen nicht
zur Verfügung. In diesem Fall verwendet das Gerät automatisch eine ähnliche
Standardschrift. Das Label bleibt vollständig lesbar, einzelne Zahlen und
Texte können dann nur minimal anders aussehen.

## Konfiguration

**Modellkennung** ("Model identifier" im Label): dreistufige Prioritätenliste
aus Artikelnummer, Herstellerartikelnummer und EAN – der erste am Artikel
gepflegte Wert wird verwendet (Standard: Artikelnummer, Herstellerartikelnummer,
EAN).

**Pflege pro Artikel** unter **Freitextfelder**: "EU GARAN: Garantiedauer in
Jahren" ausfüllen (leer = kein Label). Optional "EU GARAN: Eigenes Label"
zum Übersteuern des generierten Labels. Felder sind pro Variante möglich.

## Installation

1. ZIP herunterladen: https://github.com/onlineshop-consulting/sw5-onco-commercial-garan/releases
2. Im Shopware-Backend unter **Einstellungen > Plugin-Manager** installieren und aktivieren
3. Cache leeren und Theme neu kompilieren

Bei der Deinstallation werden die Freitextfelder entfernt (außer bei
"Daten behalten").

## Kompatibilität

Shopware 5.4.0+
PHP 5.6+

## Nicht enthalten

Label im Warenkorb sowie Werbe-Platzierungen (Abschnitt 3.4 der
EU-Leitlinien). Der gesetzliche Gewährleistungs-Hinweis ist im separaten
Plugin https://github.com/onlineshop-consulting/sw5-onco-legal-garan umgesetzt.
