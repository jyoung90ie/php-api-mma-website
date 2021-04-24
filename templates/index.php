<?php

namespace templates;

use helpers\APIRequest;
use helpers\TemplatesHelper;

if (!constant("API_URL")) {
    echo 'Api address not set';
    return;
}

// get 2 most recent events
$apiModule = "/event";
$queryString['start'] = 0;
$queryString['limit'] = 2;


$apiRequest = new APIRequest(constant("API_URL"), $apiModule, null, $queryString);
$results = $apiRequest->fetchApiData();

if (isset($results['Error']) || !$results) {
    echo 'API request failed';
    return;
}

$events = $results['data'];

?>

<main class="content container-fluid">
    <!-- NewsContainer -->
    <div class="news container">
        <h2><i class="far fa-newspaper"></i> Latest News</h2>
        <div class="row">
            <!-- News1 -->
            <div class="card mb-3 col-lg-5">
                <div class="row no-gutters">
                    <div class="col-md-4 my-card-img">
                        <img src="images/news1.jpg" class="card-img" alt="...">
                    </div>
                    <div class="col-md-8">
                        <div class="card-body">
                            <h5 class="card-title">Fighter Retires</h5>
                            <p class="card-text">After wide speculation it has been confirmed that Fighter McFighter
                                has retired...</p>
                            <p class="card-text"><small class="text-muted">Last updated 6 hours ago</small></p>
                        </div>
                    </div>
                </div>
            </div>
            <!-- ./News1 -->
            <!-- News2 -->
            <div class="card mb-3 offset-lg-1 col-lg-5">
                <div class="row no-gutters">
                    <div class="col-md-4 my-card-img">
                        <img src="images/news2.jpg" class="card-img" alt="...">
                    </div>
                    <div class="col-md-8">
                        <div class="card-body">
                            <h5 class="card-title">Pro MMA 125 Date Confirmed</h5>
                            <p class="card-text">
                                Today it has been confirmed that Fighter A will face Fighter B for the belt on
                                24 August 2021. <br /> <br />The main card is still shaping up with news expected in
                                the
                                coming
                                weeks.
                            </p>
                            <p class="card-text"><small class="text-muted">Last updated 2 days ago</small></p>
                        </div>
                    </div>
                </div>
            </div>
            <!-- ./News2 -->
        </div>
        <!-- ./Horizontal Card -->
    </div>
    <!-- ./NewsContainer -->

    <!-- EventsContainer -->
    <div class="home-events-container">
        <h2><i class="fas fa-calendar-alt"></i> Upcoming Events</h2>
        <div class="container">
            <!-- Events -->
            <?php
            foreach ($events as $event) {
                TemplatesHelper::displayEvent($event);
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
