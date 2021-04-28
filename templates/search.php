<?php

use helpers\APIRequest;
use helpers\HelperFunctions;

if (!constant("API_URL")) {
    echo '<p>Api address not set</p>';
    return;
}

const MIN_LENGTH = 5; // minimum length of search term in characters

$apiEndPoint = API_URL . '/search?apiKey=' . API_KEY;


if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $fields = [
        'searchTerm'
    ];
    $validationErrors = [];

    foreach ($fields as $field) {
        if (!isset($_POST[$field]) || empty($_POST[$field])) {
            $validationErrors[$field] = 'Field ' . $field . ' must be populated';
        } else {
            if (strlen($_POST[$field]) < MIN_LENGTH) {
                $validationErrors[$field] = 'Field ' . $field . ' must be at least ' . MIN_LENGTH . ' characters long';
            }
        }
    }


    // api processing
    if (sizeof($validationErrors) == 0) {
        // convert form data into array
        $formContents = file_get_contents("php://input");
        parse_str($formContents, $formContents);
        // remove whitespace
        $formContents['searchTerm'] = trim($formContents['searchTerm']);

        $jsonContents = json_encode($formContents);

        $apiRequest = curl_init();
        curl_setopt($apiRequest, CURLOPT_URL, $apiEndPoint);
        curl_setopt($apiRequest, CURLOPT_POST, true);
        curl_setopt($apiRequest, CURLOPT_POSTFIELDS, $jsonContents);
        curl_setopt($apiRequest, CURLOPT_RETURNTRANSFER, true);

        $searchResponse = json_decode(curl_exec($apiRequest), true);
        $metaResponse = curl_getinfo($apiRequest);

        // if created, header will return 200
        if (isset($metaResponse['http_code']) && $metaResponse['http_code'] == 200) {

        }
    }
}

$searchTerm = trim(htmlspecialchars($_POST['searchTerm'] ?? ''));
?>

<main class="container">
    <h1>Search for Athletes</h1>

    <?= \helpers\HelperFunctions::displayApiError($authResponse ?? []); ?>
    <form action="" method="post">
        <div class="search-box">
            <div>
                <input type="text" name="searchTerm" class="form-control" placeholder="Athlete name"
                       value="<?= $searchTerm ?>">
                <span id="searchTermErrors" class="form-text error">
                    <?= $validationErrors['searchTerm'] ?? '' ?>
                </span>
            </div>
            <div>
                <button type="submit" class="btn btn-primary">Search</button>
            </div>
        </div>
    </form>

    <!-- search results -->
    <?php

    if (isset($searchResponse['Error'])) {
        ?>
        <h2 class="error">No results found</h2>
        <?php
    } elseif (isset($searchResponse)) {
        $numOfResults = sizeof($searchResponse);

        ?>
        <h2>Displaying <?= $numOfResults ?> results for '<?= $searchTerm ?>'</h2>
        <?php

        foreach ($searchResponse as $athlete) {
            ?>

            <div class="fight row" onclick="window.location='?page=athlete&id=<?= $athlete['AthleteID'] ?>'">
                <div class="col-4">
                    <div class="row">
                        <div class="athlete-img col-12 col-md-6">
                            <img src="<?= $athlete['AthleteImage'] ?>"/>
                        </div>
                        <div class="col-12 col-md-6 text-uppercase">
                            <span class="athlete-name"><?= $athlete['AthleteName'] ?></span>
                        </div>
                    </div>
                </div>

                <div class="col-8 fight-detail">
                    <span class="weight-class"><?= $athlete['WeightClass'] ?? 'Unknown' ?></span>
                    <div class="row">
                        <div class="col-12 col-md-4">
                            <span class="item">Total Fights</span>
                            <span class="value"><?= $athlete['TotalFights'] ?? 'Unknown' ?></span>
                        </div>
                        <div class="col-12 col-md-4">
                            <span class="item">Last Fought On</span>
                            <span class="value"><?= $athlete['EventDate'] ?? 'Unknown' ?></span>
                        </div>
                        <div class="col-12 col-md-4">
                            <span class="item">Last Fought At</span>
                            <span class="value"><?= $athlete['EventLocation'] ?? 'Unknown' ?></span>
                        </div>

                    </div>
                </div>
            </div>
            <?php
        }
    }
    ?>
</main>