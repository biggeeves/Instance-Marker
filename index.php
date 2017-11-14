<?php
/*
 * Author: Greg Neils
 * Columbia University
 * Data Coordinating Center
 */

require_once dirname(__FILE__) . DS . "Version_logos.php";
require_once dirname(__FILE__) . DS . "inc" . DS . "functions" . DS . "general.php";


$version_logos= new DCC\Version_logos\Version_logos();
$version_logos->init();

// Initialize variables
$project_users = REDCap::getUsers();
$this_user = USERID;
$user_rights = REDCap::getUserRights($this_user);

// Security Checks:  User Rights
if (!user_in_project($this_user, $project_users)) {
    echo "You do not have access to this project.";
    die();
}
if (!user_has_rights($user_rights)) {
    die("You do not have rights to this page");
}

// OPTIONAL: Display the project header

require_once APP_PATH_DOCROOT . 'ProjectGeneral/footer.php';
