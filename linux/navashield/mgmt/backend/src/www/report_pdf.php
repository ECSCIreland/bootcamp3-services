<?php

$running = 1;

include_once("auth.php");
include_once("db.php");
require_once('HTMLPurifier.standalone.php');



if (!isset($_GET['id'])) {
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



$stmt = $db->prepare('SELECT * FROM filters WHERE reportid=:id');
$stmt->bindValue(':id', $report['id'], SQLITE3_INTEGER);
$result = $stmt->execute();
while ($val = $result->fetchArray()) {
  $filters[] = $val;
}


$sql = "SELECT * from policies WHERE owner=:owner";


if(isset($filters))
{
  foreach($filters as $i=>$filter)
  {
    $sql .= " AND " . $filter['column'] . ' ' . $filter['operator'] . ' :' . $i;
  }
}

$stmt = $db->prepare($sql);
$stmt->bindValue(':owner', $username, SQLITE3_TEXT);

if(isset($filters))
{
  foreach($filters as $i=>$filter)
  {
    if($filter['column'] === 'priority')
    {
      $stmt->bindValue(':'.$i, $filter['value'], SQLITE3_INTEGER);
    } else {
      $stmt->bindValue(':'.$i, $filter['value'], SQLITE3_TEXT);
    }
    
  }
}

$html = "";


$result = $stmt->execute();
$counter = 0;
while($policy = $result->fetchArray())
{
  if($counter > 0)
  {
    $html .= '<pagebreak>';
  }
  if($counter >= 5)
  {
    die("Too many policies matched the filter.");
  }
  $content = file_get_contents("../policies/" . intval($policy['id']) . ".txt");
  $template = $report['template'];
  $config = HTMLPurifier_Config::createDefault();
  $purifier = new HTMLPurifier($config);
  $template = $purifier->purify($template);


  if(isset($variables))
  {
    foreach($variables as $var)
    {
      $template = str_replace("%" . $var . "%", $_POST[$var], $template); 
    }
  }
  $template = str_replace("%name%", $policy['name'], $template); 
  $template = str_replace("%content%", $content, $template); 

  $html .= $template;
  $html .= "<annotation content=\"Original policy #$counter\" file=\"../policies/" . $policy['id'] . ".txt\">";

  $counter++;
  
}

require_once __DIR__ . '/vendor/autoload.php';

$mpdf = new \Mpdf\Mpdf(['allowAnnotationFiles'=>true]);
$mpdf->WriteHTML($html);
$mpdf->Output();