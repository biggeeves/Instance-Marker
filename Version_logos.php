<?php

/**
 * User: Greg Neils
 * Site: Columbia University Data Coordinating Center
 * Date: 11/13/2017
 * Time: 12:04 PM
 */

namespace DCC\Version_logos;

use ExternalModules\AbstractExternalModule;
use ExternalModules\ExternalModules;

/**
 * Description of Version Logos
 * 
 * @author Greg
 */
class Version_logos extends \ExternalModules\AbstractExternalModule {

    private $logo_selector;
    private $logo_size;
    private $logo_size_name;
    private $offset_left;
    private $offset_top;
    private $instance;
    private $instance_url;
    private $selected;
    private $other_text;

    public function init() {
        $this->set_instance_url();
        $this->set_logo_size();
        $this->select_instance();
        $this->construct_js();
    }

    public function set_instance_url() {

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

    public function clean_other_text($other_text) {
        return htmlspecialchars(strip_tags($other_text));
    }

    public function set_logo_size() {
        $this->logo_size = 'medium';
        switch (PAGE) {
            case "redcap/index.php":
            case "ControlCenter/index.php":
            case "redcap/external_modules/manager/control_center.php":
                $this->logo_size = 'small';
                break;
        }

        if ($this->logo_size === 'small') {
            $this->logo_selector = "a.navbar-brand";
            $this->offset_top = "34";
            $this->offset_left = "28";
        } else if ($this->logo_size === 'medium') {
            $this->logo_selector = "#project-menu-logo a";
            $this->offset_top = "42";
            $this->offset_left = "64";
        } else {
            // Do not change logo
        }
    }

    private function select_instance() {
        $this->selected = 0;

        foreach ($this->instance_url as $key => $value) {
            if (strpos($value, APP_PATH_WEBROOT_FULL) !== false) {
                $this->instance = $key;
                $this->selected = 1;
                if ($key === 'other') {
                    $this->display_text = $this->other_text;
                } else {
                    $this->display_text = $key;
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

    public function construct_js() {
        $display = '<div class=\"instance-type\" style=\"position:absolute;' .
                'top:' . $this->offset_top . 'px; ' .
                'left:' . $this->offset_left . 'px;' .
                'color:red;' . 'font-weight:bold;' . 
                'font-style:italic;' . 
                'z-index:1040;' . '\">' .
                $this->display_text .
                '</div>';

        $this->js = '<script>' .
                '$(document).ready(function(){' .
                '  old_title = $("title").text(); ' .
                '  new_title = old_title.replace("REDCap ","REDCap-' . $this->display_text . '")  ; ' .
                '  display_text = "' . $display . '";' .
                '  $(document).prop("title", new_title);' .
                '  $("body")' .
                '.prepend(display_text);' .
                '});' .
                '</script>';
//                '  $("' . $this->logo_selector . '").first()' .
//                '.append(display_text);' .
    }

}
