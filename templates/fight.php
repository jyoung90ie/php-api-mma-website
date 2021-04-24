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
        <div class="fight-overview">
            <span class="description">Results</span>
        </div>
        <div class="fight-breakdown">

        </div>
    </main>