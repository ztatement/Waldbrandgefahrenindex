<?php

/**
  * Klasse zur Abfrage des Waldbrandgefahrenindex für Brandenburg.
  *
  * Diese Klasse lädt und verarbeitet die Waldbrandgefahreninformationen
  * aus einer XML-Datei des Ministeriums für Landwirtschaft, Umwelt und Klimaschutz
  * des Landes Brandenburg. Die Daten werden zwischengespeichert, um die
  * Performance zu verbessern und die Serverlast zu reduzieren.
  *
  * ----------------
  *
  * @author Thomas Boettcher <github[at]ztatement[dot]com>
  * @copyright (c) 2025 ztatement
  *
  * @version 1.0.0.2025.03.11
  * @link https://github.com/ztatement/Waldbrandgefahrenindex
  *
  * @file $Id: Waldbrandgefahrenindex.php 1 2025-03-11 07:45:53Z ztatement $
  *
  * ----------------
  *
  * @license The MIT License (MIT)
  * @see /LICENSE
  * @see https://opensource.org/licenses/MIT Hiermit wird unentgeltlich jeder Person, die eine Kopie der Software und der zugehörigen
  *      Dokumentationen (die "Software") erhält, die Erlaubnis erteilt, sie uneingeschränkt zu nutzen,
  *      inklusive und ohne Ausnahme mit dem Recht, sie zu verwenden, zu kopieren, zu verändern,
  *      zusammenzufügen, zu veröffentlichen, zu verbreiten, zu unterlizenzieren und/oder zu verkaufen,
  *      und Personen, denen diese Software überlassen wird, diese Rechte zu verschaffen,
  *      unter den folgenden Bedingungen:
  *     
  *      Der obige Urheberrechtsvermerk und dieser Erlaubnisvermerk sind in allen Kopien
  *      oder Teilkopien der Software beizulegen.
  *     
  *      DIE SOFTWARE WIRD OHNE JEDE AUSDRÜCKLICHE ODER IMPLIZIERTE GARANTIE BEREITGESTELLT,
  *      EINSCHLIEẞLICH DER GARANTIE ZUR BENUTZUNG FÜR DEN VORGESEHENEN ODER EINEM BESTIMMTEN
  *      ZWECK SOWIE JEGLICHER RECHTSVERLETZUNG, JEDOCH NICHT DARAUF BESCHRÄNKT.
  *      IN KEINEM FALL SIND DIE AUTOREN ODER COPYRIGHTINHABER FÜR JEGLICHEN SCHADEN
  *      ODER SONSTIGE ANSPRÜCHE HAFTBAR ZU MACHEN, OB INFOLGE DER ERFÜLLUNG EINES VERTRAGES,
  *      EINES DELIKTES ODER ANDERS IM ZUSAMMENHANG MIT DER SOFTWARE
  *      ODER SONSTIGER VERWENDUNG DER SOFTWARE ENTSTANDEN.
  *      ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
  */

