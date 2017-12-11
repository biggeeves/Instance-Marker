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

    private $offset_top;
    private $offset_right;
    private $offset_bottom;
    private $offset_left;
    private $opacity;
    private $instance;
    private $instance_url;
    private $bk_color;
    private $selected;
    private $other_text;
    private $debug_js;
    private $debug_mode = false;

    public function init() {

        $this->get_settings();
        $this->set_other_text();
        $this->set_location();
        $this->set_height_width();
        $this->select_instance();
        $this->set_bk_color();
        $this->set_opacity();
        $this->set_debug_js();
        $this->construct_js();
    }

    // Get stored settings
    private function get_settings() {
        $this->instance_url['localhost'] = AbstractExternalModule::getSystemSetting('localhost_url');
        $this->instance_url['development'] = AbstractExternalModule::getSystemSetting('development_url');
        $this->instance_url['testing'] = AbstractExternalModule::getSystemSetting('testing_url');
        $this->instance_url['backup'] = AbstractExternalModule::getSystemSetting('backup_url');
        $this->instance_url['staging'] = AbstractExternalModule::getSystemSetting('staging_url');
        $this->instance_url['other'] = AbstractExternalModule::getSystemSetting('other_url');
        $this->other_text = AbstractExternalModule::getSystemSetting('other_text');
        $this->other_text = htmlspecialchars(strip_tags($this->other_text));
        $this->offset_top = AbstractExternalModule::getSystemSetting('offset_top');
        $this->offset_right = AbstractExternalModule::getSystemSetting('offset_right');
        $this->offset_bottom = AbstractExternalModule::getSystemSetting('offset_bottom');
        $this->offset_left = AbstractExternalModule::getSystemSetting('offset_left');
        $this->height = (int) AbstractExternalModule::getSystemSetting('height');
        $this->width = (int) AbstractExternalModule::getSystemSetting('width');
        $this->bk_color = AbstractExternalModule::getSystemSetting('bk_color');
        $this->bk_color = htmlspecialchars(strip_tags($this->bk_color));
        $this->opacity = (int) AbstractExternalModule::getSystemSetting('opacity');
    }

    // Set other text to default "Other" if blank.
    private function set_other_text() {
        if (($this->other_text === '') || (is_null($this->other_text))) {
            $this->other_text = 'Other';
        }
    }

    /*
     * Set location of top, right, left, and bottom fixed points
     */

    private function set_location() {
        if (!is_null($this_top)) {
            if ($this->offset_top <= 0 || $this->offset_top > 2000) {
                $this->offset_top = 45;
            }
        }
        if (!is_null($this_right)) {
            if ($this->offset_right <= 0 || $this->offset_right > 2000) {
                $this->offset_right = 18;
            }
        }
        if (!is_null($this_bottom)) {
            if ($this->offset_bottom <= 0 || $this->offset_bottom > 2000) {
                $this->offset_bottom = 45;
            }
        }
        if (!is_null($this_left)) {
            if ($this->offset_left <= 0 || $this->offset_left > 2000) {
                $this->offset_left = 18;
            }
        }
    }

    /*
     * Set the opacity of the marker. 
     */

    private function set_opacity() {
        if ($this->opacity <= 0 || $this->offset_top >= 100) {
            $this->opacity = 1;
        } else {
            $this->opacity = $this->opacity / 100;
        }
    }

    /*
     * Set Height and width of marker
     */

    private function set_height_width() {
        if ($this->width <= 15 || $this->width > 1000) {
            $this->width = "";
        } else {
            $this->width .= "px";
        }
        if ($this->height <= 15 || $this->height > 800) {
            $this->height = "";
        } else {
            $this->height .= "px";
        }
    }

    /*
     * Compare Base URL to Instance URLs in settings.
     * Select the marker and text to be displayed
     * 
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
     * Set background color based on instance if it was not specified
     */

    private function set_bk_color() {

        if (is_null($this->bk_color) || $this->bk_color == "") {
            $this->bk_color = '#000000';
            switch ($this->instance) {
                case 'localhost':
                    $this->bk_color = '#449d44';  // Green
                    break;
                case 'development':
                    $this->bk_color = '#286090';  // Blue
                    break;
                case 'testing':
                    $this->bk_color = '#835C3B';  // Brown
                    break;
                case 'backup':
                    $this->bk_color = '#DD3236';  // Redish
                    break;
                case 'staging':
                    $this->bk_color = '#BA9215'; //  Goldish
                    break;
                case 'other':
                    $this->bk_color = '#f0ad4e'; // Orange
                    break;
            }
        }

    }

    public function set_debug_mode($mode) {
        $this->debug_mode = $mode;
    }

    public function get_debug_mode() {
        return $this->debug_mode;
    }

    /*
     * Output value to console for easier debugging.
     */

    private function construct_js() {
        $css_styles = '.instance-type {' .
                'position:fixed;' .
                'top: ' . $this->offset_top . 'px; ' .
                'right: ' . $this->offset_right . 'px;' .
                'bottom: ' . $this->offset_bottom . 'px;' .
                'left: ' . $this->offset_left . 'px;' .
                'color: ' . $this->color . ';' .
                'opacity: ' . $this->opacity . ';' .
                'height: ' . $this->height . ';' .
                'width: ' . $this->width . ';' .
                'background-color:' . $this->bk_color . ';' . 'font-weight:bold;' .
                'font-style:italic;' .
                'z-index:1040;' .
                'color: white;' .
                'border-radius: 2px;' .
                'padding: 5px;' .
                'text-align: center;' .
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

    /*
     * Construct debug information displayed in console window.
     */

    private function set_debug_js() {
        $this->debug_js = '<script>' .
                '$(document).ready(function(){' . PHP_EOL .
                'console.log("Instance Tagger ran"); ' .
                'console.log("' . 'top:' . $this->offset_top . 'px; ' . '"); ' .
                'console.log("' . 'right:' . $this->offset_right . 'px;' . '"); ' .
                'console.log("' . 'bottom:' . $this->offset_bottom . 'px; ' . '"); ' .
                'console.log("' . 'left:' . $this->offset_left . 'px;' . '"); ' .
                'console.log("' . 'color:' . $this->bk_color . '"); ' .
                'console.log("' . 'opacity:' . $this->opacity . '"); ' .
                'console.log("' . 'height:' . $this->height . '"); ' .
                'console.log("' . 'width:' . $this->width . '"); ' .
                'console.log("' . 'text:' . $this->display_text . '"); ' .
                'h = $("img[alt=\'REDCap\']").height();' .
                'w = $("img[alt=\'REDCap\']").width();' .
                'console.log("Height: " + h + " Width: " + w); ' .
                '});' . PHP_EOL .
                '</script>';
    }

}
