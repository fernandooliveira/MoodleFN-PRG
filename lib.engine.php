<?php

defined('MOODLE_INTERNAL') || die;

class progress_report_subplugin_manager {

    const CORE_FORMAT = 'base';

    static private $instance = null;

    public $plugindir;
    
    public $formats = array();
    
    public $reports = array();
    
    private function __construct() {
        
        global $CFG;
        
        $this->plugindir = $CFG->dirroot . '/grade/report/progress';
        $this->formats = get_directory_list($this->plugindir . '/formats', '', false, true, false);
        $this->reports = get_directory_list($this->plugindir . '/reports', '', false, true, false);
    }

    //singleton
    static public function get_instance() {
        if (self::$instance == null) {
            self::$instance = new self;
        }
        return self::$instance;
    }
    
    //get all file paths for a subplugin type
    public function get_subplugin_files($type, $filename) {
        global $CFG;
        
        $files = array();
        if ($type == 'formats' || $type == 'reports') {
            foreach ($this->$type as $subplugin) {
                $file = $this->plugindir . '/' . $type . '/' . $subplugin . '/' . $filename;
                if (file_exists($file)) {
                    $files[] = $file;
                }
            }
            if (!empty($files)){
                return $files;
            }
        }
        
        return false;
    }

    public function get_report_css_files_relative($filename) {
        global $CFG;

        $files = array();

        foreach ($this->reports as $report) {
            $file = $this->plugindir . '/reports/' . $report . '/'.$filename;
            if (file_exists($file)) {
                $files[] = '/grade/report/progress/reports/'.$report.'/'.$filename;
            }
        }
        if (!empty($files)){
            return $files;
        }

        return false;
    }


    //updates subplugin versions and forces Moodle to run a plugin update through notifications
    public function update_version($corefileversion) {
        
        $changed = false;
        $fileversions = array();
        $dbversions = array();
        
        //require all subplugin version files
        if ($formats = $this->get_subplugin_files('formats', 'version.php')) {
            foreach ($formats as $format) {
                if (file_exists($format)) {
                    require_once($format);
                }
            }
        }
        if ($reports = $this->get_subplugin_files('reports', 'version.php')) {
            foreach ($reports as $report) {
                if (file_exists($report)) {
                    require_once($report);
                }
            }
        }
        
        //compare versions of subplugins in files to version in database
        foreach ($fileversions as $key => $value) {
            if ($dbversions[$key] = get_config('gradereport_progress', 'version_' . $key)) {
                if ( (int)$dbversions[$key] > (int)$fileversions[$key] ) {
                    set_config('version_' . $key, $fileversions[$key] , 'gradereport_progress');
                    $changed = true;
                }
            }
        }
        
        //has the progress report plugin changed versions?
        if ($coredbversion = get_config('gradereport_progress', 'version')) { //progress report generator is installed
            if ( (int)$corefileversion < (int)$coredbversion ) { //is the core version up to date
                if ($changed) { //are the subplugins up to date, if not, then Moodle must be tricked
                    set_config('version', (int)$corefileversion - 1 , 'gradereport_progress');
                }
            }
        }
        return $corefileversion;
    }
}    