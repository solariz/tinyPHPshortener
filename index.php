<?php
// disable browser caching this file
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');
header("X-Robots-Tag: noindex, nofollow");

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

$ALLOWED = false;
$clientIP = $_SERVER['REMOTE_ADDR'];
if (filter_var($clientIP, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
    // IPv6 address
    foreach ($ipAddresses as $allowedIP) {
        if (strpos($clientIP, $allowedIP) === 0) {
            $ALLOWED = true;
            break;
        }
    }
} else {
    // IPv4 address
    if (in_array($clientIP, $ipAddresses)) {
        $ALLOWED = true;
    }
}

if ($ALLOWED !== true) {
    // Client IP is not in the allowed list
    // Initialize basic auth request
    // Check if the user is already authenticated
    if (!isset($_SERVER['PHP_AUTH_USER'])) {
        // Send authentication headers
        header('WWW-Authenticate: Basic realm="Restricted Area"');
        header('HTTP/1.0 401 Unauthorized');
        include('pages/001-head.php');
        include('pages/050-noauth.php');
        include('pages/090-foot.php');
        exit;
    } else {
        // Verify the username and password
        $username = $_SERVER['PHP_AUTH_USER'];
        $password = $_SERVER['PHP_AUTH_PW'];

        // Perform your authentication logic here
        if ($username === $AUTH['USER'] && $password === $AUTH['PASS'] && !empty($username) && !empty($password)) {
            // User is authenticated
            $ALLOWED = true;
            logMessage("Login via basic auth from: ".$clientIP);
        } else {
            // Invalid username or password
            header('WWW-Authenticate: Basic realm="Restricted Area"');
            header('HTTP/1.0 401 Unauthorized');
            echo 'Invalid username or password';
            logMessage("Invalid basic auth login from: ".$clientIP);
            exit;
        }
    }
}
require('inc/functions.php');
include('pages/001-head.php');
if($ALLOWED === true) {
    $url = trim(urldecode($_POST['url']));
    $HoursValid = !empty($_POST['HoursValid']) ? intval($_POST['HoursValid']) : 720;
    // Only accept between 1h to 8640h (1yr)
    if ($HoursValid < 1 || $HoursValid > 8640) {
        header("HTTP/1.1 400 Bad Request");
        exit("Invalid date validity");
    }
    if (filter_var($url, FILTER_VALIDATE_URL)) {
        // Valid URL
        // Checking that the URL is not pointing to ourself
        $currentDomain = $_SERVER['HTTP_HOST'];
        if (strpos($url, $currentDomain) !== false) {
            // $url contains the current called web server domain
            echo "ERROR The URL cannot contain the current domain.";
        } else {
            StorageInit($StoragePath);
            $link = GetShortLink($url, $HoursValid);
            if(!empty($link)) {
                $ShortURL = "https://".$_SERVER['HTTP_HOST']."/".$link;
                $expireTimestamp = time() + ($HoursValid * 3600);
                $ExpireDate = date("d. M. Y H:i", $expireTimestamp);
                include('pages/030-success.php');
                logMessage($clientIP." shortened [".$url."] to [".$ShortURL."] valid ".$HoursValid."h");
            }
        }
    } else {
        include('pages/020-form.php');
    }



} else {
    include('pages/050-noauth.php');
}
include('pages/090-foot.php');
