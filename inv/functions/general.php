<?php

function user_has_rights($user_rights) {
    $rights = array_shift($user_rights);
    if ($rights['design'] == "1") {
        $has_rights = true;
    } else if ($rights['reports']) {
        $has_rights = true;
    } else if ($rights['graphical']) {
        $has_rights = true;
    } else {
        $has_rights = false;
    }
    return $has_rights;
}

function user_in_project($this_user, $project_users) {
    if (!in_array($this_user, $project_users)) {
        return false;
    }
    return true;
}
