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
     * Generates links for the navbar - highlighting the active page.
     *
     * @param array $navbarPages consisting of two elements: link and text
     * @param string $activePage the active page in the format '?page=pageName'
     * @return string generated HTML for navbar
     */
    static function genNavbar(array $navbarPages, string $activePage): string
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
}

?>
