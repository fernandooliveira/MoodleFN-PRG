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
 * Subplugin class definition
 *
 * @package    gradebook
 * @subpackage progress
 * @copyright  2011 MoodleFN
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
 
require_once($CFG->dirroot . '/grade/report/lib.php');
require_once($CFG->dirroot . '/grade/lib.php');
require_once($CFG->dirroot . '/grade/report/lib.php');
require_once($CFG->dirroot . '/course/lib.php');

defined('MOODLE_INTERNAL') || die;

/**
 * Section subreport
 * @package gradebook
 * @subpackage progress
 */
class progress_report_subreport_section{
    /**
     * consumed by get_string
     * @const string NAME_CODE_STRING
     */
    const NAME_CODE_STRING               = 'section';
    /**
     * consumed by get_string
     * @const string TITLE_STRING
     */
    const TITLE_STRING                   = 'sectiontitle';
    /**
     * consumed by get_string
     * @const string NOT_APPLICABLE_STRING
     */
    const NOT_APPLICABLE_STRING          = 'sectionnotapplicable';

    /**
     * consumed by get_string
     * @const string TOTAL_ACTIVITIES
     */
    const TOTAL_ACTIVITIES   = 'sectiontotal';
    
    /**
     * consumed by get_string
     * @const string COMPLETE
     */
    const COMPLETE   = 'sectioncomplete';
    /**
     * consumed by get_string
     * @const string INCOMPLETE
     */
    const INCOMPLETE = 'sectionincomplete';
    /**
     * consumed by get_string
     * @const string SAVED_NOT_SUBMITTED
     */
    const SAVED_NOT_SUBMITTED   = 'sectionsaved';
    /**
     * consumed by get_string
     * @const string NOT_ATTEMPTED
     */
    const NOT_ATTEMPTED = 'sectionnotattempted';
    /**
     * consumed by get_string
     * @const string WAITING_FOR_GRADE
     */
    const WAITING_FOR_GRADE   = 'sectionwaitingforgrade';

    /**
     * consumed by grade_get_setting
     * @const string SETTING_ENABLED
     */
    const SETTING_ENABLED                = 'report_grade_progress_sec_enabled';
    /**
     * consumed by grade_get_setting
     * @const string SETTING_WEIGHT
     */
    const SETTING_WEIGHT                 = 'report_grade_progress_sec_weight';

    /**
     * short name for subreport used for tagging URL/POST/GET parameters
     * @const string SHORT_NAME
     */
    const SHORT_NAME                     = 'sec';

    /**
     * id of the user who is the subject of the subreport
     * @var int $userid
     */
    protected $userid;

    /**
     * id of the course being reported on
     * @var int $courseid
     */
    protected $courseid;

    /**
     * Moodle course object
     * @var object $course
     */
    protected $course;
    
    /**
     * Moodle context object
     * @var object $context
     */
    protected $context;

    /**
     * object representation of gradeable course items
     * @var object $gtree
     */
    protected $gtree;

    /**
     * whether or not $USER can view hidden content
     * @var bool $canviewhidden
     */
    protected $canviewhidden;

    /**
     * collection of grade item activities
     * @var array $activities
     */
    protected $activities = array();

    /**
     * collection of course sections with grade and activity information
     * @var array $sections
     */
    protected $sections = array();

    /**
     * array representation of module instances and course module ids
     * @var array $coursemodules
     */
    protected $coursemodules = array();
    
    /**
     * array of module names and ids
     * @var array $modules
     */
    protected $modules = array();
    
    /**
     * Constructor. Sets local copies of user preferences
     * @param int $userid
     * @param object $course
     * @param object $context
     * @param object $params
     * @param string $format The format to output the report in
     */
    public function __construct($userid, $courseid, $context, $course, $params) {
        global $DB, $CFG;

        $this->userid = $userid;
        $this->courseid = $courseid;
        $this->course = $course;
        $this->context = $context;
        $this->canviewhidden = has_capability('moodle/grade:viewhidden', get_context_instance(CONTEXT_COURSE, $this->courseid));
        //grade tree - no fillers, category grade item is not last child, no outcomes
        $this->gtree         = new grade_tree($this->courseid, false, false, null, false);

        $this->initialize_modules();
        $this->initialize_course_modules();
        $this->initialize_activities();
        $this->initialize_sections();
    }

    /**
     * Builds $this->modules
     */
    protected function initialize_modules(){
        global $DB;
        $sql = "SELECT name, id  
                FROM {modules} ";
        $params = array();
        $modules = $DB->get_recordset_sql($sql, $params);
        foreach ($modules as $module) {
            $this->modules[$module->name] = $module->id;
        }
    }

