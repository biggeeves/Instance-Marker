<?php

/**
 * User: Greg Neils
 * Date: 11/13/2017
 * Time: 12:04 PM
 */

namespace DCC\Version_logos;

use REDCap;
use ExternalModules\AbstractExternalModule;
use ExternalModules\ExternalModules;

/**
 * Description of Version Logos
 *
 * @author Greg
 */
class Version_logos extends \ExternalModules\AbstractExternalModule {

    private $logo_url;
    private $logo_selector;
    private $logo_size;

    public function init() {
        $this->set_instance();
        $this->set_logo_size();
        $this->set_logo_url();
        $this->construct_js();
    }

    public function set_instance() {
        $this->instance_url = AbstractExternalModule::getSystemSetting('vname1');
        $this->logo_url = AbstractExternalModule::getSystemSetting('vurl1');
    }

    public function set_logo_size() {
        $this->logo_size = 'medium';
        switch (PAGE) {
            case "redcap/index.php":
            case "ControlCenter/index.php":
                $this->logo_size = 'small';
                break;
        }
    }

    public function set_logo_url() {
        if ($this->logo_size === 'small') {
            $this->logo_url = APP_PATH_WEBROOT_FULL . 'logos/dev/redcaplogo_120x36.gif';
            $this->logo_selector = "a.navbar-brand img";
            
        } else if ($this->logo_size === 'medium') {
            $this->logo_url = APP_PATH_WEBROOT_FULL . 'logos/dev/redcaplogo_150x45.gif';
            $this->logo_selector = "#project-menu-logo a img";
        } else {
            // Do not change logo!
            $this->logo_url = APP_PATH_WEBROOT_FULL . 'logos/dev/redcaplogo_150x45.gif';
            $this->logo_selector = "#project-menu-logo a img";            
        }
    }

    private function select_logo() {
        if (strpos($this->instance_url, 'url') !== false) {
            
        }
    }

    public function redcap_every_page_top($project_id) {
        $this->init();
        echo $this->js;
    }

    public function construct_js() {
        $this->js = '<script>' .
                '$(document).ready(function(){' .
                '  $(document).prop("title", "REDCap-DEV");' .
                '  $("' . $this->logo_selector . '")' .
                '.attr("src","' . $this->logo_url . '");' .
                'console.log("'. $this->logo_size . '");' .
                'console.log("'. $this->logo_url . '");' .
                'console.log("'. $this->logo_selector . '");' .
                'console.log("Page: '. PAGE . '");' .
                '});' .
                '</script>';
    }

}
