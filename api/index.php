<?php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json; charset=UTF-8');

// api endpoint variable names
const API_KEY = 'key';
const API_LIST = 'view-all';
const API_CREATE = 'create';
const API_UPDATE = 'update';
const API_DELETE = 'delete';

// api endpoint modules
const API_FIGHT = 'fight';
const API_ATHLETE = 'athlete';
const API_EVENT = 'event';

// provides database connection
include_once '../lib/Database.php';
include_once '../models/APIAccess.php';
include_once '../models/User.php';

$db = (new Database())->getConnection();
//$_GET['key'] = 'test123';
//$_GET['fight'] = 141;
//$_POST['fight'] = 1;
//$_GET['athlete'] = 141;
$data = [];


if (!isset($db)) {
    die('No database connection');
}

if (!isset($_GET['key'])) {
    die('API key missing');
}

$key = $_GET[API_KEY];
$valid_key = (new APIAccess($db))->verifyKey($key);

if (empty($valid_key) || !$valid_key) {
    die('Invalid API key');
}

$user = new User($db);

if (empty($user)) {
    die('Could not identify user');
}

// return athlete data
if (isset($_GET[API_FIGHT])) {
    $fight = $_GET[API_FIGHT];

    if (empty($fight)) {
        if (isset($_GET[API_CREATE])) {
            // create
            include_once 'fight/create.php';
        } else if (isset($_GET[API_LIST])) {
            // view all
            echo 'view all';
        }
    } else if (is_numeric($fight)) {
        if (isset($_GET[API_UPDATE])) {
            // update
            echo 'update';
        } else if (isset($_GET[API_DELETE])) {
            // delete
            echo 'delete';
        } else {
            // read
            echo $fight;
            include_once 'fight/detail.php';
        }
    } else {
        die('Unknown');
    }

}

echo "\n";
echo json_encode(['status' => $status, 'type' => $type, 'data' => $data]);

// EOF