    /**
     * Builds $this->coursemodules
     */
    protected function initialize_course_modules(){
        global $DB;
        $sql = "SELECT id, module, instance 
                FROM {course_modules} 
                WHERE course = ? ";
        $params = array($this->courseid);
        $cms = $DB->get_recordset_sql($sql, $params);        
        foreach ($cms as $cm) {
            $this->coursemodules[$cm->module][$cm->instance] = $cm->id;
        }
    }    
    
    /**
     * Builds $this->activities
     */
    protected function initialize_activities(){
        $this->initialize_activities_recursive($this->gtree->top_element);
    }

    /**
     * Uses grade items and grade grades to gather data on gradeable activities completed by a user
     * @param object $element
     */
    protected function initialize_activities_recursive(&$element){
        global $DB, $CFG;

        $type = $element['type'];
        $grade_object = $element['object'];
        $eid = $grade_object->id;

        // End recursion if this is a hidden grade category, hide it completely from the user
        if ($type == 'category' && $grade_object->is_hidden() && !$this->canviewhidden) {
            return false;
        }

        /// Process those items that have scores associated
        if ($type == 'category' or $type == 'item' or $type == 'categoryitem' or $type == 'courseitem') { //category and courseitem are included for their children
            if ($type == 'item') {//interested in this
                $activity = array();

                if (! $grade_grade = grade_grade::fetch(array('itemid'=>$grade_object->id,'userid'=>$this->userid))) {
                    //user has no grade for this grade item
                    $grade_grade = new grade_grade();
                    $grade_grade->userid = $this->userid;
                    $grade_grade->itemid = $grade_object->id;
                    $activity['incomplete'] = true;
                } else {
                   $activity['incomplete'] = false;
                }
                $grade_grade->load_grade_item();

                /// Hidden Items
                $activity['hidden'] = $grade_grade->grade_item->is_hidden();

                /// Actual Grade
                $activity['gradeval'] = $grade_grade->finalgrade;

                /// Has the user passed
                $activity['pass'] = $grade_grade->is_passed($grade_object);

                /// Percentage and Date Submitted
                $activity['datesubmitted'] = 0;
                $activity['percentage'] = 0;
                if ($grade_object->needsupdate == false){
                    $activity['datesubmitted'] = $grade_grade->get_datesubmitted();
                    $activity['percentage'] = grade_format_gradevalue($activity['gradeval'], $grade_grade->grade_item, true, GRADE_DISPLAY_TYPE_PERCENTAGE);
                }

                ///submitted but not yet marked... at present, only assignments are considered.
                $itemtype = $grade_object->itemtype;
                $itemmodule = $grade_object->itemmodule;
                $iteminstance = $grade_object->iteminstance;
                $activity['waiting'] = true;
                $activity['savednotfinal'] = false;
                if ($itemtype=='mod' && $iteminstance && $itemmodule=='assignment') {
                    $params = array($this->userid, $iteminstance);
                    $sql = "SELECT id FROM {assignment_submissions} " .
                           "WHERE  timemodified < timemarked " .
                           "  AND  userid = ? AND assignment = ?";
                    if ($waitingforgrade = $DB->get_record_sql($sql, $params)) {
                        
                        $activity['waiting'] = false;
                    }
                    //assignment record
                    if (!$assignment = $DB->get_record("assignment", array("id"=>$iteminstance))) {
                        print_error('invalidid', 'assignment');
                    }
                    if ($assignment->assignmenttype == 'upload') {
                        require_once($CFG->dirroot . '/mod/assignment/type/upload/assignment.class.php');
                        if (! $cm = get_coursemodule_from_instance("assignment", $assignment->id, $this->course->id)) {
                            print_error('invalidcoursemodule');
                        }
                        $instance = new assignment_upload($cm->id, $assignment, $cm, $this->course);
                        if ($submission = $instance->get_submission($this->userid)) {
                            $filecount = $instance->count_user_files($submission->id);
                            if ($filecount) {
                                if ($instance->drafts_tracked() or $instance->isopen() or !$instance->is_finalized($submission)) {
                                    $activity['savednotfinal'] = true;
                                }
                            }
                        }
                    }
                }

                if (isset($iteminstance) && isset($itemmodule)) {
                    $mid  = $this->modules[$itemmodule];
                    $cmid = $this->coursemodules[$mid][$iteminstance];
                    $this->activities[$cmid] = $activity;
                }
                
            }
            if (isset($element['children'])) {
                foreach ($element['children'] as $key=>$child) {
                    $this->initialize_activities_recursive($element['children'][$key]);
                }
            }
        }
    }

