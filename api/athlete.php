<?php
/**
 * This handles all API requests relating to athletes.
 */

// do not allow direct access to this file
if (count(get_included_files()) == 1) {
    header("Location: index.php");
    die();
}

$athlete_id = intval($_GET['athlete']);

$event_query = $db->query("SELECT * FROM Athletes WHERE AthleteID=$athlete_id");
if ($event_query->num_rows > 0) {
    $row = $event_query->fetch_object();
    $data = $row;
} else {
    $status = 'error';
    $type = 'Invalid athlete';
}

// EOF