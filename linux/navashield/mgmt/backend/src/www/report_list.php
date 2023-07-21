<?php
if (!isset($running)) {
    die("nope");
}

$stmt = $db->prepare('SELECT * FROM reports WHERE owner=:owner');
$stmt->bindValue(':owner', $username, SQLITE3_TEXT);
$result = $stmt->execute();

function nicePriority($priorityInt)
{
    $text = "undefined";
    $bg = "dark";
    switch ($priorityInt) {
        case 0:
            $text = "Low";
            $bg = "success";
            break;
        case 1:
            $text = "Medium";
            $bg = "primary";
            break;
        case 2:
            $text = "High";
            $bg = "secondary";
            break;
        case 3:
            $text = "Critical";
            $bg = "danger";
            break;
    }
    return "<span class=\"badge bg-$bg rounded-3 fw-semibold\">$text</span>";
}

?>

<div class="card">
    <div class="card-body">
        <h5 class="card-title fw-semibold mb-4">Your report templates</h5>
        <a href="report.php?cmd=new" class="btn btn-primary">New template</a>
        <div class="table-responsive">
            <table class="table text-nowrap mb-0 align-middle table-striped">
                <thead class="text-dark fs-4">
                    <tr>
                        <th class="border-bottom-0">
                            <h6 class="fw-semibold mb-0">Name</h6>
                        </th>

                    </tr>
                </thead>
                <tbody>
                    <?php
                    while ($row = $result->fetchArray()) {
                    ?>

                        <tr>
                            <td class="border-bottom-0"  style="width: 100%"><a href="report.php?cmd=view&id=<?= htmlspecialchars($row['id'], ENT_QUOTES | ENT_HTML5, 'UTF-8') ?>">
                                    <h6 class="fw-semibold mb-0"><?= htmlspecialchars($row['name'], ENT_QUOTES | ENT_HTML5, 'UTF-8') ?></h6>
                                </a></td>

                            <td class="border-bottom-0" style="width: auto">
                                <a href="report.php?cmd=generate&id=<?= htmlspecialchars($row['id'], ENT_QUOTES | ENT_HTML5, 'UTF-8') ?>" class="btn btn-primary">Generate</a>
                            </td>
                        </tr>


                    <?php  } ?>
                </tbody>
            </table>
        </div>
    </div>
</div>