    /**
     * builds $this->sections
     */
    protected function initialize_sections(){
        $sections = get_all_sections($this->courseid);
        $viewhiddensections = (has_capability('moodle/course:viewhiddensections', $this->context));
        //print_r($this->activities);
        foreach ($sections as $section) {
            $showsection = ($viewhiddensections or $section->visible or !$this->course->hiddensections);
            if ($showsection) {
                $totalactivities   = 0;
                $countcomplete     = 0;
                $countincomplete   = 0;
                $countsaved        = 0;
                $countnotattempted = 0;
                $countwaiting      = 0;
                $name = get_section_name($this->course, $section);
                if (!empty($section->sequence)) {
                    $sectionmods = explode(",", $section->sequence);
                    foreach ($sectionmods as $cmid) {
                        if (isset($this->activities[$cmid])) { //if the activity can be seen by the user requesting report
                            $totalactivities++;

                            //not submitted at all
                            if ($this->activities[$cmid]['incomplete']) { //attempted
                                $countnotattempted++;
                            }
                            //submitted and passed
                            if (!$this->activities[$cmid]['incomplete'] && $this->activities[$cmid]['pass']) { //complete/incomplete
                                $countcomplete++;
                            }
                            //failing grade but submitted
                            if (!$this->activities[$cmid]['incomplete'] && !$this->activities[$cmid]['pass']) { //complete/incomplete
                                $countincomplete++;
                            }
                            //waiting for grade
                            if ($this->activities[$cmid]['waiting']) {
                                $countwaiting++;
                            }
                            //saved but not submitted
                            if ($this->activities[$cmid]['savednotfinal']) {
                                $countsaved++;
                            }

                        }
                    }
                }
                $item = array();
                $item['total'] = $totalactivities;
                $item['complete'] = $countcomplete;
                $item['incomplete'] = $countincomplete;
                $item['saved'] = $countsaved;
                $item['notattempted'] = $countnotattempted;
                $item['waiting'] = $countwaiting;
                $item['name'] = $name;
                $this->sections[] = $item;
            }
        }


    }

