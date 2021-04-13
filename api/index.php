<?php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json; charset=UTF-8');

// provides database connection
include_once '../config/Database.php';

$mysql = new Database();
$db = $mysql->getConnection();

$_GET['token'] = 'test123';
$_GET['fight'] = 1;
$data = [];


if (isset($db)) {
    if (isset($_GET['token'])) {

        $token = $db->real_escape_string($_GET['token']);

        // check the token is valid
        $event_query = $db->query("SELECT * FROM ApiAccess WHERE ApiKey='$token'");

        if ($event_query->num_rows > 0) {
            $status = 'successful';
            $type = 'connected with valid token';

            // return athlete data
            if (isset($_GET['athlete'])) {
                include_once 'athlete.php';
            }

            // return event data including fights
            if (isset($_GET['event'])) {
                include_once 'event.php';
            }

            // return fight data
            if (isset($_GET['fight'])) {
                include_once 'fight.php';
            }

        } else {
            $status = 'error';
            $type = 'Invalid token';
        }
    } else {
        $status = 'error';
        $type = 'Missing token';
    }
} else {
    $status = 'error';
    $type = 'DB connection problem';
}
echo "\n";
echo json_encode(['status' => $status, 'type' => $type, 'data' => $data]);

// EOF