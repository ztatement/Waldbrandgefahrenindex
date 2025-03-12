<?php

/**
  * 
  * @description: ${project.description}
  *
  * ----------------
  *
  * @author Thomas Boettcher <github[at]ztatement[dot]com>
  * @copyright (c) 2025 ztatement
  * 
  * @version: ${project.version}
  * @website: ${project.url}
  * @link https://github.com/ztatement/Waldbrandgefahrenindex
  *
  * @file: $Id: waldbrandgefahrenindex.template.php  1 Tue Mar 11 2025 07:45:53 GMT+0100Z ztatement $
  *
  * ----------------
  *
  * @license The MIT License (MIT)
  * @main: ${project.groupId}.${project.artifactId}.${project.name}
  */

  require_once ('Waldbrandgefahrenindex.php');
  $waldbrand = new Waldbrandgefahrenindex();
  $landkreise = $waldbrand->getAlleLandkreise();
  $wbidatum = $waldbrand->getWBIDatum();

  // Verarbeitung des Formulars
/**
  * Der Standard-Landkreis, der im Dropdown-Menü vorausgewählt ist.
  *
  * Wird verwendet, wenn das Formular noch nicht abgeschickt wurde oder keine
  * andere Auswahl getroffen wurde.
  */
  $defaultLandkreis = 'Märkisch-Oderland'; // Standardwert für das Dropdown
  if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['landkreis']))
  {
      $ausgewaehlterLandkreis = $_POST['landkreis'];
  }
  else
  {
      $ausgewaehlterLandkreis = $defaultLandkreis; // Falls nichts ausgewählt wurde, setze den Standardwert
  }

?>
<!DOCTYPE html>
<html lang="de">
<head>

  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="Erhalte aktuelle Informationen über den Waldbrandgefahrenindex für Brandenburg.">

  <title>Waldbrandgefahrenindex</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">

  <style>
  .wbi { height:72px !important;width:72px !important; display:flex;align-items:center;justify-content:center; }
   a {text-decoration:none;color:inherit;}
  .h0 { font-size:3rem; }
  .br-0 { border-radius:0; }
  .text-blue { color:darkblue;}
  </style>

</head>
<body>

  <div class="container my-5">

    <div class="card shadow-sm br-0" data-bs-theme="info">
      <div class="card-header bg-info-subtle">
        <h1 class="h2 m-0">Waldbrandgefahrenindex für Brandenburg</h1>
      </div>

      <div class="card-body">
        <?php if ($wbidatum): ?>
          <p aria-live="polite" class="text-muted">Letzte Aktualisierung: <?php echo htmlspecialchars($wbidatum); ?></p>
        <?php endif; ?>

        <form method="post" class="mb-4" aria-label="Formular zur Auswahl eines Landkreises und Anzeige der Waldbrandstufe">
          <div class="mb-3">
            <label for="landkreis" class="form-label">Landkreis:</label>
            <select name="landkreis" id="landkreis" class="form-select" aria-describedby="landkreis-hilfe">
              <?php foreach ($landkreise as $landkreis): ?>
                <option value="<?php echo htmlspecialchars($landkreis); ?>"
                  <?php echo ($ausgewaehlterLandkreis === $landkreis) ? 'selected' : ''; ?>>
                  <?php echo htmlspecialchars($landkreis); ?>
                </option>
              <?php endforeach; ?>
            </select>
            <small id="landkreis-hilfe" class="form-text text-muted">Bitte wähle einen Landkreis aus der Liste.</small>
          </div>
          <button type="submit" class="btn btn-info text-white">Waldbrandstufe anzeigen</button>
        </form>

<?php if (isset($ausgewaehlterLandkreis)): ?>
    <?php $stufenInfo = $waldbrand->getWaldbrandstufe($ausgewaehlterLandkreis); ?>
    <?php if ($stufenInfo !== null): ?>
        <div class="card mt-4">
            <div class="card-body text-center" style="background-color: <?php echo $stufenInfo['farbe']; ?>;">
                <h5 class="card-title text-white">Waldbrandgefahrenstufe für <?php echo htmlspecialchars($ausgewaehlterLandkreis); ?></h5>
                <p class="card-text">
                    <span class="mt-3 mx-auto wbi border border-2 rounded-circle align-middle">&nbsp;<span class="align-middle fw-bold h0"><?php echo $stufenInfo['stufe']; ?></span>&nbsp;</span><br>
                    <span class="fs-4 ms-3 text-white"><?php echo $stufenInfo['beschreibung']; ?></span>
                </p>
            </div>
        </div>
    <?php else: ?>
        <p class="text-danger">Landkreis <?php echo htmlspecialchars($ausgewaehlterLandkreis); ?> nicht gefunden.</p>
    <?php endif; ?>
<?php endif; ?>
      </div>

      <div class="card-footer text-muted bg-info-subtle">
        © 2025 <a href="https://github.com/ztatement/Waldbrandgefahrenindex">Ztatement</a>
        <span class="float-end">
          <a href="#" class="d-inline-block" data-bs-toggle="modal" data-bs-target="#infoModal" aria-label="Mehr Informationen zum Waldbrandgefahrenindex">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-info-circle" viewBox="0 0 16 16">
              <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14m0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16"/>
              <path d="m8.93 6.588-2.29.287-.082.38.45.083c.294.07.352.176.288.469l-.738 3.468c-.194.897.105 1.319.808 1.319.545 0 1.178-.252 1.465-.598l.088-.416c-.2.176-.492.246-.686.246-.275 0-.375-.193-.304-.533zM9 4.5a1 1 0 1 1-2 0 1 1 0 0 1 2 0"/>
            </svg>
          </a>
        </span>
      </div>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="infoModal" tabindex="-1" aria-labelledby="infoModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="infoModalLabel">Informationen zum Waldbrandgefahrenindex</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Schließen"></button>
          </div>
          <div class="modal-body">
            <p>Das Ministerium für Landwirtschaft, Umwelt und Klimaschutz (MLUK) des Landes Brandenburg<br>
               Referat 46 - Wald und Forstwirtschaft, Oberste Jagdbehörde;</p>

            <p>veröffentlicht jeden Tag die aktuellen Waldbrandgefahrenstufen auf ihrer Website. Unter diesem Link können jeweils eine allgemeine Karte und eine .XML Datei mit allen Werten abgerufen werden. Die Daten werden täglich in einem Zeitraum von Anfang März bis Ende September aktualisiert und veröffentlicht. In den Wintermonaten werden keine Daten zur Verfügung gestellt.</p>

            <p>Weitere Informationen zu den Waldbrandgefahrenstufen erhalten Sie auf dieser Internetseite: <a href="https://mleuv.brandenburg.de/mleuv/de/umwelt/forst/waldschutz/waldbrandgefahr-in-brandenburg/waldbrandgefahrenstufen/" target="_blank" rel="noopener noreferrer" class="text-blue">Ministerium für Landwirtschaft, Umwelt und Klimaschutz (MLUK)</a></p>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Schließen</button>
          </div>
        </div>
      </div>
    </div>

  </div> <!-- ./container -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>

</body>
</html>
