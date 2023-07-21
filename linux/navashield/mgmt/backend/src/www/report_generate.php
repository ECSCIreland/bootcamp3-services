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


?>

<div class="card">
  <div class="card-body">
    <h5 class="card-title fw-semibold mb-4">Generate report "<?= htmlspecialchars($report['name'], ENT_QUOTES | ENT_HTML5, 'UTF-8') ?>"</h5>
    <?php if (isset($error)) { ?>
      <div class="alert alert-danger" role="alert">
        <?= $error ?>
      </div>
    <?php } ?>
    <form method="POST" action="/report_pdf.php?id=<?= htmlspecialchars($report['id'], ENT_QUOTES | ENT_HTML5, 'UTF-8') ?>">
      <?php if (isset($variables)) {
        foreach ($variables as $var) { ?>
          <div class="mb-3">
            <label for="<?= $var ?>" class="form-label"><?= htmlspecialchars($var, ENT_QUOTES | ENT_HTML5, 'UTF-8') ?></label>
            <input type="text" class="form-control" id="<?= $var ?>" name="<?= $var ?>">
          </div>
      <?php }
      } ?>
      <button name="submit" type="submit" class="btn btn-primary">Create</button>
    </form>
  </div>
</div>