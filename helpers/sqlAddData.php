<?php
require_once '../autoload.php';

use helpers\Database;

// create db connection
$db = (new Database())->getConnection();

// uncomment to run DB update operations

// addAthleteImages($db);
// addResultType($db);


/**
 * Adds a random imageUrl to each athlete dependent on whether they are male or female.
 *
 * If an athlete's gender cannot be determined (due to no fight history), a placeholder image is used.
 * @param PDO $db DB connection
 */
function addAthleteImages(PDO $db)
{
    $maleHeadshots = [
        "https://dmxg5wxfqgb4u.cloudfront.net/styles/event_results_athlete_headshot/s3/%5Bdate%3Acustom%3AY%5D-%5Bdate%3Acustom%3Am%5D/67275_profile-galery_profile-picture_STERLING_ALJAMAIN_BELT.png",
        "https://dmxg5wxfqgb4u.cloudfront.net/styles/athlete_bio_full_body/s3/2020-07/USMAN_KAMARU_L_BELT_12-14.png?OuM8S6Z8qBwPrEeJFMYuDd8lvVcgBsyI&itok=L3ZTbg1B",
        "https://dmxg5wxfqgb4u.cloudfront.net/styles/athlete_matchup_stats_full_body/s3/2020-07/MASVIDAL_JORGE_R_03-16.png?U6LJizOG_EEAXVa.9_7soJ3JQbDnFR1y&itok=6bMLXRj6",
        "https://dmxg5wxfqgb4u.cloudfront.net/styles/event_fight_card_upper_body_of_standing_athlete/s3/2020-10/69435%252Fprofile-galery%252Ffullbodyleft-picture%252FHALL_URIAH_L_10-31.png?null&itok=0B3T7-KQ",
        "https://dmxg5wxfqgb4u.cloudfront.net/styles/event_fight_card_upper_body_of_standing_athlete/s3/2020-08/SMITH_ANTHONY_L_08-29.png?2.M2KSqwh6ZPd6sn5hgQSLt2Kp6jIY8C&itok=nGvyEBA2",
        "https://dmxg5wxfqgb4u.cloudfront.net/styles/event_fight_card_upper_body_of_standing_athlete/s3/image/ufc-fighter-container/67613/profile-galery/fullbodyright-picture/WEIDMAN_CHRIS_R.png?SAUt7EbYRGzzZOhN3y9sOvJzMs91kItu&itok=FUc2rm9d",
        "https://dmxg5wxfqgb4u.cloudfront.net/styles/event_fight_card_upper_body_of_standing_athlete/s3/2020-10/68255%252Fprofile-galery%252Ffullbodyright-picture%252FCRUTE_JIMMY_R_10-17.png?null&itok=jMCHTawj",
        "https://dmxg5wxfqgb4u.cloudfront.net/styles/athlete_profile_listing_medium_1x/s3/2020-09/JONES_JON_12-29.png?_D9_IoLshL2u7P9kI.xElSVHvnSjHODe&itok=M_k-MDqN",
        "https://dmxg5wxfqgb4u.cloudfront.net/styles/athlete_profile_listing_medium_1x/s3/2020-08/FIGUEIREDO_DEIVESON_BELT.png?CR8EqURePHivMu9UQAjyy0PK1bEwQo6m&itok=YJvYUOyv",
        "https://dmxg5wxfqgb4u.cloudfront.net/styles/athlete_profile_listing_medium_1x/s3/2020-07/VOLKANOVSKI_ALEXANDER_BELT_07-11.png?DomqxzrujBL5G7BQpQoUNLqzEsscAat2&itok=2LFzlPPm",
        "https://dmxg5wxfqgb4u.cloudfront.net/styles/athlete_profile_listing_medium_1x/s3/2021-03/68129%252Fprofile-galery%252Fprofile-picture%252FADESANYA_ISRAEL_BELT_03-06.png?null&itok=dd9-3zDu",
        "https://dmxg5wxfqgb4u.cloudfront.net/styles/athlete_profile_listing_medium_1x/s3/2021-03/68179%252Fprofile-galery%252Fprofile-picture%252FBLACHOWICZ_JAN_BELT_03-06.png?null&itok=mUfEMuo4",
        "https://dmxg5wxfqgb4u.cloudfront.net/styles/athlete_profile_listing_medium_1x/s3/2021-04/67964%252Fprofile-galery%252Fprofile-picture%252FNGANNOU_FRANCIS_BELT.png?null&itok=g4LYm2hq",
        "https://dmxg5wxfqgb4u.cloudfront.net/styles/event_results_athlete_headshot/s3/2021-04/69114%252Fprofile-galery%252Fprofile-picture%252FWHITTAKER_ROBERT_04-17.png?null&itok=aSjlrExl",
        "https://dmxg5wxfqgb4u.cloudfront.net/styles/event_results_athlete_headshot/s3/2021-04/68475%252Fprofile-galery%252Fprofile-picture%252FGASTELUM_KELVIN_04-17.png?null&itok=Szi-acsw",
        "https://dmxg5wxfqgb4u.cloudfront.net/styles/event_results_athlete_headshot/s3/2021-04/69607%252Fprofile-galery%252Fprofile-picture%252FHOLLAND_KEVIN_04-10.png?null&itok=DOdzBLs_",
        "https://dmxg5wxfqgb4u.cloudfront.net/styles/event_results_athlete_headshot/s3/2019-05/MIOCIC_STIPE.png?null&itok=I3T4jRso",
        "https://dmxg5wxfqgb4u.cloudfront.net/styles/event_results_athlete_headshot/s3/2021-03/68564%252Fprofile-galery%252Fprofile-picture%252FEDWARDS_LEON_03-13.png?null&itok=Jb7ZQs8J",
        "https://dmxg5wxfqgb4u.cloudfront.net/styles/event_results_athlete_headshot/s3/2020-10/68724%252Fprofile-galery%252Fprofile-picture%252FMORAES_MARLON_10-10.png?null&itok=5AziWHuS",
    ];

    $femaleHeadshots = [
        "https://dmxg5wxfqgb4u.cloudfront.net/styles/event_fight_card_upper_body_of_standing_athlete/s3/2020-07/NAMAJUNAS_ROSE_R_11-04.png?.ITQPy4BQex0Yo_MvJUImGESw7wMxO_w&itok=gAqznuPU",
        "https://dmxg5wxfqgb4u.cloudfront.net/styles/event_fight_card_upper_body_of_standing_athlete/s3/2020-10/68243%252Fprofile-galery%252Ffullbodyright-picture%252FANDRADE_JESSICA_R_10-17.png?null&itok=F4wfnIiv",
        "https://dmxg5wxfqgb4u.cloudfront.net/styles/event_fight_card_upper_body_of_standing_athlete/s3/2020-02/SHEVCHENKO_VALENTINA_L_BELT.png?auAU1HqvFlunqB.PVzetwdZAP7FZqSKe&itok=FIi4k3BM",
        "https://dmxg5wxfqgb4u.cloudfront.net/styles/event_fight_card_upper_body_of_standing_athlete/s3/2020-03/WEILI_ZHANG_L_BELT.png?RBXbRqzQF0hQEdiAzt1Tle8X6eWpCZ.1&itok=LCd7x2gX",
        "https://dmxg5wxfqgb4u.cloudfront.net/styles/athlete_profile_listing_medium_1x/s3/2021-03/67289%252Fprofile-galery%252Fprofile-picture%252FNUNES_AMANDA_BELT_03-06.png?null&itok=T_TfzMQS",
        "https://dmxg5wxfqgb4u.cloudfront.net/styles/event_results_athlete_headshot/s3/2020-03/DE_RANDAMIE_GERMAINE.png?kRHpt6dcTYveGcAHLxWIGloQyv9ocGCO&itok=qTrjksNR",
        "https://dmxg5wxfqgb4u.cloudfront.net/styles/event_results_athlete_headshot/s3/image/ufc-fighter-container/68403/profile-galery/profile-picture/PENA_JULIANNA.png?hgQ2EOXHTzNi0onqVBBo9ojORVMZcRlf&itok=py-wvmmf",
        "https://dmxg5wxfqgb4u.cloudfront.net/styles/event_results_athlete_headshot/s3/2020-02/LADD_ASPEN.png?IBucY.vClZYqWbwmE5PmayPwTXhk5.eh&itok=eP1XIM9X",
        "https://dmxg5wxfqgb4u.cloudfront.net/styles/event_results_athlete_headshot/s3/2020-10/68095%252Fprofile-galery%252Fprofile-picture%252FHOLM_HOLLY_10-03.png?vq.83C2mkbZkhnqGr_JBj2WshVQg31p_&itok=qzhq-QWn",
        "https://dmxg5wxfqgb4u.cloudfront.net/styles/event_results_athlete_headshot/s3/2020-10/68120%252Fprofile-galery%252Fprofile-picture%252FALDANA_IRENE_10-03.png?rZN36oTM.5fEf.2Ubc79cCh9Aezl9qHT&itok=jVMDclTP",
        "https://dmxg5wxfqgb4u.cloudfront.net/styles/event_results_athlete_headshot/s3/2020-11/67642%252Fprofile-galery%252Fprofile-picture%252FGADELHA_CLAUDIA_07-06.png?null&itok=whf609V-",
        "https://dmxg5wxfqgb4u.cloudfront.net/styles/event_results_athlete_headshot/s3/2020-11/69520%252Fprofile-galery%252Fprofile-picture%252FXIAONAN_YAN_06-08.png?null&itok=NKxQtOOD",
        "https://dmxg5wxfqgb4u.cloudfront.net/styles/event_results_athlete_headshot/s3/2020-09/HILL_ANGELA_01-25.png?do0RIhmeLBMzPdcQNOxutjy3fP3XKXVD&itok=-wP46bYB",
        "https://dmxg5wxfqgb4u.cloudfront.net/styles/event_results_athlete_headshot/s3/2020-11/69044%252Fprofile-galery%252Fprofile-picture%252FMARKOS_RANDA_07-06.png?null&itok=fwhzUydy",
        "https://dmxg5wxfqgb4u.cloudfront.net/styles/event_results_athlete_headshot/s3/image/ufc-fighter-container/67688/profile-galery/profile-picture/CALVILLO_CYNTHIA.png?sJlWNFXt6qFfa0DgQ65gqjt25fIMWbhz&itok=PRsLlF5P",
        "https://dmxg5wxfqgb4u.cloudfront.net/styles/event_results_athlete_headshot/s3/2018-10/RODRIGUEZ_MARINA.png?dQzn8n1qO8TjjEMf_PJ3t1C0EacDKkXK&itok=oGYOruda",
        "https://dmxg5wxfqgb4u.cloudfront.net/styles/event_results_athlete_headshot/s3/2020-08/JANDIROBA_VIRNA_04-27.png?nQkgV.DvXTU4K0VU4kZazHzrzOknpqYY&itok=-AR_izPF",
        "https://dmxg5wxfqgb4u.cloudfront.net/styles/event_results_athlete_headshot/s3/2018-12/silhouette-headshot-female.png?9KD6GuyihG7ztXQGF7L2nnLb49LubRvI&itok=wv1HqXVj",
        "https://dmxg5wxfqgb4u.cloudfront.net/styles/event_results_athlete_headshot/s3/image/fighter_images/Amanda_Cooper/COOPER_AMANDA.png?iA6zgbezqqqwNdi0rD9lXXv0RLjQrWwg&itok=TC62Tjsp",
    ];

    $placeholderHeadshot = "https://www.ufc.com/themes/custom/ufc/assets/img/no-profile-image.png";

    // returns a list of athleteID's and boolean indicating whether female or not
    $query = "SELECT
                    A.AthleteID,
                    IF(WC.WeightClass LIKE 'women%', 1, 0) AS Female
                FROM Athletes A
                LEFT JOIN FightAthletes FA ON A.AthleteID=FA.AthleteID
                LEFT JOIN Fights F ON FA.FightID = F.FightID
                LEFT JOIN Events E ON F.EventID = E.EventID
                LEFT JOIN WeightClasses WC ON F.WeightClassID = WC.`WeightClassID`
                GROUP BY A.AthleteID, Female";

    $query = $db->query($query);
    $athletes = $query->fetchAll();

    foreach ($athletes as $athlete) {
        $imageSource = ($athlete['Female'] == 1 ? $femaleHeadshots : $maleHeadshots);
        $athleteImage = $imageSource[rand(0, sizeof($imageSource))];

        // run update query
        $updateQuery = 'UPDATE Athletes SET AthleteImage=? WHERE AthleteID=?; ';
        $query = $db->prepare($updateQuery);
        $query->execute([$athleteImage, $athlete['AthleteID']]);
    }

    // for those athletes that haven't been in a fight
    //  AthleteImage is still null at this point
    $query = "SELECT * FROM Athletes WHERE AthleteImage IS NULL";
    $query = $db->query($query);
    $athletes = $query->fetchAll();

    foreach ($athletes as $athlete) {
        $athleteImage = $placeholderHeadshot;

        // run update query
        $updateQuery = 'UPDATE Athletes SET AthleteImage=? WHERE AthleteID=?; ';
        $query = $db->prepare($updateQuery);
        $query->execute([$athleteImage, $athlete['AthleteID']]);
    }

    // check whether successful
    $query = "SELECT * FROM Athletes WHERE AthleteImage IS NULL";
    $query = $db->query($query);

    $remainingAthletes = $query->rowCount();

    if ($remainingAthletes == 0) {
        echo 'Successful';
    } else {
        echo 'There was a problem - ' . $remainingAthletes . ' athletes do not have an image.';
    }

    echo "\naddAthleteImages operation completed";
}

