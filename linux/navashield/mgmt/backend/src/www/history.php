<?php

$running = 1;

require_once("auth.php");
require_once("nav.php");
require_once("db.php");

require_once("header.php");

if (isset($_GET['threadid'])) {
    $stmt = $db->prepare('SELECT * FROM history WHERE threadid=:threadid ORDER BY id ASC');
    $stmt->bindValue(':threadid', $_GET['threadid'], SQLITE3_TEXT);
    $result = $stmt->execute();
    $searchBox = false;

    $title = "Scan history for Thread ID " . $_GET['threadid'];
} else {
    $stmt = $db->prepare('SELECT * FROM history ORDER BY id DESC LIMIT 25');
    $stmt->bindValue(':threadid', $_GET['threadid'], SQLITE3_TEXT);
    $result = $stmt->execute();

    $title = "Last 25 scanned items";
    $searchBox = true;
}
?>

<div class="card">
    <div class="card-body">
        
        <?php if ($searchBox) { ?>
            <div class="card">
                <div class="card-header">
                    Search by Thread ID
                </div>
                <div class="card-body">
                    <form method="GET">
                        <div class="mb-3">
                            <input type="text" class="form-control" id="threadid" name="threadid">
                        </div>
                        <button class="btn btn-primary"><i class="ti ti-search"></i></button>
                    </form>
                </div>
            </div>

        <?php } ?>
        <h5 class="card-title fw-semibold mb-4"><?= htmlspecialchars($title, ENT_QUOTES | ENT_HTML5, 'UTF-8') ?></h5>
        <div class="table-responsive">
            <table class="table text-nowrap mb-0 align-middle table-striped">
                <thead class="text-dark fs-4">
                    <tr>
                        <th class="border-bottom-0">
                            <h6 class="fw-semibold mb-0">Thread ID</h6>
                        </th>
                        <th class="border-bottom-0">
                            <h6 class="fw-semibold mb-0">Hash</h6>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    while ($row = $result->fetchArray()) {
                    ?>

                        <tr>
                            <td class="border-bottom-0" style="width: 100%"><a href="history.php?threadid=<?= htmlspecialchars($row['threadid'], ENT_QUOTES | ENT_HTML5, 'UTF-8') ?>">
                                    <h6 class="fw-semibold mb-0"><?= htmlspecialchars($row['threadid'], ENT_QUOTES | ENT_HTML5, 'UTF-8') ?></h6>
                                </a></td>

                            <td class="border-bottom-0">
                                <div class="d-flex align-items-center gap-2">
                                <?= htmlspecialchars($row['hash'], ENT_QUOTES | ENT_HTML5, 'UTF-8') ?></h6>
                                </div>
                            </td>
                        </tr>


                    <?php  } ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php
require_once("footer.php");
