<?php
/**
 * Responsible for creation of a new fight entry in the database.
 */

// do not allow direct access to this file
if (count(get_included_files()) == 1) {
    header("Location: index.php");
    die();
}

if (!isset($db)) {
    exit("No database connection");
}
// helpers
$base = dirname(__FILE__);
include_once "$base/../../lib/helper.php";

// fight database model file
include_once "$base/../../models/Fight.php";
include_once "$base/../../models/Referee.php";
include_once "$base/../../models/WeightClass.php";
include_once "$base/../../models/FightAthlete.php";
//include_once "./models/FightResult.php";
include_once "$base/../../models/Event.php";
include_once "$base/../../models/Athlete.php";

$input_fields = ['EventID', 'RefereeID', 'TitleBout', 'WeightClassID', 'NumOfRounds', 'AthleteID1', 'AthleteID2'];

$errors = [];
// validation of fields
foreach ($input_fields as $field) {
    if (!isset($_POST[$field]) || !has_value($_POST[$field])) {
        $error = "$field is missing";
        array_push($errors, $error);
    }
}

if (sizeof($errors) == 0) {
    // all fields populated

    $form_event_id = intval($_POST['EventID']);
    $form_weight_class_id = intval($_POST['WeightClassID']);
    $form_referee_id = intval($_POST['RefereeID']);
    $form_num_of_rounds = intval($_POST['NumOfRounds']);
    $form_athletes = [];
    array_push($form_athletes, ($_POST['AthleteID1']));
    array_push($form_athletes, ($_POST['AthleteID2']));

    try {
        // validate event id
        $event = new Event($db);
        $event->getOne($form_event_id);

        if (is_null($event->getId())) {
            die('Invalid Event ID');
        }

        echo $event->getDate();

        // validate weight class id
        $weight_class = new WeightClass($db);
        $weight_class->getOne($form_weight_class_id);

        if (!isset($weight_class) || !$weight_class) {
            die('Invalid Weight Class ID');
        }

        // validate referee id
        $referee = new Referee($db);
        $referee->getOne($form_referee_id);

        if (!isset($referee) || !$referee) {
            die('Invalid Referee ID');
        }

        // validate athlete id's
        $athlete = new Athlete($db);


        foreach ($form_athletes as $form_athlete) {
            $athlete->getOne($form_athlete);

            if (!isset($athlete) || !$athlete) {
                die('Invalid Athlete ID for ' . $form_athlete);
            }
        }

        // create new fight
        $fight = new Fight($db);

        // validation takes place within the setter
        $fight->setTitleBout($_POST['TitleBout']);
        $fight->setEventID($form_event_id);
        $fight->setRefereeId($form_referee_id);
        $fight->setWeightClassId($form_weight_class_id);
        $fight->setRounds($form_num_of_rounds);

        if (!$fight->create()) {
            die('Fight could not be created');
        }

        $fight_id = $fight->getId();

        // create fight athletes
        $fight_athlete = new FightAthlete($db);

        $athletes_for_json = [];
        foreach ($form_athletes as $form_athlete) {
            $fight_athlete->setFightId($fight_id);
            $fight_athlete->setAthleteId($form_athlete);

            if (!$fight_athlete->create()) {
                die($form_athlete . 'could not be created');
            }

            /*array_push($athletes_for_json, [
                'data' => [
                    'type' => 'athlete',
                    'id' => $fight_athlete->getAthleteId()
                ],
                'links' => [
                    'related' => '/athlete/' . $fight_athlete->getAthleteId()
                ]
            ]);*/
        }

        /*$response = [
            'links' => [
                'self' => '', 'next' => '', 'last' => ''
            ],
            'data' => [
                [
                    'type' => 'fight',
                    'id' => $fight->getId(),
                    'attributes' => [
                        '' => ''
                    ],
                    'relationships' => [
                        'event' => [
                            'data' => [
                                'type' => 'event',
                                'id' => $event->getId()
                            ],
                            'links' => [
                                'related' => '/event/' . $event->getId()
                            ]
                        ],
                        'athletes' => [$athletes_for_json]
                    ],
                    'links' => [
                        'self' => $_SERVER['REQUEST_URI']
                    ]
                ]
            ]
        ];*/


        echo 'Fight and FightAthletes created';
    } catch (InvalidArgumentException $invalidArgumentException) {
        echo $invalidArgumentException->getMessage();
    }

} else {
    var_dump($errors);
}




