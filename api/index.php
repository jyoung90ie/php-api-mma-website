<?php
namespace api;

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json; charset=UTF-8');
header("Access-Control-Allow-Methods: GET,POST,PUT,DELETE");

include_once '../autoload.php';

//use helpers\Database;
use models\{APIAccess, Athlete, Event, Fight, User};
use controllers\Controller;
use \helpers\Database;

// api endpoint modules
const FIGHT_MODULE = 'fight';
const ATHLETE_MODULE = 'athlete';
const EVENT_MODULE = 'event';


$db = (new Database())->getConnection();
$url_path = explode('/', $_SERVER['REQUEST_URI']);
$requestMethod = $_SERVER['REQUEST_METHOD'];
$api_path_prefix = 'api';
// get position of /api in the url path
$api_pos = array_search($api_path_prefix, $url_path);

$api_key = $url_path[$api_pos + 1];
$api_module = $url_path[$api_pos + 2];
$id = $url_path[$api_pos + 3];
$id = (!empty($id) ? intval($id) : null);

////////////////////////////////////////////////////////
///         manual overrides
////////////////////////////////////////////////////////
///
//$api_key = 'test123';
//$api_module = 'event';
//$requestMethod = 'DELETE';
//$id = 505;

////////////////////////////////////////////////////////

$api_access = new APIAccess($db);

if (!$api_access->verifyKey($api_key)) {
    header(Controller::HTTP_UNAUTHORIZED);
    exit();
}

// get user
$user = new User($db);
if (!$user->getByUserId($api_access->getUserId())) {
    header(CONTROLLER::HTTP_NOT_FOUND);
    echo json_encode(['error' => 'User could not be found']);
    exit();
}

switch ($api_module) {
    case FIGHT_MODULE:
        $fight = new Controller(new Fight($db), $user, $requestMethod, $id);
        $fight->process_request();
        break;
    case EVENT_MODULE:
        $event = new Controller(new Event($db), $user, $requestMethod, $id);
        $event->process_request();
        break;
    case ATHLETE_MODULE:
        $athlete = new Controller(new Athlete($db), $user, $requestMethod, $id);
        $athlete->process_request();
        break;
    default:
        header(Controller::HTTP_NOT_FOUND);
        exit();
}
/*
echo 'api_key: ' . $api_key . "\n";
echo 'module: ' . $api_module . "\n";
echo 'id: ' . $id . "\n";
*/
// EOF