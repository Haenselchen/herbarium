Herbarium-Datenbanksystem
Eine webbasierte Anwendung zur Verwaltung einer Pflanzensammlung mit Bildern, wissenschaftlichen Namen und weiteren Informationen.
Funktionen

Pflanzen hinzufügen, bearbeiten und löschen
Hochladen und Verwalten von Bildern zu jeder Pflanze
Detaillierte Informationen zu jeder Pflanze speichern (wissenschaftlicher Name, Familien, Entdeckungsdaten, etc.)
Suchfunktion für die Pflanzensammlung

Technologien

PHP
MariaDB
Docker
HTML/CSS/JavaScript

Installation

Repository klonen:
git clone https://github.com/dein-username/herbarium.git
cd herbarium

Konfigurationsdateien erstellen:
cp .env.example .env
cp config/config.php.example config/config.php

Konfigurationsdateien anpassen:

Bearbeite .env und config/config.php mit deinen gewünschten Einstellungen
Stelle sicher, dass die Datenbank-Anmeldedaten in beiden Dateien übereinstimmen


Docker-Container starten:
docker-compose up -d

Die Anwendung ist verfügbar unter:

Weboberfläche: http://localhost:8080 (oder den Port, den du in der .env-Datei konfiguriert hast)
phpMyAdmin: http://localhost:8081 (oder den konfigurierten Port)



Projektstruktur

src/: Quellcode der Webanwendung

includes/: PHP-Hilfsfunktionen und Datenbankverbindungen
images/: Hochgeladene Pflanzenbilder
css/: Stylesheets


config/: Konfigurationsdateien
db_init/: SQL-Skripte für die Initialisierung der Datenbank
Dockerfile: Docker-Konfiguration für die PHP-Umgebung
docker-compose.yaml: Docker-Compose-Konfiguration für alle benötigten Services

Sicherheit

Ändere alle Standardkennwörter in den Konfigurationsdateien
Die .env- und config/config.php-Dateien sollten niemals in ein öffentliches Repository gepusht werden
Für den Produktiveinsatz sollten zusätzliche Sicherheitsmaßnahmen implementiert werden

Mitwirken
Beiträge sind willkommen! Bitte erstelle einen Pull Request oder öffne ein Issue, um Verbesserungen vorzuschlagen.