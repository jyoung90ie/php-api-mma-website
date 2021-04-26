<?php

namespace helpers;

$defaultApiKey = 'cbb82851ccacb0e895ede1ea7730d7e2'; // should be a user with roleID = 4

if (stripos($_SERVER['HTTP_HOST'], 'jyoung22') !== false) {
    $dbHost = 'jyoung22.lampt.eeecs.qub.ac.uk';
    $dbUser = 'jyoung22';
    $dbPass = 'r5hk97fKmQNJQKNh';
    $dbName = 'jyoung22';
    $dbPort = 3306;
    $baseUrl = 'http://jyoung22.lampt.eeecs.qub.ac.uk/promma';
    $apiEndPoint = '/api';
} else {
    $dbHost = 'localhost';
    $dbUser = 'root';
    $dbPass = 'root';
    $dbName = 'promma';
    $dbPort = 8889;
    $baseUrl = 'http://localhost:8888/promma';
    $apiEndPoint = '/api';
}
$apiUrl = rtrim($baseUrl, '/ ') . '/' . trim($apiEndPoint, '/ ');

define('DB_HOST', $dbHost);
define('DB_USER', $dbUser);
define('DB_PASS', $dbPass);
define('DB_NAME', $dbName);
define('DB_PORT', $dbPort);
define('BASE_URL', $baseUrl);
define('API_URL', $apiUrl);
define('DEFAULT_API_KEY', $defaultApiKey);

// setting to true will display all errors
define('DEBUG', true);

if (DEBUG) {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
}