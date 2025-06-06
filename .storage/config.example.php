<?php
if (__FILE__ === $_SERVER['SCRIPT_FILENAME']) {
    exit('Direct access not allowed');
}

// Whitelist of IPs which do not need to auth
$ipAddresses = array(
    '10.10.21.71',  // whitelist IP
    '10.10.21.149', // 2nd whitelist IP
    '1234:1234:1234:' // IPv6 example
);
// If the IP is not in the whitelist user need to authenticate
// Set $AUTH = false; to disable authentication
$AUTH['USER'] = "login";
$AUTH['PASS'] = "password";  // TODO only store hashed PWD here

// All invalid calls like empty short url will be redirected to this URL
// this doesnt include valid shortcodes which been expired or not existing
// they will display a error message instead.
$RedirectInvalid = 'https://google.com';


$MaxValidityHours = 8640; // if nothing is set when a link is created this will be Valid X hours, default 1 year

$LogFile = $StoragePath."shorturl.log";

$FOOTER = "by tinyPHPshortener";