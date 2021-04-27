<?php
// permission requirements
use helpers\APIRequest;

// for put request
$permissionModule = \models\Event::PERMISSION_AREA;
$permissionType = 'UPDATE';
$apiModule = 'event';

\helpers\HelperFunctions::checkPermission($permissionModule, $permissionType);

if (!constant("API_URL")) {
    echo 'Api address not set';
    return;
}

if (!is_numeric($_GET['id']) || !($_GET['id'] > 0)) {
    \helpers\HelperFunctions::addNotification("The Event ID used is invalid");
    header('Location: ?page=events');
}

$id = intval($_GET['id']);
$apiEndPoint = API_URL . '/' . $apiModule . '/' . $id . '?apiKey=' . API_KEY;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $fields = [
        'EventLocation', 'EventDate'
    ];
    $validationErrors = [];

    foreach ($fields as $field) {
        if (!isset($_POST[$field]) || empty($_POST[$field])) {
            $validationErrors[$field] = 'Field ' . $field . ' must be populated';
        }
    }


    // api processing
    if (sizeof($validationErrors) == 0) {
        // convert form data into array
        $formContents = file_get_contents("php://input");
        parse_str($formContents, $formContents);
        $jsonContents = json_encode($formContents);

        $apiRequest = curl_init();
        curl_setopt($apiRequest, CURLOPT_URL, $apiEndPoint);
        curl_setopt($apiRequest, CURLOPT_CUSTOMREQUEST, 'PUT');
        curl_setopt($apiRequest, CURLOPT_POSTFIELDS, $jsonContents);
        curl_setopt($apiRequest, CURLOPT_RETURNTRANSFER, true);

        $apiResponse = json_decode(curl_exec($apiRequest), true);
        $metaResponse = curl_getinfo($apiRequest);

        // if created, header will return 204
        if (isset($metaResponse['http_code']) && $metaResponse['http_code'] == 204) {

            unset($apiResponse); // api will return number of records created when successfully
            unset($queryString['action']); // remove so that redirect takes user to event page

            $userNotification = 'Event updated';
            \helpers\HelperFunctions::addNotification($userNotification);

            $redirect = '?' . http_build_query($queryString);

            header("Location: $redirect");
        }
    }

}

// get existing values for event
$apiRequest = new APIRequest(API_URL, $apiModule, API_KEY, $id, $queryString);
$results = $apiRequest->fetchApiData();

if (isset($results['Error'])) {
    if ($_SERVER['REQUEST_METHOD'] == 'GET') {
        // get request means no data has been submitted yet.. so error is a result of non-existent record
        header('Location: ?page=events');
    }
}

?>

<main class="container">
    <h2>Update Event</h2>

    <?= \helpers\HelperFunctions::displayApiError($apiResponse ?? []); ?>
    <form action="" method="post">

        <div class="row g-3 align-items-center mb-3">
            <div class="col-2">
                <label for="EventID" class="col-form-label">Event ID</label>
            </div>
            <div class="col-auto">
                <input type="text" class="form-control" value="<?= $id ?>" readonly>
            </div>
        </div>
        <div class="row g-3 align-items-center mb-3">
            <div class="col-2">
                <label for="EventLocation" class="col-form-label">Event Location</label>
            </div>
            <div class="col-auto">
                <input type="text" name="EventLocation" class="form-control"
                       value="<?= $_POST['EventLocation'] ?? $results['EventLocation'] ?? '' ?>" required>
            </div>
            <div class="col-auto">
                    <span id="EventLocationErrors" class="form-text error">
                    <?= $validationErrors['EventLocation'] ?? '' ?>
                    </span>
            </div>
        </div>

        <div class="row g-3 align-items-center mb-3">
            <div class="col-2">
                <label for="EventDate" class="col-form-label">Event Date</label>
            </div>
            <div class="col-auto">
                <input type="date" name="EventDate" class="form-control"
                       value="<?= $_POST['EventDate'] ?? $results['EventDate'] ?? '' ?>" required>
            </div>
            <div class="col-auto">
                    <span id="EventDateErrors" class="form-text error">
                    <?= $validationErrors['EventDate'] ?? '' ?>
                    </span>
            </div>
        </div>

        <button type="submit" class="btn btn-primary">Update</button>
    </form>
</main>