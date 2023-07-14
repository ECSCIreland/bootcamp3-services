<?php
if (!isset($running)) {
    die("nope");
}

$stmt = $db->prepare('SELECT * FROM iocs WHERE owner=:owner');
$stmt->bindValue(':owner', $username, SQLITE3_TEXT);
$result = $stmt->execute();

?>

<div class="card">
    <div class="card-body">
        <h5 class="card-title fw-semibold mb-4">Your IOCs</h5>
        <a href="ioc.php?cmd=new" class="btn btn-primary">New IOC</a>
        <div class="table-responsive">
            <table class="table text-nowrap mb-0 align-middle table-striped">
                <thead class="text-dark fs-4">
                    <tr>
                        <th class="border-bottom-0">
                            <h6 class="fw-semibold mb-0">Name</h6>
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
                            <td class="border-bottom-0" style="width: 100%"><a href="ioc.php?cmd=view&id=<?= $row['id'] ?>">
                                    <h6 class="fw-semibold mb-0"><?= htmlspecialchars($row['name'], ENT_QUOTES | ENT_HTML5, 'UTF-8') ?></h6>
                                </a></td>

                            <td class="border-bottom-0">
                                <div class="d-flex align-items-center gap-2">
                                <?= htmlspecialchars($row['hash'], ENT_QUOTES | ENT_HTML5, 'UTF-8') ?>
                                </div>
                            </td>
                        </tr>


                    <?php  } ?>
                </tbody>
            </table>
        </div>
    </div>
</div>