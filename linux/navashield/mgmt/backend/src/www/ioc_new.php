<?php
if (!isset($running)) {
  die("nope");
}

function validateForm()
{
  global $error;
  global $hash;
  if (!isset($_POST['name']) || $_POST['name'] == '') {
    $error = "Name cannot be empty.";
    return false;
  }

  if (strlen($_POST['name']) > 24) {
    $error = "Name too long.";
    return false;
  }
  
  if(!isset($_FILES["hunt"]))
  {
    $error = "No file.";
    return false;
  }

  if ($_FILES["hunt"]["size"] > 1024) {
    $error = "File too big.";
    return false;
  }

  $hash = hash("crc32b", file_get_contents($_FILES['hunt']['tmp_name']));

  return true;
}



$stmt = $db->prepare('SELECT COUNT(*) FROM iocs WHERE owner=:owner');
$stmt->bindValue(':owner', $username, SQLITE3_TEXT);
$val = $stmt->execute()->fetchArray()[0];
if ($val >= 5) {
  echo "<h5>Too many IOCs</h5>";
} else {



  if (isset($_POST['submit']) && validateForm()) {
    $stmt = $db->prepare('INSERT into iocs (owner,name,hash) VALUES (:user, :name, :hash)');
    $stmt->bindValue(':user', $username, SQLITE3_TEXT);
    $stmt->bindValue(':name', $_POST['name'], SQLITE3_TEXT);
    $stmt->bindValue(':hash', $hash, SQLITE3_TEXT);
    $res = $stmt->execute();

?>
    <div class="card">
      <div class="card-body">
        <h5 class="card-title fw-semibold mb-4">IOC created!</h5>
        <a href="ioc.php" class="btn btn-primary">Back to IOCs list</a>
      </div>
    </div>
  <?php
  } else {





  ?>

    <div class="card">
      <div class="card-body">
        <h5 class="card-title fw-semibold mb-4">New IOC</h5>
        <?php if (isset($error)) { ?>
          <div class="alert alert-danger" role="alert">
            <?= $error ?>
          </div>
        <?php } ?>
        <form method="POST" enctype="multipart/form-data">
          <div class="mb-3">
            <label for="name" class="form-label">Name</label>
            <input type="text" class="form-control" id="name" name="name">
          </div>
          <div class="mb-3">
            <label for="name" class="form-label">File to hunt for:</label>
            <input type="file" name="hunt" id="hunt">
          </div>
          <button name="submit" type="submit" class="btn btn-primary">Create</button>
        </form>
      </div>
    </div>

<?php
  }
}
?>