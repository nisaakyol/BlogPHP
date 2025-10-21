# BlogPHP

#Funktionen-SOLL
Artikel suchen
Artikel anlegen
Artikel ändern
Artikel löschen
Artikel-Attribute: Titel, Text, Autor, Veröffentlichungszeit
Artikel absteigend nach Veröffentlichungszeit anzeigen
Kommentare durch Besucher ermöglichen
Login-Schutz für Anlegen/Ändern/Löschen (Lesen & Kommentieren offen)
Threaded Comments (Antworten auf Kommentare)
HTML/Markdown-Eingabe für Artikeltext
Zusätzliche Artikel-Attribute (Bild, Kategorie, Untertitel, …)
Freigabe-Workflow (Admin/Chef-Redakteur)
Erweiterte Suche inkl. Fuzzy Search
RSS-Feed
Spam-Protection (z. B. CAPTCHA)
Bemerkungen/Notizen
Benutzerverwaltung 

#Funktionen-IST
Öffentliche Startseite mit Topic-Filter (t_id) und Suche über Titel & Body (LIKE), nur published Posts, absteigend nach created_at
Single-Post-Seite
Kommentare anlegen (Form), inkl. Antwort auf Kommentar (threaded via parent_id)
Login, Logout und Registrierung (Passwort-Hashing, Fehlermeldungen/Flash, Redirects)
Honeypot-Spamschutz im Registrierungsformular
Rollen & Schutz: usersOnly() und adminOnly() Middleware (Admin-Flag in users.admin)
Admin-Dashboard (Kennzahlen-Übersicht; ausbaufähig)
Posts (Admin): Liste, Erstellen, Bearbeiten, Löschen, Publish/Unpublish-Toggle
Bild-Upload für Posts (Speicher unter assets/images/, Timestamp-Dateiname)
Topics (Admin): Erstellen, Bearbeiten, Löschen, Liste
Users (Admin): Liste, Löschen
E-Mail-Versand (einfacher Textmailer/Service; z. B. für Kontakt)
DB-Abstraktion (Repository) mit Prepared Statements; OOP-Struktur (Controller/Services/Repositories)
Legacy-Kompatibilität (Bootstrap/LegacyDB verfügbar, Seiten-Includes Header/Footer)
Assets/Styles (bestehendes Frontend, Navigation, Listen/Forms)

#ToDo
HTML/Markdown-Eingabe für Artikeltexte
Freigabe-Workflow (Admin/Chef-Redakteur mit Review/Approve)
Erweiterte Suche mit Fuzzy (z. B. Toleranz bei Tippfehlern, Relevanz-Ranking)
RSS-Feed (z. B. /feed.xml)
Bemerkungen/Notizen (interne Redaktionsnotizen pro Artikel)
Teilweise umgesetzt / Lücke schließen:
Zusätzliche Artikel-Attribute → Bild & Kategorie sind da, Untertitel (o. ä.) fehlt noch.
Spam-Protection → Honeypot existiert, CAPTCHA (z. B. hCaptcha/reCAPTCHA) optional ergänzen.
Benutzerverwaltung → Liste/Löschen vorhanden; fehlt: Benutzer anlegen/bearbeiten, Rollen/Zuweisungen, Passwort-Reset.

