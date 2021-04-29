<?php

use helpers\APIRequest;
use helpers\HelperFunctions;
use models\Athlete;

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
$permissionModule = Athlete::PERMISSION_AREA;

$id = intval($_GET['id']);

$apiRequest = new APIRequest(API_URL, $apiModule, API_KEY, $id, $queryString);
$allData = $apiRequest->fetchApiData();
$athleteData = $allData['data'];

if (isset($athleteData['Error']) || !$athleteData) {
    HelperFunctions::addNotification('Athlete does not exist');
    header("Location: ?page=events");
}

$numOfFights = sizeof($athleteData['Fights']);


// variables for back button
$referrer = $_SERVER['HTTP_REFERER'] ?? '';
$backButtonUrl = '';

if (stripos($referrer, 'search') !== false) {
    $backButtonUrl = '?page=search';
    $backButtonText = 'Back to Search';
} elseif (stripos($referrer, 'fight') !== false) {
    $backButtonUrl = $referrer;
    $backButtonText = 'Back to Fight';
} else {
    $backButtonUrl = '?page=index';
    $backButtonText = 'Back to Homepage';
}


if ($numOfFights > 0) {

// store data from api response
    $totalStrikesLanded = $athleteData['TotalStrikesLanded'] ?? 0;
    $totalStrikesThrown = $athleteData['TotalStrikesThrown'] ?? 0;
    $totalSignificantStrikesLanded = $athleteData['TotalSignificantStrikesLanded'] ?? 0;
    $totalSignificantStrikesThrown = $athleteData['TotalSignificantStrikesThrown'] ?? 0;
    $totalTakedownsLanded = $athleteData['TotalTakedownsLanded'] ?? 0;
    $totalTakedownsThrown = $athleteData['TotalTakedownsThrown'] ?? 0;
    $totalFights = $athleteData['TotalFights'] ?? 0;
    $totalWins = $athleteData['TotalWins'] ?? 0;
    $totalDraws = $athleteData['TotalDraws'] ?? 0;
    $totalDecisionWins = $athleteData['TotalDecisionWins'] ?? 0;
    $totalSubmissions = $athleteData['TotalSubmissions'] ?? 0;
    $totalLoses = $totalFights - $totalWins - $totalDraws;


// process vars for displaying
    $percentStrikesLanded = intval($totalStrikesLanded / $totalStrikesThrown * 100);
    $percentStrikesNotLanded = 100 - $percentStrikesLanded;
    $percentSigStrikesLanded = intval($totalSignificantStrikesLanded / $totalSignificantStrikesThrown * 100);
    $percentSigStrikesNotLanded = 100 - $percentStrikesLanded;
    $percentTakedownsLanded = intval($totalTakedownsLanded / $totalTakedownsThrown * 100);
    $percentTakedownsNotLanded = 100 - $percentStrikesLanded;
    $fightRecord = $totalWins . 'W ' . $totalDraws . 'D ' . $totalLoses . 'L';

    $percentDecisionWins = intval($totalDecisionWins / $totalWins * 100);
    $percentSubmissionWins = intval($totalSubmissions / $totalWins * 100);
    $percentOtherWins = 100 - $percentDecisionWins - $percentSubmissionWins;


// create data array for chart
    $chartInputData = [
        [
            'id' => 'totalWins',
            'title' => 'Wins',
            'labels' => "['Decision', 'Submission', 'Other']",
            'colours' => "['#2a6a99', '#5596D4', '#A7CAE9']",
            'data' => "[20, 30, 50]",
            'chartType' => 'doughnut'
        ],
        [
            'id' => 'totalStrikes',
            'title' => 'Total Strikes',
            'labels' => "['Landed', 'Missed']",
            'data' => "[$percentStrikesLanded, $percentStrikesNotLanded]",
            'chartType' => 'doughnut'
        ],
        [
            'id' => 'totalSignificantStrikes',
            'title' => 'Total Significant Strikes',
            'labels' => "['Landed', 'Missed']",
            'data' => "[$percentSigStrikesLanded, $percentSigStrikesNotLanded]",
            'chartType' => 'doughnut'
        ],
        [
            'id' => 'totalTakeDowns',
            'title' => 'Total Takedowns',
            'labels' => "['Landed', 'Missed']",
            'data' => "[$percentTakedownsLanded, $percentTakedownsNotLanded]",
            'chartType' => 'doughnut'
        ],


    ];
}

?>

    <main class="athlete-container container">
        <h1><?= $athleteData['AthleteName'] ?></h1>
        <div class="mb-5">
            <a class="btn btn-more" href="<?= $backButtonUrl ?>"><?= $backButtonText ?></a>
        </div>
        <div class="athlete-image">
            <img src="<?= $athleteData['AthleteImage'] ?>" alt="Image of <?= $athleteData['AthleteName'] ?>">
        </div>
        <div class="athlete-overview">
            <h2>Athlete Stats</h2>
            <?php
            if ($numOfFights == 0) {
                echo '<h3>Athlete has no fights</h3>';
            } else {
            ?>
            <div class="d-flex flex-column p-4">
                <div class="p-2">
                    <span class="type">Total Fights</span>
                    <span class="value"><?= $totalFights ?></span>
                </div>
                <div class="p-2">
                    <span class="type">Record</span>
                    <span class="value"><?= $fightRecord ?></span>
                </div>
            </div>
            <div class="athlete-stats row">
                <?php displayChart($chartInputData); ?>
            </div>
        </div>
        <h2>Fight Results</h2>
        <?php HelperFunctions::displayFights($athleteData, $permissionModule) ?>
        <!-- Pagination -->
        <nav aria-label="Athlete fights page navigation">
            <ul class="pagination justify-content-center">
                <?= $apiRequest->displayPagination(); ?>
            </ul>
        </nav>
        <?php
        }
        ?>
    </main>
<?php
/**
 * @param array $inputData
 */
function displayChart(array $inputData)
{
    foreach ($inputData as $chart) {
        ?>
        <div class="col-12 col-md-6 col-lg-3">
            <div class="chart">
                <canvas id="<?= $chart['id'] ?>"></canvas>
            </div>
            <span class="chart-text"><?= $chart['title'] ?></span>
        </div>
        <?php
    }

    ?>
    <script>
        <?php
        // generate javascript for charts
        foreach ($inputData as $chart) {
            $colours = "['#bf0d0d', '#ddd']";
            if (isset($chart['colours'])) {
                $colours = $chart['colours'];
            }

            echo "var {$chart['id']} = document.getElementById('{$chart['id']}');\n";
            echo "var chart{$chart['id']} = new Chart({$chart['id']}, 
                    {
                        type: '{$chart['chartType']}',
                        data: 
                        {
                            labels: {$chart['labels']},
                            datasets: [{
                                label: '',
                                data: {$chart['data']},
                                backgroundColor: $colours
                            }]
                        },
                        options: { 
                            scales: {   y: { display: false, beginAtZero: true },
                                        x: { display: false, grid: { display: false }, },
                            },
                            plugins: {  
                                legend: { display: true },
                                tooltip: {
                                    callbacks: {
                                        label: function(context) {
                                            var label = context.label + ': ' + context.parsed + '%';
                                            
                                            return label;    
                                        }
                                    }
                                }
                            }
                        }
                    });\n";
        }
        ?>
    </script>
    <?php
}