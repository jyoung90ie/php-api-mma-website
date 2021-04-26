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
$id = intval($_GET['id']);

$apiRequest = new APIRequest(constant("API_URL"), $apiModule, $id, $queryString);
$results = $apiRequest->fetchApiData();

if (isset($results['Error']) || !$results) {
    echo '<p>API request failed</p>';
    return;
}

$athletes = $results['Athletes'];

if (sizeof($athletes) < 2) {
    echo '<p>Athletes have not yet been added</p>';
    return;
}

$statsData = processApiResult($athletes);

$winnerId = $results['WinnerAthleteID'];

$athleteRed = $athletes[0];
$athleteBlue = $athletes[1];

$athleteNameRed = $athleteRed['AthleteName'];
$athleteNameBlue = $athleteBlue['AthleteName'];

$finishRound = $results['WinRound'] ?? 'TBC';
$finishTime = $results['WinRoundTime'] ?? 'TBC';
$titleBout = ($results['TitleBout'] == 1 ? 'Yes' : 'No');
$outcome = $results['Outcome'] ?? 'TBC';

$eventUrl = '?page=event&id=' . $results['EventID'];

?>

    <main class="fight-detail-container container">
        <a class="btn btn-more" href="<?= $eventUrl ?>">Back to Event</a>
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
                <span class="athlete-name"><?= $athleteNameRed ?></span>
                <?= HelperFunctions::displayOutcomeBadge($athleteRed, $winnerId) ?>
            </div>
            <div class="col-6">
                <span class="athlete-name"><?= $athleteNameBlue ?></span>
                <?= HelperFunctions::displayOutcomeBadge($athleteBlue, $winnerId) ?>
            </div>
        </div>

        <?php HelperFunctions::displayFightComparisonData($statsData, $athleteNameRed, $athleteNameBlue); ?>
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