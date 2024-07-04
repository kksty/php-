<?php

$url = $_POST['url'];

$myfile = fopen("xss_info.txt", "w") or die("Unable to open file!");
$txt = "url=" . $url . "\n";
fwrite($myfile, $txt);
fclose($myfile);
