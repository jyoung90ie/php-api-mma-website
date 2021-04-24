<?php

namespace templates;

use helpers\APIRequest;
use helpers\TemplatesHelper;


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
        TemplatesHelper::displayEvent($event);
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
