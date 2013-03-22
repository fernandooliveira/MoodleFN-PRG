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
 * Class definition of subplugin
 *
 * @package    gradebook
 * @subpackage progress
 * @copyright  2011 MoodleFN
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
 
defined('MOODLE_INTERNAL') || die;

/**
 * PDF Formatter
 * @package gradebook
 * @subpackage progress
 */
class progress_report_format_pdf{
    /**
     * consumed by get_string
     * @const string HEADING
     */  
    const HEADING          = 'pdfreportheading';

    /**
     * consumed by get_string
     * @const string GENERATED
     */
    const GENERATED        = 'pdfreportgenerated';

    /**
     * consumed by get_string
     * @const string BY
     */
    const BY               = 'pdfreportby';

    /**
     * consumed by get_string
     * @const string FILE_NAME
     */  
    const FILE_NAME        = 'pdffilename';
    
    /**
     * consumed by get_string
     * @const string SHORT_NAME
     */  
    const SHORT_NAME       = 'pdf';
    
    /**
     * consumed by get_string
     * @const string NAME_CODE_STRING
     */  
    const NAME_CODE_STRING = 'pdf';

    /**
     * consumed by grade_get_setting
     * @const string SETTING_ENABLED
     */  
    const SETTING_ENABLED                = 'report_grade_progress_pdf_enabled';
    
    /**
     * renderer object used by formatter to create formatted outputs
     * override renderer in your theme to alter formatter outputs
     * @var object $renderer
     */  
    public $renderer;
    
    /**
     * tcpdf object
     * @var object $pdf
     */  
    public $pdf;
    
    /**
     * associative array of parameters used to build url query strings
     * @var array $params
     */  
    public $params;

    /**
     * collection of pdf content pages
     * @var array $pages
     */  
    public $pages = array();
    
    /**
     * Constructor. Initializes the progress report generator values for courseid, course, context, selected user id, and format
     * @param array $params an associative array of parameters used to build url query strings
     * @return void
     */
    public function __construct($params) {
        global $CFG, $PAGE;
        
        $this->params = $params;
        
        //include Moodle wrapper for TCPDF class
        require_once($CFG->libdir . '/pdflib.php');
        
        $this->renderer = $PAGE->get_renderer('gradereport_progress', 'pdf');
        $this->pdf = new pdf();

    }

    /**
     * Builds string representation of report html
     * @param array $contents data representation of report content
     * @return string
     */    
    public function format_content($contents) {
    
        $formatted  = "";
        
        $first = true;
        foreach ($contents as $content) { //each "content" is a report
            if (!$first) { //add page break after
                $formatted .= $this->renderer->add_html_page_break();
            }
            $formatted .= $this->format_content_recursive($content);
            $first = false;
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
                if ($content['html'] == $classname::HTML_FORM){
                    //do not write forms to pdf
                    break;
                }
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
        global $DB, $USER;

        $courseid = $this->params['id'];

        if (!$course = $DB->get_record('course', array('id' => $courseid))) {
            print_error('nocourseid');
        }

        $coursename = $course->fullname;

        $date       = date('Y_m_d');

        $author     = $USER->firstname . ' ' . $USER->lastname;

        $title      = get_string(self::HEADING, 'gradereport_progress') . ' ' . $coursename . ' ' . $user['firstname'] . ' ' . $user['lastname']
                      . ' ' .get_string(self::GENERATED, 'gradereport_progress') . ' ' . $date . ' ' . get_string(self::BY, 'gradereport_progress'). $author;

        $heading    = get_string(self::HEADING, 'gradereport_progress') . ' ' . $coursename . ' ' . $user['firstname'] . ' ' . $user['lastname']
                      . ': ' . $date;

        $html  = '';
        $html .= $this->renderer->write_html_document_start($title); //body tag and start of head
        $html .= $this->renderer->write_html_add_css();
        $html .= $this->renderer->write_html_document_end_head(); //end of head, start of body
        $html .= $this->renderer->write_html_document_front_page($heading); //end of head, start of body
        $html .= $formatted;
        $html .= $this->renderer->write_html_document_end();
        //echo $html;
        
        require_once('MPDF54/mpdf.php');

        $mpdf = new mPDF('utf-8','A4','','' , 0 , 0 , 0 , 0 , 0 , 0);
        $mpdf->SetDisplayMode('fullpage');
        $mpdf->WriteHTML($html);
        $mpdf->Output();
        
        exit;
        
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
        global $CFG;
        //rename setting because scope operator throws syntax error when used to get variable object property
        $classenabled = self::SETTING_ENABLED;
        //if the subplugin has not been installed, allow this to be false
        $enabled = isset($CFG->$classenabled)? $CFG->$classenabled : false;
        return grade_get_setting($courseid, $classenabled, $enabled);
    
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