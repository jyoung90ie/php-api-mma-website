<?php

spl_autoload_register(function ($class) {
    $class = __DIR__ . '\\' . $class;
    $class = str_replace('\\', DIRECTORY_SEPARATOR, $class);
    if (file_exists($class . '.php')) {
        include_once $class . '.php';
    }
});