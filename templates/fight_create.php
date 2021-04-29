<?php
// permission requirements
use helpers\APIRequest;

$permissionModule = \models\Fight::PERMISSION_AREA;
$permissionType = 'CREATE';

\helpers\HelperFunctions::checkPermission($permissionModule, $permissionType);

$apiEndPoint = API_URL . '/fight?apiKey=' . API_KEY;


// no/invalid id - redirect
if (!isset($_GET['eventid']) || !is_numeric($_GET['eventid'])) {
    header("Location: ?page=events");
}

if (!constant("API_URL")) {
    echo 'Api address not set';
    return;
}

$id = intval($_GET['eventid']);


if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $fields = [
        'EventID', 'RefereeID', 'TitleBout', 'WeightClassID', 'NumOfRounds', 'AthleteID1', 'AthleteID2'
    ];
    $validationErrors = [];

    foreach ($fields as $field) {
        if (!isset($_POST[$field]) || empty($_POST[$field])) {
            $validationErrors[$field] = 'Field ' . $field . ' must be populated';
        }
    }

    // make sure the same fighter hasn't been selected twice
    if (!empty($_POST['AthleteID1']) && $_POST['AthleteID1'] == $_POST['AthleteID2']) {
        $validationErrors['AthleteID1'] = 'This athlete has been selected twice - please select another athlete';
        $validationErrors['AthleteID2'] = $validationErrors['AthleteID1'];
    }

    // api processing
    if (sizeof($validationErrors) == 0) {
        // convert form data into array
        $formContents = file_get_contents("php://input");
        parse_str($formContents, $formContents);

        // transform form contents
        $formContents['TitleBout'] = $formContents['TitleBout'] == 'yes' ? 1 : 0;

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

            $userNotification = 'Fight has been created and added to the Event';
            \helpers\HelperFunctions::addNotification($userNotification);

            header("Location: ?page=event&id=$id");
        }

    }
}

// get data from API
$eventRequest = new APIRequest(API_URL, 'event', API_KEY, $id,  null);
$event = $eventRequest->fetchApiData();

if (!isset($event['EventID'])) {
    \helpers\HelperFunctions::addNotification('Invalid Event ID - redirected to Events page');
    header('Location: ?page=events');
}

// get data from API
$refereeRequest = new APIRequest(API_URL, 'referee', API_KEY, null,  ['limit' => 5000, 'limitOverride' => true]);
$referees = $refereeRequest->fetchApiData();

$weightRequest = new APIRequest(API_URL, 'weight', API_KEY, null, null);
$weights = $weightRequest->fetchApiData();

// total athletes > 3000 - need new approach
$athleteRequest = new APIRequest(API_URL, 'athlete', API_KEY, null, ['limit' => 5000, 'limitOverride' => true]);
$athletes = $athleteRequest->fetchApiData();

$eventUrl = '?page=event&id=' . $id;
?>

