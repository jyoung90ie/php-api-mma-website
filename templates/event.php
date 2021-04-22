<?php

use helpers\APIRequest;

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


?>

<main class="event-detail-container container">
    <div class="text-center">
        <h2><?= $results['EventDate'] ?></h2>
        <h3><?= $results['EventLocation'] ?></h3>
    </div>
    <div class="row">
        <div class="offset-4 col-2">Number of Fights</div>
        <div class="col-2"><?= sizeof($results['Fights']) ?></div>
        <div class="col-6"></div>
        <div class="col-6"></div>
    </div>

    <?php
    foreach ($results['Fights'] as $fight) {
        $fightText = $fight['Athletes'][0]['AthleteName'] . ' vs ' . $fight['Athletes'][1]['AthleteName'];
        $fightText = strtoupper($fightText);

        ?>
        <div class="mt-1 mb-1 pt-3 pb-3 fight row" onclick="window.location='?page=fight&id=<?= $fight['FightID'] ?>'">

            <div class="col-2 "></div>
            <div class="col-2"><?= $fight['Athletes'][0]['AthleteName'] ?></div>
            <div class="col-1"> vs</div>
            <div class="col-3"><?= $fight['Athletes'][1]['AthleteName'] ?></div>
            <div class="col-3"><?= $fight['WeightClass'] ?></div>

        </div>
        <?php
    }
    ?>
</main>
