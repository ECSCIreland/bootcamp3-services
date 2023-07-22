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

# I know this is path traversal, just don't know how to fix it.
# ChatGPT recommends the following (but who knows if it would work):
# $policyId = basename($policy['id']);
# if(preg_match('/^[a-zA-Z0-9]+$/', $policyId)) {
#     $content = file_get_contents("../policies/" . $policyId . ".txt");
# } 
# else {
      # $content = "File not found";
#   }
$content = file_get_contents("../policies/" . basename($policy['id']) . ".txt");

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
        <?= nicePriority($policy['priority']) ?>
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