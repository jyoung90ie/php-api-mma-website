<?php
/**
 * Responsible for updating a specific fight entry in the database.
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
include_once "./models/Fight.php";
include_once "./models/Referee.php";
include_once "./models/WeightClass.php";
include_once "./models/FightAthlete.php";
include_once "./models/FightResult.php";
include_once "./models/Event.php";
include_once "./models/Athlete.php";

// EOF