<?php
require_once 'autoload.php';

require_once 'templates/header.php';

$templatesFolder = 'templates/';

if (!isset($_GET['page'])) {
    $page = '';
} else {
    $page = $_GET['page'];
}

switch ($page) {
    case 'athletes':
        include_once $templatesFolder . 'athletes.php';
        break;
    case 'events':
        include_once $templatesFolder . 'events.php';
        break;
    case 'event':
        include_once $templatesFolder . 'event.php';
        break;
    case 'rankings':
        include_once $templatesFolder . 'rankings.php';
        break;
    default:
        include_once $templatesFolder . 'index.php';
        break;

}


require_once 'templates/footer.php';
?>