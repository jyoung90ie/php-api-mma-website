<?php

namespace helpers;

use DateTime;

class HelperFunctions
{
    /**
     * Creates HTML to display a single clickable event which is used for the event listing page.
     *
     * @param array $events containing all event data
     */
    static function displayEvents(array $events, string $permissionModule)
    {
        if (isset($events['Headliners'])) {

            // generate athlete images
            $athletes = $events['Headliners'];
            unset($athleteOneImage, $athleteTwoImage);

            // variables for rendering in template
            $athleteOne = $athletes[0];
            $athleteTwo = $athletes[1];

            $athleteOneSurname = explode(" ", $athleteOne['AthleteName']);
            $athleteOneSurname = $athleteOneSurname[sizeof($athleteOneSurname) - 1];

            $athleteTwoSurname = explode(" ", $athleteTwo['AthleteName']);
            $athleteTwoSurname = $athleteTwoSurname[sizeof($athleteTwoSurname) - 1];

            $eventHeadliner = $athleteOneSurname . ' vs ' . $athleteTwoSurname;
            $eventHeadliner = strtoupper($eventHeadliner);
        } else {
            // dummy data - no fights added yet
            $athleteOne['AthleteName'] = 'TBC';
            $athleteTwo['AthleteName'] = $athleteOne['AthleteName'];
            $athleteOne['AthleteImage'] = 'https://www.ufc.com/themes/custom/ufc/assets/img/silhouette-headshot-male.png';
            $athleteTwo['AthleteImage'] = $athleteOne['AthleteImage'];

            $eventHeadliner = 'TBC VS TBC';
        }

        $eventUrl = '?page=event&id=' . $events['EventID'];

        ?>
        <!-- Event -->
        <div class="event row" onclick="window.location='<?= $eventUrl ?>'">
            <div class="col-12 col-md-2 name">
                Pro MMA <?= sprintf('%03d', $events['EventID']) ?>
            </div>
            <div class="col-12 col-md-4 athlete-images">
                <div class="athlete-left">
                    <img src="<?= $athleteOne['AthleteImage'] ?>" alt="<?= $athleteOne['AthleteName'] ?>"/>
                </div>
                <div class="athlete-right">
                    <img src="<?= $athleteTwo['AthleteImage'] ?>" alt="<?= $athleteTwo['AthleteName'] ?>"/>
                </div>
            </div>
            <div class="col-12 col-md-6">
                <div class="row text-center text-md-left">
                    <div class="col-12 col-md-8 ">
                        <span class="headliner">
                            <a href="<?= $eventUrl ?>"><?= $eventHeadliner ?></a>
                        </span>
                        <span class="date"><?= DateTime::createFromFormat('Y-m-d', $events['EventDate'])->format('D, d F Y, h:i A T') ?></span>
                        <span class="location"><?= $events['EventLocation'] ?></span>
                    </div>
                    <div class="col-12 col-md-4">
                        <span><a href="<?= $eventUrl ?>" class="btn btn-sm btn-more">View</a></span>
                        <?php
                        if (HelperFunctions::hasPermission($permissionModule, 'UPDATE')) {
                            echo '<a href="' . $eventUrl . '&action=update" class="btn btn-sm btn-outline-secondary m-1">Update</a>';
                        }
                        if (HelperFunctions::hasPermission($permissionModule, 'DELETE')) {
                            echo '<a href="' . $eventUrl . '&action=delete" class="btn btn-sm btn-outline-danger m-1">Delete</a>';
                        }
                        ?>
                    </div>
                </div>

            </div>
        </div>
        <!-- ./Event -->
        <?php
    }


