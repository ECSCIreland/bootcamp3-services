<?php
if (!isset($running)) {
  die("nope");
}

function validateForm()
{
  global $error;
  if (!isset($_POST['name']) || $_POST['name'] == '') {
    $error = "Name cannot be empty.";
    return false;
  }

  if (strlen($_POST['name']) > 24) {
    $error = "Name too long.";
    return false;
  }

  if (!isset($_POST['priority']) || !is_numeric($_POST['priority'])) {
    $error = "Wrong priority.";
    return false;
  }

  if (!isset($_POST['content']) || $_POST['content'] == '') {
    $error = "Content cannot be empty.";
    return false;
  }

  if (strlen($_POST['content']) > 1024) {
    $error = "Content too long.";
    return false;
  }

  return true;
}



$stmt = $db->prepare('SELECT COUNT(*) FROM policies WHERE owner=:owner');
$stmt->bindValue(':owner', $username, SQLITE3_TEXT);
$val = $stmt->execute()->fetchArray()[0];
if ($val >= 5) {
  echo "<h5>Too many policies</h5>";
} else {



  if (isset($_POST['submit']) && validateForm()) {
    $stmt = $db->prepare('INSERT into policies (owner,name,priority) VALUES (:user, :name, :priority)');
    $stmt->bindValue(':user', $username, SQLITE3_TEXT);
    $stmt->bindValue(':name', $_POST['name'], SQLITE3_TEXT);
    $stmt->bindValue(':priority', $_POST['priority'], SQLITE3_INTEGER);
    $res = $stmt->execute();
    file_put_contents("../policies/" . $db->lastInsertRowID() . ".txt", $_POST['content']);

?>
    <div class="card">
      <div class="card-body">
        <h5 class="card-title fw-semibold mb-4">Policy created!</h5>
        <a href="policy.php" class="btn btn-primary">Back to policies list</a>
      </div>
    </div>
  <?php
  } else {





  ?>

    <div class="card">
      <div class="card-body">
        <h5 class="card-title fw-semibold mb-4">New policy</h5>
        <?php if (isset($error)) { ?>
          <div class="alert alert-danger" role="alert">
            <?= $error ?>
          </div>
        <?php } ?>
        <form method="POST">
          <div class="mb-3">
            <label for="name" class="form-label">Name</label>
            <input type="text" class="form-control" id="name" name="name">
          </div>
          <div class="mb-3">
            <label for="priority" class="form-label">Priority</label>
            <select id="priority" class="form-select" name="priority">
              <option value="0">Low</option>
              <option value="1">Medium</option>
              <option value="2">High</option>
              <option value="3">Critical</option>
            </select>
          </div>
          <div class="mb-3">
            <label for="content" class="form-label">Policy content</label>
            <textarea class="form-control" id="content" rows="8" name="content"></textarea>
          </div>
          <button name="submit" type="submit" class="btn btn-primary">Create</button>
        </form>
      </div>
    </div>

<?php
  }
}
?>