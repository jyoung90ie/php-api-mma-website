<?php

namespace templates;

use helpers\APIRequest;
use helpers\HelperFunctions;
use models\Event;


if (!constant("API_URL")) {
    echo 'Api address not set';
    return;
}

$apiModule = "/event";
$queryString['start'] = $queryString['start'] ?? 0;

$permissionModule = Event::PERMISSION_AREA;

$apiRequest = new APIRequest(API_URL, $apiModule, API_KEY, null, $queryString);
$results = $apiRequest->fetchApiData();

if (isset($results['Error']) || !$results) {
    echo 'API request failed';
    return;
}

$events = $results['data'];
?>
<main class="events-container container">
    <h2><i class="far fa-calendar-alt"></i> All events</h2>


    <div class="events-overview">
        <span class="total"><?= $results['totalResults'] ?> events</span>
        <?php
        if (HelperFunctions::hasPermission($permissionModule, 'CREATE')) {
            ?>
            <div class="mt-2 d-flex justify-content-center justify-content-md-end">
                <a href="?page=event&action=create" class="btn btn-outline-success btn-lg">Create</a>
            </div>
            <?php
        }
        ?>
    </div>
    <hr>
    <?php
    foreach ($events as $event) {
        HelperFunctions::displayEvent($event, $permissionModule);
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
