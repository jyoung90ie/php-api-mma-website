<?php

namespace templates;

use models\Event;
use helpers\APIRequest;
use helpers\HelperFunctions;

if (!constant("API_URL")) {
    echo 'Api address not set';
    return;
}


// get 2 most recent events
$apiModule = "/event";
$queryString['start'] = 0;
$queryString['limit'] = 2;

$apiRequest = new APIRequest(API_URL, $apiModule, API_KEY, null, $queryString);
$results = $apiRequest->fetchApiData();

if (isset($results['Error']) || !$results) {
    echo 'API request failed';
    return;
}

$events = $results['data'];

$apiModule = 'athlete';
$queryString['random'] = 1; // needed to invoke getRandom() function

$apiRequest = new APIRequest(API_URL, $apiModule, API_KEY, null, $queryString);
$apiResponse = $apiRequest->fetchApiData();
$athleteData = $apiResponse['data'];

?>

<main class="content container-fluid">
    <!-- FeaturedAthlete -->
    <div class="featured-athletes container">
        <h2><i class="fas fa-users"></i> Featured Athletes</h2>
        <div class="row justify-content-evenly">
            <?php
            foreach ($athleteData as $athlete) {
                $athleteUrl = '?page=athlete&id=' . $athlete['AthleteID'];
                ?>
                <!-- Athlete -->
                <div class="card mb-3 col-lg-3 m-2" onclick="window.location='<?= $athleteUrl ?>'">
                    <span class="athlete-name"><?= $athlete['AthleteName'] ?></span>
                    <img src="<?= $athlete['AthleteImage'] ?>" class="card-img" alt="Headshot image">
                    <div class="d-flex flex-row justify-content-evenly athlete-stats">
                        <div class="flex-column">
                            <div>Fights</div>
                            <div><?= $athlete['TotalFights'] ?></div>
                        </div>
                        <div class="flex-column">
                            <div>Wins</div>
                            <div><?= $athlete['TotalWins'] ?></div>
                        </div>
                        <div class="flex-column">
                            <div>Submissions</div>
                            <div><?= $athlete['TotalSubmissions'] ?></div>
                        </div>
                    </div>

                </div>
                <!-- ./Athlete -->
                <?php
            }
            ?>
        </div>
    </div>
    <!-- ./FeaturedAthletes -->

    <!-- EventsContainer -->
    <div class="home-events-container">
        <h2><i class="fas fa-calendar-alt"></i> Events</h2>
        <div class="container">
            <!-- Events -->
            <?php
            foreach ($events as $event) {
                HelperFunctions::displayEvents($event, Event::PERMISSION_AREA);
            }
            ?>
            <!-- ./Events -->
            <div class="text-center">
                <a href="?page=events" class="btn btn-more">See More</a>
            </div>
        </div>
    </div>
    <!-- ./EventsContainer -->
</main>
