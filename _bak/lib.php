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
 * File containing class definition for progress report generator
 *
 * @package    gradebook
 * @subpackage progress
 * @copyright  2011 MoodleFN
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once($CFG->libdir . '/accesslib.php');
require_once($CFG->libdir . '/grouplib.php');
require_once($CFG->dirroot . '/grade/report/progress/libformatters.php');
require_once($CFG->dirroot . '/grade/report/progress/libsubreports.php');

defined('MOODLE_INTERNAL') || die;

/**
 * Definition of progress report generator class.  Handles error checking and dynamic inclusion of formatters and subreports
 * Instantiates with subreport and formatter classes
 *
 * @package    gradebook
 * @subpackage progress
 * @copyright  2011 Moodle FN
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class progress_report_generator {                 
    ///defining application state constants
  
    /**
     * represents progress report generator has error
     * @const int STATE_ERROR
     */
    const STATE_ERROR = 0;
    
    /**
     * represents progress report generator is in 'Moodle' format with GUI
     * but subreports and/or a user must be selected
     * @const int STATE_CORE_CONFIGURATION_REQUIRED
     */
    const STATE_CORE_CONFIGURATION_REQUIRED = 1;
    
    /**
     * represents progress report generator is configured and outputting reports
     * in 'Moodle' format
     * @const int STATE_CORE_CONFIGURED
     */    
    const STATE_CORE_CONFIGURED = 2;
    
    /**
     * represents progress report generator is configured, an export option is activated
     * but the export option requires configuration
     * @const int STATE_FORMAT_CONFIGURATION_REQUIRED
     */    
    const STATE_FORMAT_CONFIGURATION_REQUIRED = 3;
    
    /**
     * represents progress report generator and export option are configured
     * @const int STATE_FORMAT_CONFIGURED
     */    
    const STATE_FORMAT_CONFIGURED = 4;
    
    ///defining content node types
    /**
     * content representing a container node
     * @const int NODE_CONTAINER
     */    
    const NODE_CONTAINER = 0;
    
    /**
     * content representing a text node
     * @const int NODE_TEXT
     */    
    const NODE_TEXT = 1;
    
    /**
     * content representing an image node
     * @const int NODE_IMAGE
     */    
    const NODE_AUTONOMOUS = 2;
    
    ///defining html tag values
    const HTML_DIV = 'div';
    const HTML_SPAN = 'span';
    const HTML_LIST_ORDERED = 'ol';
    const HTML_LIST_UNORDERED = 'ul';
    const HTML_LIST_ITEM = 'li';
    const HTML_DEFINITION_LIST = 'dl';
    const HTML_DEFINITION_TERM = 'dt';
    const HTML_DEFINITION_DEFINITION = 'dd';
    const HTML_PARAGRAPH = 'p';
    const HTML_HEADING_3 = 'h3';
    const HTML_HEADING_4 = 'h4';
    const HTML_HYPERLINK = 'a';
    const HTML_SELECT = 'select';
    const HTML_SELECT_OPTION = 'option';
    const HTML_LABEL = 'label';
    const HTML_FORM = 'form';
    const HTML_FIELDSET = 'fieldset';
    const HTML_LEGEND   = 'legend';
    const HTML_INPUT    = 'input';
    const HTML_TEXT_BOX = 'textarea';
    const HTML_TABLE = 'table';
    const HTML_TABLE_HEADER = 'th';
    const HTML_TABLE_CELL = 'td';
    const HTML_TABLE_ROW = 'tr';
    const HTML_IMAGE = 'img';

    ///Dummy user record is sometimes used to create grades that are offset from users for reporting purposes

    /**
     * property of progress report generator
     * @const string DUMMY_LAST_NAME
     */    
    const DUMMY_LAST_NAME = 'dummy user';  

    /**
     * property of progress report generator
     * @const string DUMMY_FIRST_NAME
     */    
    const DUMMY_FIRST_NAME = 'progress report';  

    /**
     * property of progress report generator
     * @const string DUMMY_EMAIL
     */    
    const DUMMY_EMAIL = 'root@localhost';  

    /**
     * property of progress report generator
     * @const string DUMMY_USER_NAME_SEED
     */    
    const DUMMY_USER_NAME_SEED = 'progress report generator';      
    
    ///defining component constants.  These are applied to highest level component boxes so that formatters can omit them
    
    /**
     * marker for report weight and user select ui content block
     * @const int COMPONENT_REPORT_SELECT
     */    
    const COMPONENT_REPORT_SELECT = 0;    
    
    /**
     * marker for list of format links in ui content block
     * @const int COMPONENT_FORMAT_SELECT
     */    
    const COMPONENT_FORMAT_SELECT = 1;  
    
    /**
     * marker for instruction block of content
     * @const int COMPONENT_INSTRUCTIONS
     */    
    const COMPONENT_INSTRUCTIONS = 2;    
    
    ///defining other constants used in functions
    
    /**
     * represents no selected user
     * @const int NO_USER_SELECTED
     */    
    const NO_USER_SELECTED = -1;    

    /**
     * defines default subreport weight
     * @const int DEFAULT_SUBREPORT_WEIGHT
     */    
    const DEFAULT_SUBREPORT_WEIGHT = 0;  
     
    /**
     * represents report with no weight such that it is not included
     * @const int WEIGHTLESS
     */    
    const WEIGHTLESS = -1;       
    
    /**
     * represents no selected user
     * @const int NO_USER_SELECTED
     */    
    const DEFAULT_FORMAT = 'base';  
         
    ///configuration variables
    
    /**
     * subreports to be handled by the progress report generator
     * @var array $subreports
     */    
    protected $allowedsubreports;
    
    /**
     * formats to be handled by the progress report generator
     * @var array $formats
     */    
    protected $allowedformats;
    
    ///initialization params
    
    /**
     * id of the user who is the subject of the report
     * @var int $selecteduserid
     */    
    protected $selecteduserid;

    /**
     * Permission context that the progress report generator is operating within
     * @var object $context
     */    
    protected $context;
    
    /**
     * id of the course being reported on
     * @var int $course
     */    
    protected $courseid;
    
    /**
     * Course object that the progress report generator is operating on
     * @var object $course
     */    
    protected $course;

    /**
     * Format for report output
     * @var string $format
     */    
    protected $format;
    
    ///state management
    /**
     * Error code.  Used for report outputs and debugging.
     * @var string $errorcode
     */    
    protected $errorcode;
    
    /**
     * Storage of users that $USER is permitted to report on in $this->context.  can be false
     * @var array $selectableusers
     */    
    protected $selectableusers;
    
    /**
     * Associative array for storing configuration of subreports, format, and progress report generator
     * @var array $config
     */
    protected $config = array();
    
    /**
     * Constructor. Initializes the progress report generator values for courseid, course, context, selected user id, and format
     * @param array $opt an associative array of configuration options, or a single integer representing courseid.
     * @return void
     */
    public function __construct($opt) {
        //if an associative array of parameters is specified, map them to object properties        
        if (is_array($opt)) {
            foreach ($opt as $key => $value) {
                switch ($key) {
                    case 'course' : 
                        if (gettype($value) == 'object') {
                            $this->course = $value;
                        }
                        break;
                    case 'courseid' :
                        if (gettype($value) == 'integer') {
                            $this->courseid = $value;
                        }
                        break;
                    case 'userid' :
                        if (gettype($value) == 'object') {
                            $this->selecteduserid = $value;
                        }
                        break;
                    case 'context' :
                        if (gettype($value) == 'object') {
                            $this->context = $value;
                        }
                        break;
                    case 'format' :
                        if (gettype($value) == 'string') {
                            $this->format = $value;
                        }
                        break;
                }
            }
        } else {
            //attempt to treat the single parameter as courseid
            if (gettype($opt) == 'integer') {
                $this->courseid = $opt;
            } else {
                //general initialization error
                print_error('errorinitcourseid', 'gradereport_progress', $CFG->wwwroot.'/grade/report/progress/index.php?' . $this->courseid);
            }
        }
        
        //attempt to define format
        if (!isset($this->format)) {
            $this->format = optional_param('format', self::DEFAULT_FORMAT, PARAM_ALPHANUM);
        }
        
        //attempt to define selecteduserid
        if (!isset($this->selecteduserid)) {
            $this->selecteduserid = optional_param('userid', self::NO_USER_SELECTED, PARAM_INT);
        }
        
        //attempt to define course
        if (!isset($this->course)) {
            if (!$this->course = $DB->get_record('course', array('id' => $this->courseid))) {
                print_error('nocourseid');
            }
        }
        //attempt to define context
        if (!isset($this->context)) {
            get_context_instance(CONTEXT_COURSE, $this->course->id);
        }
        
        //this needs to be changed to a subplugin framework when the framework is finished
        ///scraping for these as included files makes the report generator more flexible.
        $this->allowedsubreports = array('activity', 'category', 'section', 'summary');
        $this->allowedformats = array('base', 'email', 'pdf', 'print');
    }
    
    /**
     * Uses state to inform which content is included.  Sends content to formatter object.
     *
     * @return void
     */    
    public function report() {
        $state = $this->get_report_state();
        ///setting up variables according to state
        $content = array();
        $classname = '';
        //user is null or the selected user.  consumed by formatter
        $user = null;
        if ($state > self::STATE_CORE_CONFIGURATION_REQUIRED) {
            $user = $this->selectableusers[$this->selecteduserid];
            
        }
        //params for GET/POST/querystrings
        $params = array();
        if ($state > self::STATE_ERROR) {
            $params = array_merge($params, $this->config['urlparams']['core']);
            $params = array_merge($params, $this->config['urlparams']['allsubreports']);
        }
        if ($state > self::STATE_CORE_CONFIGURED) {
            $params = array_merge($params, $this->config['urlparams']['format']);
        }
        //report weights.  used to sort subreports
        $weights = array();
        if ($state == self::STATE_CORE_CONFIGURED || $state == self::STATE_FORMAT_CONFIGURED) {
            $i = 0;
            foreach ($this->config['subreports'] as $subreport) {
                if ($subreport['available']) {
                    $weights[$i] = $subreport['weight'];
                } else {
                    $weights[$i] = -1;
                }
                $i++;
            }
            asort($weights);
        }

        ///assembling content dependant on state.
        switch($state){
            case self::STATE_ERROR : 
                $classname = 'progress_report_format_' . self::DEFAULT_FORMAT;
                print_error($this->errorcode, 'gradereport_progress', $CFG->wwwroot.'/grade/report/progress/index.php?' . $this->courseid);
                break;
            
            case self::STATE_CORE_CONFIGURATION_REQUIRED :
                $content[] = $this->get_content_report_select_ui();
                $content[] = $this->get_content_report_instructions();
                $classname = 'progress_report_format_' . self::DEFAULT_FORMAT;
                break;
                
            case self::STATE_CORE_CONFIGURED :
                $content[] = $this->get_content_report_select_ui();
                //$content[] = $this->get_content_report_instructions();
                $content[] = $this->get_content_report_format_list();
                //get subreport content
                foreach ($weights as $key => $value) {
                    if ($value > self::WEIGHTLESS) {
                        $classname = $this->config['subreports'][$key]['classname'];
                        $instance = new $classname($user['id'], $this->courseid, $this->context, $this->course, $params);
                        $content[] = $instance->get_subreport_content();
                    }
                }
                //prepare formatter $classname
                $classname = 'progress_report_format_' . self::DEFAULT_FORMAT;
                break;
                
            case self::STATE_FORMAT_CONFIGURATION_REQUIRED :
                $classname = 'progress_report_format_' .$this->format;
                $content[] = $classname::get_content_configuration_form($params);
                $classname = 'progress_report_format_' . self::DEFAULT_FORMAT;
                break;
                
            case self::STATE_FORMAT_CONFIGURED :
                //get subreport content
                foreach ($weights as $key => $value) {
                    if ($value > self::WEIGHTLESS) {
                        $classname = $this->config['subreports'][$key]['classname'];
                        $instance = new $classname($user['id'], $this->courseid, $this->context, $this->course, $params);
                        $content[] = $instance->get_subreport_content();
                    }
                }
                $classname = 'progress_report_format_' .$this->format;
                //status messages handled in $formatter code
                break;
                
        }

        $formatter = new $classname($params);
        $formattedcontent = $formatter->format_content($content);
        $formatter->render_content($formattedcontent, $user);
        return true;
    }
    
    /**
     * Performs configuration required to determine the state of the progress report generator
     * @return int state
     */
    protected function get_report_state() {
        ///configure and validate selectable users
        if (!$this->selectableusers = $this->get_selectable_users($this->courseid)) {
            //may need to handle this based on role or do all errors in base format
            $this->errorcode = 'errornoselectableusers';
            return self::STATE_ERROR;
        }
        
        //validate user selection
        if ($this->selecteduserid != self::NO_USER_SELECTED && !isset($this->selectableusers[$this->selecteduserid])) {
            //userid is set and the selected user is invalid
            $this->errorcode = 'errorinvaliduserselected';
            return self::STATE_ERROR;            
        }
        ///configure url/post/get parameters
        $this->config['urlparams']  = array();
        //progress report generator parameters        
        $this->config['urlparams']['core'] = array('id' => $this->courseid);
        if ($this->format != self::DEFAULT_FORMAT) {
            $this->config['urlparams']['core']['format'] = $this->format;
        }
        if ($this->selecteduserid != self::NO_USER_SELECTED) {
            $this->config['urlparams']['core']['userid'] = $this->selecteduserid;
        }
        //subreport parameters
        $this->config['urlparams']['allsubreports'] = array();
        //$this->config['urlparams']['format'];
        
        ///configure and validate subreports
        $this->config['subreports'] = array();
        //$this->config['format'] = array();
        $i = 0;
        
        $validreports = false;
        $hasweight = false;
        
        foreach ($this->allowedsubreports as $subreport) {
            $classname = 'progress_report_subreport_' . $subreport;
            $this->config['subreports'][$i]['classname']  = $classname;
            $this->config['subreports'][$i]['shortname']  = $classname::SHORT_NAME;
            $this->config['subreports'][$i]['namestring'] = $classname::get_name_string();
            $this->config['subreports'][$i]['weight']     = $classname::get_weight($this->courseid);
            //configure and store parameters that are not set to default values
            $this->config['subreports'][$i]['params']     = $classname::get_params($this->config['subreports'][$i]['weight']);
            $this->config['urlparams'][$classname::SHORT_NAME] = $this->config['subreports'][$i]['params'];
            $this->config['urlparams']['allsubreports'] = array_merge($this->config['urlparams']['allsubreports'], 
                                                                      $this->config['urlparams'][$classname::SHORT_NAME]);
            
            $this->config['subreports'][$i]['available']  = $classname::available($this->courseid);         
            if ($this->config['subreports'][$i]['available']) {
                $validreports = true;
                if ($this->config['subreports'][$i]['weight'] > self::WEIGHTLESS) {
                    $hasweight = true;
                }
            }
            $i++;
        }
        if (!$validreports) {
            $this->errorcode = 'errornovalidreports';
            return self::STATE_ERROR;            
        }
        
        ///configure and validate formats
        $this->config['formats'] = array();
        $i = 0;
        $validformats = false;
        
        foreach ($this->allowedformats as $format) {
            $classname = 'progress_report_format_' . $format;
            
            $this->config['formats'][$i]['classname']  = $classname;
            $this->config['formats'][$i]['shortname']  = $classname::SHORT_NAME;
            $this->config['formats'][$i]['namestring'] = $classname::get_name_string();
            $this->config['formats'][$i]['available']  = $classname::available($this->courseid);
            ///check that selected format is available.
            if ($this->format == $this->config['formats'][$i]['shortname']) {
                if (!$this->config['formats'][$i]['available']) {
                    $this->errorcode = 'errorillegalformat';
                    return self::STATE_ERROR;            
                }
                //if selected format is available, get it's parameters
                $this->config['formats'][$i]['params'] = $classname::get_params();
                $this->config['urlparams']['format'] = $this->config['formats'][$i]['params'];
                $this->config['selectedformat'] = $i;
                $this->config['formats'][$i]['classname'] = $classname;
                $validformats = true;
            } else {
                if ($this->config['formats'][$i]['available']) {
                    $validformats = true;
                }
            }            
            $i++;
        }        
        
        ///determine state
        //if no user is selected OR all subreports lack weight
        if ($this->selecteduserid == self::NO_USER_SELECTED || !$hasweight) {
            return self::STATE_CORE_CONFIGURATION_REQUIRED;
        }
        //user is selected and there are subreports to process
        if ($this->format == self::DEFAULT_FORMAT) {
            return self::STATE_CORE_CONFIGURED;
        }
        if (isset($this->config['selectedformat'])) {
            $j = $this->config['selectedformat'];
            //$params = $this->config['formats'][$j]['params'];
            $params = array_merge($this->config['formats'][$j]['params'], $this->config['urlparams']['core']);
            $classname = $this->config['formats'][$j]['classname'];
            if ($classname::requires_configuration($params)) {
                return self::STATE_FORMAT_CONFIGURATION_REQUIRED;
            } else {
                return self::STATE_FORMAT_CONFIGURED;
            }
        }
        
        ///unknown error
        $this->errorcode = 'errorunknown';
        return self::STATE_ERROR;    
    }
    
    /**
     * Creates an array of users which are enrolled, gradeable and visible to the $USER
     *
     * @param int $courseid course report is drawn from
     * @param int $userid user of the report, NOT the subject
     * @return array or false
     */    
    public function get_selectable_users($courseid, $userid = null) {
        global $CFG, $DB, $USER;
        
        if (!isset($userid)) {
            $userid = $USER->id;
        }

        //get sql to restrict result set to users with gradeable roles
        $coursecontext = get_context_instance(CONTEXT_COURSE, $this->course->id);
        $relatedcontexts = get_related_contexts_string($coursecontext);
        list($gradebookroles_sql, $roleparams) = $DB->get_in_or_equal(explode(',', $CFG->gradebookroles), SQL_PARAMS_NAMED, 'grbr');
        $rolesql =   "SELECT DISTINCT ra.userid 
                      FROM {role_assignments} ra 
                      WHERE ra.roleid $gradebookroles_sql 
                      AND ra.contextid $relatedcontexts ";

        //get sql to restrict result set to enrolled users
        list($enrolledsql, $enrolledparams) = get_enrolled_sql($coursecontext);

        //initialize params that can change based on the role and settings of the course and progress report generator
        $samecoursesql = "";
        $samecourseparams = array();
        $menteesql = "SELECT u2.id 
                      FROM {role_assignments} ra1, {context} c1, {user} u2 
                      WHERE ra1.userid = ? 
                      AND ra1.contextid = c1.id 
                      AND c1.instanceid = u2.id 
                      AND c1.contextlevel = ".CONTEXT_USER;
        $menteeparams = array($userid);
        
        //if teacher and course has separate groups and teacher is subject to group boundaries
        if (has_capability('gradereport/progress:viewall', $coursecontext) && $this->course->groupmode == SEPARATEGROUPS && !has_capability('moodle/site:accessallgroups', $coursecontext)) {
            //get same group members
            $groupssql = "SELECT g.id 
                          FROM {groups} g 
                          JOIN {groups_members} gm ON gm.groupid = g.id 
                          WHERE gm.userid = ? AND g.courseid = ? ";
                                  
            $groupmembersql =  "SELECT DISTINCT gm1.userid 
                                FROM {groups_members} gm1 
                                WHERE gm1.groupid IN ($groupssql)";
                                
            $groupsparams = array($userid, $courseid);
            
            //get all users who are either mentees or sharing the USER's group in this course.
            $samecoursesql =  "JOIN 
                              (
                              SELECT u1.id 
                              FROM {user} u1 
                              WHERE u1.id IN($groupmembersql) 
                                 OR u1.id IN($menteesql) 
                              ) jg ON jg.id = u.id ";
            $samecourseparams = array_merge($samecourseparams, $groupsparams);
            $samecourseparams = array_merge($samecourseparams, $menteeparams);
                              
        }
        //not a teacher
        if (!has_capability('gradereport/progress:viewall', $coursecontext)){ 
            if (grade_get_setting($courseid, 'grade_report_progress_allow_students', false, false)) {
                $samecoursesql =  "JOIN 
                                  (
                                  SELECT u1.id 
                                  FROM {user} u1 
                                  WHERE u1.id = ? 
                                     OR u1.id IN($menteesql) 
                                  ) jm ON jm.id = u.id ";     
                $samecourseparams[] = $userid;
                $samecourseparams = array_merge($samecourseparams, $menteeparams);                          
            } else { //cannot report on self and is student
                //get mentees only
                $samecoursesql = "JOIN ($menteesql) jm ON jm.id = u.id ";
                $samecourseparams =  array_merge($samecourseparams, $menteeparams);   
            }
        }
        //$samecoursesql is empty string for users with gradereport/progress:viewall and no group limitations
        $userssql =  "SELECT u.id, u.firstname, u.lastname 
                      FROM {user} u 
                      JOIN ($enrolledsql) je ON je.id = u.id 
                      $samecoursesql 
                      JOIN ($rolesql)     jr ON jr.userid = u.id 
                      WHERE u.deleted = 0 
                      ORDER BY u.firstname";
                      
        //merge parameter lists for use in $DB->get_recordset_sql
        $params = array_merge($enrolledparams, $samecourseparams);
        $params = array_merge($params, $roleparams);

        //return recordset into associative array or return false.
        if (!$users_rs = $DB->get_recordset_sql($userssql, $params)){
            return false;
        } else {
            $users = array();
            foreach ($users_rs as $record) {
                $users[$record->id] = array('id' => $record->id , 'firstname' => $record->firstname, 'lastname' => $record->lastname);
            }
            return $users;
        }
    }
   
    /**
     * Builds a content array representing the nodes making up the report and user selects.
     *
     * @return array
     */        
    public function get_content_report_select_ui() {
        //box
        $item = self::build_content_container_node(self::HTML_DIV, array('id' => 'progress-select-ui'), self::COMPONENT_REPORT_SELECT);
        
        //heading                           
        $item['children'][0] = self::build_content_container_node(self::HTML_HEADING_3);
        $text = get_string('corereportselectuiheading', 'gradereport_progress');
        $item['children'][0]['children'][0] = self::build_content_text_node($text);
        
        //form
        $urlparams = array_merge($this->config['urlparams']['allsubreports'], array('id'=>$this->courseid));
        $url = new moodle_url('/grade/report/progress/index.php', $urlparams);
        $item['children'][1] = self::build_content_container_node(self::HTML_FORM, array('id' => 'progress-report-form', 'action' => $url, 'method' => 'post'));
        
        //subreport weight boxes
        $hiddentext = get_string('corereportselectuihiddenweight', 'gradereport_progress');
        $i = 0;
        foreach ($this->config['subreports'] as $subreport) {
            if ($subreport['available']) {
                $inputid = 'progress-' . $subreport['shortname'] . '-select';
                $text    = $subreport['namestring'];
                $weight  = $subreport['weight'];
                $weightstring = $subreport['shortname'] . 'weight';

                //label
                $item['children'][1]['children'][$i] = self::build_content_container_node(self::HTML_LABEL, array('for' => $inputid));

                //label text
                $item['children'][1]['children'][$i]['children'][0] = self::build_content_text_node($text);

                //span
                $item['children'][1]['children'][$i]['children'][1] = self::build_content_container_node(self::HTML_SPAN, array('class' => 'screenreader'));

                //hidden appended text
                $item['children'][1]['children'][$i]['children'][1]['children'][0] = self::build_content_text_node($hiddentext);
                
                //input box
                $i++;
                $item['children'][1]['children'][$i] = self::build_content_autonomous_node(self::HTML_INPUT, array('id' => $inputid, 'name' => $weightstring, 'type' => 'text', 'value' => $weight));
                 $i++;
            }
        }
       
        //ensure there are users to select
        if ($this->selectableusers) {
            $hiddentext = get_string('corereportselectuihiddenuser', 'gradereport_progress');
            $text       = get_string('corereportselectuiuserselect', 'gradereport_progress');
            
            //label
            $item['children'][1]['children'][$i] = self::build_content_container_node(self::HTML_LABEL, array('for' => 'user_select'));

            //label text
            $item['children'][1]['children'][$i]['children'][0] = self::build_content_text_node($text);

            //span
            $item['children'][1]['children'][$i]['children'][1] = self::build_content_container_node(self::HTML_SPAN, array('class' => 'screenreader'));

            //hidden appended text
            $item['children'][1]['children'][$i]['children'][1]['children'][0] = self::build_content_text_node($hiddentext);            
            
            //select user control
            $i++;
            $item['children'][1]['children'][$i] = self::build_content_container_node(self::HTML_SELECT, array('id' => 'user_select', 'name' => 'userid'));
            
            //select user options
            $j = 0;
            foreach ($this->selectableusers as $selectable) {
                $fullname = $selectable['firstname'] . ' ' . $selectable['lastname'];
                $value = $selectable['id'];
                if ($selectable['id'] == $this->selecteduserid) {
                    //option selected
                    $item['children'][1]['children'][$i]['children'][$j] = self::build_content_container_node(self::HTML_SELECT_OPTION, array('value' => $value, 'selected' => 'selected' ));
                    $item['children'][1]['children'][$i]['children'][$j]['children'][0] = self::build_content_text_node($fullname);
                } else {
                    //option not selected
                    $item['children'][1]['children'][$i]['children'][$j] = self::build_content_container_node(self::HTML_SELECT_OPTION, array('value' => $value));
                    $item['children'][1]['children'][$i]['children'][$j]['children'][0] = self::build_content_text_node($fullname);
                }
                $j++;                                              
            }
        }
        $i++;
        $item['children'][1]['children'][$i] = self::build_content_autonomous_node(self::HTML_INPUT, 
                                                                      array('id'    => 'progress-select-button',
                                                                            'type'  => 'submit',
                                                                             'name'  => 'report-select-submit',
                                                                             'value' => get_string('corereportselectuisubmit', 'gradereport_progress')
                                                                            ));
        return $item;
    }
    
    /**
     * Builds a content array representing the nodes making up the list of format export links.
     *
     * @return array
     */
    public function get_content_report_format_list() {
        //box
        $item = self::build_content_container_node(self::HTML_DIV, array('id' => 'progress-format-list'), 
                                                   self::COMPONENT_FORMAT_SELECT);
        //heading                           
        $item['children'][0] = self::build_content_container_node(self::HTML_HEADING_3);
        $text = get_string('corereportformatlistheading', 'gradereport_progress');
        $item['children'][0]['children'][0] = self::build_content_text_node($text);
        
        //list
        $item['children'][1] = self::build_content_container_node(self::HTML_LIST_UNORDERED);
        
        //list items for each format
        $urlparams = array_merge($this->config['urlparams']['allsubreports'], $this->config['urlparams']['core']);
        $i = 0;
        foreach ($this->config['formats'] as $format) {
                if ($format['available']) {
                    $url = new moodle_url('/grade/report/progress/index.php', $urlparams);
                    $override = array('format' => $format['shortname']);
                    $url->params($override);
                    $item['children'][1]['children'][$i] = self::build_content_container_node(self::HTML_LIST_ITEM);
                    $item['children'][1]['children'][$i]['children'][0] = self::build_content_container_node(self::HTML_HYPERLINK, array('target' => '_blank', 'href' => $url));
                    $item['children'][1]['children'][$i]['children'][0]['children'][0] = self::build_content_text_node($format['namestring']);
                    $i++;
                }
        }
        return $item;
    }
    
    /**
     * Builds a content array representing the nodes making up the progress report's instructions.
     *
     * @return array
     */    
    public function get_content_report_instructions() {
        //box
        $item = self::build_content_container_node(self::HTML_DIV, array('id' => 'progress-instructions'), 
                                                   self::COMPONENT_INSTRUCTIONS);
        //heading                           
        $item['children'][0] = self::build_content_container_node(self::HTML_HEADING_3);
        $text = get_string('coreinstructionsheading', 'gradereport_progress');
        $item['children'][0]['children'][0] = self::build_content_text_node($text);
        
        //paragraph
        $item['children'][1] = self::build_content_container_node(self::HTML_PARAGRAPH);
        $text = get_string('coreinstructionsparagraph', 'gradereport_progress');
        $item['children'][1]['children'][0] = self::build_content_text_node($text);
        
        //list
        $item['children'][2] = self::build_content_container_node(self::HTML_LIST_ORDERED);
        
        //list item 1
        $item['children'][2]['children'][0] = self::build_content_container_node(self::HTML_LIST_ITEM);
        $text = get_string('coreinstruction1', 'gradereport_progress');
        $item['children'][2]['children'][0]['children'][0] = self::build_content_text_node($text);
        
        //list item 2
        $item['children'][2]['children'][1] = self::build_content_container_node(self::HTML_LIST_ITEM);
        $text = get_string('coreinstruction2', 'gradereport_progress');
        $item['children'][2]['children'][1]['children'][0] = self::build_content_text_node($text);
        
        //list item 3
        $item['children'][2]['children'][2] = self::build_content_container_node(self::HTML_LIST_ITEM);
        $text = get_string('coreinstruction3', 'gradereport_progress');
        $item['children'][2]['children'][2]['children'][0] = self::build_content_text_node($text);
        
        //list item 4
        $item['children'][2]['children'][3] = self::build_content_container_node(self::HTML_LIST_ITEM);
        $text = get_string('coreinstruction4', 'gradereport_progress');
        $item['children'][2]['children'][3]['children'][0] = self::build_content_text_node($text);
                                                                                     
        return $item;   
    }
    /* copy to formatter and modify self::
    public function get_content_header_format() {
        $item = self::build_content_node(self::COMPONENT_FORMAT_HEADER, self::NODE_CONTAINER, 
                                         self::HTML_DIV, array('id' => 'progress-header'), null);
                                   
        $item['children'][0] = self::build_content_node(null, self::NODE_CONTAINER, self::HTML_HEADING, null);
        
        $item['children'][0]['children'][] = self::build_content_node(null, self::NODE_TEXT, null, null, 
                                                                     get_string('coreheadingformat', 'gradereport_progress') . 
                                                                     $this->selectableusers[$this->selecteduserid]['firstname'] .
                                                                     ' ' . $this->selectableusers[$this->selecteduserid]['lastname']);
        return $item;   
    }
    */
    
    /**
     * Builds an array representing a single element of text content.
     *
     * @param string $text text content
     * @return array
     */    
    static public function build_content_text_node($text) {
        $item = array();
        $item['component']  = null;
        $item['type']       = self::NODE_TEXT;
        $item['html']       = null;
        $item['attributes'] = null;
        $item['text']       = $text;
        return $item;
    }
    
    /**
     * Builds an array representing a single content node.
     *
     * @param int $component a CONSTANT used for tagging box content so that some formatters might ignore it.
     * @param string $tag a CONSTANT value mapping the content to an HTML tag
     * @param array $attributes attribute values for consumption by HTML/XML formatters
     * @return array
     */    
    static public function build_content_container_node($tag, $attributes = array(), $component = null) {
        $item = array();
        $item['component']  = $component;
        $item['type']       = self::NODE_CONTAINER;
        $item['html']       = $tag;
        $item['attributes'] = $attributes;
        $item['text']       = null;
        return $item;
    }
    
    /**
     * Builds an array representing a single content node.
     *
     * @param string $tag a CONSTANT value mapping the content to an HTML tag
     * @param array $attributes attribute values for consumption by HTML/XML formatters
     * @return array
     */    
    static public function build_content_autonomous_node($tag, $attributes = array()) {
        $item = array();
        $item['component']  = null;
        $item['type']       = self::NODE_AUTONOMOUS;
        $item['html']       = $tag;
        $item['attributes'] = $attributes;
        $item['text']       = null;
        return $item;
    }
     
    /**
     * determines whether $USER can report on selected user in the context of the course.
     * this function should be consumed by subreports, where they use information from contexts 
     * outside of this class' instance.
     *
     * @param int $courseid course report is drawn from
     * @param int $userid user of the report, NOT the subject
     * @param int $selecteduserid
     * @return bool
     */        
    static public function validate_selected_user($courseid, $userid = null, $selecteduserid) {
        global $CFG, $DB, $USER;
        
        if (!isset($userid)) {
            $userid = $USER->id;
        }
        $course = $DB->get_record('course', array('id' => $courseid));
        
        //get sql to restrict result set to users with gradeable roles
        $coursecontext = get_context_instance(CONTEXT_COURSE, $courseid);
        $relatedcontexts = get_related_contexts_string($coursecontext);
        list($gradebookroles_sql, $roleparams) = $DB->get_in_or_equal(explode(',', $CFG->gradebookroles), SQL_PARAMS_NAMED, 'grbr');
        $rolesql =   "SELECT DISTINCT ra.userid 
                      FROM {role_assignments} ra 
                      WHERE ra.roleid $gradebookroles_sql 
                      AND ra.contextid $relatedcontexts ";

        //get sql to restrict result set to enrolled users
        list($enrolledsql, $enrolledparams) = get_enrolled_sql($coursecontext);

        //initialize params that can change based on the role and settings of the course and progress report generator
        $samecoursesql = "";
        $samecourseparams = array();
        $menteesql = "SELECT u2.id 
                      FROM {role_assignments} ra1, {context} c1, {user} u2 
                      WHERE ra1.userid = ? 
                      AND ra1.contextid = c1.id 
                      AND c1.instanceid = u2.id 
                      AND c1.contextlevel = ".CONTEXT_USER;
        $menteeparams = array($userid);
            
        if (has_capability('gradereport/progress:viewall', $coursecontext) && $course->groupmode == SEPARATEGROUPS && !has_capability('moodle/site:accessallgroups', $coursecontext)) {
            //get same group members
            $groupssql = "SELECT g.id 
                          FROM {groups} g 
                          JOIN {groups_members} gm ON gm.groupid = g.id 
                          WHERE gm.userid = ? AND g.courseid = ? ";
                                  
            $groupmembersql =  "SELECT DISTINCT gm1.userid 
                                FROM {groups_members} gm1 
                                WHERE gm1.groupid IN ($groupssql)";
                                
            $groupsparams = array($userid, $courseid);
            
            //get all users who are either mentees or sharing the USER's group in this course.
            $samecoursesql =  "JOIN 
                              (
                              SELECT u1.id 
                              FROM {user} u1 
                              WHERE u1.id IN($groupmembersql) 
                                 OR u1.id IN($menteesql) 
                              ) jg ON jg.id = u.id ";
            $samecourseparams = array_merge($samecourseparams, $groupsparams);
            $samecourseparams = array_merge($samecourseparams, $menteeparams);
                              
        }
        
        if (!has_capability('gradereport/progress:viewall', $coursecontext)){
            if (grade_get_setting($courseid, 'grade_report_progress_allow_students', false, false)) {
                $samecoursesql =  "JOIN 
                                  (
                                  SELECT u1.id 
                                  FROM {user} u1 
                                  WHERE u1.id = ? 
                                     OR u1.id IN($menteesql) 
                                  ) jm ON jm.id = u.id ";     
                $samecourseparams[] = $userid;
                $samecourseparams = array_merge($samecourseparams, $menteeparams);                          
            } else { //cannot report on self and is student
                //get mentees only
                $samecoursesql = "JOIN ($menteesql) jm ON jm.id = u.id ";
                $samecourseparams =  array_merge($samecourseparams, $menteeparams);   
            }
        }
    

        $userssql =  "SELECT u.id  
                      FROM {user} u 
                      JOIN ($enrolledsql) je ON je.id = u.id 
                      $samecoursesql 
                      JOIN ($rolesql)     jr ON jr.userid = u.id 
                      WHERE u.deleted = 0 AND u.id = :u_uid 
                      ORDER BY u.firstname";
                      
        //merge parameter lists for use in $DB->get_recordset_sql
        $params = array_merge($enrolledparams, $samecourseparams);
        $params = array_merge($params, $roleparams);
        //last parameter checks that the u.id is the one selected.
        $params['u_uid'] = $selecteduserid;
        
        //return recordset into associative array or return false.
        if (!$users_rs = $DB->get_recordset_sql($userssql, $params)){
            return false;
        } else {
            return true;
        }
    }
    
    /**
     * Returns id of the PRG dummy user.  This user is used to clone and extend the other grades of users
     * for reporting purposes.  Returns false if the dummy user is in use by another $USER.  This prevents
     * the dummy user from becoming a mashup of several users and eliminates the situation where many dummy
     * users clutter the {user} table.
     *
     * @return int or false
     */    
    static public function get_dummy_user_id($courseid) {    
        global $DB, $CFG;
        require_once($CFG->libdir  . '/enrollib.php');
        require_once($CFG->dirroot . '/user/lib.php');
        
        $firstname = self::DUMMY_FIRST_NAME;
        $lastname  = self::DUMMY_LAST_NAME;
        
        //***user_delete_user breaks the email and username such that these cannot identify the dummy user.
        //***instead we must use firstname and lastname and sift through the non-unique result set  
        $params = array($firstname, $lastname, 1);
        $sql = "SELECT id, deleted " .
               "FROM {user} " . 
               "WHERE firstname = ? AND lastname = ? AND deleted = ?";
        if ($dummies = $DB->get_records_sql($sql, $params)) {
            foreach ($dummies as $dummy) {
                //set dummy deleted to 0 - locking prg until the plugin is done with the dummy.
                $dummy->deleted = 0;
                $DB->update_record('user', $dummy);
                $dummyid = $dummy->id;
                break;
            }
        } else {
            $newdummy = self::get_dummy_user();
            $dummyid = user_create_user($newdummy);
        }
        
        $coursecontext = get_context_instance(CONTEXT_COURSE, $courseid, MUST_EXIST);
        //enroll the dummy user
        if (!is_enrolled($coursecontext, $dummyid, '', true)) {
            enrol_try_internal_enrol($courseid, $dummyid);
        }         
        return $dummyid;
    }

    /**
     * Returns a dummy user object to write to the database.
     *
     * @return object dummy user
     */    
    static public function get_dummy_user() {
        //create the dummy
        //***user_delete_user breaks the email and username such that these cannot identify the dummy user.
        $newdummy = new stdClass();
        $randuname = self::get_random_string(8);
        $randemail = $randuname . '@' . $randuname . '.com';
        
        $newdummy->username = $randuname;
        $newdummy->firstname = self::DUMMY_FIRST_NAME;
        $newdummy->lastname  = self::DUMMY_LAST_NAME;
        $newdummy->email     = $randemail;
        $newdummy->password = self::get_random_string(12);
        
        return $newdummy;
    }

    /**
     * Generates and returns a random alphanum string
     *
     * @return string
     */    
    static public function get_random_string($strlen) {    
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $randstring = '';
        for ($i = 0; $i < $strlen; $i++) {
            $randstring .= $characters[rand(0, strlen($characters) -1)];
        }
        return $randstring;
    }
    
    /**
     * Deletes the dummy user.
     *
     * @return bool
     */    
    static public function delete_dummy_user($userid) {    
        global $DB, $CFG;
        require_once($CFG->dirroot . '/user/lib.php');
        
        $params = array($userid);
        $sql = "SELECT * " .
               "FROM {user} " . 
               "WHERE id = ? ";
        
        if ($user = $DB->get_record_sql($sql, $params)) {
            //***user_delete_user breaks the email and username such that these cannot identify the dummy user.
            if (user_delete_user($user)) {
                return true;
            } else {
                //attempt to change flag manually.  This will not do cleanup.
                $user->deleted = 1;
                $DB->update_record('user', $user);
            }
        } else {
            return false;
        }
    }
    
}