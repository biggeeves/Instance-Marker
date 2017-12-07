<?php

/**
 * User: Greg Neils
 * Site: Columbia University Data Coordinating Center
 * Date: 11/13/2017
 * Time: 12:04 PM
 */

namespace DCC\Instance_tagger;

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
class Instance_tagger extends \ExternalModules\AbstractExternalModule {

    private $logo_selector;
    private $offset_top;
    private $offset_right;
    private $instance;
    private $instance_url;
    private $instance_color;
    private $selected;
    private $other_text;
    private $debug_js;
    private $debug_mode = false;

    public function init() {

        $this->set_instance_url();
        $this->set_location();
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
        $this->offset_top = (int) AbstractExternalModule::getSystemSetting('offset_top');
        $this->offset_right = (int) AbstractExternalModule::getSystemSetting('offset_right');
    }

    private function set_location() {
        if ($this->offset_top <= 0 || $this->offset_top > 2000) {
            $this->offset_top = "45";
        }
        if ($this->offset_right <= 0 || $this->offset_right > 2000) {
            $this->offset_right = "18";
        }
    }

    /*
     * The instance is based on mathcing a portion of the Base URL.
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

    public function set_debug_mode($mode) {
        $this->debug_mode = $mode;
    }

    public function get_debug_mode() {
        return $this->debug_mode;
    }

    private function construct_js() {
        $css_styles = '.instance-type {' .
                'position:fixed;' .
                'top: ' . $this->offset_top . 'px; ' .
                'right: ' . $this->offset_right . 'px;' .
                'background-color:' . $this->instance_color . ';' . 'font-weight:bold;' .
                'font-style:italic;' .
                'z-index:1040;' .
                'color: white;' .
                'border-radius: 2px;' .
                'padding: 5px;' .
                '}';


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
                'console.log("Instance Tagger ran"); ' .
                'console.log("' . 'top:' . $this->offset_top . 'px; ' . '"); ' .
                'console.log("' . 'right:' . $this->offset_right . 'px;' . '"); ' .
                'console.log("' . 'color:' . $this->instance_color . '"); ' .
                'console.log("' . 'text:' . $this->display_text . '"); ' .
                'h = $("img[alt=\'REDCap\']").height();' .
                'w = $("img[alt=\'REDCap\']").width();' .
                'console.log("Height: " + h + " Width: " + w); ' .
                '});' . PHP_EOL .
                '</script>';
    }

}
