<?php
if (!isset($running) or !isset($_GET['id'])) {
  die("nope");
}

$stmt = $db->prepare('SELECT * FROM policies WHERE id=:id');
$stmt->bindValue(':id', $_GET['id'], SQLITE3_INTEGER);
$result = $stmt->execute();
$policy = $result->fetchArray();

if (!$policy) {
  die("nope");
}

// Using basename so cant use any path treversal
$fileName = basename($$policy['id']);
$filePath = "../policies/" . $fileName . ".txt";
// Checking if file exists, if not sets to file not found. Ensures no shits leaked. idk Im not a php dev, chat gpt suggested it lol.
if (if_file($filePath)){
  $content = file_get_contents($filePath);
} else {
  $content = "File not found";
}

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
    <h5 class="card-title fw-semibold mb-4">Policy "<?= htmlspecialchars($policy['name'], ENT_QUOTES | ENT_HTML5, 'UTF-8') ?>"</h5>
    <div class="card">
      <div class="card-header">
        Priority
      </div>
      <div class="card-body">
        <?=  htmlspecialchars(nicePriority($policy['priority']), ENT_QUOTES | ENT_HTML5, 'UTF-8') ?>
      </div>
    </div>

    <div class="card">
      <div class="card-header">
        Policy text
      </div>
      <div class="card-body">
        <?= str_replace("\n", "<br/>", htmlspecialchars($content, ENT_QUOTES | ENT_HTML5, 'UTF-8')) ?>
      </div>
    </div>
  </div>
</div>