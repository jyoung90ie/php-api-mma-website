<?php
/**
 * Responsible for returning data for a specific fight.
 */

// do not allow direct access to this file
if (count(get_included_files()) == 1) {
    header("Location: index.php");
    die();
}

if (!isset($db)) {
    exit("No database connection");
}

// fight database model file
$base = dirname(__FILE__);
include_once "$base/../../models/Fight.php";
include_once "$base/../../models/Referee.php";
include_once "$base/../../models/WeightClass.php";
include_once "$base/../../models/FightAthlete.php";
include_once "$base/../../models/FightResult.php";
include_once "$base/../../models/Event.php";
include_once "$base/../../models/Athlete.php";

$fight_id = intval($_GET['fight']);

$fight = new Fight($db);

if ($fight->getOne($fight_id)) {
    $referee = new Referee($db);
    $referee->getOne($fight->getRefereeId());

    $weight_class = new WeightClass($db);
    $weight_class->getOne($fight->getWeightClassId());

    $fight_athletes = new FightAthlete($db);
    $fight_athletes->getAllByFight($fight->getId());

    $fight_result = new FightResult($db);
    $fight_result->getByFight($fight->getId());

    $event = new Event($db);
    $event->getOne($fight->getEventId());


    $athlete_data = [];
    $athletes = [];
    $winner = "";

    $athlete = new Athlete($db);

    foreach ($fight_athletes->getResults() as $result) {
        $athlete->getOne($result['AthleteID']);
        $id = $athlete->getId();
        $name = $athlete->getName();

        if ($id == $fight_result->getWinnerId()) {
            $winner = $name;
        }

        $athletes[$id] = $name;
        $athlete_data[$name] = $result;
    }

    if (intval($fight->getTitleBout()) == 1) {
        $title_bout = "Yes";
    } else {
        $title_bout = "No";
    }

    $fight_detail = [
        "EventID" => $event->getId(),
        "Date" => $event->getDate(),
        "FightID" => $fight->getId(),
        "TitleBout" => $title_bout,
        "WeightClass" => $weight_class->getWeightClass(),
        "Referee" => $referee->getName(),
        "Rounds" => $fight->getRounds(),
        "Outcome" => $fight_result->getResultId(),
        "Winner" => $winner
    ];

    $data = ['overview' => $fight_detail, 'athleteFightData' => $athlete_data];
} else {
    $status = 'error';
    $type = 'Invalid fight';
}

// EOF