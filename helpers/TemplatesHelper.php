<?php

namespace helpers;

use DateTime;

class TemplatesHelper
{
    /**
     * Creates HTML to display a single clickable event.
     *
     * @param array $event containing all event data
     */
    static function displayEvent(array $event)
    {
        if (!isset($event['Headliners'])) {
            // do not display events until a fight has been added (which contains headliners)
            return;
        }
        // generate athlete images
        $athletes = $event['Headliners'];
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

        $eventUrl = '?page=event&id=' . $event['EventID'];

        ?>
        <!-- Event -->
        <div class="event row" onclick="window.location='<?= $eventUrl ?>'">
            <div class="col-12 col-md-2 name">
                Pro MMA <?= sprintf('%03d', $event['EventID']) ?>
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
                        <span class="date"><?= DateTime::createFromFormat('Y-m-d', $event['EventDate'])->format('D, d F Y, h:i A T') ?></span>
                        <span class="location"><?= $event['EventLocation'] ?></span>
                    </div>
                    <div class="col-12 col-md-4">
                        <a href="<?= $eventUrl ?>" class="btn btn-more">View</a>
                    </div>
                </div>

            </div>
        </div>
        <!-- ./Event -->
        <?php
    }

    /**
     * @param array $fightStats
     */
    static function displayFightComparisonData(array $fightStats, string $redAthlete, string $blueAthlete)
    {
        if (!isset($fightStats) || !isset($redAthlete) || !isset($blueAthlete)) {
            return;
        }

        foreach ($fightStats as $fightStat) {
            if (sizeof($fightStats) > 0) {
                $type = $fightStat['type'] ?? '';
                $redStats = $fightStat[$redAthlete];
                $blueStats = $fightStat[$blueAthlete];

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
                    // does not contain thrown and landed data points
                    $redThrownText = '';
                    $blueThrownText = '';

                    // use reset() to get first element
                    $redLanded = reset($redStats);
                    $blueLanded = reset($blueStats);
                }


                ?>
                <div class="fight-stats row">
                    <div class="col-4">
                        <span class="total-landed red"><?= $redLanded ?></span>
                        <span class="total-thrown"><?= $redThrownText ?></span>
                    </div>
                    <div class="col-4 charts">
                        <div class="chart">

                        </div>
                        <span class="chart-text"><?= $type ?></span>
                    </div>
                    <div class="col-4">
                        <span class="total-landed blue"><?= $blueLanded ?></span>
                        <span class="total-thrown"><?= $blueThrownText ?></span>
                    </div>
                </div>
                <?php
            }

        }
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

            if ((isset($_SESSION['UserID']) && $page['showLoggedIn']) ||
                (!isset($_SESSION['UserID']) && !$page['showLoggedIn'])) {
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
    static function outcomeBadge(array $athlete, ?int $winnerId): string
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
}


?>
