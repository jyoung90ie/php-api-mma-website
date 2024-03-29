<?php

namespace api;

include_once '../autoload.php';
include_once '../helpers/config.php';

header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json; charset=UTF-8');
header("Access-Control-Allow-Methods: GET,POST,PUT,DELETE");


use models\{APIAccess,
    Athlete,
    AthleteStance,
    Event,
    Fight,
    FightAthlete,
    FightResult,
    Referee,
    ResultType,
    Search,
    User,
    WeightClass
};
use controllers\AuthController;
use controllers\CRUDController;
use Exception;
use helpers\Database;

// api endpoint modules
const FIGHT_MODULE = 'fight';                   // yes
const FIGHT_RESULT_MODULE = 'fight-result';     // yes
const ATHLETE_MODULE = 'athlete';               // yes
const ATHLETE_STANCE_MODULE = 'athlete-stance'; // yes
const FIGHT_ATHLETE_MODULE = 'fight-athlete';   // yes
const EVENT_MODULE = 'event';                   // yes
const USER_MODULE = 'user';                     // yes
const REFEREE_MODULE = 'referee';               // yes
const WEIGHT_MODULE = 'weight';                 // yes
const RESULT_TYPE_MODULE = 'result-type';
const AUTHENTICATION_MODULE = 'auth';           // yes
const SEARCH_MODULE = 'search';                 // yes

$db = (new Database())->getConnection();

// remove trailing / and any query strings, then break up uri into associate array
$urlPath = explode('/', parse_url(rtrim($_SERVER['REQUEST_URI'], '/'), PHP_URL_PATH));
$urlQueryStrings = parse_url($_SERVER['REQUEST_URI'], PHP_URL_QUERY);
// create array from urlQueryStrings
parse_str($urlQueryStrings, $queryStrings);

