Test: User
- Passwort vergessen (Mit E-Mail oder auch mit Benutzername)
- Bekommt E-Mail mit Passwort

--------------------------


Test: Agentur
- Kann Profil bearbeiten, aber nicht zwingend erforderlich
- Kann Kampagnen erstellen und bearbeiten
- Dashboard: Neuigkeiten, zuletzt bearbeitete Kampagnen
- Kampagne erstellen und einreichen
- Nach dem Einreichen wird eine PM an den LK gesendet
- Kann die freigeschalteten Kampagnen sehen
- Kann aber keine anderen Kampagnen sehen

Todos:
- im /user/%user/kampagnen, der Exposed block ist unter den Kampagnen
- Workflow ueberarbeiten, huebscher machen
- Moderatoren das anlegen von Agenturen gestatten
- Kann keine Messages schreiben, Form wird aber angezeigt. Sollte zumindest an den Support schreiben koennen
- Log-Meldungen koennen mehr informationen beinhalten (Titel)

Moderation:
- Kann Status veraendern und Kampagnen wieder zurueck zur Agentur senden.


--------------------------

Test: Verlags-Account

* Verlagsdaten editieren
- PDF-Vorschau

* Mitarbeiter & Teams
- Verkaufsleiter erstellen
- MA erstellen
- MA aktivieren
- MA deaktiveren
- Team editieren
- Einstellung: Mitarbeiter-Protokoll
- Dashboard: Standard: MA

Todos:
- Beim Anlegen wird das passwort nicht korrekt gesetzt
- Wenn Verlag keine Ausgaben hat, sollte er keine MA anlegen koennen


--------------------------

Test: VUM
- Kann in alle Bereiche des Verlages hereinschauen
- Kann keine Accounts bearbeiten
- Dashboard: Standard: MA

--------------------------

Todos:
- Teamleiter koennen nicht deaktiviert werden
- Teams entfernen... (tbd)
- Log-Messages bei Usern anpassen
- Lizenzen subtrahieren in Stats, wenn geloescht

--------------------------

Test: Merklisten
- Kampagne hinzufuegen
- Merkliste umbenennen
- Merkliste versenden
- Merkliste 2 VKU
- Merkliste loeschen
- Leer: Hinweismeldung

Todos:
- Refactor complete Merklisten, in \LK\System (tbd)
  Kaum Log-Meldungen zur Merkliste

--------------------------

Test: Zuletzt angesehen
- Formular: Filtern nach Datum
- Verlauf zuruecksetzen
- Leer: Hinweismeldung

--------------------------

Test Kampagnensuche:
- Ausgaben umstellen
- Autocomplete 
- Related Kampagnen
- Sortieren
- Ansichten - Teaser + Grid
- Redaktionelle Empfehlungen
- Suchanfrage
- Leere Suche mit Suchanfrage

Todos: 
- Autocomplete gibt etwas falsche resultate (ntc)
- Rewrite related Kampagnen (ntc)
- Unterschiedliche Anzahl von Resultatetn in Grid und Teaser-Ansicht bei zwei Suchbegriffen

--------------------------

Nachrichten:
- Neue schreiben
- Antworten
- Loeschen
- Kampagnen versenden
- Neuigkeiten versenden

Todos: 
- UI Verbessern, z.B. Statt Username, User-Avatar (ist nicht so lang)

--------------------------

Mein Profil
- Profil-Uebersicht
- Profildaten bearbeiten
- Passwort aendern
- Suchhistorie
- Eigene Statistiken

Todos: 
- Zugriff auf die Suchehistorie verbessern.


--------------------------

VKU-Old:
- Kampagnen hinzufuegen
- Titelseite editieren
- Loeschen
- Hinzufuegen von geloeschten Dokumenten
- Generieren der VKU
- Herunterladen der VKU (PDF)
- Ausgaben wechseln
- Erneuern der VKU
- Lizenz bestellen
- Lizenz herunterladen
- Sperre aktivieren
- Gesperrte Kampagne, Sichtbarkeit
- Test: Gesperrte Kampagnen

--------------------------

VKU-New: (baut auf dem alten auf)
- Test funktional
- PPTX Generierung

--------------------------

Test: Direkt-Lizenz erstellen:
- Download
- Naechste mal die Kampagne besuchen.

--------------------------

Test: Lizenz: Limit erreicht
- Lizenz wird nicht mehr in der Kampagne angezeigt
- Lizenz wird als Abgelaufen in der VKU angezeigt

--------------------------

Test: Testverlag
- Es kann keine Lizenz direkt erworben werden
- Es kann keine Lizenz ueber die VKU erworben werden.

--------------------------

Test: Neuigkeiten erstellen
- Als verschiedene Rollen

Todo: IDEE
- Vielleicht Bereich abspecken, da er nicht genutzt wird.
- Villeicht Bereich komplett ueberarbeiten, vielleicht Blog-Aehnlich
- Sperren auf bestimmte Nutzer oder Gruppen vielleicht weg nehmen

--------------------------

Test: Kurzzeitsperre von Kampagnen:
- Hinweis wird in der VKU erstellen angezeigt
- Hinweis wird bei anderen Benutzern angezeigt, z.T. kann die Kampagne nicht in die VKU hinzugefuegt werden.

--------------------------

Moderations-Funktionen:
- Kampagnen editieren
- Adminbereich
- ntbc.
