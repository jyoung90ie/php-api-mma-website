<?php

use helpers\APIRequest;
use helpers\HelperFunctions;

// no/invalid id - redirect
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo '<p>Invalid Fight ID</p>';
    return;
}

if (!constant("API_URL")) {
    echo '<p>Api address not set</p>';
    return;
}

$apiModule = "/fight";
$permissionModule = \models\Fight::PERMISSION_AREA;

$id = intval($_GET['id']);

$apiRequest = new APIRequest(API_URL, $apiModule, API_KEY, $id, $queryString);
$results = $apiRequest->fetchApiData();

if (isset($results['Error']) || !$results) {
    header("Location: ?page=events");
}

$athletes = $results['Athletes'];

if (sizeof($athletes) < 2) {
    echo '<p>Athletes have not yet been added</p>';
    return;
}

$statsData = processApiResult($athletes);


$athleteRed = $athletes[0];
$athleteBlue = $athletes[1];

$athleteNameRed = $athleteRed['AthleteName'];
$athleteNameBlue = $athleteBlue['AthleteName'];

$finishRound = $results['WinRound'] ?? 'TBC';
$finishTime = $results['WinRoundTime'] ?? 'TBC';
$titleBout = ($results['TitleBout'] == 1 ? 'Yes' : 'No');
$outcome = $results['Outcome'] ?? 'TBC';

// if outcome is TBC then set winnerId to TBC so that displayOutcomeBadge knows that it wasn't a draw (null = draw)
$winnerId = ($outcome == 'TBC' ? -1 : $results['WinnerAthleteID']);

$eventUrl = '?page=event&id=' . $results['EventID'];

?>

    <main class="fight-detail-container container">
        <div class="d-flex flex-row">
            <div>
                <a class="btn btn-more" href="<?= $eventUrl ?>">Back to Event</a>
            </div>
            <div class="ms-auto">
                <?php
                if (HelperFunctions::hasPermission($permissionModule, 'CREATE')) {
                    echo '<a href="?page=fight&action=update&id=' . $id . '" class="mx-2 btn btn-outline-secondary">Update Fight</a>';
                }
                if ($outcome == 'TBC') {
                    if (HelperFunctions::hasPermission($permissionModule, 'CREATE')) {
                        echo '<a href="?page=fightresult&action=create&fightid=' . $id . '" class="mx-2 btn btn-outline-success">Add Result</a>';
                    }
                } else {
                    if (HelperFunctions::hasPermission($permissionModule, 'UPDATE')) {
                        echo '<a href="?page=fightresult&action=update&fightid=' . $id . '" class="mx-2 btn btn-outline-secondary">Update Result</a>';
                    }
                }
                if (HelperFunctions::hasPermission($permissionModule, 'DELETE')) {
                    echo '<a href="?page=fight&action=delete&id=' . $id . '" class="mx-2 btn btn-outline-danger">Delete Fight</a>';
                }
                ?>
            </div>
        </div>

        <h2>Fight Breakdown</h2>
        <div class="fight-outcome row">
            <div class="col-6 col-md-3">
                <span>Round</span>
                <span><?= $finishRound ?></span>
            </div>
            <div class="col-6 col-md-3">
                <span>Time</span>
                <span><?= $finishTime ?></span>
            </div>
            <div class="col-6 col-md-3">
                <span>Outcome</span>
                <span><?= $outcome ?></span>
            </div>
            <div class="col-6 col-md-3">
                <span>Title Bout?</span>
                <span><?= $titleBout ?></span>
            </div>
        </div>
        <div class="fight-athletes row">
            <div class="col-6">
                <a href="?page=athlete&id=<?= $athleteRed['AthleteID'] ?>">
                    <img src="<?= $athleteRed['AthleteImage'] ?>" alt="Profile picture of <?= $athleteNameRed ?>">
                    <span class="athlete-name"><?= $athleteNameRed ?></span>
                </a>
                <?= HelperFunctions::displayOutcomeBadge($athleteRed, $winnerId) ?>
            </div>
            <div class="col-6">
                <a href="?page=athlete&id=<?= $athleteBlue['AthleteID'] ?>">
                    <img src="<?= $athleteBlue['AthleteImage'] ?>" alt="Profile picture of <?= $athleteNameBlue ?>">
                    <span class="athlete-name"><?= $athleteNameBlue ?></span>
                </a>
                <?= HelperFunctions::displayOutcomeBadge($athleteBlue, $winnerId) ?>
            </div>
        </div>


        <?php
        if ($outcome != 'TBC') {
            HelperFunctions::displayFightComparisonData($statsData, $athleteNameRed, $athleteNameBlue);
        } else {
            echo '<h4 class="text-center p-4">Fight stats are not yet available</h4>';
        } ?>

    </main>


<?php

/**
 * Generates an array of fight data which can be used by TemplatesHelper::displayFightComparisonData.
 *
 * Array format:
 * [
 *      ['Data Point Name'] =>
 *      {
 *          'type' => 'Data Point Name',
 *          'athleteRed' => { 'landed' => int, 'thrown' => int},
 *          'athleteRed' => { 'landed' => int, 'thrown' => int}
 *      },
 *      [...] => { ... }
 * ]
 *
 * @param array $athletesData data from fight detail API endpoint
 * @return array|void
 */
function processApiResult(array $athletesData)
{
    $statDisplayNames = [
        'stats_strikesLanded' => 'Strikes',
        'stats_strikesThrown' => 'Strikes',
        'stats_significantStrikesLanded' => 'Significant Strikes',
        'stats_significantStrikesThrown' => 'Significant Strikes',
        'stats_takedownsLanded' => 'Takedowns',
        'stats_takedownsThrown' => 'Takedowns',
        'stats_positionReversals' => 'Reversals',
        'stats_knockDowns' => 'Knockdowns',
        'stats_submissionAttempts' => 'Submission Attempts'
    ];

    $statData = [];

    if (sizeof($athletesData) !== 2) {
        echo 'Must have two athletes';
        return;
    }


    $athleteRed = $athletesData[0]['AthleteName'];
    $athleteBlue = $athletesData[1]['AthleteName'];

    foreach ($statDisplayNames as $key => $value) {
        if (!isset($statData[$value][$athleteRed][$key])) {
            $statData[$value]['type'] = $value;

            if (stripos($key, 'landed') !== false) {
                $keyDisplay = 'landed';
            } else if (stripos($key, 'thrown') !== false) {
                $keyDisplay = 'thrown';
            } else {
                $keyDisplay = $key;
            }

            $statData[$value][$athleteRed][$keyDisplay] = $athletesData[0][$key];
            $statData[$value][$athleteBlue][$keyDisplay] = $athletesData[1][$key];
        }
    }

    return $statData;
}