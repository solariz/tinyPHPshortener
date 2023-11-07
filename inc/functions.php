<?php
if (__FILE__ === $_SERVER['SCRIPT_FILENAME']) {
    exit('Direct access not allowed');
}

function StorageInit($StoragePath) {
    if (!is_dir($StoragePath)) {
        // Create the storage directory with write permissions
        $success = mkdir($StoragePath, 0777, true);
        if (!$success) {
            // Handle the case where directory creation failed
            header("HTTP/1.1 500 Internal Server Error");
            echo 'Failed to create the storage directory.';
            //echo $StoragePath;
            exit;
        }
    }
    if (!is_dir($StoragePath."/url")) mkdir($StoragePath."/url", 0777, true);
    if (!is_dir($StoragePath."/short")) mkdir($StoragePath."/short", 0777, true);
}

function StorageGetSub($StoragePath, $sub, $string) {
    $subdir = rtrim($StoragePath,"/") . "/" . $sub . "/" . substr($string, 0, 1);
    return $subdir."/";
}

function GetFullLinkArray($short) {
    GLOBAL $StoragePath, $MaxValidityHours;
    $FileDir  = StorageGetSub($StoragePath,"short",$short);
    $FileName = $FileDir.$short.".json";
    $data = json_decode(file_get_contents($FileName),true);
    if ($data !== null && !empty($data['uh'])) {
        $uh = $data['uh'];
        // uh = URL Hash, getting it from url storage
        $FileDir  = StorageGetSub($StoragePath,"url",$uh);
        $FileName = $FileDir.$uh.".json";
        if (file_exists($FileName)) {
            $data = json_decode(file_get_contents($FileName),true);
            if ($data !== null) {
                // Check if expired
                // DATA Example: {"ul":"https:\/\/wuwuwu.com","time":1699307486,"us":"Jop8vro9","vh":24}
                // Get Validity
                if(empty($data['vh'])) {
                    // Set to maximum validity
                    $data['vh'] = $MaxValidityHours; // 1 Year
                }
                $ValidSeconds = $data['vh'] * 3600;
                if (time() - $data['time'] < $ValidSeconds) {
                    // add hash to data
                    $data['uh'] = $uh;
                    return $data;
                }
            }
        }
    }
    // Something wrong. Invalid or parts doesnt exist so we delete it:
    DeleteFromStorage($short,$uh);
    return false;
}

function DeleteFromStorage($short = false, $hash = false) {
    GLOBAL $StoragePath;
    $short = CheckStrType("short",$short);
    $hash  = CheckStrType("hash",$hash);
    if(!empty($short)) {
        // Cleaning up by short
        $FileDir  = StorageGetSub($StoragePath,"short",$short);
        $FileName = $FileDir.$short.".json";
        if (file_exists($FileName)) unlink($FileName);
        logMessage("Unlink short: ".$short);
    }
    if(!empty($hash)) {
        // Cleaning up by hash
        $FileDir  = StorageGetSub($StoragePath,"url",$hash);
        $FileName = $FileDir.$hash.".json";
        if (file_exists($FileName)) unlink($FileName);
        logMessage("Unlink hash: ".$hash);
    }
}

function DoRedirect($redirectUrl) {
    $redirectUrl = CheckStrType("url",$redirectUrl);
    if(empty($redirectUrl)) {
        logMessage("Warning: Empty redirectUrl in DoRedirect() call!");
        return false;
    }
    header("Referrer-Policy: no-referrer");
    header("Location: ".$redirectUrl, true, 302);
    include('pages/095-redir.php');
    flush();
    exit;
}

function StatRecord($hash) {
    $hash = CheckStrType("hash",$hash);
    if(empty($hash)) {
        logMessage("Warning: Empty hash in StatRecord() call!");
        return false;
    }
    global $StoragePath;
    $FileDir  = StorageGetSub($StoragePath,"stats",$hash);
    if (!is_dir($FileDir)) mkdir($FileDir, 0777, true);
    $FileName = $FileDir.$hash.".json";
    if (file_exists($FileName)) {
        $statsArray = json_decode(file_get_contents($FileName),true);
    } else {
        $statsArray = array();
    }
    // increase counter for today
    $today = date("Y-m-d");
    if (array_key_exists($today, $statsArray)) {
        $statsArray[$today] += 1;
    } else {
        $statsArray[$today] = 1;
    }
    // write back to file
    $updatedStatsData = json_encode($statsArray);
    return file_put_contents($FileName, $updatedStatsData);
}

