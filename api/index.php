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
use controllers\AuthController;
use controllers\CRUDController;
use \helpers\Database;

// api endpoint modules
const FIGHT_MODULE = 'fight';
const ATHLETE_MODULE = 'athlete';
const EVENT_MODULE = 'event';
const USER_MODULE = 'user';
const AUTHENTICATION_MODULE = 'auth';

$db = (new Database())->getConnection();

$urlPath = explode('/', parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
$urlQueryStrings = parse_url($_SERVER['REQUEST_URI'], PHP_URL_QUERY);
// create array from urlQueryStrings
parse_str($urlQueryStrings, $queryStrings);

$requestMethod = $_SERVER['REQUEST_METHOD'];
$authPathPrefix = 'api';
// get position of /api in the url path
$api_pos = array_search($authPathPrefix, $urlPath);


$apiRequest = $urlPath[$api_pos + 1];

if ($apiRequest != AUTHENTICATION_MODULE) {
    // treat as normal api request - verify api key
    $apiKey = $urlPath[$api_pos + 1];
    $apiModule = $urlPath[$api_pos + 2];
    $id = $urlPath[$api_pos + 3];
    $id = (!empty($id) ? intval($id) : null);

    ////////////////////////////////////////////////////////
    ///         manual overrides
    ////////////////////////////////////////////////////////
    ///
    //$api_key = 'test123';
    //$api_module = 'user';
    //$requestMethod = 'PUT';
    //$id = 94;

    ////////////////////////////////////////////////////////

    $apiAccess = new APIAccess($db);

    if (!$apiAccess->verifyKey($apiKey)) {
        header(CRUDController::HTTP_UNAUTHORIZED);
        exit();
    }

    // get user
    $user = new User($db);

    if (!$user->getOne($apiAccess->getUserId())) {
        header(CRUDController::HTTP_NOT_FOUND);
        echo json_encode(['error' => 'User could not be found']);
        exit();
    }
    // get user's access rights
    $user->fetchPermissions();
} else {
    // treat as authentication request - no apikey required
    $apiModule = $apiRequest;
}



try {
    switch ($apiModule) {
        case FIGHT_MODULE:
            $fightRequest = new CRUDController(new Fight($db), $user, $requestMethod, $id, $queryStrings);
            $fightRequest->process_request();
            break;
        case EVENT_MODULE:
            $eventRequest = new CRUDController(new Event($db), $user, $requestMethod, $id, $queryStrings);
            $eventRequest->process_request();
            break;
        case ATHLETE_MODULE:
            $athleteRequest = new CRUDController(new Athlete($db), $user, $requestMethod, $id, $queryStrings);
            $athleteRequest->process_request();
            break;
        case USER_MODULE:
            $userRequest = new CRUDController(new User($db), $user, $requestMethod, $id, $queryStrings);
            $userRequest->process_request();
            break;
        case AUTHENTICATION_MODULE:
            $userRequest = new AuthController(new User($db), $requestMethod);
            $userRequest->process_request();
            break;
        default:
            header(CRUDController::HTTP_NOT_FOUND);
            exit();
    }
} catch (\Exception $exception) {
    exit($exception->getMessage());
}

// EOF