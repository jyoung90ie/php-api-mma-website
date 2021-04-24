<?php

use helpers\APIRequest;

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
    <div class="events-overview text-center">
        <span class="description">All events</span>
        <span class="total"><?= $results['totalResults'] ?> events</span>
    </div>
    <hr>
    <?php
    foreach ($events as $event) {
        // generate athlete images
        $athletes = $event['Headliners'];
        unset($athleteOneImage, $athleteTwoImage);

        // variables for rendering in template
        $athleteOne = $athletes[0];
        $athleteTwo = $athletes[1];

        $athleteOneSurname = explode(" ", $athleteOne['AthleteName']);
        $athleteOneSurname = $athleteOneSurname[sizeof($athleteOneSurname) - 1];

        $athleteTwoSurname = explode(" ", $athleteTwo['AthleteName']);
        $athleteTwoSurname = $athleteTwoSurname[sizeof($athleteTwoSurname) - 1];

        $eventHeadliner = $athleteOneSurname . ' vs ' . $athleteTwoSurname;
        $eventHeadliner = strtoupper($eventHeadliner);

        $eventUrl = '?page=event&id=' . $event['EventID'];

        ?>
        <!-- Event -->
        <div class="event row" onclick="window.location='<?= $eventUrl ?>'">
            <div class="col-12 col-md-2 name">
                Pro MMA <?= sprintf('%03d', $event['EventID']) ?>
            </div>
            <div class="col-12 col-md-4 athlete-images">
                <div class="athlete-left">
                    <img src="<?= $athleteOne['AthleteImage'] ?>" alt="<?= $athleteOne['AthleteName'] ?>"/>
                </div>
                <div class="athlete-right">
                    <img src="<?= $athleteTwo['AthleteImage'] ?>" alt="<?= $athleteTwo['AthleteName'] ?>"/>
                </div>
            </div>
            <div class="col-12 col-md-6">
                <span class="headliner">
                    <a href="<?= $eventUrl ?>"><?= $eventHeadliner ?></a>
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