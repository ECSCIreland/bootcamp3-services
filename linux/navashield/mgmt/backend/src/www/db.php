<?php

if(!isset($running))
{
    die("nope");
}

$db = new SQLite3('../db.sqlite3');
$db->busyTimeout(60000);
$db->exec('PRAGMA journal_mode = wal; PRAGMA synchronous = normal; PRAGMA analysis_limit=400; PRAGMA optimize;');


if($db->querySingle("SELECT COUNT(*) FROM sqlite_master WHERE name='policies'") == 0)
{
    //Initialization
    $db->exec("CREATE TABLE policies(
        id INTEGER PRIMARY KEY,
        owner TEXT,
        name TEXT,
        priority INTEGER
    )");

    $db->exec("CREATE TABLE reports(
        id INTEGER PRIMARY KEY,
        owner TEXT,
        name TEXT,
        template TEXT
    )");

    $db->exec("CREATE TABLE variables(
        id INTEGER PRIMARY KEY,
        reportid INTEGER,
        name TEXT,
        FOREIGN KEY(reportid) REFERENCES reports(id)
    )");

    $db->exec("CREATE TABLE filters(
        id INTEGER PRIMARY KEY,
        reportid INTEGER,
        column TEXT,
        operator TEXT,
        value TEXT,
        FOREIGN KEY(reportid) REFERENCES reports(id)
    )");

    $db->exec("CREATE TABLE iocs(
        id INTEGER PRIMARY KEY,
        owner TEXT,
        name TEXT,
        hash TEXT
    )");

    $db->exec("CREATE TABLE history(
        id INTEGER PRIMARY KEY,
        threadid TEXT,
        hash TEXT
    )");


    $db->exec("CREATE TABLE capturedfiles(
        id INTEGER PRIMARY KEY,
        threadid TEXT,
        filename TEXT
    )");

    $db->exec("CREATE TABLE captures(
        id INTEGER PRIMARY KEY,
        iocid INTEGER,
        fileid INTEGER,
        FOREIGN KEY(fileid) REFERENCES capturedfiles(id),
        FOREIGN KEY(iocid) REFERENCES iocs(id)
    )");
}