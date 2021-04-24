<?php

use helpers\APIRequest;

include 'helpers/athleteImages.php';

if (!constant("API_URL")) {
    echo 'Api address not set';
    return;
}

$apiModule = "/event";
$queryString['start'] = $queryString['start'] ?? 0;


$apiRequest = new APIRequest(constant("API_URL"), $apiModule, null, $queryString);
$results = $apiRequest->fetchApiData();

if (isset($results['Error']) || !$results) {
    echo 'API request failed';
    return;
}

$events = $results['data'];
?>
<main class="events-container container">
    <ul class="h2 text-center list-inline header-font">
        <li class="list-inline-item active">Upcoming</li>
        <li class="list-inline-item">Past</li>
    </ul>

    <div class="text-center">
        <span><?= $results['totalResults'] ?> events</span>
    </div>
    <hr>
    <?php
    foreach ($events as $event) {

        $eventUrl = '?page=event&id=' . $event['EventID'];
        ?>
        <!-- Event -->
        <div class="event row" onclick="window.location='<?= $eventUrl ?>'">
            <div class="col-12 col-md-2 name">
                Pro MMA <?= sprintf('%03d', $event['EventID']) ?>
            </div>
            <div class="col-12 col-md-4 athlete-images">
                <div class="athlete-left">
                    <img src="https://dmxg5wxfqgb4u.cloudfront.net/styles/event_results_athlete_headshot/s3/%5Bdate%3Acustom%3AY%5D-%5Bdate%3Acustom%3Am%5D/67275_profile-galery_profile-picture_STERLING_ALJAMAIN_BELT.png" alt=""/>
                </div>
                <div class="athlete-right">
                    <img src="https://dmxg5wxfqgb4u.cloudfront.net/styles/event_results_athlete_headshot/s3/2020-07/YAN_PETR_07-11.png" alt=""/>
                </div>
            </div>
            <div class="col-12 col-md-6">
                <span class="headliner">
                    <a href="<?= $eventUrl ?>">FIGHTER1 VS FIGHTER2</a>
                </span>
                <span class="date"><?= DateTime::createFromFormat('Y-m-d', $event['EventDate'])->format('D, d F Y, h:i A T') ?></span>
                <span class="location"><?= $event['EventLocation'] ?></span>
            </div>
        </div>
        <!-- ./Event -->
        <?php
    }
    ?>

    <!-- Pagination -->
    <nav aria-label="Events page navigation">
        <ul class="pagination justify-content-center">
            <?= $apiRequest->displayPagination(); ?>
        </ul>
    </nav>
    <!-- ./Pagination -->
</main>