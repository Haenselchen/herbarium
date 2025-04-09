# 🌿 Herbarium-Datenbanksystem

Eine **webbasierte Anwendung** zur Verwaltung einer Pflanzensammlung mit Bildern, wissenschaftlichen Namen und weiteren Informationen.

## 🚀 Funktionen

- Pflanzen **hinzufügen**, **bearbeiten** und **löschen**
- **Bilder hochladen** und verwalten zu jeder Pflanze
- **Detaillierte Informationen** speichern:
  - Wissenschaftlicher Name
  - Familie
  - Entdeckungsdatum
  - u.v.m.
- **Suchfunktion** für die Pflanzensammlung

---

## 🛠️ Technologien

- **PHP**
- **MariaDB**
- **Docker**
- **HTML / CSS / JavaScript**

---

## ⚙️ Installation

### 📦 Repository klonen

```bash
git clone https://github.com/Haenselchen/herbarium.git
cd herbarium
```

### 🧾 Konfigurationsdateien erstellen
```bash
cp .env.example .env
cp config/config.php.example config/config.php
```

✏️ Bearbeite anschließend .env und config/config.php mit deinen gewünschten Einstellungen.
☑️ Stelle sicher, dass die Datenbank-Anmeldedaten in beiden Dateien übereinstimmen.

### 🐳 Docker-Container starten
```bash
docker-compose up -d
```

---

## 🌐 Anwendung aufrufen

- **Weboberfläche:** http://localhost:8080
- **phpMyAdmin:** http://localhost:8081

📌 Die Ports können in der .env-Datei angepasst werden.

---

## 🧾 Konfigurationsdateien erstellen
```bash
herbarium/
├── src/                # Quellcode der Webanwendung
├── includes/           # PHP-Hilfsfunktionen & Datenbankverbindungen
├── images/             # Hochgeladene Pflanzenbilder
├── css/                # Stylesheets
├── config/             # Konfigurationsdateien
├── db_init/            # SQL-Skripte für die Initialisierung der Datenbank
├── Dockerfile          # Docker-Konfiguration für die PHP-Umgebung
└── docker-compose.yaml # Docker-Compose Konfiguration für alle Services
```

---

## 🔐 Sicherheit
- Ändere alle Standardpasswörter in den Konfigurationsdateien

- Die Dateien .env und config/config.php niemals öffentlich hochladen

- Für produktive Umgebungen sollten zusätzliche Sicherheitsmaßnahmen implementiert werden (z. B. HTTPS, Zugriffsbeschränkungen, regelmäßige Updates)