/**
 * Where the fight outcome is unknown (ResultTypeID is null), this will update the outcome to be one of those in the DB.
 *
 * @param PDO $db
 */
function addResultType(PDO $db)
{
    $query = "SELECT * FROM ResultTypes";
    $query = $db->query($query);

    // store values as array - outcome types will be equally weighted
    $resultTypes = $query->fetchAll();

    // get fights without a result
    $query = "SELECT * FROM FightResults";
    $query = $db->query($query);

    if ($query->rowCount() > 0) {
        // there are records without a result
        $fights = $query->fetchAll();

        foreach ($fights as $fight) {
            // check if there was a winner
            $winner = isset($fight['WinnerAthleteID']);


            // generate random end of fight time
            $timeMins = rand(0, 4);
            $timeSecs = rand(0, 59);
            $fightTime = sprintf('%01d:%02d', $timeMins, $timeSecs);

            // round fight was stopped
            $random = rand(0, 100);
            $round = $random <= 70 ? rand(0, 3) : rand(0, 5); // 70% of fights will have stopped by round 3 - 30% by 5


            // loop is used to make sure result aligns with whether there was a winner or not,
            //  if a winnerAtheleteID is set, outcome should be a win, if not, outcome should be a draw
            do {
                // get a random fight result
                $random = rand(0, sizeof($resultTypes) - 1);
                $fightResult = $resultTypes[$random];
            } while (($winner && $fightResult['ResultDescription'] == 'Draw') ||
                (!$winner && $fightResult['ResultDescription'] != 'Draw'));

            // check if result was a decision or draw - means the fight went the distance
            if (stripos($fightResult['ResultDescription'], 'decision') !== false ||
                stripos($fightResult['ResultDescription'], 'draw') !== false) {
                $fightTime = "5:00";
                $round = ($random <= 70 ? 3 : 5); // 70% chance of 3 rounds - 30% chance of 5 rounds
            }

            // update query
            $updateQuery = "UPDATE FightResults 
                        SET 
                            ResultTypeID=?,
                            WinRound=?,
                            WinRoundTime=?
                        WHERE FightResultID=?";

             $updateQuery = $db->prepare($updateQuery);
            $updateQuery->execute([
                $fightResult['ResultTypeID'],
                $round,
                $fightTime,
                $fight['FightResultID']]);

        }
    }

    // check that all records now have an outcome
    $query = "SELECT * FROM FightResults WHERE ResultTypeID IS NULL";
    $query = $db->query($query);

    $remaining = $query->rowCount();

    if ($remaining == 0) {
        echo 'Successful';
    } else {
        echo 'There was a problem - ' . $remaining . ' fights do not have a result.';
    }

    echo "\naddResultType operation completed";
}