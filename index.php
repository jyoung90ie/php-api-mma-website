<?php
session_start();
include_once 'autoload.php';
include_once 'helpers/config.php';

const TEMPLATES_FOLDER = 'templates/';

const NAVBAR_PAGES = [
    ['link' => './?page=index', 'text' => 'Home'],
    ['link' => './?page=events', 'text' => 'Events'],
    ['link' => './?page=rankings', 'text' => 'Rankings'],
    ['link' => './?page=athletes', 'text' => 'Athletes'],
];

parse_str(parse_url($_SERVER['REQUEST_URI'], PHP_URL_QUERY), $queryString);
$activePage = '?page=' . $queryString['page'] ?? 'index';

include_once TEMPLATES_FOLDER . 'header.php';

if (!isset($_GET['page'])) {
    $page = '';
} else {
    $page = TEMPLATES_FOLDER . htmlspecialchars($_GET['page']) . '.php';
}

// include pages as needed
if (file_exists($page)) {
    include_once $page;
} else {
    include_once TEMPLATES_FOLDER . 'index.php';
}


include_once TEMPLATES_FOLDER . 'footer.php';
?>