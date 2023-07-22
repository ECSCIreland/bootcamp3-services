<?php
$running = 1;

require_once("../www/db.php");

if(!isset($_GET['hash']) || !isset($_GET['tid']))
{
    die('nope');
}


if($_SERVER['REQUEST_METHOD'] == 'GET')
{
    $stmt = $db->prepare('INSERT INTO history (threadid,hash) VALUES (:threadid,:hash)');
    $stmt->bindValue(':threadid', $_GET['tid'], SQLITE3_TEXT);
    $stmt->bindValue(':hash', $_GET['hash'], SQLITE3_TEXT);
    $result = $stmt->execute();

    $stmt = $db->prepare('SELECT COUNT(*) FROM iocs WHERE hash=:hash');
    $stmt->bindValue(':hash', $_GET['hash'], SQLITE3_TEXT);

    $val = $stmt->execute()->fetchArray()[0];
    if($val > 0)
    {
        http_response_code(200);
    } else 
    {
        http_response_code(404);
    }
} else if($_SERVER['REQUEST_METHOD'] == 'POST')
{
    $filename = bin2hex(random_bytes(16));
    file_put_contents('../ioc/' . $filename, file_get_contents('php://input'));

    $stmt = $db->prepare('INSERT INTO capturedfiles (threadid,filename) VALUES (:threadid,:filename)');
    $stmt->bindValue(':filename', $filename, SQLITE3_TEXT);
    $stmt->bindValue(':threadid', $_GET['tid'], SQLITE3_TEXT);
    $stmt->execute();

    $fileID = $db->lastInsertRowID();

    $stmt = $db->prepare('SELECT * FROM iocs WHERE hash=:hash');
    $stmt->bindValue(':hash', $_GET['hash'], SQLITE3_TEXT);

    $result = $stmt->execute();
    while ($row = $result->fetchArray())
    {
        $iocID = $row['id'];
        # $db->exec("INSERT INTO captures (iocid,fileid) VALUES ($iocID, $fileID)"); # vuln to sqli
        # solution according to chatgpt
        $stmt = $db->prepare("INSERT INTO captures (iocid, fileid) VALUES (?, ?)");
        $stmt->execute([$iocID, $fileID]);
    
    }

} else {
    die('nope');
}