$requestMethod = $_SERVER['REQUEST_METHOD'];
$apiPathPrefix = 'api';
// get position of /api in the url path
$apiUrlPosition = array_search($apiPathPrefix, $urlPath);

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
        echo json_encode(['Error' => 'Invalid API Key']);
        exit();
    }

    // get user
    $user = new User($db);

    if (!$user->getOne($apiAccess->getUserId())) {
        header(CRUDController::HTTP_NOT_FOUND);
        echo json_encode(['Error' => 'User could not be found']);
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
        case FIGHT_RESULT_MODULE:
            $fightResult = new CRUDController(new FightResult($db), $user, $requestMethod, $id, $queryStrings);
            $fightResult->process_request();
            break;
        case EVENT_MODULE:
            $eventRequest = new CRUDController(new Event($db), $user, $requestMethod, $id, $queryStrings);
            $eventRequest->process_request();
            break;
        case ATHLETE_MODULE:
            $athleteRequest = new CRUDController(new Athlete($db), $user, $requestMethod, $id, $queryStrings);
            $athleteRequest->process_request();
            break;
        case ATHLETE_STANCE_MODULE:
            $athleteStanceRequest = new CRUDController(new AthleteStance($db), $user, $requestMethod, $id, $queryStrings);
            $athleteStanceRequest->process_request();
            break;
        case FIGHT_ATHLETE_MODULE:
            $fightAthleteRequest = new CRUDController(new FightAthlete($db), $user, $requestMethod, $id, $queryStrings);
            $fightAthleteRequest->process_request();
            break;
        case REFEREE_MODULE:
            $refereeRequest = new CRUDController(new Referee($db), $user, $requestMethod, $id, $queryStrings);
            $refereeRequest->process_request();
            break;
        case WEIGHT_MODULE:
            $weightRequest = new CRUDController(new WeightClass($db), $user, $requestMethod, $id, $queryStrings);
            $weightRequest->process_request();
            break;
        case USER_MODULE:
            $userRequest = new CRUDController(new User($db), $user, $requestMethod, $id, $queryStrings);
            $userRequest->process_request();
            break;
        case RESULT_TYPE_MODULE:
            $resultTypeRequest = new CRUDController(new ResultType($db), $user, $requestMethod, $id, $queryStrings);
            $resultTypeRequest->process_request();
            break;
        case SEARCH_MODULE:
            $searchRequest = new CRUDController(new Search($db), $user, $requestMethod, null, $queryStrings);
            $searchRequest->process_request();
            break;
        case AUTHENTICATION_MODULE:
            $authRequest = new AuthController(new User($db), $requestMethod);
            $authRequest->process_request();
            break;
        default:
            header(CRUDController::HTTP_NOT_FOUND);
            exit(json_encode(['Error' => 'API endpoint does not exist']));
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
        '/search' => [
            'post' => [
                'description' => 'Returns all athlete records that match/partially match the search term.'
            ]
        ],
        '/athlete' => [
            'get' => [
                'description' => 'Returns all athletes that the user has permission to view.'
            ],
            'post' => [
                'description' => 'Creates a new athlete subject to user permission. Requires that a json object is provided.'
            ]
        ],
        '/athlete/{athleteId}' => [
            'get' => [
                'description' => 'Returns detailed information for the specified athlete if the user has permission.'
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
                'description' => 'Returns all event records ordered by event Id in descending order (i.e. newest first).'
            ],
            'post' => [
                'description' => 'Creates a new event subject to user permission. Requires that a json object is provided.'
            ]
        ],
        '/event/{eventId}' => [
            'get' => [
                'description' => 'Returns detailed information for the specified event if the user has permission.'
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
            ],
            'post' => [
                'description' => 'Creates a new fight subject to user permission. Requires that a json object is provided.'
            ]
        ],
        '/fight/{fightId}' => [
            'get' => [
                'description' => 'Returns detailed information for the specified fight if the user has permission.'
            ],
            'put' => [
                'description' => 'Updates the fight if the user has permission. Requires that a json object is provided.'
            ],
            'delete' => [
                'description' => 'The fight will be deleted subject to requester permission.'
            ]
        ],
        '/fight-result' => [
            'get' => [
                'description' => 'Returns all fight result records that the user has permission to view.'
            ],
            'post' => [
                'description' => 'Creates a new fight result record subject to user permission. Requires that a json object is provided.'
            ]
        ],
        '/fight-result/{fightId}' => [
            'get' => [
                'description' => 'Returns detailed information for the specified fight result if the user has permission.'
            ],
            'put' => [
                'description' => 'Updates the fight result if the user has permission. Requires that a json object is provided.'
            ],
            'delete' => [
                'description' => 'The fight result will be deleted subject to requester permission.'
            ]
        ],
        '/fight-athlete' => [
            'get' => [
                'description' => 'Returns all fight athlete records using paginated results.'
            ],
            'post' => [
                'description' => 'Creates a new fight athlete record subject to user permission. Requires that a json object is provided.'
            ],
        ],
        '/fight-athlete/{fightId}' => [
            'get' => [
                'description' => 'Returns all fight athlete records for the specified Fight ID.'
            ]
        ],
        '/fight-athlete/{fightAthleteId}' => [
            'put' => [
                'description' => 'Updates the fight athlete record if the user has permission. Requires that a json object is provided.'
            ],
            'delete' => [
                'description' => 'Deletes the specified fight athlete record - subject to requester permission.'
            ]
        ],
        '/referee' => [
            'get' => [
                'description' => 'Returns all referee records.'
            ],
            'post' => [
                'description' => 'Creates a new referee record subject to user permission. Requires that a json object is provided.'
            ],
        ],
        '/referee/{refereeId}' => [
            'get' => [
                'description' => 'Returns the specified referee record.'
            ],
            'put' => [
                'description' => 'Updates the specified referee record if the user has permission. Requires that a json object is provided.'
            ],
            'delete' => [
                'description' => 'Deletes the specified referee record - subject to requester permission.'
            ]
        ],
        '/result-type' => [
            'get' => [
                'description' => 'Returns all result type records.'
            ],
            'post' => [
                'description' => 'Creates a new result type record subject to user permission. Requires that a json object is provided.'
            ],
        ],
        '/result-type/{resultTypeId}' => [
            'get' => [
                'description' => 'Returns the specified result type record by alphabetical order.'
            ],
            'put' => [
                'description' => 'Updates the specified result type record if the user has permission. Requires that a json object is provided.'
            ],
            'delete' => [
                'description' => 'Deletes the specified result type record - subject to requester permission.'
            ]
        ],
        '/user' => [
            'get' => [
                'description' => 'Returns all users for which the requester has permission to view.'
            ],
            'post' => [
                'description' => 'Creates a new user subject to user permission. Requires that a json object is provided.'
            ]
        ],
        '/user/{userId}' => [
            'get' => [
                'description' => 'Returns detailed information for the specified user if the user has permission.'
            ],
            'put' => [
                'description' => 'Updates the user if the user has permission. Requires that a json object is provided.'
            ],
            'delete' => [
                'description' => 'The user will be deleted subject to requester permission.'
            ]
        ],
        '/weight' => [
            'get' => [
                'description' => 'Returns all weight class records ordered by the maximum weight of the weight class.'
            ],
            'post' => [
                'description' => 'Creates a new weight class record subject to user permission. Requires that a json object is provided.'
            ]
        ],
        '/weight/{weightClassId}' => [
            'get' => [
                'description' => 'Returns the specified weight class record.'
            ],
            'put' => [
                'description' => 'Updates the specified weight class record. Requires that a json object is provided.'
            ],
            'delete' => [
                'description' => 'Deletes the specified weight class record - subject to requester permission.'
            ]
        ]
    ];
}
// EOF