<?php

use helpers\TemplatesHelper;


?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pro MMA</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta2/dist/css/bootstrap.min.css" rel="stylesheet"
          integrity="sha384-BmbxuPwQa2lc/FVzBcNJ7UAyJxM6wuqIj61tLrc4wSX0szH/Ev+nYRRuWlolflfl" crossorigin="anonymous">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.15.2/css/all.css"
          integrity="sha384-vSIIfh2YWi9wW0r9iZe7RJPrKwp6bG+s9QZMoITbCckVJqGCCRhc+ccxNcdpHuYu" crossorigin="anonymous">
    <link rel="stylesheet" href="css/main.css">
</head>

<body>
<nav class="navbar fixed-top navbar-expand-lg navbar-light bg-light">
    <div class="container-fluid">
        <a class="navbar-brand d-lg-none header-font" href="../index.php">Pro MMA</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
                data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false"
                aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <ul class="navbar-nav mb-2 mb-lg-0">
                <?= TemplatesHelper::displayNavBar(constant("NAVBAR_PAGES"), $activePage); ?>
                <?= TemplatesHelper::displayUserMenu(true); ?>
            </ul>
        </div>
        <div class="my-navbar d-none d-lg-flex">
            <div class="m-auto">
                <h1>Pro MMA</h1>
            </div>
            <div class="flex-row-reverse">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <?= TemplatesHelper::displayUserMenu(false); ?>
                    <li class="nav-item">
                        <a class="nav-link" href="https://twitter.com/" target="_blank"><i
                                    class="fab fa-twitter fa-2x"></i></a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="https://instagram.com/" target="_blank"><i
                                    class="fab fa-instagram fa-2x"></i></a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="https://www.facebook.com/" target="_blank"><i
                                    class="fab fa-facebook fa-2x"></i></a>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</nav>
<header id="logo"></header>