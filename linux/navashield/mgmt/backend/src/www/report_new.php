<?php
if (!isset($running)) {
  die("nope");
}

$allowedColumns = ["name", "priority"];
$allowedOperators = ["=", "!=", "<", ">", "<=", ">=", "LIKE"];

function validateForm()
{
  global $error, $allowedColumns, $allowedOperators;
  if (!isset($_POST['name']) || $_POST['name'] == '') {
    $error = "Name cannot be empty.";
    return false;
  }

  if (strlen($_POST['name']) > 24) {
    $error = "Name too long.";
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

  if(isset($_POST['variable']))
  {
      if(!is_array($_POST['variable'])) 
      {
        $error = "Expected an array.";
        return false;
      }

      foreach($_POST['variable'] as $varName)
      {
        if($varName == '')
        {
          $error = "Variable name cannot be empty.";
          return false;
        }
        if(!ctype_lower($varName))
        {
          $error = "Variable names can only contain lowercase characters.";
          return false;
        }
      }
  }

  if(isset($_POST['filterCol']))
  {
      if(!is_array($_POST['filterCol']) || !is_array($_POST['filterOperator']) || !is_array($_POST['filterValue'])) 
      {
        $error = "Expected an array.";
        return false;
      }

      for ($i = 0; $i < count($_POST['filterCol']); $i++)
      {
        if(!in_array($_POST['filterCol'][$i], $allowedColumns, true))
        {
          $error = "Invalid column name.";
          return false;
        }
        if(!in_array($_POST['filterOperator'][$i], $allowedOperators, true))
        {
          $error = "Invalid operator name.";
          return false;
        }
      }
  }


  return true;
}



$stmt = $db->prepare('SELECT COUNT(*) FROM reports WHERE owner=:owner');
$stmt->bindValue(':owner', $username, SQLITE3_TEXT);
$val = $stmt->execute()->fetchArray()[0];
if ($val >= 5) {
  echo "<h5>Too many reports</h5>";
} else {



  if (isset($_POST['submit']) && validateForm()) {
    
      $stmt = $db->prepare('INSERT into reports (owner,name,template) VALUES (:user, :name, :template)');
      $stmt->bindValue(':user', $username, SQLITE3_TEXT);
      $stmt->bindValue(':name', $_POST['name'], SQLITE3_TEXT);
      $stmt->bindValue(':template', $_POST['content'], SQLITE3_TEXT);
      $res = $stmt->execute();
      $reportId = $db->lastInsertRowID();

      if(isset($_POST['variable']))
      {
          foreach($_POST['variable'] as $varName)
          {
              $stmt = $db->prepare('INSERT into variables (reportid,name) VALUES (:id, :name)');
              $stmt->bindValue(':id', $reportId, SQLITE3_INTEGER);
              $stmt->bindValue(':name', $varName, SQLITE3_TEXT);
              $stmt->execute();
          }
      }

      if(isset($_POST['filterOperator']))
      {
          for ($i = 0; $i < count($_POST['filterOperator']); $i++)
          {
            $stmt = $db->prepare('INSERT into filters (reportid,column,operator,value) VALUES (:id, :column, :operator, :value)');
            $stmt->bindValue(':id', $reportId, SQLITE3_INTEGER);
            $stmt->bindValue(':column', $_POST['filterCol'][$i], SQLITE3_TEXT);
            $stmt->bindValue(':operator', $_POST['filterOperator'][$i], SQLITE3_TEXT);
            $stmt->bindValue(':value', $_POST['filterValue'][$i], SQLITE3_TEXT);
            $stmt->execute();
          
          }
      }

      ?>
      <div class="card">
      <div class="card-body">
        <h5 class="card-title fw-semibold mb-4">Report template created!</h5>
        <a href="report.php" class="btn btn-primary">Back to report templates list</a>
      </div>
    </div>
      <?php
  } else {





?>

    <div class="card">
      <div class="card-body">
        <h5 class="card-title fw-semibold mb-4">New report template</h5>
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
            <span id="variables">
              <label for="variableInput" class="form-label" style="margin-right: 1em;">Variable names</label>
              <span id="variableInput">
                <input type="text" class="form-control" name="variable[]" style="display: inline-block; width: 95%">
                <a href="#" class="btn btn-danger" onclick="trash(this.parentNode)"><i class="ti ti-trash"></i></a>
              </span>
            </span>
            <a href="#" class="btn btn-primary" onclick="addVariable()">+ Add</a>

          </div>
          <div class="mb-3">
            <span id="filters">
              <label for="filter" class="form-label">Policies filter</label>
              <table>
                <thead>
                  <th>Column</th>
                  <th>Operator</th>
                  <th>Value</th>
                </thead>
                <tbody>
                  <tr id="filterInput">
                    <td>
                      <select class="form-select" name="filterCol[]">
                        <?php foreach ($allowedColumns as $opt) { ?>
                          <option><?= $opt ?></option>
                        <?php } ?>
                      </select>
                    </td>
                    <td>
                      <select class="form-select" name="filterOperator[]">
                        <?php foreach ($allowedOperators as $opt) { ?>
                          <option><?= $opt ?></option>
                        <?php } ?>
                      </select>
                    </td>
                    <td>
                      <input type="text" class="form-control" name="filterValue[]" style="min-width: 256px;">
                    </td>
                    <td>
                      <a href="#" class="btn btn-danger" onclick="trash(this.parentNode.parentNode)"><i class="ti ti-trash"></i></a>
                    </td>
                  </tr>
                </tbody>
              </table>
            </span>
            <a href="#" class="btn btn-primary" onclick="addFilter()">+ Add</a>
          </div>
          <div class="mb-3">
            <label for="content" class="form-label">Template content (may contain safe HTML)</label>
            <textarea class="form-control" id="content" rows="8" name="content"></textarea>
          </div>
          <div class="form-text">%variablename% will be replaced with a value provided during generation. Predefined variables are
            %name%, %content% and %priority%.
          </div>
          <button name="submit" type="submit" class="btn btn-primary">Create</button>
        </form>
      </div>
    </div>
    <script>
      let v = document.getElementById("variableInput");
      let p = v.parentNode;
      p.removeChild(v);

      let vv = document.getElementById("filterInput");
      let pp = vv.parentNode;
      pp.removeChild(vv);


      function addVariable() {
        let v2 = v.cloneNode(true);
        p.appendChild(v2);
      }

      function addFilter() {
        let vv2 = vv.cloneNode(true);
        pp.appendChild(vv2);
      }

      function trash(ele) {
        ele.parentNode.removeChild(ele);
      }
    </script>

<?php
  }
}
?>