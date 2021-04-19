<?php
/**
 * This handles all API requests relating to athletes.
 */

// do not allow direct access to this file
if (count(get_included_files()) == 1) {
    header("Location: index.php");
    die();
}

include_once '../models/Athlete.php';

$athlete_id = intval($_GET['athlete']);

$athlete = new Athlete($db);
$result = $athlete->getOne($athlete_id);
echo 'test';

if (!is_null($athlete->getName())) {
    $row = $result->fetch_object();
    $data = $row;
} else {
    $status = 'error';
    $type = 'Invalid athlete';
}

// EOF