    /**
     * Creates HTML to display a single clickable fight which is used to create a list of fights.
     *
     * @param array $event containing all fight data
     */
    static function displayFights(array $fights, string $permissionModule)
    {
        for ($fightIndex = sizeof($fights['Fights']) - 1; $fightIndex >= 0; $fightIndex--) {
            $fight = $fights['Fights'][$fightIndex];

            $athleteOne = $fight['Athletes'][0];
            $athleteTwo = $fight['Athletes'][1];

            $athleteOneName = str_replace(" ", "<br />", $athleteOne['AthleteName']);
            $athleteTwoName = str_replace(" ", "<br />", $athleteTwo['AthleteName']);

            // indicate whether a fight is a title bout or not
            $boutType = $fight['WeightClass'] . ($fight['TitleBout'] == 1 ? ' Title' : '');

            $winRound = $fight['WinRound'] ?? 'TBC';
            $winRoundTime = $fight['WinRoundTime'] ?? 'TBC';
            $outcome = $fight['ResultDescription'] ?? 'TBC';

            $winnerId = ($outcome == 'TBC' ? -1 : $fight['WinnerAthleteID']); // use -1 to indicate fight result is TBC


            ?>
            <!--        Fight-->
            <div class="fight row" onclick="window.location='?page=fight&id=<?= $fight['FightID'] ?>'">
                <div class="col-4">
                    <div class="row">
                        <div class="athlete-img col-12 col-md-6">
                            <img src="<?= $athleteOne['AthleteImage'] ?>"/>
                        </div>
                        <div class="col-12 col-md-6 text-uppercase">
                            <?= HelperFunctions::displayOutcomeBadge($athleteOne, $winnerId) ?>
                            <span class="athlete-name"><?= $athleteOneName ?></span>
                        </div>
                    </div>
                </div>

                <div class="col-4 fight-detail">
                    <span class="weight-class"><?= $boutType ?> Bout</span>
                    <span class="versus">vs</span>
                    <div class="row">
                        <div class="col-12 col-md-4">
                            <span class="item">Round</span>
                            <span class="value"><?= $winRound ?></span>
                        </div>
                        <div class="col-12 col-md-4">
                            <span class="item">Time</span>
                            <span class="value"><?= $winRoundTime ?></span>
                        </div>
                        <div class="col-12 col-md-4">
                            <span class="item">Method</span>
                            <span class="value"><?= $outcome ?></span>
                        </div>

                    </div>
                </div>

                <div class="col-4">
                    <div class="row ">
                        <div class="col-12 order-1 order-md-0 col-md-6 text-uppercase text-end">
                            <?= HelperFunctions::displayOutcomeBadge($athleteTwo, $winnerId) ?>
                            <span class="athlete-name"><?= $athleteTwoName ?></span>
                        </div>
                        <div class="athlete-img order-0 order-md-1 col-12 col-md-6">
                            <img src="<?= $athleteTwo['AthleteImage'] ?>"/>
                        </div>
                    </div>
                </div>

            </div>
            <!--        ./Fight-->
            <?php
        }
    }

