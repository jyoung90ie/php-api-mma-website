<?php

use helpers\APIRequest;

include 'helpers/athleteImages.php';

// no/invalid id - redirect
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: ?page=events");
}

if (!constant("API_URL")) {
    echo 'Api address not set';
    return;
}

$apiModule = "/event";
$id = intval($_GET['id']);


$apiRequest = new APIRequest(constant("API_URL"), $apiModule, $id, $queryString);
$results = $apiRequest->fetchApiData();

if (isset($results['Error']) || !$results) {
    echo 'API request failed';
    return;
}

$pastEvent = (date('Y-m-d') > $results['EventDate']);
?>

<main class="event-detail-container container">
    <div>
        <a class="btn btn-more" href="<?= $_SERVER['HTTP_REFERER'] ?>">Back to previous page</a>
    </div>
    <div class="event-overview">
        <span class="event-name">ProMMA <?= $results['EventID'] ?></span>
        <span class="event-date"><?= DateTime::createFromFormat('Y-m-d', $results['EventDate'])->format('d F Y, h:i A T') ?></span>
        <span class="event-location"><?= $results['EventLocation'] ?></span>
    </div>
    <h2><?= ($pastEvent ? 'Results' : 'Upcoming') ?> </h2>
    <hr/>
    <?php
    //    foreach ($results['Fights'] as $fight) {
    for ($fightIndex = sizeof($results['Fights']) - 1; $fightIndex >= 0; $fightIndex--) {
        $fight = $results['Fights'][$fightIndex];
        $femaleFight = stripos($fight['WeightClass'], 'women') !== false;

        // get random images for athletes - images will never be the same
        unset($athleteOneImage, $athleteTwoImage);
        $athleteOneImage = (!$femaleFight ? $maleHeadshots[rand(0, sizeof($maleHeadshots) - 1)] : $femaleHeadshots[rand(0, sizeof($femaleHeadshots) - 1)]);

        while (!isset($athleteTwoImage) || $athleteOneImage == $athleteTwoImage) {
            $athleteTwoImage = (!$femaleFight ? $maleHeadshots[rand(0, sizeof($maleHeadshots) - 1)] : $femaleHeadshots[rand(0, sizeof($femaleHeadshots) - 1)]);
        }

        $athleteOneName = str_replace(" ", "<br />", $fight['Athletes'][0]['AthleteName']);
        $athleteTwoName = str_replace(" ", "<br />", $fight['Athletes'][1]['AthleteName']);

        // indicate whether a fight is a title bout or not
        $boutType = $fight['WeightClass'] . ($fight['TitleBout'] == 1 ? ' Title' : '');

        // generate a round time for end of fight
        $timeMins = rand(0, 5);
        $timeSecs = ($timeMins == 5 ? 0 : rand(0, 59));
        $fightTime = ($pastEvent ? sprintf('%02d:%02d', $timeMins, $timeSecs) : 'TBC');

        ?>
        <!--        Fight-->
        <div class="fight row" onclick="window.location='?page=fight&id=<?= $fight['FightID'] ?>'">
            <div class="col-4">
                <div class="row">
                    <div class="athlete-img col-6">
                        <img src="<?= $athleteOneImage ?>"/>
                    </div>
                    <div class="col-6 text-uppercase">
                        <span class="fight-outcome badge bg-secondary">win</span>
                        <span class="athlete-name"><?= $athleteOneName ?></span>
                    </div>
                </div>
            </div>

            <div class="col-4 fight-detail">
                <span class="weight-class"><?= $boutType ?> Bout</span>
                <span class="versus">vs</span>
                <div class="row">
                    <div class="col-4">
                        <span class="item">Rounds</span>
                        <span class="value"><?= $fight['NumOfRounds'] ?></span>
                    </div>
                    <div class="col-4">
                        <span class="item">Time</span>
                        <span class="value"><?= $fightTime ?></span>
                    </div>
                    <div class="col-4">
                        <span class="item">Method</span>
                        <span class="value"><?= ($pastEvent ? 'DECISION UNANIMOUS' : 'TBC') ?></span>
                    </div>

                </div>

            </div>

            <div class="col-4">
                <div class="row">
                    <div class="col-6 text-uppercase text-end">
                        <span class="fight-outcome">win</span>
                        <span class="athlete-name"><?= $athleteTwoName ?></span>
                    </div>
                    <div class="athlete-img col-6">
                        <img src="<?= $athleteTwoImage ?>"/>
                    </div>
                </div>
            </div>

        </div>
        <!--        ./Fight-->
        <?php
    }
    ?>
</main>
