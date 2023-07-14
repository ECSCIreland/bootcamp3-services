<?php
if (!isset($running) or !isset($_GET['id'])) {
  die("nope");
}

$stmt = $db->prepare('SELECT * FROM reports WHERE owner=:owner AND id=:id');
$stmt->bindValue(':id', $_GET['id'], SQLITE3_INTEGER);
$stmt->bindValue(':owner', $username, SQLITE3_TEXT);
$result = $stmt->execute();
$report = $result->fetchArray();



if (!$report) {
  die("nope");
}


$stmt = $db->prepare('SELECT name FROM variables WHERE reportid=:id');
$stmt->bindValue(':id', $report['id'], SQLITE3_INTEGER);
$result = $stmt->execute();
while ($val = $result->fetchArray()) {
  $variables[] = $val[0];
}



$stmt = $db->prepare('SELECT * FROM filters WHERE reportid=:id');
$stmt->bindValue(':id', $report['id'], SQLITE3_INTEGER);
$result = $stmt->execute();
while ($val = $result->fetchArray()) {
  $filters[] = $val;
}


?>

<div class="card">
  <div class="card-body">
    <h5 class="card-title fw-semibold mb-4">Report template "<?= htmlspecialchars($report['name'], ENT_QUOTES | ENT_HTML5, 'UTF-8') ?>"</h5>
    <?php if (isset($variables)) { ?>
      <div class="card">
        <div class="card-header">
          Variables
        </div>
        <div class="card-body">
          <ul style="list-style-type:disc">
            <?php foreach ($variables as $var) { ?>
              <li><?= $var ?></li>
            <?php } ?>
          </ul>
        </div>
      </div>
    <?php } ?>
    <?php if (isset($filters)) { ?>
      <div class="card">
        <div class="card-header">
          Filters
        </div>
        <div class="card-body">
          <ul style="list-style-type:disc">
            <?php foreach ($filters as $filter) { ?>
              <li><?= htmlspecialchars($filter['column'] . " " . $filter['operator'] . " " . $filter['value'], ENT_QUOTES | ENT_HTML5, 'UTF-8') ?></li>
            <?php } ?>
          </ul>
        </div>
      </div>
    <?php } ?>

    <div class="card">
      <div class="card-header">
        Content
      </div>
      <div class="card-body">
        <?= str_replace("\n", "<br/>", htmlspecialchars($report['template'], ENT_QUOTES | ENT_HTML5, 'UTF-8')) ?>
      </div>
    </div>
  </div>
</div>