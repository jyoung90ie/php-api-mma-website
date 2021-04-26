<?php

namespace api;

include_once '../autoload.php';
include_once '../helpers/config.php';

header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json; charset=UTF-8');
header("Access-Control-Allow-Methods: GET,POST,PUT,DELETE");


use models\{APIAccess, Athlete, Event, Fight, User};
use controllers\AuthController;
use controllers\CRUDController;
use Exception;
use helpers\Database;

// api endpoint modules
const FIGHT_MODULE = 'fight';
const ATHLETE_MODULE = 'athlete';
const EVENT_MODULE = 'event';
const USER_MODULE = 'user';
const AUTHENTICATION_MODULE = 'auth';

$db = (new Database())->getConnection();

// remove trailing / and any query strings, then break up uri into associate array
$urlPath = explode('/', parse_url(rtrim($_SERVER['REQUEST_URI'], '/'), PHP_URL_PATH));
$urlQueryStrings = parse_url($_SERVER['REQUEST_URI'], PHP_URL_QUERY);
// create array from urlQueryStrings
parse_str($urlQueryStrings, $queryStrings);

$requestMethod = $_SERVER['REQUEST_METHOD'];
$authPathPrefix = 'api';
// get position of /api in the url path
$apiUrlPosition = array_search($authPathPrefix, $urlPath);

// if no modules have been called then return api information
if (sizeof($urlPath) == $apiUrlPosition + 1) {
    header(CRUDController::HTTP_SUCCESS);
    echo json_encode(apiInformation());
    exit();
}

$apiModule = $urlPath[$apiUrlPosition + 1] ?? null;
$apiKey = ($queryStrings['apiKey'] ?? "");

// if not authentication endpoint then require an api key
if ($apiModule != AUTHENTICATION_MODULE) {
    $id = $urlPath[$apiUrlPosition + 2] ?? null;
    $id = (!empty($id) ? intval($id) : null);

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
} catch (Exception $exception) {
    exit($exception->getMessage());
}


function apiInformation(): array
{
    $response['paths'] = apiEndPoints();
    return $response;
}

function apiEndPoints(): array
{
    return [
        '/auth' => [
            'post' => [
                'description' => 'Validates user credentials and returns user data when successful.'
            ]
        ],
        '/athlete' => [
            'get' => [
                'description' => 'Returns all athletes that the user has permission to view.'
            ]
        ],
        '/athlete/{athleteId}' => [
            'get' => [
                'description' => 'Returns detailed information for the specified athlete if the user has permission.'
            ],
            'post' => [
                'description' => 'Creates a new athlete subject to user permission. Requires that a json object is provided.'
            ],
            'put' => [
                'description' => 'Updates the athlete if the user has permission. Requires that a json object is provided.'
            ],
            'delete' => [
                'description' => 'The athlete will be deleted subject to requester permission.'
            ]
        ],
        '/event' => [
            'get' => [
                'description' => 'Returns all events that the user has permission to view.'
            ]
        ],
        '/event/{eventId}' => [
            'get' => [
                'description' => 'Returns detailed information for the specified event if the user has permission.'
            ],
            'post' => [
                'description' => 'Creates a new event subject to user permission. Requires that a json object is provided.'
            ],
            'put' => [
                'description' => 'Updates the event if the user has permission. Requires that a json object is provided.'
            ],
            'delete' => [
                'description' => 'The event will be deleted subject to requester permission.'
            ]
        ],
        '/fight' => [
            'get' => [
                'description' => 'Returns all events that the user has permission to view.'
            ]
        ],
        '/fight/{fightId}' => [
            'get' => [
                'description' => 'Returns detailed information for the specified fight if the user has permission.'
            ],
            'post' => [
                'description' => 'Creates a new fight subject to user permission. Requires that a json object is provided.'
            ],
            'put' => [
                'description' => 'Updates the fight if the user has permission. Requires that a json object is provided.'
            ],
            'delete' => [
                'description' => 'The fight will be deleted subject to requester permission.'
            ]
        ],
        '/user' => [
            'get' => [
                'description' => 'Returns all users for which the requester has permission to view.'
            ]
        ],
        '/user/{userId}' => [
            'get' => [
                'description' => 'Returns detailed information for the specified user if the user has permission.'
            ],
            'post' => [
                'description' => 'Creates a new user subject to user permission. Requires that a json object is provided.'
            ],
            'put' => [
                'description' => 'Updates the user if the user has permission. Requires that a json object is provided.'
            ],
            'delete' => [
                'description' => 'The user will be deleted subject to requester permission.'
            ]
        ]
    ];
}
// EOF