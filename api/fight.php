<?php
/**
 * This handles all API requests relating to fights.
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
include_once "../models/Fight.php";

$fight_id = intval($_GET['fight']);

$fight = new Fight($db);

// set fight id in object and query
$fight->id = $fight_id;

if ($fight->getOne()->num_rows > 0) {
//    $fight_q = $fight_query->fetch_assoc();
    $fight_q = $fight->results->fetch_assoc();

    echo $fight_q;

    $referee = $db->query("SELECT RefereeName FROM Referees WHERE RefereeID=$fight->referee_id");
    $weight_class = $db->query("SELECT WeightClass FROM WeightClasses WHERE WeightClassID={$fight->weight_class_id}");
    $fight_athletes = $db->query("SELECT * FROM FightAthletes WHERE FightID={$fight->id}");
    $fight_outcome = $db->query("SELECT * FROM FightResults WHERE FightID={$fight->id}")->fetch_assoc();

    $event = $db->query("SELECT * FROM Events WHERE EventID={$fight->event_id}")->fetch_assoc();

    $athlete_data = [];
    $athletes = [];

    while ($row = $fight_athletes->fetch_assoc()) {
        $athlete = $db->query("SELECT AthleteName, AthleteID FROM Athletes WHERE AthleteID={$row['AthleteID']}")->fetch_assoc();

        $athletes[$athlete['AthleteID']] = $athlete['AthleteName'];
        $athlete_data[$athlete['AthleteName']] = $row;
    }

    if (intval($fight->title_bout) == 1) {
        $title_bout = "Yes";
    } else {
        $title_bout = "No";
    }

    $fight_detail = [
        "EventID" => $event['EventID'],
        "Date" => $event['EventDate'],
        "FightID" => $fight->id,
        "TitleBout" => $title_bout,
        "WeightClass" => $weight_class->fetch_assoc()['WeightClass'],
        "Referee" => $referee->fetch_assoc()['RefereeName'],
        "Rounds" => $fight->rounds,
        "Outcome" => $fight_outcome['ResultTypeID'],
        "Winner" => $athletes[$fight_outcome['WinnerAthleteID']]
    ];

    $data = ['overview' => $fight_detail, 'athleteFightData' => $athlete_data];
} else {
    $status = 'error';
    $type = 'Invalid fight';
}

// EOF