<main class="container">
    <h2>Event - Add Fight</h2>
    <div class="mb-5">
        <a class="btn btn-more" href="<?= $eventUrl ?>">Back to Event</a>
    </div>

    <?= \helpers\HelperFunctions::displayApiError($apiResponse ?? []); ?>
    <form action="" method="post">
        <div class="row g-3 align-items-center mb-3">
            <div class="col-2">
                <label for="EventID" class="col-form-label">Event ID</label>
            </div>
            <div class="col-auto">
                <input type="text" name="EventID" class="form-control" value="<?= $id ?>" readonly>
            </div>
        </div>

        <div class="row g-3 align-items-center mb-3">
            <div class="col-2">
                <label for="RefereeID" class="col-form-label">Referee</label>
            </div>
            <div class="col-auto">
                <select name="RefereeID" class="form-select" aria-label="Select fight referee">
                    <option value="" selected></option>
                    <?php
                    if (isset($referees['data'])) {
                        foreach ($referees['data'] as $ref) {
                            $selected = ($_POST['RefereeID'] == $ref['RefereeID'] ? ' selected' : '');
                            echo '<option value="' . $ref['RefereeID'] . '"' . $selected . '>';
                            echo $ref['RefereeName'];
                            echo '</option>';
                        }
                    }
                    ?>
                </select>
            </div>
            <div class="col-auto">
                    <span id="RefereeIDErrors" class="form-text error">
                    <?= $validationErrors['RefereeID'] ?? '' ?>
                    </span>
            </div>
        </div>

        <div class="row g-3 align-items-center mb-3">
            <div class="col-2">
                <label for="TitleBout" class="col-form-label">Title Bout?</label>
            </div>
            <div class="col-auto">
                <select name="TitleBout" class="form-select" aria-label="Is this a title fight?">
                    <option value="no" selected>No</option>
                    <option value="yes">Yes</option>
                </select>
            </div>
            <div class="col-auto">
                    <span id="TitleBoutErrors" class="form-text error">
                    <?= $validationErrors['TitleBout'] ?? '' ?>
                    </span>
            </div>
        </div>

        <div class="row g-3 align-items-center mb-3">
            <div class="col-2">
                <label for="NumOfRounds" class="col-form-label">Number of Rounds</label>
            </div>
            <div class="col-auto">
                <select name="NumOfRounds" class="form-select" aria-label="Select the number of rounds">
                    <option value="3" selected>3</option>
                    <option value="5">5</option>
                </select>
            </div>
            <div class="col-auto">
                    <span id="NumOfRoundsErrors" class="form-text error">
                    <?= $validationErrors['NumOfRounds'] ?? '' ?>
                    </span>
            </div>
        </div>

        <div class="row g-3 align-items-center mb-3">
            <div class="col-2">
                <label for="WeightClassID" class="col-form-label">Weight class</label>
            </div>
            <div class="col-auto">
                <select name="WeightClassID" class="form-select" aria-label="Select fight weight class">
                    <option value="" selected></option>
                    <?php
                    if (isset($weights['data'])) {
                        foreach ($weights['data'] as $weight) {
                            $selected = ($_POST['WeightClassID'] == $weight['WeightClassID'] ? ' selected' : '');
                            echo '<option value="' . $weight['WeightClassID'] . '"' . $selected . '>';
                            echo $weight['WeightClass'];
                            echo '</option>';
                        }
                    }
                    ?>
                </select>
            </div>
            <div class="col-auto">
                    <span id="WeightClassIDErrors" class="form-text error">
                    <?= $validationErrors['WeightClassID'] ?? '' ?>
                    </span>
            </div>
        </div>

        <div class="row g-3 align-items-center mb-3">
            <div class="col-2">
                <label for="AthleteID1" class="col-form-label">Athlete 1</label>
            </div>
            <div class="col-auto">
                <select name="AthleteID1" class="form-select" aria-label="Select the first fight athlete">
                    <option value="" selected></option>
                    <?php
                    if (isset($athletes['data'])) {
                        foreach ($athletes['data'] as $athlete) {
                            $selected = ($_POST['AthleteID1'] == $athlete['AthleteID'] ? ' selected' : '');
                            echo '<option value="' . $athlete['AthleteID'] . '"' . $selected . '>';
                            echo $athlete['AthleteName'];
                            echo '</option>';
                        }
                    }
                    ?>
                </select>
            </div>
            <div class="col-auto">
                    <span id="AthleteID1Errors" class="form-text error">
                    <?= $validationErrors['AthleteID1'] ?? '' ?>
                    </span>
            </div>
        </div>

        <div class="row g-3 align-items-center mb-3">
            <div class="col-2">
                <label for="AthleteID2" class="col-form-label">Athlete 2</label>
            </div>
            <div class="col-auto">
                <select name="AthleteID2" class="form-select" aria-label="Select the second fight athlete">
                    <option value="" selected></option>
                    <?php
                    if (isset($athletes['data'])) {
                        foreach ($athletes['data'] as $athlete) {
                            $selected = ($_POST['AthleteID2'] == $athlete['AthleteID'] ? ' selected' : '');
                            echo '<option value="' . $athlete['AthleteID'] . '"' . $selected . '>';
                            echo $athlete['AthleteName'];
                            echo '</option>';
                        }
                    }
                    ?>
                </select>
            </div>
            <div class="col-auto">
                    <span id="AthleteID2Errors" class="form-text error">
                    <?= $validationErrors['AthleteID2'] ?? '' ?>
                    </span>
            </div>
        </div>

        <button type="submit" class="btn btn-primary">Create</button>
    </form>
</main>