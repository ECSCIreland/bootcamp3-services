<?php

$running = 1;

include_once("auth.php");
include_once("db.php");

if (!isset($_GET['id'])) {
  die("nope");
}

$stmt = $db->prepare('SELECT * FROM captures WHERE id=:id');
$stmt->bindValue(':id', $_GET['id'], SQLITE3_INTEGER);
$result = $stmt->execute();
$capture = $result->fetchArray();
if(!$capture)
{
  die('nope');
}


$stmt = $db->prepare('SELECT COUNT(*) FROM iocs WHERE owner=:owner AND id=:id');
$stmt->bindValue(':id', $capture['iocid'], SQLITE3_INTEGER);
$stmt->bindValue(':owner', $username, SQLITE3_TEXT);
$result = $stmt->execute();
$val = $stmt->execute()->fetchArray()[0];

if ($val <= 0) {
  die("nope");
}

$stmt = $db->prepare('SELECT filename FROM capturedfiles WHERE id=:id');
$stmt->bindValue(':id', $capture['fileid'], SQLITE3_INTEGER);
$result = $stmt->execute();
$val = $stmt->execute()->fetchArray()[0];

$fn = "../ioc/" . $val;
$fp = fopen($fn, 'rb');
header("Content-Length: " . filesize($fn));

fpassthru($fp);