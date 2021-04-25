<?php

use helpers\APIRequest;
use helpers\TemplatesHelper;

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

$eventUrl = '?page=events'

?>

<main class="event-detail-container container">
    <a class="btn btn-more" href="<?= $eventUrl ?>">Back to Events</a>
    <div class="event-overview">
        <span class="event-name">ProMMA <?= $results['EventID'] ?></span>
        <span class="event-date"><?= DateTime::createFromFormat('Y-m-d', $results['EventDate'])->format('d F Y, h:i A T') ?></span>
        <span class="event-location"><?= $results['EventLocation'] ?></span>
    </div>

    <h2><i class="fas fa-list"></i> <?= ($pastEvent ? 'Results' : 'Upcoming') ?> </h2>
    <hr/>
    <?php
    //    foreach ($results['Fights'] as $fight) {
    for ($fightIndex = sizeof($results['Fights']) - 1; $fightIndex >= 0; $fightIndex--) {
        $fight = $results['Fights'][$fightIndex];
        $femaleFight = stripos($fight['WeightClass'], 'women') !== false;

        // get random images for athletes - images will never be the same
        unset($athleteOneImage, $athleteTwoImage);

        $athleteOne = $fight['Athletes'][0];
        $athleteTwo = $fight['Athletes'][1];

        $athleteOneName = str_replace(" ", "<br />", $athleteOne['AthleteName']);
        $athleteTwoName = str_replace(" ", "<br />", $athleteTwo['AthleteName']);

        // indicate whether a fight is a title bout or not
        $boutType = $fight['WeightClass'] . ($fight['TitleBout'] == 1 ? ' Title' : '');

        // was the outcome a draw?
        $winner = ($fight['WinnerAthleteID'])


        ?>
        <!--        Fight-->
        <div class="fight row" onclick="window.location='?page=fight&id=<?= $fight['FightID'] ?>'">
            <div class="col-4">
                <div class="row">
                    <div class="athlete-img col-6">
                        <img src="<?= $athleteOne['AthleteImage'] ?>"/>
                    </div>
                    <div class="col-6 text-uppercase">
                        <?= TemplatesHelper::outcomeBadge($athleteOne, $winner) ?>
                        <span class="athlete-name"><?= $athleteOneName ?></span>
                    </div>
                </div>
            </div>

            <div class="col-4 fight-detail">
                <span class="weight-class"><?= $boutType ?> Bout</span>
                <span class="versus">vs</span>
                <div class="row">
                    <div class="col-4">
                        <span class="item">Round</span>
                        <span class="value"><?= $fight['WinRound'] ?></span>
                    </div>
                    <div class="col-4">
                        <span class="item">Time</span>
                        <span class="value"><?= $fight['WinRoundTime'] ?></span>
                    </div>
                    <div class="col-4">
                        <span class="item">Method</span>
                        <span class="value"><?= ($pastEvent ? $fight['ResultDescription'] : 'TBC') ?></span>
                    </div>

                </div>
            </div>

            <div class="col-4">
                <div class="row">
                    <div class="col-6 text-uppercase text-end">
                        <?= TemplatesHelper::outcomeBadge($athleteTwo, $winner) ?>
                        <span class="athlete-name"><?= $athleteTwoName ?></span>
                    </div>
                    <div class="athlete-img col-6">
                        <img src="<?= $athleteTwo['AthleteImage'] ?>"/>
                    </div>
                </div>
            </div>

        </div>
        <!--        ./Fight-->
        <?php
    }
    ?>
</main>
