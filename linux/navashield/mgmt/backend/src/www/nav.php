<?php

if(!isset($running))
{
    die("nope");
}

$nav[] = ["name" => "Dashboard", "icon" => "ti-home", "link" => "/"];
$nav[] = ["name" => "Scan history", "icon" => "ti-bug", "link" => "/history.php"];
$nav[] = ["name" => "IOCs", "icon" => "ti-virus-search", "link" => "/ioc.php"];
$nav[] = ["name" => "Policies", "icon" => "ti-notebook", "link" => "/policy.php"];
$nav[] = ["name" => "Reports", "icon" => "ti-report-analytics", "link" => "/report.php"];