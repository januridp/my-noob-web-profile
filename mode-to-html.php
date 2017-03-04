<?php
    define("DATE_FORMAT","d-m-Y H:i:s");
    define("LOG_FILE","visitors.html");

    $logfileHeader='
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "DTD/xhtml1-transitional.dtd">
<html>
<head>
   <title>Visitors log</title>
   <link href="style/style.css" rel="stylesheet" type="text/css" />
</head>
<body>
  <table cellpadding="0" cellspacing="1">
    <tr><th>DATE</th><th>IP</th><th>HOSTNAME</th><th>BROWSER</th><th>URI</th><th>REFERRER</th></tr>'."\n";

    $userAgent = (isset($_SERVER['HTTP_USER_AGENT']) && ($_SERVER['HTTP_USER_AGENT'] != "")) ? $_SERVER['HTTP_USER_AGENT'] : "Unknown";
    $userIp    = (isset($_SERVER['REMOTE_ADDR'])     && ($_SERVER['REMOTE_ADDR'] != ""))     ? $_SERVER['REMOTE_ADDR']     : "Unknown";
    $refferer  = (isset($_SERVER['HTTP_REFERER'])    && ($_SERVER['HTTP_REFERER'] != ""))    ? $_SERVER['HTTP_REFERER']    : "Unknown";
    $uri       = (isset($_SERVER['REQUEST_URI'])     && ($_SERVER['REQUEST_URI'] != ""))     ? $_SERVER['REQUEST_URI']     : "Unknown";

    $hostName   = gethostbyaddr($userIp);
    $actualTime = date(DATE_FORMAT);

    $logEntry = "     <tr><td>$actualTime</td><td>$userIp</td><td>$hostName</td><td>$userAgent</td><td>$uri</td><td>$refferer</td></tr>\n";

    if (!file_exists(LOG_FILE)) {
        $logFile = fopen(LOG_FILE,"w");
        fwrite($logFile, $logfileHeader);
    }
    else {
        $logFile = fopen(LOG_FILE,"a");
    }

    fwrite($logFile,$logEntry);
    fclose($logFile);
?>