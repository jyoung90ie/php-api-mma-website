<?php

use helpers\APIRequest;
use helpers\HelperFunctions;

// no/invalid id - redirect
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: ?page=events");
}

if (!constant("API_URL")) {
    echo 'Api address not set';
    return;
}

$apiModule = "/event";
$eventPermissionModule = \models\Event::PERMISSION_AREA;
$fightPermissionModule = \models\Fight::PERMISSION_AREA;

$id = intval($_GET['id']);

$apiRequest = new APIRequest(API_URL, $apiModule, API_KEY, $id, $queryString);
$results = $apiRequest->fetchApiData();

if (isset($results['Error']) || !$results) {
    header("Location: ?page=events");
}

$pastEvent = (date('Y-m-d') > $results['EventDate']);
$eventUrl = '?page=events'

?>

<main class="event-detail-container container">
    <div class="d-flex flex-row">
        <div>
            <a class="btn btn-more" href="<?= $eventUrl ?>">Back to Events</a>
        </div>
        <div class="ms-auto">
            <?php
            if (HelperFunctions::hasPermission($fightPermissionModule, 'CREATE')) {
                echo '<a href="?page=fight&action=create&eventid=' . $id . '" class="mx-2 btn btn-outline-success">Add Fight</a>';
            }
            if (HelperFunctions::hasPermission($eventPermissionModule, 'UPDATE')) {
                echo '<a href="?page=event&action=update&id=' . $id . '" class="mx-2 btn btn-outline-secondary">Update Event</a>';
            }
            if (HelperFunctions::hasPermission($eventPermissionModule, 'DELETE')) {
                echo '<a href="?page=event&action=delete&id=' . $id . '" class="mx-2 btn btn-outline-danger">Delete Event</a>';
            }
            ?>
        </div>
    </div>
    <div class="event-overview">
        <span class="event-name">ProMMA <?= $results['EventID'] ?></span>
        <span class="event-date"><?= DateTime::createFromFormat('Y-m-d', $results['EventDate'])->format('d F Y, h:i A T') ?></span>
        <span class="event-location"><?= $results['EventLocation'] ?></span>
    </div>
    <h2><i class="fas fa-list"></i> <?= ($pastEvent ? 'Results' : 'Upcoming') ?> </h2>

    <hr/>

    <?php
    if (sizeof($results['Fights']) == 0) {
        echo '<h3 class="text-center p-3">This event does not yet have any fights</h3>';
    } else {
        HelperFunctions::displayFights($results, $fightPermissionModule);
    }
    ?>
</main>
