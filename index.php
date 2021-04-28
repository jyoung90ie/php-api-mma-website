<?php
session_start();
include_once 'autoload.php';
include_once 'helpers/config.php';

const TEMPLATES_FOLDER = 'templates/';
// api_key used by the website
define('API_KEY', $_SESSION['User']['ApiKey'] ?? DEFAULT_API_KEY);

const NAVBAR_PAGES = [
    ['link' => './?page=index', 'text' => 'Home'],
    ['link' => './?page=events', 'text' => 'Events'],
    ['link' => './?page=search', 'text' => 'Search'],
];

parse_str(parse_url($_SERVER['REQUEST_URI'], PHP_URL_QUERY), $queryString);
$activePage = '?page=' . $queryString['page'] ?? 'index';

// html header
include_once TEMPLATES_FOLDER . 'header.php';

if (!isset($_GET['page'])) {
    $page = '';
} else {
    $page = TEMPLATES_FOLDER . htmlspecialchars($_GET['page']);

    if (isset($_GET['action'])) {
        $action = htmlspecialchars($_GET['action']);
        switch ($action) {
            case 'create':
                $page .= '_create';
                break;
            case 'update':
                $page .= '_update';
                break;
            case 'delete':
                $page .= '_delete';
                break;
            default:
                // do nothing
        }
    }

    $page .= '.php';
}

// include pages as needed
if (file_exists($page)) {
    include_once $page;
} else {
    include_once TEMPLATES_FOLDER . 'index.php';
}

// html footer
include_once TEMPLATES_FOLDER . 'footer.php';