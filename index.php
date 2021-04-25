<?php
session_start();
require_once 'autoload.php';


const TEMPLATES_FOLDER = 'templates/';
const API_URL = 'http://localhost:8888/promma/api'; // no trailing backslash

const NAVBAR_PAGES = [
    ['link' => './?page=index', 'text' => 'Home'],
    ['link' => './?page=events', 'text' => 'Events'],
    ['link' => './?page=rankings', 'text' => 'Rankings'],
    ['link' => './?page=athletes', 'text' => 'Athletes'],
];

parse_str(parse_url($_SERVER['REQUEST_URI'], PHP_URL_QUERY), $queryString);
$activePage = '?page=' . $queryString['page'] ?? 'index';


require_once TEMPLATES_FOLDER . 'header.php';


if (!isset($_GET['page'])) {
    $page = '';
} else {
    $page = $_GET['page'];
}


$apiAddress = ""; // used within templates

switch ($page) {
    case 'athletes':
        include_once TEMPLATES_FOLDER . 'athletes.php';
        break;
    case 'events':
        include_once TEMPLATES_FOLDER . 'events.php';
        break;
    case 'event':
        include_once TEMPLATES_FOLDER . 'event.php';
        break;
    case 'fight':
        include_once TEMPLATES_FOLDER . 'fight.php';
        break;
    case 'rankings':
        include_once TEMPLATES_FOLDER . 'rankings.php';
        break;
    case 'login':
        include_once TEMPLATES_FOLDER . 'login.php';
        break;
    default:
        include_once TEMPLATES_FOLDER . 'index.php';
        break;

}


require_once TEMPLATES_FOLDER . 'footer.php';
?>