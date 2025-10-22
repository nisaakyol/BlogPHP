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
Z- usätzliche Artikel-Attribute (Bild, Kategorie, Untertitel, …)

#andere Funktionen
- Öffentliche Startseite mit Topic-Filter (t_id) und Suche über Titel & Body (LIKE), nur published Posts, absteigend nach created_at
- Kommentare anlegen (Form), inkl. Antwort auf Kommentar (threaded via parent_id)
- Rollen & Schutz: usersOnly() und adminOnly() Middleware (Admin-Flag in users.admin)


#ToDo
- Sign Up mit eintrag in die DB
- User eigene Posts verwalten über dashboard
- Freigabe-Workflow (Admin/Chef-Redakteur mit Review/Approve)
- kommentare nur über eigenen User-Name schreiben
- HTML/Markdown-Eingabe für Artikeltexte
- Ordner-Struktur
- Erweiterte Suche mit Fuzzy (z. B. Toleranz bei Tippfehlern, Relevanz-Ranking)
- RSS-Feed (z. B. /feed.xml)
- Bemerkungen/Notizen (interne Redaktionsnotizen pro Artikel)

    Teilweise umgesetzt / Lücke schließen:
    - Zusätzliche Artikel-Attribute → Bild & Kategorie sind da, Untertitel (o. ä.) fehlt noch.
    - Spam-Protection → Honeypot existiert, CAPTCHA (z. B. hCaptcha/reCAPTCHA) optional ergänzen.
    - Benutzerverwaltung → Liste/Löschen vorhanden; fehlt: Benutzer anlegen/bearbeiten, Rollen/Zuweisungen, Passwort-Reset.
    - E-Mail-Versand (einfacher Textmailer/Service; z. B. für Kontakt)

