<?php
if (!isset($running) or !isset($_GET['id'])) {
  die("nope");
}

$stmt = $db->prepare('SELECT * FROM iocs WHERE owner=:owner AND id=:id');
$stmt->bindValue(':id', $_GET['id'], SQLITE3_INTEGER);
$stmt->bindValue(':owner', $username, SQLITE3_TEXT);
$result = $stmt->execute();
$ioc = $result->fetchArray();

if (!$ioc) {
  die("nope");
}

$stmt = $db->prepare('SELECT captures.id id, capturedfiles.threadid threadid FROM captures INNER JOIN capturedfiles ON captures.fileid = capturedfiles.id WHERE iocid=:id');
$stmt->bindValue(':id', $ioc['id'], SQLITE3_INTEGER);
$result = $stmt->execute();

?>

<div class="card">
  <div class="card-body">
    <h5 class="card-title fw-semibold mb-4">IOC "<?= htmlspecialchars($ioc['name'], ENT_QUOTES | ENT_HTML5, 'UTF-8') ?>" captures</h5>
    <div class="card">
      <div class="card-header">
        Hash
      </div>
      <div class="card-body">
        <?= htmlspecialchars($ioc['hash'], ENT_QUOTES | ENT_HTML5, 'UTF-8') ?>
      </div>
    </div>

    <div class="card">
      <div class="card-header">
        Captures
      </div>
      <div class="card-body">
        <div class="table-responsive">
          <table class="table text-nowrap mb-0 align-middle table-striped">
            <thead class="text-dark fs-4">
              <tr>
                <th class="border-bottom-0">
                  <h6 class="fw-semibold mb-0">Thread ID</h6>
                </th>
              </tr>
            </thead>
            <tbody>
              <?php
              while ($row = $result->fetchArray()) {
              ?>
                <tr>
                  <td class="border-bottom-0" style="width: 100%"><a href="ioc_dl.php?id=<?= htmlspecialchars($row['id'], ENT_QUOTES | ENT_HTML5, 'UTF-8') ?>">
                      <h6 class="fw-semibold mb-0"><?= htmlspecialchars($row['threadid'], ENT_QUOTES | ENT_HTML5, 'UTF-8') ?></h6>
                    </a></td>
                </tr>
                
              <?php  } ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>