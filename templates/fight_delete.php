<?php
// permission requirements
use helpers\APIRequest;

$permissionModule = \models\Fight::PERMISSION_AREA;
$permissionType = 'DELETE';
$apiModule = 'fight';

\helpers\HelperFunctions::checkPermission($permissionModule, $permissionType);

if (!constant("API_URL")) {
    echo 'Api address not set';
    return;
}

if (!is_numeric($_GET['id']) || !($_GET['id'] > 0)) {
    \helpers\HelperFunctions::addNotification("The Fight ID used is invalid");
    header('Location: ?page=events');
}

$id = intval($_GET['id']);
$apiEndPoint = API_URL . '/' . $apiModule . '/' . $id . '?apiKey=' . API_KEY;


// get existing values for event
$apiRequest = new APIRequest(API_URL, $apiModule, API_KEY, $id, $queryString);
$results = $apiRequest->fetchApiData();

$eventId = $results['EventID'];


if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // convert form data into array
    $formContents = file_get_contents("php://input");
    parse_str($formContents, $formContents);
    // add default role
    $jsonContents = json_encode($formContents);

    $apiRequest = curl_init();
    curl_setopt($apiRequest, CURLOPT_URL, $apiEndPoint);
    curl_setopt($apiRequest, CURLOPT_CUSTOMREQUEST, 'DELETE');
    curl_setopt($apiRequest, CURLOPT_RETURNTRANSFER, true);

    $apiResponse = json_decode(curl_exec($apiRequest), true);
    $metaResponse = curl_getinfo($apiRequest);

    // if created, header will return 204
    if (isset($metaResponse['http_code']) && $metaResponse['http_code'] == 204) {

        unset($apiResponse); // api will return number of records created when successfully

        $userNotification = 'Fight deleted';
        \helpers\HelperFunctions::addNotification($userNotification);

        header("Location: ?page=event&id=$eventId");
    }

}


if (isset($results['Error'])) {
    header('Location: ?page=events');
}

?>

<main class="container">
    <h2>Delete Fight</h2>

    <div class="row">
        <div class="col-12 text-center">
            <form method="post">
                <h4 class="p-3">Are you sure you want to delete Fight ID <strong><?= $id ?></strong>?</h4>
                <button type="submit" class="btn btn-danger mx-3">Delete</button>
                <a href="?page=fight&id=<?= $id ?>" class="btn btn-secondary mx-4">Cancel</a>
            </form>
        </div>

    </div>


</main>