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
 * A single site may have multiple instances of REDCap.
 * Examples: Production, Backup, Development instances.
 * Instances differentiated by a unique URL
 * will have the instance clearly marked on every page based on the URL.
 * The tab title is modified by prepending the first letter of the instance type.
 *
 * Note: The live/production instance of REDCap should remain unmarked.
 *
 */
class Instance_tagger extends \ExternalModules\AbstractExternalModule
{

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
    private $height;
    private $width;
    private $width_pixels;
    private $width_percent;
    private $display_text;
    private $width_percent_helper;
    private $js;

    /**
     *
     */
    public function init_tagger()
    {

        $this->get_settings();
        $this->set_other_text();
        $this->set_location();
        $this->set_height_and_width();
        $this->select_instance();
        $this->set_bk_color();
        $this->set_opacity();
        $this->set_debug_js();
        $this->construct_js();
    }


    /**
     * Fetch user stored settings from REDCap
     */
    private function get_settings()
    {
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
        $this->height = AbstractExternalModule::getSystemSetting('height');
        $this->width_pixels = AbstractExternalModule::getSystemSetting('width');
        $this->width_percent = AbstractExternalModule::getSystemSetting('width_percent');
        $this->bk_color = AbstractExternalModule::getSystemSetting('bk_color');
        $this->bk_color = htmlspecialchars(strip_tags($this->bk_color));
        $this->opacity = AbstractExternalModule::getSystemSetting('opacity');
    }

    /**
     * Set other text to default "Other" if blank.
     */
    private function set_other_text()
    {
        if (($this->other_text === '') || (is_null($this->other_text))) {
            $this->other_text = 'Other';
        }
    }

    /*
     * Set location of top, right, left, and bottom fixed points
     */

    /**
     *
     */
    private function set_location()
    {
        if (!is_null($this->offset_top)) {
            if ($this->offset_top < 0 || $this->offset_top > 2000) {
                $this->offset_top = null;
            }
        }
        if (!is_null($this->offset_right)) {
            if ($this->offset_right < 0 || $this->offset_right > 2000) {
                $this->offset_right = null;
            }
        }
        if (!is_null($this->offset_bottom)) {
            if ($this->offset_bottom < 0 || $this->offset_bottom > 2000) {
                $this->offset_bottom = null;
            }
        }
        if (!is_null($this->offset_left)) {
            if ($this->offset_left < 0 || $this->offset_left > 2000) {
                $this->offset_left = null;
            }
        }
        if(is_null($this->offset_top) && is_null($this->offset_bottom)) {
            $this->offset_bottom = 0;
        }
    }

    /**
     * Set the opacity of the marker.
     */
    private function set_opacity()
    {
        if ($this->opacity <= 0 || $this->offset_top > 100) {
            $this->opacity = 1;
        } else {
            $this->opacity = $this->opacity / 100;
        }
    }

    /**
     * Set Height and Width of marker
     * The default for width is in percent.
     */
    private function set_height_and_width()
    {
        // Set Width
        $this->width_percent_helper = "";
        if (is_null($this->width_percent) && is_null($this->width_pixels)) {
            $this->width = "100%";
        } else {
            if ($this->width_percent >= 0 &&
                $this->width_percent <= 100 &&
                !is_null($this->width_percent)) {
                $this->width = $this->width_percent . '%';
                $this->width_percent_helper = 'margin-left: ' . (100 - $this->width_percent) / 2 . '%;';
            } else if ($this->width_pixels >= 5 || $this->width_pixels <= 1000) {
                $this->width = $this->width_pixels . "px";
            } else {
                $this->width = "100%";
            }
        }

        // Set Height
        if ($this->height < 5 || $this->height >= 800) {
            $this->height = "25px";
        } else {
            $this->height .= "px";
        }
    }


