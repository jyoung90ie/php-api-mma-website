<?php

use helpers\APIRequest;

// no/invalid id - redirect
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo '<p>Invalid Fight ID</p>';
    return;
}

if (!constant("API_URL")) {
    echo 'Api address not set';
    return;
}

$apiModule = "/fight";
$id = intval($_GET['id']);


$apiRequest = new APIRequest(constant("API_URL"), $apiModule, $id, $queryString);
$results = $apiRequest->fetchApiData();

if (isset($results['Error']) || !$results) {
    echo 'API request failed';
    return;
}

?>

<main class="fight-detail-container container">
    <?php
    if (isset($_SERVER['HTTP_REFERER'])) {
        ?>
        <div>
            <a class="btn btn-more" href="<?= $_SERVER['HTTP_REFERER'] ?>">Back to previous page</a>
        </div>
        <?php
    }
    ?>
    <h2>Fight Breakdown</h2>
    <div class="fight-athletes row">
        <div class="col-6">Fighter 1</div>
        <div class="col-6">Fighter 2</div>
    </div>
    <div class="fight-outcome row">
        <div class="col-4">
            <span>Round</span>
            <span>5</span>
        </div>
        <div class="col-4">
            <span>Time</span>
            <span>5:00</span>
        </div>
        <div class="col-4">
            <span>Method</span>
            <span>DECISION - UNANIMOUS</span>
        </div>
    </div>
    <div class="fight-breakdown row">
        <div class="col-12 col-md-6">

        </div>
        <div class="col-12 col-md-6">

        </div>
    </div>
</main>