/**
 * Checks the type of a given string and validates it based on the specified type.
 *
 * @param string $type The type to check against (url, hash, short).
 * @param string $str The string to be validated.
 * @return string|false The validated string if it matches the specified type, false otherwise.
 */
function CheckStrType($type, $str) {
    $str = trim($str);
    if ($type === "url") {
        if (filter_var($str, FILTER_VALIDATE_URL)) {
            return $str;
        } else {
            return false;
        }
    } else if ($type === "hash") {
        if (preg_match('/^[a-z0-9]{16}$/', $str)) {
            return $str;
        } else {
            return false;
        }
    } else if ($type === "short") {
        if (preg_match('/^[a-zA-Z0-9\-_.:,]{8}$/', $str)) {
            return $str;
        } else {
            return false;
        }
    }
}

function GetShortLink($url, $HoursValid = 720) {
    GLOBAL $StoragePath;
    // Data explained:
    // us  = URL Short  (Just the 6 char short code)
    // ul  = URL Long   (Full original URL)
    // uh  = hash of the URL
    // vh   = valid in hours, $HoursValid
    // time = Unix timestamp when the shortlink was created

    // first check if url already exists in storage
    $url_hash = GetUrlHash($url);
    $FileDir  = StorageGetSub($StoragePath,"url",$url_hash);
    $FileName = $FileDir.$url_hash.".json";
    if (file_exists($FileName)) {
        // file exists check if short file exists
        $data = json_decode(file_get_contents($FileName),true);
        $short = $data['us'];
        $ShortDir = StorageGetSub($StoragePath,"short",$short);
        $ShortFile = $ShortDir.$short.".json";
        if (file_exists($ShortFile)) {
            // Check if $ShortFile contains valid JSON
            $jsonData = json_decode(file_get_contents($ShortFile), true);
            if ($jsonData !== null) {
                // Check if the 'uh' property is not empty
                if (!empty($jsonData['uh'])) {
                    // Update $FileName json content time to current time()
                    $data['time'] = time();
                    file_put_contents($FileName, json_encode($data));
                    return $short;
                }
            }
        }
        // If the file doesn't exist, or the JSON is invalid or 'uh' property is empty, unlink the file
        unlink($FileName);
        unset($short,$ShortDir,$ShortFile,$data);
    }
    if (!file_exists($FileName)) {
        // file does not exist, generate short url
        $short = generateCustomShort($url);
        $data = array('ul' => $url, 'time' => time(), 'us' => $short, 'vh' => $HoursValid);
        if (!is_dir($FileDir)) mkdir($FileDir, 0777, true);
        if(file_put_contents($FileName, json_encode($data)) === false) {
            // Handle file write error
            header("HTTP/1.1 500 Internal Server Error");
            echo 'Failed to write file! ';
            exit;
        }
        // Save the short url
        $ShortDir = StorageGetSub($StoragePath,"short",$short);
        $ShortFile = $ShortDir.$short.".json";
        if (!is_dir($ShortDir)) mkdir($ShortDir, 0777, true);
        file_put_contents($ShortFile, json_encode(array('uh' => $url_hash)));
    }

    return $short;
}

function GetUrlHash($url) {
    $hash = hash('crc32', $url) . hash('sha256', $url);
    $medHash = substr($hash, 0, 16);
    return $medHash;
}

function generateCustomShort($input) {
    $baseUrlChars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789-_.:,';
    $numberOfCharsToSelect = 8;
    $length = strlen($baseUrlChars);
    $shortUrl = '';
    for ($i = 0; $i < $numberOfCharsToSelect; $i++) {
        $randomNumber = random_int(0, $length - 1);
        $char = substr($baseUrlChars, $randomNumber, 1);
        // Ensure the first and last character are not one of '-_.:+'
        while (($i === 0 || $i === $numberOfCharsToSelect - 1) && strpos('-_.:@!$=*+,', $char) !== false) {
            $randomNumber = random_int(0, $length - 1);
            $char = substr($baseUrlChars, $randomNumber, 1);
        }
        $shortUrl .= $char;
    }
    return $shortUrl;
}


function logMessage($message) {
    global $LogFile;
    if(empty($LogFile)) return false;
    $timestamp = date("Y-m-d H:i:s");
    $logLine = "[$timestamp] $message" . PHP_EOL;
    return file_put_contents($LogFile, $logLine, FILE_APPEND);
}
