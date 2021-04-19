<?php

/**
 * Checks that a variable exists and it has a value.
 *
 * @param $field - the variable to be checked
 */
function has_value($field): bool {
    if (isset($field) && !empty($field)) {
        return true;
    }

    return false;
}