    /**
     * Create an array representation of subreport based on activities
     * @return array content representation of subreport
     */
    public function get_subreport_content() {
        //strings
        $strings = array();
        $strings['waiting']      = get_string(self::WAITING_FOR_GRADE, 'gradereport_progress');
        $strings['total']        = get_string(self::TOTAL_ACTIVITIES, 'gradereport_progress');
        $strings['complete']     = get_string(self::COMPLETE, 'gradereport_progress');
        $strings['incomplete']   = get_string(self::INCOMPLETE, 'gradereport_progress');
        $strings['saved']        = get_string(self::SAVED_NOT_SUBMITTED, 'gradereport_progress');
        $strings['notattempted'] = get_string(self::NOT_ATTEMPTED, 'gradereport_progress');

        //progress report generator class name
        $prg = "progress_report_generator";

        //box
        $item = $prg::build_content_container_node($prg::HTML_DIV, array('id' => 'progress-subreport-' . self::SHORT_NAME));
        ///title
        $item['children'][0] = $prg::build_content_container_node($prg::HTML_HEADING_3);
        $text = get_string(self::TITLE_STRING, 'gradereport_progress');
        $item['children'][0]['children'][0] = $prg::build_content_text_node($text);
        //ul
        $item['children'][1] = $prg::build_content_container_node($prg::HTML_LIST_UNORDERED);
        
        $i = 0;
        foreach ($this->sections as $section) {
            //li
            $item['children'][1]['children'][$i] = $prg::build_content_container_node($prg::HTML_LIST_ITEM);

            //heading 4
            $item['children'][1]['children'][$i]['children'][0] = $prg::build_content_container_node($prg::HTML_HEADING_4);
            $item['children'][1]['children'][$i]['children'][0]['children'][0] = $prg::build_content_text_node($section['name']);

            //dl
            $item['children'][1]['children'][$i]['children'][1] = $prg::build_content_container_node($prg::HTML_DEFINITION_LIST);
            
            $j = 0;
            //dt complete
            $attributes = array();
            $text = $strings['total'];
            $item['children'][1]['children'][$i]['children'][1]['children'][$j] = $prg::build_content_container_node($prg::HTML_DEFINITION_TERM, $attributes);
            $item['children'][1]['children'][$i]['children'][1]['children'][$j]['children'][0] = $prg::build_content_text_node($text);
            $j++;
            //dd
            $text = $section['total'];
            $item['children'][1]['children'][$i]['children'][1]['children'][$j] = $prg::build_content_container_node($prg::HTML_DEFINITION_DEFINITION, $attributes);
            $item['children'][1]['children'][$i]['children'][1]['children'][$j]['children'][0] = $prg::build_content_text_node($text);            
            $j++;
            
            //dt complete
            $attributes = array();
            $text = $strings['complete'];
            $item['children'][1]['children'][$i]['children'][1]['children'][$j] = $prg::build_content_container_node($prg::HTML_DEFINITION_TERM, $attributes);
            $item['children'][1]['children'][$i]['children'][1]['children'][$j]['children'][0] = $prg::build_content_text_node($text);
            $j++;
            //dd
            $text = $section['complete'];
            $item['children'][1]['children'][$i]['children'][1]['children'][$j] = $prg::build_content_container_node($prg::HTML_DEFINITION_DEFINITION, $attributes);
            $item['children'][1]['children'][$i]['children'][1]['children'][$j]['children'][0] = $prg::build_content_text_node($text);
            $j++;
            
            //dt incomplete
            $attributes = ($section['incomplete'] > 0) ? array('class' => 'highlight') : array();
            $text = $strings['incomplete'];
            $item['children'][1]['children'][$i]['children'][1]['children'][$j] = $prg::build_content_container_node($prg::HTML_DEFINITION_TERM, $attributes);
            $item['children'][1]['children'][$i]['children'][1]['children'][$j]['children'][0] = $prg::build_content_text_node($text);
            $j++;
            //dd
            $text = $section['incomplete'];
            $item['children'][1]['children'][$i]['children'][1]['children'][$j] = $prg::build_content_container_node($prg::HTML_DEFINITION_DEFINITION, $attributes);
            $item['children'][1]['children'][$i]['children'][1]['children'][$j]['children'][0] = $prg::build_content_text_node($text);
            $j++;
            
            //dt saved (not submitted)
            $attributes = ($section['saved'] > 0) ? array('class' => 'highlight') : array();
            $text = $strings['saved'];
            $item['children'][1]['children'][$i]['children'][1]['children'][$j] = $prg::build_content_container_node($prg::HTML_DEFINITION_TERM, $attributes);
            $item['children'][1]['children'][$i]['children'][1]['children'][$j]['children'][0] = $prg::build_content_text_node($text);
            $j++;
            //dd
            $text = $section['saved'];
            $item['children'][1]['children'][$i]['children'][1]['children'][$j] = $prg::build_content_container_node($prg::HTML_DEFINITION_DEFINITION, $attributes);
            $item['children'][1]['children'][$i]['children'][1]['children'][$j]['children'][0] = $prg::build_content_text_node($text);
            $j++;
            
            //dt not attempted
            $attributes = ($section['notattempted'] > 0) ? array('class' => 'highlight') : array();
            $text = $strings['notattempted'];
            $item['children'][1]['children'][$i]['children'][1]['children'][$j] = $prg::build_content_container_node($prg::HTML_DEFINITION_TERM, $attributes);
            $item['children'][1]['children'][$i]['children'][1]['children'][$j]['children'][0] = $prg::build_content_text_node($text);
            $j++;
            //dd
            $text = $section['notattempted'];
            $item['children'][1]['children'][$i]['children'][1]['children'][$j] = $prg::build_content_container_node($prg::HTML_DEFINITION_DEFINITION, $attributes);
            $item['children'][1]['children'][$i]['children'][1]['children'][$j]['children'][0] = $prg::build_content_text_node($text);
            $j++;
            
            //dt waiting
            $attributes = array();
            $text = $strings['waiting'];
            $item['children'][1]['children'][$i]['children'][1]['children'][$j] = $prg::build_content_container_node($prg::HTML_DEFINITION_TERM, $attributes);
            $item['children'][1]['children'][$i]['children'][1]['children'][$j]['children'][0] = $prg::build_content_text_node($text);
            $j++;
            //dd
            $text = $section['waiting'];
            $item['children'][1]['children'][$i]['children'][1]['children'][$j] = $prg::build_content_container_node($prg::HTML_DEFINITION_DEFINITION, $attributes);
            $item['children'][1]['children'][$i]['children'][1]['children'][$j]['children'][0] = $prg::build_content_text_node($text);

            $i++;
        }
        
        return $item;
    }

    /**
     * Gets the name string of this subreport for use in the progress report generator ui.
     * @return string
     */
    static public function get_name_string() {
        return get_string(self::NAME_CODE_STRING, 'gradereport_progress');
    }

    /**
     * Determines the weight of this subreport
     * @return int weight
     */
    static public function get_weight($courseid) {
        global $CFG;
        
        $defaultweight = progress_report_generator::DEFAULT_SUBREPORT_WEIGHT;
        $classweight = self::SETTING_WEIGHT;
        $weightless = progress_report_generator::WEIGHTLESS;
        
        $defaultvalue = isset($CFG->$classweight)? $CFG->$classweight : $weightless;
        return grade_get_setting($courseid, $classweight, $defaultvalue);
    }

    /**
     * Returns an array of parameters that this subreport consumes
     * @param $weight the weight of the report is used to test if it is not at the default value
     * @return array parameters consumed by subreport
     */
    static public function get_params($weight) {
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
}
