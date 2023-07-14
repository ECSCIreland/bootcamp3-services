<?php

if(!isset($running))
{
    die("nope");
}

if (!isset($_SERVER['HTTP_REMOTE_USER']) )
{
    header("Location: /login/");
    exit;
}

$username = $_SERVER['HTTP_REMOTE_USER'];