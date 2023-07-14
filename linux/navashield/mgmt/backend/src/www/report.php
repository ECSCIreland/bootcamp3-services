<?php

$running = 1;

require_once("auth.php");
require_once("nav.php");
require_once("db.php");

require_once("header.php");

if(!isset($_GET['cmd']) or $_GET['cmd'] == 'list')
{
    require_once("report_list.php");
} else if ($_GET['cmd'] == 'view')
{
    require_once("report_view.php");
} else if ($_GET['cmd'] == 'new')
{
    require_once("report_new.php");
} else if ($_GET['cmd'] == 'generate')
{
    require_once("report_generate.php");
}


require_once("footer.php");