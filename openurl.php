<?php
/*******
 * This file is the target for the webserver rewrite rule
 * to open shortened urls. It is required to pass the short
 * url in the rewrite as get parameter "su", e.g. ?su=1337ff
 */

// disable browser caching
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');
header("X-Robots-Tag: noindex, follow");

// First redirect to HTTPS
if (!isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] !== 'on') {
    // Redirect to SSL
    header('Location: https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
    exit;
} else {
    // Set HSTS header
    header("Strict-Transport-Security: max-age=31536000");
}
$StoragePath = __DIR__.'/.storage/';
// Load Config
// Check if the file exists and is readable
$configFile = $StoragePath.'/config.php';
if (!file_exists($configFile) || !is_readable($configFile)) {
    header("HTTP/1.1 500 Internal Server Error");
    echo "<h1>Config file does not exist or is not readable</h1>";
    throw new Exception("Config file does not exist or is not readable");
    exit;
} else {
    require_once($configFile);
}

require('inc/functions.php');
$shortUrl = trim(urldecode(ltrim($_GET['su'], "/")));

// If the shorturl is empty or to short or to long, like someone is manually
// fabricating url to try out or call the short URL Alias without any query string
// it will be redirected.
if (empty($shortUrl) || strlen($shortUrl) < 4 || strlen($shortUrl) > 10) {
    header("Location: $RedirectInvalid", true, 302);
    exit;
}

// Check if the Short URL Exists
$URLArray = GetFullLinkArray($shortUrl);
if (!empty($URLArray) && is_array($URLArray) && !empty($URLArray['ul'])) {
    // Redirect to the original URL
    $TargetURL = CheckStrType("url",$URLArray['ul']);
    if(!empty($TargetURL)) {
        StatRecord($URLArray['uh']);
        logMessage("Redirect call for: ".$shortUrl);
        DoRedirect($URLArray['ul']);
        exit;
    }
}
// If we come this far we have an error.
include('pages/001-head.php');
include('pages/051-shorterror.php');
include('pages/090-foot.php');
exit;