class Waldbrandgefahrenindex
{
/**
  * @var string  URL der XML-Datei mit den Waldbrandgefahreninformationen.
  */
  private $xmlDatei = 'https://mleuv.brandenburg.de/mleuv/de/wgs.xml';

/**
  * @var string  Inhalt der XML-Datei (zwischengespeichert).
  */
  private $xmlInhalt;

/**
  * @var array  Assoziatives Array, das den Waldbrandgefahrenindex für jeden Landkreis speichert.
  *             Der Schlüssel ist der Name des Landkreises, der Wert die Waldbrandstufe.
  */
  private $Waldbrandgefahrenindex = [];

/**
  * @var string  Pfad zur Cache-Datei, in der die XML-Daten gespeichert werden.
  */
  private $cacheFile = './.cache/wgs_cache.xml'; // anpassen!

/**
  * @var int  Gültigkeitsdauer des Caches in Sekunden (hier: 4 Stunden).
  */
  private $cacheTime = 10800; // 3 Stunden in Sekunden

/**
  * Konstruktor der Klasse.
  *
  * Lädt den Waldbrandgefahrenindex beim Erzeugen eines Objekts der Klasse.
  */
  public function __construct()
  {
    $this->ladeWaldbrandgefahrenindex();
  }

/**
  * Lädt den Waldbrandgefahrenindex entweder aus dem Cache oder aktualisiert ihn.
  *
  * Diese Methode prüft, ob der Cache aktualisiert werden muss. Wenn ja, wird
  * die XML-Datei von der Quelle heruntergeladen und im Cache gespeichert.
  * Anschließend werden die Daten aus dem Cache geparst.
  */
  private function ladeWaldbrandgefahrenindex()
  {
    if ($this->sollAktualisieren())
    {
      $this->aktualisiereCache();
    }
    $this->parseXML();
  }

/**
  * Prüft, ob der Cache aktualisiert werden muss.
  *
  * @return bool  True, wenn der Cache aktualisiert werden muss, sonst false.
  *               Ein Update ist erforderlich, wenn die Cache-Datei nicht existiert
  *               oder älter als die definierte Cache-Zeit ist.
  */
  private function sollAktualisieren(): bool
 {
   if (!file_exists($this->cacheFile))
   {
      return true;
    }
    return (time() - filemtime($this->cacheFile)) > $this->cacheTime;
  }

/**
  * Aktualisiert den Cache mit den aktuellen Daten aus der XML-Quelle.
  *
  * Lädt den Inhalt der XML-Datei von der angegebenen URL und speichert ihn
  * in der Cache-Datei.  Bei einem Fehler beim Herunterladen (z.B. Server nicht
  * erreichbar) wird die Funktion abgebrochen, sodass die alte Cache-Datei
  * erhalten bleibt.
  */
  private function aktualisiereCache()
  {
    $neuerInhalt = @file_get_contents($this->xmlDatei);
    if ($neuerInhalt !== false)
    {
      file_put_contents($this->cacheFile, $neuerInhalt);
    }
  }

/**
  * Parst die XML-Daten aus der Cache-Datei und speichert sie in einem Array.
  *
  * Liest den Inhalt der Cache-Datei, parst ihn mit `simplexml_load_string()`
  * und speichert die Waldbrandstufen für jeden Landkreis in dem
  * `$this->Waldbrandgefahrenindex` Array.
  */
  private function parseXML()
  {
    $this->xmlInhalt = file_get_contents($this->cacheFile);
    $xml = simplexml_load_string($this->xmlInhalt);

    if ($xml === false)
    {
      die('Fehler beim Laden der XML-Datei.');
    }

    foreach ($xml->tag->landkreis as $landkreis)
    {
      $name = (string) $landkreis['name'];
      $waldbrandstufe = (int) $landkreis;
      $this->Waldbrandgefahrenindex[$name] = $waldbrandstufe;
    }
  }

/**
  * Gibt das Datum der letzten Aktualisierung des Waldbrandgefahrenindex zurück.
  *
  * @return string|null  Das Datum im Format der XML-Datei oder null, wenn
  *                      ein Fehler auftritt.
  */
  public function getWBIDatum(): ?string
  {
    $xml = simplexml_load_string($this->xmlInhalt);
    if ($xml === false)
    {
      return null;
    }

    $datum = (string) $xml->tag->datum;
    return $datum ?: null;
  }

/**
  * Gibt ein Array mit allen verfügbaren Landkreisen zurück.
  *
  * Diese Methode liefert die Namen aller Landkreise, die im Waldbrandgefahrenindex
  * enthalten sind. Die Namen werden als Schlüssel im `$Waldbrandgefahrenindex`-Array
  * gespeichert und können hiermit einfach abgerufen werden.
  *
  * @return array  Ein Array mit den Namen der Landkreise.
  */
  public function getAlleLandkreise(): array
  {
    return array_keys($this->Waldbrandgefahrenindex);
  }


/**
  * Gibt die Waldbrandstufe, Farbe und Beschreibung für einen bestimmten Landkreis zurück.
  *
  * @param string $landkreisName Der Name des Landkreises.
  *
  * @return array|null  Ein assoziatives Array mit 'stufe', 'farbe' und 'beschreibung',
  *                     oder null, wenn der Landkreis nicht gefunden wurde.
  */
  public function getWaldbrandstufe(string $landkreisName): ?array
  {
    if (isset($this->Waldbrandgefahrenindex[$landkreisName]))
    {
      $stufe = $this->Waldbrandgefahrenindex[$landkreisName];
      return [
        'stufe' => $stufe,
        'farbe' => $this->getFarbeForStufe($stufe),
        'beschreibung' => $this->getBeschreibungForStufe($stufe)
      ];
    }
    else
    {
      return null;
    }
  }

/**
  * Erzwingt eine Aktualisierung des Caches und parst die XML-Daten neu.
  *
  * Diese Methode kann verwendet werden, um den Cache manuell zu aktualisieren,
  * unabhängig von der Gültigkeitsdauer.
  */
  public function forceCacheUpdate()
  {
    $this->aktualisiereCache();
    $this->parseXML();
  }

/**
  * Zeigt die Waldbrandstufe für einen bestimmten Landkreis an.
  *
  * @param string $landkreisName Der Name des Landkreises.
  *
  * @return void  Gibt die Waldbrandstufe für den angegebenen Landkreis aus.
  *               Wenn der Landkreis nicht gefunden wird, wird eine entsprechende
  *               Meldung ausgegeben.
  */
  public function zeigeWaldbrandstufe(string $landkreisName): void
  {
    $stufe = $this->getWaldbrandstufe($landkreisName);
    if ($stufe !== null)
    {
       echo "Die Waldbrandstufe für " . $landkreisName . " beträgt: " . $stufe . "\n";
    }
    else
    {
       echo "Landkreis " . $landkreisName . " nicht gefunden.\n";
    }
  }

/**
  * Ermittelt die Farbe für eine gegebene Waldbrandstufe.
  *
  * @param int $stufe  Die Waldbrandstufe.
  * @return string  Der Hexadezimal-Farbcode für die entsprechende Stufe.
  */
  private function getFarbeForStufe(int $stufe): string
  {
    switch ($stufe)
    {
      case 1: return '#28a745';  // grün
      case 2: return '#9acd32';  // gelb-grün
      case 3: return '#ffc107';  // gelb
      case 4: return '#fd7e14';  // orange
      case 5: return '#dc3545';  // rot
      default: return '#6c757d'; // grau für Katastrophen
    }
  }

/**
  * Gibt eine textuelle Beschreibung für eine gegebene Waldbrandstufe zurück.
  *
  * @param int $stufe  Die Waldbrandstufe.
  * @return string  Die Beschreibung der Gefahrenstufe.
  */
  private function getBeschreibungForStufe(int $stufe): string
  {
    switch ($stufe)
    {
      case 1: return "Sehr geringe Gefahr";
      case 2: return "Geringe Gefahr";
      case 3: return "Mittlere Gefahr";
      case 4: return "Hohe Gefahr";
      case 5: return "Sehr hohe Gefahr";
      default: return "Katastrophen Gefahr";
    }
  }

}


