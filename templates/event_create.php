<?php
// permission requirements
$permissionModule = \models\Event::PERMISSION_AREA;
$permissionType = 'CREATE';

$apiEndPoint = API_URL . '/event?apiKey=' . API_KEY;

\helpers\HelperFunctions::checkPermission($permissionModule, $permissionType);

if (!constant("API_URL")) {
    echo 'Api address not set';
    return;
}


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
        // add default role
        $jsonContents = json_encode($formContents);

        $apiRequest = curl_init();
        curl_setopt($apiRequest, CURLOPT_URL, $apiEndPoint);
        curl_setopt($apiRequest, CURLOPT_POST, true);
        curl_setopt($apiRequest, CURLOPT_POSTFIELDS, $jsonContents);
        curl_setopt($apiRequest, CURLOPT_RETURNTRANSFER, true);

        $apiResponse = json_decode(curl_exec($apiRequest), true);
        $metaResponse = curl_getinfo($apiRequest);

        // if created, header will return 201
        if (isset($metaResponse['http_code']) && $metaResponse['http_code'] == 201) {

            unset($apiResponse); // api will return number of records created when successfully

            $userNotification = 'Event Created';
            \helpers\HelperFunctions::addNotification($userNotification);

            header("Location: ?page=events");
        }
    }
}

?>

    <main class="container">
        <h2>Create Event</h2>

        <?= \helpers\HelperFunctions::displayApiError($apiResponse ?? []); ?>
        <form action="" method="post">

            <div class="row g-3 align-items-center mb-3">
                <div class="col-2">
                    <label for="EventLocation" class="col-form-label">Event Location</label>
                </div>
                <div class="col-auto">
                    <input type="text" name="EventLocation" class="form-control"
                           value="<?= $_POST['EventLocation'] ?? '' ?>" required>
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
                           value="<?= $_POST['EventDate'] ?? '' ?>" required>
                </div>
                <div class="col-auto">
                    <span id="EventDateErrors" class="form-text error">
                    <?= $validationErrors['EventDate'] ?? '' ?>
                    </span>
                </div>
            </div>

            <button type="submit" class="btn btn-primary">Create</button>
        </form>
    </main>