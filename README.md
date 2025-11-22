# BlogPHP



#Funktionen
- Artikel suchen
- Artikel anlegen
- Artikel ändern
- Artikel löschen
- Artikel-Attribute: Titel, Text, Autor, Veröffentlichungszeit
- Artikel absteigend nach Veröffentlichungszeit anzeigen
- Kommentare durch Besucher ermöglichen
- Login-Schutz für Anlegen/Ändern/Löschen (Lesen & Kommentieren offen)
- Threaded Comments (Antworten auf Kommentare)
- Benutzerverwaltung 
- usätzliche Artikel-Attribute (Bild, Kategorie, Untertitel, …)
- Freigabe-Workflow (Admin/Chef-Redakteur mit Review/Approve)
- RSS-Feed (unter http://localhost:8080/php/BlogPHP/public/feed.php)

#andere Funktionen
- Öffentliche Startseite mit Topic-Filter (t_id) und Suche über Titel & Body (LIKE), nur published Posts, absteigend nach created_at
- Kommentare anlegen (Form), inkl. Antwort auf Kommentar (threaded via parent_id)
- Rollen & Schutz: usersOnly() und adminOnly() Middleware (Admin-Flag in users.admin)
- Benutzerverwaltung → Liste/Löschen vorhanden; fehlt: Benutzer anlegen/bearbeiten, Rollen/Zuweisungen, Passwort-Reset.
- Bemerkungen/Notizen (interne Redaktionsnotizen pro Artikel)
- E-Mail Funktion
- Kommentare durch Besucher ermöglichen mit hilfe von cookies
- Spam-Protection
- Erweiterte Suche mit Fuzzy 
- reCAPTCHA v2 beim Login
- reCAPTCHA v3 bei den Kommentaren

# Savepoint Blog

Ein voll funktionsfähiges, objektorientiertes Content Management System (CMS) für Blogs, entwickelt mit modernem PHP 8.2, MySQL und Docker.

Gruppe 6: Emily Hourtz, Nisa Akyol, Lavinia Steinbrenner, Antonia Milic

Dieses Projekt demonstriert eine saubere **MVC-Architektur** (Model-View-Controller) ohne die Verwendung großer Frameworks.

---

## Technische Kurzbeschreibung

**Sprache:** 		PHP 8.2 (Strict Types)
**Datenbank:** 		MariaDB 10.6
**Server:** 		Apache 2.4 (via Docker)
**Frontend:** 		HTML5, CSS3 (Bootstrap 5), JavaScript (Vanilla JS + Fetch API)
**Architektur:** 	Custom MVC, Front-Controller-Pattern, PSR-4 Autoloading via Composer
**Sicherheit:** 	CSRF-Protection, XSS-Filterung (HTMLPurifier), Brute-Force-Limiter, Prepared Statements, Session-Fixation-Schutz

---

## Voraussetzungen

Um das Projekt lokal auszuführen, wird lediglich folgende Software benötigt:

1.  **Docker Desktop**

Es ist **keine** lokale PHP- oder MySQL-Installation auf dem Host-System notwendig, da die gesamte Umgebung containerisiert ist.

---

## Installations- & Startanleitung

Befolgen Sie diese Schritte, um das Projekt in Betrieb zu nehmen.

### 1. Projekt vorbereiten
Navigieren Sie in das Projektverzeichnis. Erstellen Sie eine Konfigurationsdatei basierend auf der Vorlage:

```cmd
# Navigieren zum blog-group6 Verzeichnis
cd 'HierPfadReinKopieren'
# Kopieren der Umgebungsvariablen
cp .env.example .env
```
**Hinweis:** Die Standardwerte in .env.example sind bereits **perfekt** auf die Docker-Umgebung abgestimmt

### 2. Docker starten
Dieser Befehl baut das PHP-Image, startet den Webserver und die Datenbank.

```cmd
docker-compose up --build -d
```

### 3. Abhängigkeiten installieren
Da vendor-Dateien nicht im Repository gespeichert wurden, müssen diese **einmalig** über den Container installiert werden:

```cmd
docker-compose exec web composer install
```

Hinweis: Installiert htmlpurifier, phpdotenv und generiert Autoloader.

### 4. Datenbank installieren
Die Datenbank wird beim ersten Start des Containers **automatisch** importiert (aus data/init.sql).

---

## Zugang und Login

### Zugang URLs zur Webseite
Das Projekt ist nun unter folgenden Adressen erreichbar:

*Frontend: http://localhost:8000/BLOGPHP/public
*Backend: http://localhost:8000/admin
*Datenbank-Verwaltung (phpMyAdmin): http://localhost:8080

### Test-Logindaten auf der Webseite
**	Rolle	**		**	Benutzername	**		**	Passwort	**		**	Berechtigungen	**
	Administrator		admin				        admin			        Voller Zugriff
	Benutzer			testuser12					testuser12			    Eigene Posts/Kommentare
	
**Hinweis:** Als Administrator in der Benutzerverwaltung sind auch neue Nutzer und Adminstarator erstellbar.

---

## Feature-Liste

### Frontend (Öffentlich)
1. **Responsivität:** Vollständig responsives Design per *Bootstrap 5*.
2. **Header:** Stellt Links zu wichtigsten Seiten, eine Suchleiste für Posts und Signup/Login (nicht eingeloggt) bzw. Profil des Nutzers mit Dropdown (eingeloggt) zur Verfügung.
3. **Homepage:** Zeigt aktuellste Posts Anklicken per Slider und Postcards an.
4. **Slider:** Dynamische Slider für hervorgehobene Beiträge auf der Startseite.
5. **Suche:** Volltextsuche für Blogeinträge (Eingabe entweder im Header oder auf */blog*).
6. **Blog-Seite (/blog):** Anzeige aller Posts absteigend nach Datum (Standard) bzw. gemäß Suchbegriff.
7. **Paginierung:** Leichte Seitennavigation für Seiten mit Postcards (keine Posts auf nächster Seite: Button deaktiviert)
8. **Footer:** Enthält nützliche Links sowie Weiterleitungen zu Social Media und RSS-Feed über entsprechende Logos (Social Media Links nur als Platzhalter, da auf einen Account für den Blog verzichtet wurde).
9. **RSS-Feed**: XML-Feed unter */feed* für RSS-Reader.
10. **Postcard**: Vorschau des entsprechenden Posts mit Kategorie, Titel, Zusammenfassung, Autor mit Profilbild, Datum und Thumbnaill.
11. **Autoren-Seiten:** Filterung aller Beiträge eines bestimmten Autors (Aufruf durch Anklicken des Profilbildes oder Benutzernamen auf Postvorschau).
12. **Kommentare (AJAX):** Kommentieren (auch ohne Anmeldung) direkt unter einem Post *ohne* Seiten-Neuladen durch Asynchrones JavaScript.
13. **Kontakt-Seite (/contact):** Zeigt all Administratoren an mit Link zu deren Autoren-Seite sowie Button, um einem Admin eine Email mit Standard-E-Mail-Programm des Nutzers zu schreiben.

### Backend (für Admins)
1. **Dashboard:** Darstellung der Anzahl von Admins, Nutzern, Posts, Kommentaren und Kategorien.
2. **Nutzerverwaltung:**
	- Anzeige von Nutzernummer, Benutzername, Email Adresse, Rolle, Profilbild, Erstellungsdatum.
	- Hinzufügen, Bearbeiten und Löschen von Nuterprofilen (Aufruf per entsprechende Buttons).
	- Suchleiste für Benutzername und Email.
3. **Kategorieverwaltung:**
	- Anzeige von Kategorienummer, Bezeichnung, Slug und Status
	- Hinzufügen, Bearbeiten und Löschen von Kategorien (Aufruf per entsprechende Buttons).
4. **Postverwaltung:**
	- Anzeige von Postnummer, Titel, Slug, Autor, Datum, Beitragsbild, Status
	- Hinzufügen, Freigeben, Bearbeiten und Löschen von Posts (Aufruf per entsprechende Buttons).
	- Suchleiste für Posttitel.
4. **WYSIWYG-Editor:** Summernote-Integration für das benutzerfreundliche Verfassen von Beiträgen mit HTML (inklusive Bild-Uploads).
6. **Bild-Management:** Automatisches Bereinigen verwaiserter Bilder beim Bearbeiten und Löschen.
7. **Kommentarverwaltung:**
	- Anzeige von Kommentarnummer, Autor, Inhaltauszug, Posttitel, Datum, Status
	- Hinzufügen, Freigeben, Bearbeiten und Löschen von Kommentaren (Aufruf per entsprechende Buttons).
	- Suchleiste für Inhalt/Autor.
8. **Moderation:** Posts und Kommentare von Nicht-Admins müssen freigegeben werden
9. **Mein Profil:** Möglichkeit zur Abänderung der Nutzerdaten sowie Hinzufügen eines Profilbildes
10. **Einstellungen:** Möglichkeit die Anzahl der Posts auf entsprechender Seite anzupassen sowie Seitenname des Blogs.

### Backend (für Benutzer)
1. **Mein Profil:** Möglichkeit zur Abänderung der Nutzerdaten sowie Hinzufügen eines Profilbildes
2. **Persönliche Postverwaltung:**
	- Anzeige von Postnummer, Titel, Slug, Autor, Datum, Beitragsbild, Status der eigenen Posts.
	- Hinzufügen, Freigeben, Bearbeiten und Löschen von eigenen Posts (Aufruf per entsprechende Buttons).
	- Suchleiste für Posttitel.
3. **Kommentarverwaltung:**
	- Anzeige von Kommentarnummer, Autor, Inhaltauszug, Posttitel, Datum, Status der eigenen Kommentare
	- Hinzufügen, Freigeben, Bearbeiten und Löschen von eigenen Kommentaren (Aufruf per entsprechende Buttons).
	- Suchleiste für Inhalt/Autor.
	
---

### Sicherheits-Implementierung
1. **SQL-Injection:** Vollständige Nutzung von PDO Prepared Statements in der Database-Klasse.
2. **XSS (Cross-Site Scripting):**
	- Eingabe: Reinigung von HTML-Input mittelös HTMLPurifier (Whitelisting).
	- Ausgabe: Konsequente Escaping mittels htmlspecialchars() in allen Views.
3. **CSRF (Cross-Site Request Forgery):** Automatische Generierung und Validierung von Einmal-Tokens bei jedem POST-Request.
4. **Brute-Force Protection:** Login-Sperre (30 Sek.) nach 5 fehlgeschlagenen Versuchen (inkl. Live-Countdown).
5. **Session Security:** HTTPOnly Cookies und session_regenerate:id() nach Login.
6. **Upload Security:** .htaccess-Schutz im Uploads-Ordner
7. **Spam-Schutz:** Honeypot-Felder und Zeitmessung in Kommentar-Formular.
8. **Captcha:** Google reCAPTCHA v2 Integration bei Login und Registrierung.