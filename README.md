# BlogPHP

#Funktionen-SOLL
- HTML/Markdown-Eingabe für Artikeltext
- Freigabe-Workflow (Admin/Chef-Redakteur)
- Erweiterte Suche inkl. Fuzzy Search
- RSS-Feed
- Spam-Protection (z. B. CAPTCHA)
- Bemerkungen/Notizen


#Funktionen-IST
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

#andere Funktionen
- Öffentliche Startseite mit Topic-Filter (t_id) und Suche über Titel & Body (LIKE), nur published Posts, absteigend nach created_at
- Kommentare anlegen (Form), inkl. Antwort auf Kommentar (threaded via parent_id)
- Rollen & Schutz: usersOnly() und adminOnly() Middleware (Admin-Flag in users.admin)
- Benutzerverwaltung → Liste/Löschen vorhanden; fehlt: Benutzer anlegen/bearbeiten, Rollen/Zuweisungen, Passwort-Reset.
- Bemerkungen/Notizen (interne Redaktionsnotizen pro Artikel)
- E-Mail Funktion

#ToDo
prio1
- Ordner-Struktur
- LegacyDB weg

prio2
- Erweiterte Suche mit Fuzzy (z. B. Toleranz bei Tippfehlern, Relevanz-Ranking)
- RSS-Feed (z. B. /feed.xml)

    Teilweise umgesetzt / Lücke schließen:
    - Spam-Protection → Honeypot existiert, CAPTCHA (z. B. hCaptcha/reCAPTCHA) optional ergänzen.