/**
  *
  * // Beispielanwendung
  * $waldbrand = new Waldbrandgefahrenindex();
  *
  * // Waldbrandstufe für Märkisch-Oderland anzeigen
  * $waldbrand->zeigeWaldbrandstufe('Märkisch-Oderland');
  *
  * // Direkter Zugriff auf die Stufe (ohne Anzeige)
  * $landkreisName = $defaultLandkreis;
  * $stufe = $waldbrand->getWaldbrandstufe('Märkisch-Oderland');
  * if ($stufe !== null) {
  *   echo "Die Waldbrandstufe für " . $landkreisName . " beträgt: " . $stufe . "\n";
  * }
  * // Zugriff auf das Datum der letzten Akualisierung
  * $wbidatum = $waldbrand->getWBIDatum();
  * if ($wbidatum !== null) {
  *   echo "letzte Aktualisierung: " . $wbidatum . "\n";
  * } else {
  *   echo "Datum konnte nicht abgerufen werden.\n";
  * }
  *
  * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ *
  * @LastModified: 2025-03-11 
  * @date $LastChangedDate: Tue Mar 11 2025 07:45:53 GMT+0100 $
  * @editor: $LastChangedBy: ztatement $
  * -------------
  *
  * $Date$     : $Revision$          : $LastChangedBy$  - Description
  * 2025-03-11 : 1.0.0.2025.03.11    : ztatement        - added: Waldbrandgefahrenindex Klasse neu angelegt
  * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ *
  * Local variables:
  * tab-width: 2
  * c-basic-offset: 2
  * c-hanging-comment-ender-p: nil
  * End:
  */