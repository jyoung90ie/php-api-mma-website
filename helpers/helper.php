<?php

namespace helpers;

/**
 * Checks that a variable exists and it has a value.
 *
 * @param $field - the variable to be checked
 */
function has_value($field): bool
{
    if (isset($field) && !empty($field)) {
        return true;
    }

    return false;
}

/**
 * Generates links for the navbar - highlighting the active page.
 *
 * @param array $navbarPages consisting of two elements: link and text
 * @param string $activePage the active page in the format '?page=pageName'
 * @return string generated HTML for navbar
 */
function genNavbar(array $navbarPages, string $activePage): string
{
    $outputHTML = '';
    foreach ($navbarPages as $page) {
        if (ltrim($page['link'], './') == $activePage) {
            $outputHTML .= '                <li class="nav-item">
                    <a class="nav-link active" aria-current="page" href="' . $page['link'] . '">' . $page['text'] . '</a>
                </li>' . "\n";
        } else {
            $outputHTML .= '                <li class="nav-item">
                    <a class="nav-link" href="' . $page['link'] . '">' . $page['text'] . '</a>
                </li>';
        }
    }

    return $outputHTML;
}