    /**
     * Compare Base URL to Instance URLs in settings.
     * Select the marker and text to be displayed
     */
    private function select_instance()
    {
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

    /**
     * Add css and js to every page
     * if in debug mode add debug script
     *
     * @param $project_id
     */
    public function redcap_every_page_top($project_id)
    {
        if ($this->getSystemSetting('display_on_projects_only') && !isset($project_id)) {
            return;
        }
        $this->init_tagger();
        if ($this->selected === 1) {
            echo $this->js;
        }
        if ($this->get_debug_mode() == 1) {
            echo $this->debug_js;
        }
    }

    /**
     * Set background color based on instance
     */
    private function set_bk_color()
    {

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
                    $this->bk_color = '#DD3236';  // Reddish
                    break;
                case 'staging':
                    $this->bk_color = '#BA9215'; //  Gold
                    break;
                case 'other':
                    $this->bk_color = '#f0ad4e'; // Orange
                    break;
            }
        }

    }

    /**
     * Set Debug Mode 0=Off, 1=On
     * @param $mode
     */
    public function set_debug_mode($mode)
    {
        $this->debug_mode = $mode;
    }

    /**
     * Return Debug Mode.  0=Off, 1=On
     * @return bool
     */
    public function get_debug_mode()
    {
        return $this->debug_mode;
    }


    /**
     * Settings instance-type to settings from REDCap.
     * Output value and settings to console for easier debugging.
     */
    private function construct_js()
    {
        $css_styles = '.instance-type {' .
            'position:fixed;';
        if (!is_null($this->offset_top)) {
            $css_styles .= 'top: ' . $this->offset_top . 'px; ';
        }
        if (!is_null($this->offset_right)) {
            $css_styles .= 'right: ' . $this->offset_right . 'px;';
        }
        if (!is_null($this->offset_bottom)) {
            $css_styles .= 'bottom: ' . $this->offset_bottom . 'px;';
        }
        if (!is_null($this->offset_left)) {
            $css_styles .= 'left: ' . $this->offset_left . 'px;';
        }
        if (!is_null($this->opacity)) {
            $css_styles .= 'opacity: ' . $this->opacity . ';';
        }
        $css_styles .= 'height: ' . $this->height . ';' .
            'width: ' . $this->width . ';' .
            $this->width_percent_helper .
            'background-color:' . $this->bk_color . ';' .
            'font-weight:bold;' .
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
            '$(document).ready(function(){' .
            '$(\'' . $style_sheet . '\').appendTo("head");' .
            'old_title = $("title").text(); ' .
            'new_title = "' . $this->display_text . '".substring(0, 1) + "-" + old_title; ' .
            'text_div = "' . $display . '";' .
            '$(document).prop("title", new_title);' .
            '$("body").prepend(text_div);' .
            '$(".instance-type").click(function(){$(".instance-type").hide();})'.
            '});' . PHP_EOL .
            '</script>';
    }


    /**
     * debug information displayed in console window.
     */
    private function set_debug_js()
    {
        $this->debug_js = '<script>' .
            '$(document).ready(function(){' . PHP_EOL .
            'console.log("Instance Tagger ran"); ' .
            'console.log("' . 'top:' . $this->offset_top . 'px; ' . '"); ' .
            'console.log("' . 'right:' . $this->offset_right . 'px;' . '"); ' .
            'console.log("' . 'bottom:' . $this->offset_bottom . 'px; ' . '"); ' .
            'console.log("' . 'left:' . $this->offset_left . 'px;' . '"); ' .
            'console.log("' . 'bg_color:' . $this->bk_color . '"); ' .
            'console.log("' . 'opacity:' . $this->opacity . '"); ' .
            'console.log("' . 'height:' . $this->height . '"); ' .
            'console.log("' . 'width:' . $this->width . '"); ' .
            'console.log("' . 'width percent helper:' . $this->width_percent_helper . '"); ' .
            'console.log("' . 'text:' . $this->display_text . '"); ' .
            '});' . PHP_EOL .
            '</script>';
    }

}
