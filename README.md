# aw_translator
REDAXO AddOn. Exportiert für Übersetzer und importiert Übersetzungen

**ACHTUNG!** Diese Version noch nicht einsetzen! Der xml Import ist noch nicht umgesetzt!

Das AddOn ermöglicht es, den Inhalt einer Sprache zu exportieren, um den Text an Übersetzer weiterzugeben. Der übersetzte Text kann wieder importiert werden.

## Mindestvoraussetzungen

* PHP 7.1
* REDAXO 5.6

## Installation

1. Über Installer laden oder Zip-Datei im AddOn-Ordner entpacken, der Ordner muss „awtranslator“ heißen.
2. AddOn installieren und aktivieren.

## Benutzung

* In den Einstellungen festlegen, welche Modulinhalte in die Übersetzungsdatei aufgenommen werden sollen. Es kann für jedes Modul einzeln festgelegt werden, welcher Wert (Value) in die Übersetzungsdatei kommt.
* Es können auch mform und mblock Modulinhalte übersetzt werden
* Die Quellsprache (z.B. deutsch) in die Zielsprache (z.B. englisch) kopieren.
* Die Zielsprache exportieren.
* Die übersetzte Datei muss die exakte Struktur der Quelldatei haben, sonst funktioniert der Reimport nicht! Es dürfen nur die Inhalte übersetzt werden, die Metadaten müssen erhalten bleiben.

## Arbeit mit dem Version Plugin

awtranslator arbeitet auch mit dem Version Plugin. So ist es möglich von der Live- oder der Arbeitsversion einer Ursprungssprache in die Live- oder Arbeitsversion der Zielsprache zu kopieren. Beim Export kann man auswählen, ob aus der Live- oder der Arbeitsversion exportiert werden soll.

## Hinweise

Der awtranslator erzeugt eine xml Datei, die sämtliche Metainformationen enthält, die für den Reimport notwendig sind. Dies ist unter anderem die Artikel Id, die Slice Id, aber auch z.B. eine Url auf den Quellartikel. Somit ist es dem Übersetzer möglich den Text im Kontext der gesamten Seite zu sehen.

Die xml Datei kann in eine xliff Datei exportiert werden. Hierzu kann beispielsweise das kostenlose Tool von maxprograms (https://www.maxprograms.com/products/xliffmanager.html) verwendet werden.

## Fehlermeldungen - was tun

Wenn der Import nicht auf die Seite passt, werden Fehlermeldungen ausgegeben in der Art "Slice Id 123 nicht vorhanden.". In diesem Falle passt der Import nicht auf die vorhandene Seite. Entweder wurden die Slices seit dem Export gelöscht oder die gesamte Seitenstruktur hat sich verändert.

Wenn es zu Ooops-Fehlern kommt, gerne den Entwickler benachrichtigen.

## Warnung

Die Verwendung dieses AddOn geschieht auf eigenes Risiko! Es wird dringend empfohlen vor einem kompletten Einsatz einen Testlauf mit dem Übersetzer zu machen.
