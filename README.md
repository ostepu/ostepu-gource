# ostepu-Gource

Dieses Modul soll Videos erzeugen, welche den Entwicklungsverlauf der einzelnen Projekte zeigen (nur Git-Repos).
Dazu wird Gource (http://gource.io) und FFMPEG verwendet.

Es wird ein neuer Abschnitt in den Bereich `Entwicklung` des Installationsassistenten eingetragen.

![](/images/A.png)

### Speicherort

![](/images/B.png)

An diesem Ort werden die Dateien gesammelt, er sollte bereits existieren.

### Startdatum
![](/images/C.png)

Das Startdatum gibt an, aber welchen welche Commits genutzt werden (alle die mindestens dieses Datum haben).

### Repositories auswählen
![](/images/D.png)

Sie müssen auswählen, welche gefunden Repos in das Video aufgenommen werden sollen. (dabei werden nur solche aufgelistet, die durch die Paketverwaltung installiert wurde bzw. selektiert sind)

## Gource Rohdaten zusammenstellen (.captions, .dat)
![](/images/E.png)

Zunächst müssen die Git-Daten gelesen und in das Format von Gource umgewandelt werden.

![](/images/E2.png)

Wenn der Vorgang erfolgreich war, wird die Größe und der Ort der neuen Dateien aufgelistet.

![](/images/E3.png)

Die neuen Dateien befinden sich nun im Ausgabeordner.

## Video Rohdaten erzeugen (.ppm)
![](/images/F.png)

Sie können sich die erzeugten Gource-Daten anzeigen lassen

![](/images/F2.png)

Sie müssen eine auswählen und können dann entscheiden, ob Sie Gource nur ausführen und damit das Video sehen wollen oder ob Sie die Rohdaten für die spätere Videoumwandlung erzeugen wollen.

![](/images/F3.png)

Die neue Datei befindet sich nun im Ausgabeordner.

## Video rendern (.mp4)
![](/images/G.png)

Sie können sich hier die bereits vorhandenen Video-Rohdaten anzeigen lassen.

![](/images/G2.png)

Wählen Sie eine aus und löse Sie die Umwandlung aus (wird unsichtbar im Hintergrund ausgeführt).

![](/images/G3.png)

Die neue Datei befindet sich nun im Ausgabeordner.
