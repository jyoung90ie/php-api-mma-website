<?php

use helpers\APIRequest;
use helpers\HelperFunctions;

// no/invalid id - redirect
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo '<p>Invalid Athlete ID</p>';
    return;
}

if (!constant("API_URL")) {
    echo '<p>Api address not set</p>';
    return;
}

$apiModule = "/athlete";
$permissionModule = \models\Athlete::PERMISSION_AREA;

$id = intval($_GET['id']);

$apiRequest = new APIRequest(API_URL, $apiModule, API_KEY, $id, $queryString);
$athleteData = $apiRequest->fetchApiData();

if (isset($athleteData['Error']) || !$athleteData) {
    HelperFunctions::addNotification('Athlete does not exist');
    header("Location: ?page=events");
}


?>

<main class="athlete-container container">

    <h1><?= $athleteData['AthleteName'] ?></h1>
    <div class="athlete-image">
        <img src="<?= $athleteData['AthleteImage'] ?>" alt="Image of <?= $athleteData['AthleteName'] ?>">
    </div>
    <div class="athlete-overview">
        <h2>Athlete Stats</h2>
        <span>Total Fights</span>
        <span>Record</span>
    </div>
    </div>
    <h2>Fight Results</h2>
    <?php HelperFunctions::displayFights($athleteData, $permissionModule) ?>
</main>