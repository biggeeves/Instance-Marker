<?php

/**
 * User: Greg Neils
 * Site: Columbia University Data Coordinating Center
 * Date: 11/13/2017
 * Time: 12:04 PM
 */

namespace DCC\Instance_marker;

use ExternalModules\AbstractExternalModule;
use ExternalModules\ExternalModules;

/**
 * A single site may have multiple instances of REDCap in use. 
 * Examples: Production, Backup, Development instances.
 * Instances differentiated by a unique URL
 * will have the instance marked below the REDCap logo
 * indicating the specific instance based on the URL.
 * The tab title is modified by prepending the first letter of the instance type.
 * 
 * Note: The live/production instance of REDCap should remain unmarked.
 * 
 */
class Instance_marker extends \ExternalModules\AbstractExternalModule {

    private $logo_selector;
    private $logo_size;
    private $logo_size_name;
    private $offset_left;
    private $offset_top;
    private $instance;
    private $instance_url;
    private $instance_color;
    private $selected;
    private $other_text;
    private $debug_js;
    private $debug_mode = false;

    public function init() {

        $this->set_instance_url();
        $this->set_logo_size();
        $this->select_instance();
        $this->instance_color();
        $this->set_debug_js();
        $this->construct_js();
    }

    private function set_instance_url() {

        $this->instance_url['localhost'] = AbstractExternalModule::getSystemSetting('localhost_url');
        $this->instance_url['development'] = AbstractExternalModule::getSystemSetting('development_url');
        $this->instance_url['testing'] = AbstractExternalModule::getSystemSetting('testing_url');
        $this->instance_url['backup'] = AbstractExternalModule::getSystemSetting('backup_url');
        $this->instance_url['staging'] = AbstractExternalModule::getSystemSetting('staging_url');
        $this->instance_url['other'] = AbstractExternalModule::getSystemSetting('other_url');
        $this->other_text = AbstractExternalModule::getSystemSetting('other_text');
        $this->other_text = htmlspecialchars(strip_tags($this->other_text));
        if (($this->other_text === '') || (is_null($this->other_text))) {
            $this->other_text = 'Other';
        }
    }

    private function clean_other_text($other_text) {
        return htmlspecialchars(strip_tags($other_text));
    }

    /*
     * REDCap logos vary in size depending on the page
     * Sets the logo size and coordinates of the text
     */

    private function set_logo_size() {
        $this->logo_size = 'small';
        switch (PAGE) {
            case "redcap/index.php":
            case "index.php":
            case "redcap/external_modules/manager/control_center.php":
            case "ProjectSetup/index.php":
            case "Calendar/index.php":
            case "DataExport/index.php":
            case "Logging/index.php":
            case "DataQuality/field_comment_log.php":
            case "UserRights/index.php":
            case "DataAccessGroups/index.php":
            case "Locking/locking_customization.php":
            case "Locking/esign_locking_management.php":
            case "DataQuality/index.php":
            case "API/project_api.php":
            case "API/playground.php":
            case "MobileApp/index.php":
            case "ExternalModules/manager/project.php":
            case "redcap8.0.2/redcap_v8.0.2/ExternalModules/manager/project.php":
                $this->logo_size = 'medium';
                break;
        }

        if ($this->logo_size === 'small') {
            $this->logo_selector = "#project-menu-logo a";
            $this->offset_top = "36";
            $this->offset_left = "18";
        } else if ($this->logo_size === 'medium') {
            $this->logo_selector = "a.navbar-brand";
            $this->offset_top = "42";
            $this->offset_left = "64";
        } else {
// Do not change logo
        }
    }

    /*
     * The instance is based on mathcing a portion of the Base URL.
     * Sets the instance type, display text and other text as applicable
     * Sets selected if the URL is matched.
     */

    private function select_instance() {
        $this->selected = 0;
        foreach ($this->instance_url as $key => $value) {
            if (strpos(APP_PATH_WEBROOT_FULL, $value) !== false) {

                $this->instance = $key;
                $this->display_text = ucfirst($key);
                $this->selected = 1;
                if ($key === 'other') {
                    $this->display_text = $this->other_text;
                }
                break;
            }
        }
    }

    public function redcap_every_page_top($project_id) {
        $this->init();
        if ($this->selected === 1) {
            echo $this->js;
            echo $this->css;
        }
        if ($this->get_debug_mode() == 1) {
            echo $this->debug_js;
        }
    }

    /*
     * Instances are also indicated by color.
     * Sets specific color based on instance
     */

    private function instance_color() {
        $this->instance_color = '#000000';
        switch ($this->instance) {
            case 'localhost':
                $this->instance_color = '#449d44';  // Green
                break;
            case 'development':
                $this->instance_color = '#286090';  // Blue
                break;
            case 'testing':
                $this->instance_color = '#835C3B';  // Brown
                break;
            case 'backup':
                $this->instance_color = '#DD3236';  // Redish
                break;
            case 'staging':
                $this->instance_color = '#BA9215'; //  Goldish
                break;
            case 'other':
                $this->instance_color = '#f0ad4e'; // Orange
                break;
        }
    }

    /*
     * Add the javascript to the top of every REDCap page
     */

    public function set_debug_mode($mode) {
        $this->debug_mode = $mode;
    }

    public function get_debug_mode() {
        return $this->debug_mode;
    }

    private function construct_js() {
//                'right:' . $this->offset_left . 'px;' .
                'top:' . $this->offset_top . 'px; ' .
        $css_styles = '.instance-type {' .
                'position:fixed;' .
                'top: 36px; ' .
                'right: 10px;' .
                'background-color:' . $this->instance_color . ';' . 'font-weight:bold;' .
                'font-style:italic;' .
                'z-index:1040;' .
                'color: white;' .
    'border-radius: 2px;' .
    'padding: 5px;' .
                '}';
//        if ($this->logo_size === 'medium') {
//            $css_styles .= '@media screen and (max-width: 767px) {' .
//                    '.instance-type{top:36px; left:18px;}' .
//                    '}';
//        }

        $style_sheet = '<style type="text/css">' . $css_styles . '</style>';

        $display = '<div class=\"instance-type\">' .
                $this->display_text .
                '</div>';

        $this->js = '<script>' .
                '$(document).ready(function(){' . PHP_EOL .
                '  $(\'' . $style_sheet . '\').appendTo("head");' . PHP_EOL .
                '  old_title = $("title").text(); ' . PHP_EOL .
                '  new_title = "' . $this->display_text . '".substring(0, 1) + "-" + old_title; ' . PHP_EOL .
                '  text_div = "' . $display . '";' . PHP_EOL .
                '  $(document).prop("title", new_title);' . PHP_EOL .
                '  $("body")' . PHP_EOL .
                '.prepend(text_div);' . PHP_EOL .
                '});' . PHP_EOL .
                '</script>';
    }

    private function set_debug_js() {
        $this->debug_js = '<script>' .
                '$(document).ready(function(){' . PHP_EOL .
                'console.log("Instance Marker ran"); ' .
                'console.log("' . 'top:' . $this->offset_top . 'px; ' . '"); ' .
                'console.log("' . 'left:' . $this->offset_left . 'px;' . '"); ' .
                'console.log("' . 'color:' . $this->instance_color . '"); ' .
                'console.log("' . 'text:' . $this->display_text . '"); ' .
                'h = $("img[alt=\'REDCap\']").height();' .
                'w = $("img[alt=\'REDCap\']").width();' .
                'console.log("Height: " + h + " Width: " + w); ' .                
                '});' . PHP_EOL .
                '</script>';
    }

}
