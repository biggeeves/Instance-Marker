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
 * Description of Instance Tags
 * Multiple instances of REDCap may be in use. 
 * Examples: Production, Backup, Development instances
 * Instances differentiated by a unique URL
 * will have the instance marked by the REDCap logo
 * indicating the specific instance based on the URL.
 * The tab title is modified by prepending the first letter of the instance type.
 * 
 * Note: The live/production instance of REDCap should remain unmarked.
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

    public function init() {
        $this->set_instance_url();
        $this->set_logo_size();
        $this->select_instance();
        $this->instance_color();
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
        if (($this->other_text === '') || (is_null($this->other_text))) {
            $this->other_text = 'Other';
        }
    }

    private function clean_other_text($other_text) {
        return htmlspecialchars(strip_tags($other_text));
    }

    private function set_logo_size() {
        $this->logo_size = 'medium';
        
        switch (PAGE) {
            case "redcap/index.php":
            case "index.php":
            case "ControlCenter/index.php":
            case "redcap/external_modules/manager/control_center.php":
                $this->logo_size = 'small';
                break;
        }

        if ($this->logo_size === 'small') {
            $this->logo_selector = "a.navbar-brand";
            $this->offset_top = "42";
            $this->offset_left = "64";
        } else if ($this->logo_size === 'medium') {
            $this->logo_selector = "#project-menu-logo a";
            $this->offset_top = "36";
            $this->offset_left = "18";
        } else {
            // Do not change logo
        }
    }

    private function select_instance() {
        $this->selected = 0;
        foreach ($this->instance_url as $key => $value) {
            if (strpos(APP_PATH_WEBROOT_FULL, $value) !== false) {

                $this->instance = $key;
                $this->display_text = $key;
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
        }
    }

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

    private function construct_js() {
        $display = '<div class=\"instance-type\" style=\"position:fixed;' .
                'top:' . $this->offset_top . 'px; ' .
                'left:' . $this->offset_left . 'px;' .
                'color:' . $this->instance_color . ';' . 'font-weight:bold;' .
                'font-style:italic;' .
                'z-index:1040;' . '\">' .
                $this->display_text .
                '</div>';

        $this->js = '<script>' .
                '$(document).ready(function(){' . PHP_EOL . 
                '  old_title = $("title").text(); ' .  PHP_EOL . 
                '  new_title = old_title.replace("REDCap ","REDCap-' . $this->display_text . '")  ; ' . PHP_EOL . 
                '  display_text = "' . $display . '";' .  PHP_EOL . 
                '  $(document).prop("title", new_title);' .  PHP_EOL . 
                '  $("body")' . PHP_EOL . 
                '.prepend(display_text);' . PHP_EOL . 
                '});' . PHP_EOL . 
                '</script>';
    }

}