    /**
     * Renders side-by-side athlete fight statistics for comparison. This will loop through array and output a new row
     * for each fight stat.
     *
     * @param array $fightStats array of fight statistics for two athletes
     * @param string $redAthleteName name of athlete that will be displayed in red - must match name in $fightStats
     * @param string $blueAthleteName name of athlete that will be displayed in blue - must match name in $fightStats
     */
    static function displayFightComparisonData(array $fightStats, string $redAthleteName, string $blueAthleteName)
    {
        if (!isset($fightStats) || !isset($redAthleteName) || !isset($blueAthleteName)) {
            return;
        }

        // store chart data
        $charts = [];

        foreach ($fightStats as $fightStat) {
            if (sizeof($fightStats) > 0) {
                $type = $fightStat['type'] ?? '';
                $redStats = $fightStat[$redAthleteName];
                $blueStats = $fightStat[$blueAthleteName];

                $chartType = 'bar';

                // check if stats contain thrown and landed, if not, changed output
                if (isset($redStats['landed']) && isset($redStats['thrown'])) {
                    $redThrown = $redStats['thrown'] ?? 0;
                    $redLanded = $redStats['landed'] ?? 0;

                    $blueThrown = $blueStats['thrown'] ?? 0;
                    $blueLanded = $blueStats['landed'] ?? 0;

                    $redThrownPercent = ($redThrown > 0 ? ($redLanded / $redThrown) * 100 : 0);
                    $blueThrownPercent = ($blueThrown > 0 ? ($blueLanded / $blueThrown) * 100 : 0);

                    $redThrownText = sprintf('%d%% of %d', $redThrownPercent, $redThrown) ?? '';
                    $blueThrownText = sprintf('%d%% of %d', $blueThrownPercent, $blueThrown) ?? '';
                } else {
                    $chartType = 'doughnut';
                    // does not contain thrown and landed data points
                    $redThrownText = '';
                    $blueThrownText = '';

                    // use reset() to get first element
                    $redLanded = reset($redStats);
                    $blueLanded = reset($blueStats);
                }

                $chartId = str_replace(' ', '', ucwords($type));

                $chartData = [
                    'id' => $chartId,
                    'data' => [
                        'label' => $type,
                        'landed' => '[' . $redLanded . ', ' . $blueLanded . ']', // value should be a string
                    ],
                    // bar chart if thrown values exist, otherwise, doughnut chart
                    'chartType' => $chartType
                ];

                array_push($charts, $chartData);

                ?>
                <div class="fight-stats row">
                    <div class="order-lg-0 col-6 col-lg-4">
                        <span class="total-landed red"><?= $redLanded ?></span>
                        <span class="total-thrown"><?= $redThrownText ?></span>
                    </div>

                    <div class="order-lg-2 col-6 col-lg-4">
                        <span class="total-landed blue"><?= $blueLanded ?></span>
                        <span class="total-thrown"><?= $blueThrownText ?></span>
                    </div>
                    <div class="order-lg-1 col-12 col-lg-4 charts">
                        <div class="chart">
                            <canvas id="<?= $chartId ?>"></canvas>
                        </div>
                        <span class="chart-text"><?= $type ?></span>
                    </div>
                </div>
                <?php
            }
        }
        ?>
        <script>
            <?php
            // generate javascript for charts
            foreach ($charts as $chart) {

                echo "var {$chart['id']} = document.getElementById('{$chart['id']}');\n";
                echo "var chart{$chart['id']} = new Chart({$chart['id']}, 
                    {
                        type: '{$chart['chartType']}',
                        data: 
                        {
                            labels: ['$redAthleteName', '$blueAthleteName'],
                            datasets: [{
                                label: '{$chart['data']['label']}',
                                data: {$chart['data']['landed']},
                                backgroundColor: ['rgb(191, 13, 13)', 'rgba(20, 74, 142, 1)']
                            }]
                        },
                        options: { 
                            scales: { 
                                y: {
                                    display: false,
                                    beginAtZero: true 
                                },
                                x: {
                                    display: false,
                                    grid: { display: false },
                                },
                            },
                            plugins: {
                                legend: { display: false } 
                            },
                        }
                            
                    });\n";
            }
            ?>
        </script>
        <?php
    }


    /**
     * Generates links for the navbar - highlighting the active page.
     *
     * @param array $navbarPages consisting of two elements: link and text
     * @param string $activePage the active page in the format '?page=pageName'
     * @return string generated HTML for navbar
     */
    static function displayNavBar(array $navbarPages, string $activePage): string
    {
        $outputHTML = '';
        foreach ($navbarPages as $page) {
            if (ltrim($page['link'], './') == $activePage) {
                $outputHTML .= '                <li class="nav-item">
                    <a class="nav-link active" aria-current="page" href="' . $page['link'] . '">' . $page['text'] . '</a>
                </li>' . "\n";
            } else {
                $outputHTML .= '                <li class="nav-item">
                    <a class="nav-link" href="' . $page['link'] . '">' . $page['text'] . '</a>
                </li>';
            }
        }

        return $outputHTML;
    }


    /**
     * Generates links for user account management (e.g. login/register)
     *
     * @param bool $mainMenu set to true when this is called as part of the main menu
     *  this will add class to hide links when menu is not collapsed
     * @return string generated HTML for navbar
     */
    static function displayUserMenu(bool $mainMenu = false): string
    {
        $navbarPages = [
            ['text' => 'Login', 'link' => '?page=login', 'showLoggedIn' => false],
            ['text' => 'Logout', 'link' => '?page=logout', 'showLoggedIn' => true],
            ['text' => 'Register', 'link' => '?page=register', 'showLoggedIn' => false],
        ];

        $outputHTML = '';
        foreach ($navbarPages as $page) {

            if ((isset($_SESSION['User']) && $page['showLoggedIn']) ||
                (!isset($_SESSION['User']) && !$page['showLoggedIn'])) {
                // IF LOGGED IN -> only show logged in pages; OTHERWISE -> only show logged out pages
                $outputHTML .= '                <li class="nav-item' . ($mainMenu ? ' d-lg-none' : '') . '">
                    <a class="nav-link" href="' . $page['link'] . '">' . $page['text'] . '</a>
                </li>' . "\n";

            }
        }

        return $outputHTML;
    }

    /**
     * Creates HTML for indicating the winner of a fight
     *
     * @param $athlete array of athlete data
     * @param int|null $winnerId athleteId for the winner
     * @return string of HTML
     */
    static function displayOutcomeBadge(array $athlete, ?int $winnerId): string
    {
        if (is_null($winnerId)) {
            $outputHTML = '<span class="fight-outcome-badge draw">draw</span>';
        } else if ($winnerId == $athlete['AthleteID']) {
            $outputHTML = '<span class="fight-outcome-badge win">win</span>';
        } else {
            $outputHTML = '<span class="fight-outcome-badge"></span>';
        }

        return $outputHTML;
    }


    /**
     * Creates HTML to display errors to user.
     *
     * @param $data
     * @return string
     */
    static function displayApiError($data): string
    {
        if (!is_array($data)) {
            return '';
        }

        $outputHTML = '';
        if (isset($data['Error'])) {
            $outputHTML = '
            <div class="alert alert-danger" role="alert">
                ' . $data['Error'] . '
            </div>';
        }

        return $outputHTML;
    }

    /**
     * Creates HTML to display messages to the user, notifying them of something.
     *
     * Messages are set in the session variable, Messages.
     *
     * @return string
     */
    static function displayNotifications(): string
    {
        $outputHTML = '';
        if (isset($_SESSION['Notifications'])) {
            foreach ($_SESSION['Notifications'] as $notification) {
                $outputHTML .= '            <div class="alert alert-primary alert-dismissible fade show text-center mb-5" role="alert">';
                $outputHTML .= $notification . '<br />';
                $outputHTML .= '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
                $outputHTML .= '</div>';
            }

            unset($_SESSION['Notifications']);
        }

        return $outputHTML;
    }

    /**
     * Adds a notification message to a session variable which will be unset when it is displayed
     *
     * @param string $message to be displayed to user
     * @return void
     */
    static function addNotification(string $message): void
    {
        if (!isset($_SESSION['Notifications'])) {
            $_SESSION['Notifications'] = [];
        }
        array_push($_SESSION['Notifications'], $message);
    }

    /**
     * Determines whether a user has permission to access the specified area of the website.
     *
     * @param string $permissionModule area of the website (e.g. events/users/etc.)
     * @param string $permissionType type of access required (e.g. read/update/create/etc.)
     * @return bool true for permitted; false for denied.
     */
    static function hasPermission(string $permissionModule, string $permissionType): bool
    {
        if (!isset($_SESSION['User']['Permissions'])) {
            return false;
        }
        $permission = ['Area' => $permissionModule, 'Type' => $permissionType];

        return in_array($permission, $_SESSION['User']['Permissions']);
    }


    /**
     * Checks if a user has permission to access the page, if not they are redirected to the homepage and shown
     * a notification.
     *
     * @param string $permissionModule area of the website (e.g. events/users/etc.)
     * @param string $permissionType type of access required (e.g. read/update/create/etc.)
     */
    static function checkPermission(string $permissionModule, string $permissionType): void
    {
        if (!self::hasPermission($permissionModule, $permissionType)) {
            self::addNotification('You are not authorised to access the requested page or it does not exist');
            header('Location: ?page=index');
        }
    }
}


?>
