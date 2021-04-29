<?php
/**
 * Responsible for updating the entries for Fight Results and FightAthletes.
 */

// permission requirements
use helpers\APIRequest;
use helpers\HelperFunctions;
use models\Fight;
use models\FightAthlete;

$permissionModule = Fight::PERMISSION_AREA;
$permissionType = 'UPDATE';

HelperFunctions::checkPermission($permissionModule, $permissionType);


// no/invalid id - redirect
if (!isset($_GET['fightid']) || !is_numeric($_GET['fightid']) || $_GET['fightid'] <= 0) {
    $userNotification = 'Invalid Fight ID - redirected to Events page.';
    HelperFunctions::addNotification($userNotification);
    header("Location: ?page=events");
}

if (!constant("API_URL")) {
    echo 'Api address not set';
    return;
}

$fightId = intval($_GET['fightid']);


// check that a fight result record does not already exist
$fightResult = new APIRequest(API_URL, 'fight-result', API_KEY, $fightId, null);
$frResponse = $fightResult->fetchApiData();

if (!isset($frResponse['FightID'])) {
    $userNotification = 'Fight Result does not exist - redirected to relevant page.';
    HelperFunctions::addNotification($userNotification);

    header("Location: ?page=fight-result&id=$fightId&action=create");
}

// get fightAthlete record values for updating - will all be zero at this stage
$fightAthleteRequest = new APIRequest(API_URL, 'fight-athlete', API_KEY, $fightId);
$fightAthleteData = $fightAthleteRequest->fetchApiData();

$numOfAthletes = sizeof($fightAthleteData);
$numOfRounds = $fightAthleteData[0]['NumOfRounds'] ?? null;
$dataFields = array_keys($fightAthleteData[0]);
$numOfFields = sizeof($fightAthleteData[0]) ?? 0;
$fieldPrefix = 'stats_'; // prefix to remove


if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $formFightResultFields = [
        'FightID', 'ResultTypeID', 'WinnerAthleteID', 'WinRound', 'WinRoundTime'
    ];

    // join above list with list in FightAthlete model
    $fightAthleteFields = FightAthlete::DATA_FIELDS;
    // manually add fightAthleteID
    array_push($fightAthleteFields, 'FightAthleteID');

    $formFightAthleteFields = [];
    // create all data fields for each athlete (e.g. [dataField][athleteNum])
    foreach ($fightAthleteFields as $fightAthleteField) {
        for ($athleteNum = 0; $athleteNum < $numOfAthletes; $athleteNum++) {
            array_push($formFightAthleteFields, $fightAthleteField . $athleteNum);
        }
    }

    $fields = array_merge($formFightResultFields, $formFightAthleteFields);

    $validationErrors = [];

    foreach ($fields as $field) {
        if (!isset($_POST[$field]) || $_POST[$field] == '' || (empty($_POST[$field]) && !($_POST[$field] >= 0))) {
            $validationErrors[$field] = 'Field ' . $field . ' must be populated';
        }
    }


    // api processing
    if (sizeof($validationErrors) == 0) {
        // convert form data into array
        $formContents = file_get_contents("php://input");
        parse_str($formContents, $formContents);


        $processedFightResultData = [];
        // create json array for fight results api request
        foreach ($formFightResultFields as $formField) {
            $processedFightResultData[$formField] = $formContents[$formField];
        }

        $processedFightAthleteData = [];
        // create json array for fight results api request
        for ($athleteNum = 0; $athleteNum < $numOfAthletes; $athleteNum++) {
            $tmpData = [];
            // add fight athlete id

            foreach ($fightAthleteFields as $formField) {
                $tmpData[$formField] = $formContents[$formField . $athleteNum];
            }

            array_push($processedFightAthleteData, $tmpData);
        }


        $fightResultEndPoint = API_URL . '/fight-result/' . $fightId . '?apiKey=' . API_KEY;
        $result = sendApiRequest($fightResultEndPoint, 'PUT', 201,
            $processedFightResultData);

        $apiResponses = [];
        array_push($apiResponses, $result);

        // process each athlete
        foreach ($processedFightAthleteData as $data) {
            $fightAthleteEndPoint = API_URL . '/fight-athlete/' . $data['FightAthleteID'] . '?apiKey=' . API_KEY;
            $result = sendApiRequest($fightAthleteEndPoint, 'PUT', 201, $data);
            array_push($apiResponses, $result);
        }

        $apiResponse = [];
        foreach ($apiResponses as $response) {
            if (isset($response['Error'])) {
                $error = $response['Error'] . '<br />';
                if (!isset($apiResponse['Error'])) {
                    $apiResponse['Error'] = $error;
                } else {
                    $apiResponse['Error'] .= $error;
                }

            }
        }

        if (empty($apiResponse)) {
            HelperFunctions::addNotification('Fight Result updated');
            header('Location: ?page=fight&id=' . $fightId);
        }
    }
}

