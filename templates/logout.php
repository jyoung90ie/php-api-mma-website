<?php
if (isset($_SESSION['User'])) {
    $userNotification = ucwords($_SESSION['User']['UserName']) . " you are now logged out.";
    \helpers\HelperFunctions::addNotification($userNotification);

    unset($_SESSION['User']);
}

$redirectUrl = $_SERVER['HTTP_REFERER'] ?? '?page=index';

header("Location: $redirectUrl");
