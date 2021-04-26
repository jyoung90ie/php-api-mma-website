<?php

namespace helpers;

if (stripos($_SERVER['HTTP_HOST'], 'jyoung22') !== false) {
    $dbHost = 'jyoung22.lampt.eeecs.qub.ac.uk';
    $dbUser = 'jyoung22';
    $dbPass = 'r5hk97fKmQNJQKNh';
    $dbName = 'jyoung22';
    $dbPort = 3306;
    $apiUrl = 'http://jyoung22.lampt.eeecs.qub.ac.uk/promma/api';
} else {
    $dbHost = 'localhost';
    $dbUser = 'root';
    $dbPass = 'root';
    $dbName = 'promma';
    $dbPort = 8889;
    $apiUrl = 'http://localhost:8888/promma/api';
}

define('DB_HOST', $dbHost);
define('DB_USER', $dbUser);
define('DB_PASS', $dbPass);
define('DB_NAME', $dbName);
define('DB_PORT', $dbPort);
define('API_URL', rtrim($apiUrl, '/ '));

// setting to true will display all errors
define('DEBUG', true);

if (DEBUG) {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
}