// get list of possible fight outcomes (results)
$resultRequest = new APIRequest(API_URL, 'result-type', API_KEY, null, null);
$resultTypeData = $resultRequest->fetchApiData();

$fightUrl = '?page=fight&id=' . $fightId;
?>

    <main class="container">
        <h2>Fight - Update Result</h2>
        <div class="mb-5">
            <a class="btn btn-more" href="<?= $fightUrl ?>">Back to Fight</a>
        </div>

        <?= HelperFunctions::displayApiError($apiResponse ?? []); ?>
        <form action="" method="post">
            <div class="row g-3 align-items-center mb-3">
                <div class="col-2">
                    <label for="FightID" class="col-form-label">Fight ID</label>
                </div>
                <div class="col-auto">
                    <input type="text" name="FightID" class="form-control" value="<?= $fightId ?>" readonly>
                </div>
                <div class="col-auto">
                    <span id="FightIDErrors" class="form-text error">
                    <?= $validationErrors['FightID'] ?? '' ?>
                    </span>
                </div>
            </div>

            <div class="row g-3 align-items-center mb-3">
                <div class="col-2">
                    <label for="ResultTypeID" class="col-form-label">Fight Outcome</label>
                </div>
                <div class="col-auto">
                    <select name="ResultTypeID" class="form-select" aria-label="Select fight referee">
                        <option value="" selected></option>
                        <?php
                        if (isset($resultTypeData['data'])) {
                            foreach ($resultTypeData['data'] as $ref) {
                                $selected = (($frResponse['ResultTypeID'] ?? $_POST['ResultTypeID'] ?? '') == $ref['ResultTypeID'] ? ' selected' : '');
                                echo '<option value="' . $ref['ResultTypeID'] . '"' . $selected . '>';
                                echo $ref['ResultDescription'];
                                echo '</option>';
                            }
                        }
                        ?>
                    </select>
                </div>
                <div class="col-auto">
                    <span id="ResultTypeIDErrors" class="form-text error">
                    <?= $validationErrors['ResultTypeID'] ?? '' ?>
                    </span>
                </div>
            </div>

            <div class="row g-3 align-items-center mb-3">
                <div class="col-2">
                    <label for="WinnerAthleteID" class="col-form-label">Winner</label>
                </div>
                <div class="col-auto">
                    <select name="WinnerAthleteID" class="form-select"
                            aria-label="Select the athlete that won (if applicable)">
                        <option value="" selected></option>
                        <?php
                        foreach ($fightAthleteData as $athlete) {
                            $selected = (($frResponse['WinnerAthleteID'] ?? $_POST['WinnerAthleteID'] ?? '') == $athlete['AthleteID'] ? ' selected' : '');
                            if (isset($athlete['AthleteName'])) {
                                echo '<option value="' . $athlete['AthleteID'] . '"' . $selected . '>';
                                echo $athlete['AthleteName'];
                                echo '</option>';
                            }
                        }
                        ?>
                    </select>
                </div>
                <div class="col-auto">
                    <span id="WinnerAthleteIDErrors" class="form-text error">
                    <?= $validationErrors['WinnerAthleteID'] ?? '' ?>
                    </span>
                </div>
            </div>

            <div class="row g-3 align-items-center mb-3">
                <div class="col-2">
                    <label for="WinRound" class="col-form-label">Win Round</label>
                </div>
                <div class="col-auto">
                    <select name="WinRound" class="form-select" aria-label="Select the round the fight ended in">
                        <option value="" selected></option>
                        <?php
                        if (isset($numOfRounds)) {
                            for ($round = 1; $round <= $numOfRounds; $round++) {
                                $selected = (($frResponse['WinRound'] ?? $_POST['WinRound'] ?? '') == $round ? ' selected' : '');
                                echo '<option value="' . $round . '"' . $selected . '>';
                                echo $round;
                                echo '</option>';
                            }
                        }
                        ?>
                    </select>
                </div>
                <div class="col-auto">
                    <span id="WinRoundErrors" class="form-text error">
                    <?= $validationErrors['WinRound'] ?? '' ?>
                    </span>
                </div>
            </div>
            <div class="row g-3 align-items-center mb-3">
                <div class="col-2">
                    <label for="WinRoundTime" class="col-form-label">Win Time</label>
                </div>
                <div class="col-auto">
                    <input type="time" class="form-control" name="WinRoundTime" max="05:00" min="00:00"
                           value="<?= $frResponse['WinRoundTime'] ?? $_POST['WinRoundTime'] ?? '' ?>">
                </div>
                <div class="col-auto">
                    <span id="WinRoundTimeErrors" class="form-text error">
                    <?= $validationErrors['WinRoundTime'] ?? '' ?>
                    </span>
                </div>
            </div>

            <?php


            for ($dataField = 0; $dataField < $numOfFields; $dataField++) {
                $fieldName = $dataFields[$dataField];
                $fieldNameNoPrefix = str_replace($fieldPrefix, '', $fieldName);

                // split on camel case, then rejoin using spaces and capitalise
                $fieldDisplayName = ucwords(join(' ', preg_split('/(?=[A-Z])/', $fieldNameNoPrefix)));

                for ($athlete = 0; $athlete < $numOfAthletes; $athlete++) {
                    $athleteFieldName = $fieldName . $athlete;
                    $fightAthlete = $fightAthleteData[$athlete];
                    $fieldValue = $_POST[$athleteFieldName] ?? $fightAthlete[$fieldName];

                    // output a header row before data points begin
                    if ($athlete == 0 && $dataField == 0) {
                        echo '<div class="fight-athlete-header row g-3 mb-3">
            <div class="col-6 col-md-4 col-lg-3">Athlete Fight Data</div>';

                        // create headers for athlete data
                        for ($counter = 0; $counter < $numOfAthletes; $counter++) {
                            $athleteName = $fightAthleteData[$counter]['AthleteName'];
                            echo '<div class="col-3 col-md-4 col-lg-3">' . $athleteName . '</div>';

                            // also add hidden row for fightAthleteID
                            echo '<input type="hidden" name="FightAthleteID' . $counter .
                                '" value="' . $fightAthleteData[$counter]['FightAthleteID'] . '">';

                        }
                        // close row div tag
                        echo '</div>';

                    }

                    // skip fields that don't have prefix
                    if ($fieldName == $fieldNameNoPrefix) {
                        continue;
                    }

                    // for first athlete always output data label column
                    if ($athlete == 0) {
                        // first
                        echo '<div class="row fight-athlete-data g-3 align-items-center mb-3">
            <div class="col-6 col-md-4 col-lg-3">
                <label for="' . $fieldName . $athlete . '" class="col-form-label">' . $fieldDisplayName . '</label>
            </div>';
                    }

                    $errorClass = isset($validationErrors[$athleteFieldName]) ? ' field-error' : '';

                    // output input field to capture athlete data
                    echo '<div class="col-3 col-md-4 col-lg-3">
                <input type="number" class="form-control' . $errorClass . '" name="' . $athleteFieldName . '" value="' . $fieldValue . '" min="0" max="1000">
            </div>';

                    // for last athlete close the row div tag
                    if ($athlete == $numOfAthletes - 1) {
                        echo '</div>';
                    }
                }
            }
            ?>

            <button type="submit" class="btn btn-lg btn-secondary">Update Result</button>
        </form>
    </main>

<?php

function sendApiRequest(string $apiEndPoint, string $httpRequestType, int $expectedResponseHeader, array $data)
{
    $jsonData = json_encode($data);
    $apiRequest = curl_init();
    curl_setopt($apiRequest, CURLOPT_URL, $apiEndPoint);
    curl_setopt($apiRequest, CURLOPT_CUSTOMREQUEST, $httpRequestType);
    curl_setopt($apiRequest, CURLOPT_POSTFIELDS, $jsonData);
    curl_setopt($apiRequest, CURLOPT_RETURNTRANSFER, true);

    $apiResponse = json_decode(curl_exec($apiRequest), true);
    $metaResponse = curl_getinfo($apiRequest);

    return $apiResponse;
}