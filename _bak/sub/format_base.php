<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * File containing class definitions for formatters
 *
 * @package    gradebook
 * @subpackage progress
 * @copyright  2011 MoodleFN
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
  
defined('MOODLE_INTERNAL') || die;

/**
 * Base Formatter
 * @package gradebook
 * @subpackage progress
 */
class progress_report_format_base{
    /**
     * consumed by get_string
     * @const string HEADING
     */  
    const HEADING          = 'basereportheading';
    
    /**
     * consumed by get_string
     * @const string SHORT_NAME
     */  
    const SHORT_NAME       = 'base';
    
    /**
     * consumed by get_string
     * @const string NAME_CODE_STRING
     */  
    const NAME_CODE_STRING = 'base';
    
    /**
     * renderer object used by formatter to create formatted outputs
     * override renderer in your theme to alter formatter outputs
     * @var object $renderer
     */  
    public $renderer;
    
    /**
     * associative array of parameters used to build url query strings
     * @var array $params
     */  
    public $params;
    
    /**
     * Constructor. Initializes the progress report generator values for courseid, course, context, selected user id, and format
     * @param array $params an associative array of parameters used to build url query strings
     * @return void
     */
    public function __construct($params) {
        global $PAGE;
        
        $this->params = $params;
        $this->renderer = $PAGE->get_renderer('gradereport_progress', 'base');
    }

    /**
     * Builds string representation of report html
     * @param array $contents data representation of report content
     * @return string
     */    
    public function format_content($contents) {
        $formatted = "";
        foreach ($contents as $content) {
          $formatted .= $this->format_content_recursive($content);
        }
        return $formatted;
    }

    /**
     * Builds string representation of report html
     * @param array $contents data representation of report content
     * @return string
     */
    protected function format_content_recursive($content) {
        $classname = 'progress_report_generator';
        $formatted = "";
        
        switch ($content['type']) {
            case $classname::NODE_AUTONOMOUS :
                $formatted .= $this->renderer->write_node_self_closed($content['html'], $content['attributes']);
                break;
                
            case $classname::NODE_CONTAINER :
                $formatted .= $this->renderer->write_open_node($content['html'], $content['attributes']);
                if (isset($content['children'])) {
                    foreach ($content['children'] as $child) {
                        $formatted .= $this->format_content_recursive($child);
                    }
                }
                $formatted .= $this->renderer->write_close_node($content['html']);
                break;
                
            case $classname::NODE_TEXT :
                $formatted .= $content['text'];
                break;
        }
        
        return $formatted;
    }
    
    /**
     * Outputs report content
     * @param string $formatted string representation of report content
     * @param array $user data representation of user subject of report
     * @return string
     */
    public function render_content($formatted, $user) {
        global $OUTPUT;

        $courseid = $this->params['id'];
        $heading  = get_string(self::HEADING, 'gradereport_progress') . ' ' . $user['firstname'] . ' ' . $user['lastname'];
        
        print_grade_page_head($courseid, 'report', 'progress', $heading);

        echo $formatted;

        echo $OUTPUT->footer();
    }
    
    /**
     * Gets the name string of this subreport for use in the progress report generator ui.
     * @return string 
     */
    static public function get_name_string() {
        return get_string(self::NAME_CODE_STRING, 'gradereport_progress');
    }
   
    
    /**
     * Returns an array of parameters that this subreport consumes
     * @return array parameters consumed by subreport
     */
    static public function get_params() {
        $params = array();
        return $params;
    }
       
    /**
     * returns the availability of this subreport
     * @param int $courseid
     * @return bool
     */ 
    static public function available($courseid) {
        return true;
    }
    
    /**
     * returns whether or not the format is considered configured based on supplied parameters
     * @param array $params
     * @return bool
     */ 
    static public function requires_configuration($params) {
        return false;
    }    
        

}