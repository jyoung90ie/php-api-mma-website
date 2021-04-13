<?php
/**
 * This handles all API requests relating to events.
 */

// do not allow direct access to this file
if (count(get_included_files()) == 1) {
    header("Location: index.php");
    die();
}

$event_id = intval($_GET['event']);

$event_query = $db->query("SELECT * FROM Events WHERE EventID=$event_id");

if ($event_query->num_rows > 0) {
    $event_result = $event_query->fetch_assoc();

    $fight_query = $db->query("SELECT * FROM Fights WHERE EventID=$event_id");
    $total_fights = $fight_query->num_rows;

    // build event data for json
    $event_data = [
        "ID" => $event_result['EventID'],
        "Location" => $event_result['EventLocation'],
        "Date" => $event_result['EventDate'],
        "TotalFights" => $total_fights
    ];


    $fight_data = [];

    if ($total_fights > 0) {
        while ($fight = $fight_query->fetch_assoc()) {
            $referee = $db->query("SELECT RefereeName FROM Referees WHERE RefereeID={$fight['RefereeID']}");
            $weight_class = $db->query("SELECT WeightClass FROM WeightClasses WHERE WeightClassID={$fight['WeightClassID']}");
            $fight_athletes = $db->query("SELECT AthleteID FROM FightAthletes WHERE FightID={$fight['FightID']}");
            $fight_outcome = $db->query("SELECT * FROM FightResults WHERE FightID={$fight['FightID']}")->fetch_assoc();

            $athletes = [];
            $athlete_data = [];
            while ($row = $fight_athletes->fetch_assoc()) {
                $athlete = $db->query("SELECT * FROM Athletes WHERE AthleteID={$row['AthleteID']}")->fetch_assoc();

                $athletes[$athlete['AthleteID']] = $athlete['AthleteName'];
                array_push($athlete_data, $athlete);
            }

            if (intval(['TitleBout']) == 1) {
                $title_bout = "Yes";
            } else {
                $title_bout = "No";
            }

            array_push($fight_data, [
                "FightID" => $fight['FightID'],
                "Athletes" => $athlete_data,
                "TitleBout" => $title_bout,
                "WeightClass" => $weight_class->fetch_assoc()['WeightClass'],
                "Referee" => $referee->fetch_assoc()['RefereeName'],
                "Rounds" => $fight['NumOfRounds'],
                "Outcome" => $fight_outcome['ResultTypeID'],
                "Winner" => $athletes[$fight_outcome['WinnerAthleteID']]
            ]);
        }
    } else {
        $fight_data = "no fights";
    }

    $data = ['event' => $event_data, 'fights' => $fight_data];

} else {
    $status = 'error';
    $type = 'Invalid event';
}

// EOF