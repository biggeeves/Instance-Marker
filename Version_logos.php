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

    private $logo_url;
    private $logo_selector;
    private $logo_size;
    private $logo_size_name;
    private $instance;
    private $instance_url;
    private $selected;

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
            $this->logo_size_name = '120x36';
            $this->logo_selector = "a.navbar-brand img";
        } else if ($this->logo_size === 'medium') {
            $this->logo_size_name = '150x45';
            $this->logo_selector = "#project-menu-logo a img";
        } else {
            // Do not change logo!
            $this->logo_size_name = '150x45';
            $this->logo_selector = "#dont change";
        }
    }

    private function select_instance() {
        $this->selected = 0;
        $path = $this->getUrl('img/', false, false);

        foreach ($this->instance_url as $key => $value) {
           /*  echo 'Key: ' . $key . 
                    ' Value: ' . $value . 
                    ' Root: ' . APP_PATH_WEBROOT_FULL . 
                    'Strpos: ' . !strpos($value, APP_PATH_WEBROOT_FULL) . PHP_EOL; */
            if (strpos($value, APP_PATH_WEBROOT_FULL) !== false) {
                $this->instance = $key;
                $this->logo_url = $path . $key . '_' . $this->logo_size_name . '.gif';
                $this->selected = 1;
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

        $this->js = '<script>' .
                '$(document).ready(function(){' .
                '  $(document).prop("title", "REDCap-'. $this->instance . '");' .
                '  $("' . $this->logo_selector . '")' .
                '.attr("src","' . $this->logo_url . '");' .
                'console.log("' . $this->selected . '");' .
                'console.log("' . $this->instance . '");' .
                'console.log("' . $this->logo_size . '");' .
                'console.log("' . $this->logo_url . '");' .
                'console.log("' . $this->logo_selector . '");' .
                'console.log("Page: ' . PAGE . '");' .
                '});' .
                '</script>';
    }

}
