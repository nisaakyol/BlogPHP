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
- Kommentare durch Besucher ermöglichen mit hilfe von cookies
- Spam-Protection
- Erweiterte Suche mit Fuzzy 
- reCAPTCHA v2 beim Login
- reCAPTCHA v3 bei den Kommentaren

#ToDo

- Ordner-Struktur
- Kommentare

- RSS-Feed (z. B. /feed.xml)
- barrierefreiheit
- bilder in der view
- bilder wenn man auf den artikel klickt


