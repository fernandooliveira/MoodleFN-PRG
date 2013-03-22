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
 * File containing class definitions for subreports.
 *
 * @package    gradebook
 * @subpackage progress
 * @copyright  2011 MoodleFN
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once($CFG->dirroot . '/grade/report/lib.php');
require_once($CFG->dirroot . '/grade/lib.php');
require_once($CFG->dirroot . '/grade/report/lib.php');

defined('MOODLE_INTERNAL') || die;

/**
 * Activity subreport
 * @package gradebook
 * @subpackage progress
 */
class progress_report_subreport_activity{
    /**
     * consumed by get_string
     * @const string NAME_CODE_STRING
     */
    const NAME_CODE_STRING               = 'activity';
    /**
     * consumed by get_string
     * @const string TITLE_STRING
     */
    const TITLE_STRING                   = 'activitytitle';
    /**
     * consumed by get_string
     * @const string SELECT_LABEL_STRING
     */
    const SELECT_LABEL_STRING            = 'activityselectlabel';
    /**
     * consumed by get_string
     * @const string SUBMIT_BUTTON_STRING
     */
    const SUBMIT_BUTTON_STRING           = 'activitysubmitbutton';
    /**
     * consumed by get_string
     * @const string SORT_WEIGHT_INCOMPLETE_STRING
     */
    const SORT_WEIGHT_INCOMPLETE_STRING  = 'activitysortweightincomplete';
    /**
     * consumed by get_string
     * @const string SORT_WEIGHT_STRING
     */
    const SORT_WEIGHT_STRING             = 'activitysortweight';
    /**
     * consumed by get_string
     * @const string SORT_COURSE_INCOMPLETE_STRING
     */
    const SORT_COURSE_INCOMPLETE_STRING  = 'activitysortcourseincomplete';
    /**
     * consumed by get_string
     * @const string SORT_COURSE_STRING
     */
    const SORT_COURSE_STRING             = 'activitysortcourse';
    /**
     * consumed by get_string
     * @const string TABLE_HEADER_ACTIVITY_STRING
     */
    const TABLE_HEADER_ACTIVITY_STRING   = 'activitytableheaderactivity';
    /**
     * consumed by get_string
     * @const string TABLE_HEADER_PERCENTAGE_STRING
     */
    const TABLE_HEADER_PERCENTAGE_STRING = 'activitytableheaderpercentage';

    /**
     * consumed by grade_get_setting
     * @const string SETTING_ENABLED
     */
    const SETTING_ENABLED                = 'grade_report_progress_act_enabled';
    /**
     * consumed by grade_get_setting
     * @const string SETTING_WEIGHT
     */
    const SETTING_WEIGHT                 = 'grade_report_progress_act_weight';

    /**
     * short name for subreport used for tagging URL/POST/GET parameters
     * @const string SHORT_NAME
     */
    const SHORT_NAME                     = 'act';
    /**
     * represents activities sorted in course order
     * @const int SORT_COURSE
     */
    const SORT_COURSE                    = '0';
    /**
     * represents activities sorted in course order with incomplete activities first
     * @const int SORT_COURSE_INCOMPLETE
     */
    const SORT_COURSE_INCOMPLETE         = '1';
    /**
     * represents activities sorted by heaviness
     * @const int SORT_WEIGHT
     */
    const SORT_WEIGHT                    = '2';
    /**
     * represents activities sorted by heaviness with incomplete activities first
     * @const int SORT_WEIGHT_INCOMPLETE
     */
    const SORT_WEIGHT_INCOMPLETE         = '3';

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
     * Moodle context object
     * @var object $context
     */
    protected $context;

    /**
     * value of the sort parameter.
     * @var int $sort
     */
    protected $sort;

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
     * tracking of indices of grade item activities used to sort them when content is built
     * @var array $activitysortedindex
     */
    protected $activitysortedindex = array();

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
        //ensure that parameters required by this subreport are initialized.
        $sortfound = false;
        foreach ($params as $key => $value) {
            switch ($key) {
                case 'actsort' :
                    if (gettype($value) == 'integer') {
                        if ($value >= 0 && $value <= 2) {
                            $this->sort = $value;
                        } else {
                            $this->sort = optional_param('actsort', self::SORT_COURSE, PARAM_INT);
                        }
                    }
                    $sortfound = true;
                    break;
            }
        }
        if (!$sortfound) {
            $this->sort = optional_param('actsort', self::SORT_COURSE, PARAM_INT);
            $params['actsort'] = $this->sort;
        }
        $this->params = $params;

        $this->canviewhidden = has_capability('moodle/grade:viewhidden', get_context_instance(CONTEXT_COURSE, $this->courseid));
        //grade tree - no fillers, category grade item is not last child, no outcomes
        $this->gtree         = new grade_tree($this->courseid, false, false, null, false);
        $this->initialize_activities();
    }

    /**
     * Builds $this->activities and $this->activitysortedindex
     */
    protected function initialize_activities(){
        $this->initialize_activities_recursive($this->gtree->top_element);  

        //initialize activitysortedindex
        switch($this->sort) {
            case self::SORT_COURSE :
                foreach ($this->activities as $key => $value) {
                    $this->activitysortedindex[$key] = $key;
                }
                break;

            case self::SORT_COURSE_INCOMPLETE :
                $completes = array();
                $incompletes = array();
                foreach ($this->activities as $key => $value) {
                    if ($value['incomplete']) {
                        $incompletes[] = $key;
                    } else {
                        $completes[] = $key;
                    }
                }
                foreach ($incompletes as $key => $value) {
                    $this->activitysortedindex[] = $value;
                }
                foreach ($completes as $key => $value) {
                    $this->activitysortedindex[] = $value;
                }
                break;

            case self::SORT_WEIGHT :
                $weights = array();
                foreach ($this->activities as $key => $value) {
                    $weights[$key] = $value['weight'];
                }
                asort($weights);
                foreach ($weights as $key => $value) {
                    $this->activitysortedindex[] = $key;
                }
                break;

            case self::SORT_WEIGHT_INCOMPLETE :
                $weights = array();
                foreach ($this->activities as $key => $value) {
                    $weights[$key] = $value['weight'];
                }
                asort($weights);
                $completes = array();
                $incompletes = array();
                foreach ($weights as $key => $value) {
                    if($this->activities[$key]['incomplete']) {
                        $incompletes[] = $key;
                    } else {
                        $completes[] = $key;
                    }
                }
                foreach ($incompletes as $key => $value) {
                    $this->activitysortedindex[] = $value;
                }
                foreach ($completes as $key => $value) {
                    $this->activitysortedindex[] = $value;
                }
                break;
        }
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
                //get name information
                $activity['name'] = $this->get_element_header_content($element);
                $activity['icon'] = $this->get_element_icon_content($element);

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

                /// Excluded Items
                $activity['excluded'] = $grade_grade->is_excluded();

                /// Actual Grade
                $activity['gradeval'] = $grade_grade->finalgrade;

                /// Maximum Possible Grade For Item
                //$activity['maxgrade'] = $grade_object->maxgrade;

                /// Has the user passed
                $activity['pass'] = $grade_grade->is_passed($grade_object);

                /// Weight
                $activity['weight'] = 1;
                if ($grade_object->aggregationcoef > 0 && $type == 'item') {
                    $activity['weight'] = $grade_object->aggregationcoef;
                }

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
                if ($itemtype=='mod' && $iteminstance && $itemmodule=='assignment') {
                    $params = array($this->userid, $iteminstance);
                    $sql = "SELECT id FROM {assignment_submissions} " .
                           "WHERE  timemodified > timemarked " .
                           "  AND  userid = ? AND assignment = ?";
                    if ($waitingforgrade = $DB->get_record_sql($sql, $params)) {
                        $activity['waiting'] = false;
                    }
                }
                $this->activities[] = $activity;
            }
            if (isset($element['children'])) {
                foreach ($element['children'] as $key=>$child) {
                    $this->initialize_activities_recursive($element['children'][$key]);
                }
            }
        }
    }

    /**
     * Create an array representation of subreport based on activities
     * @return array content representation of subreport
     */
    public function get_subreport_content() {
        $prg = "progress_report_generator";
        //box
        $item = $prg::build_content_container_node($prg::HTML_DIV, array('id' => 'progress-subreport-' . self::SHORT_NAME));
        ///title
        $item['children'][0] = $prg::build_content_container_node($prg::HTML_HEADING_3);
        $text = get_string(self::TITLE_STRING, 'gradereport_progress');
        $item['children'][0]['children'][0] = $prg::build_content_text_node($text);
        ///ui form
        $url = new moodle_url('/grade/report/progress/index.php', $this->params);
        $item['children'][1] = $prg::build_content_container_node($prg::HTML_FORM, array('id' => 'progress-subreport-form-'.self::SHORT_NAME, 'action' => $url, 'method' => 'post'));
        
        $selectid = 'act-sort-select';
        //label <=>
        $item['children'][1]['children'][0] = $prg::build_content_container_node($prg::HTML_LABEL, array('for' => $selectid));
        //label text
        $text = get_string(self::SELECT_LABEL_STRING, 'gradereport_progress');
        $item['children'][1]['children'][0]['children'][0] = $prg::build_content_text_node($text);
        //select
        $item['children'][1]['children'][1] = $prg::build_content_container_node($prg::HTML_SELECT, array('id' =>$selectid, 'name' => 'actsort'));

        //select option and text
        if ($this->sort == self::SORT_COURSE) {
            $attributes = array('value' => self::SORT_COURSE, 'selected' => 'selected');
        } else {
            $attributes = array('value' => self::SORT_COURSE);
        }
        $value = self::SORT_COURSE;
        $text = get_string(self::SORT_COURSE_STRING, 'gradereport_progress');
        $item['children'][1]['children'][1]['children'][0] = $prg::build_content_container_node($prg::HTML_SELECT_OPTION, array('value' => $value));
        $item['children'][1]['children'][1]['children'][0]['children'][0] = $prg::build_content_text_node($text);

        //select option and text
        if ($this->sort == self::SORT_COURSE_INCOMPLETE) {
            $attributes = array('value' => self::SORT_COURSE_INCOMPLETE, 'selected' => 'selected');
        } else {
                $attributes = array('value' => self::SORT_COURSE_INCOMPLETE);
        }
        $value = self::SORT_COURSE_INCOMPLETE;
        $text = get_string(self::SORT_COURSE_INCOMPLETE_STRING, 'gradereport_progress');
        $item['children'][1]['children'][1]['children'][1] = $prg::build_content_container_node($prg::HTML_SELECT_OPTION, array('value' => $value));
        $item['children'][1]['children'][1]['children'][1]['children'][0] = $prg::build_content_text_node($text);

        //select option and text
        if ($this->sort == self::SORT_WEIGHT) {
            $attributes = array('value' => self::SORT_WEIGHT, 'selected' => 'selected');
        } else {
            $attributes = array('value' => self::SORT_WEIGHT);
        }
        $value = self::SORT_WEIGHT;
        $text = get_string(self::SORT_WEIGHT_STRING, 'gradereport_progress');
        $item['children'][1]['children'][1]['children'][2] = $prg::build_content_container_node($prg::HTML_SELECT_OPTION, array('value' => $value));
        $item['children'][1]['children'][1]['children'][2]['children'][0] = $prg::build_content_text_node($text);

        //select option and text
        if ($this->sort == self::SORT_WEIGHT_INCOMPLETE) {
            $attributes = array('value' => self::SORT_WEIGHT_INCOMPLETE, 'selected' => 'selected');
        } else {
            $attributes = array('value' => self::SORT_WEIGHT_INCOMPLETE);
        }
        $value = self::SORT_WEIGHT_INCOMPLETE;
        $text = get_string(self::SORT_WEIGHT_INCOMPLETE_STRING, 'gradereport_progress');
        $item['children'][1]['children'][1]['children'][3] = $prg::build_content_container_node($prg::HTML_SELECT_OPTION, array('value' => $value));
        $item['children'][1]['children'][1]['children'][3]['children'][0] = $prg::build_content_text_node($text);
        //submit button
        $item['children'][1]['children'][2] = $prg::build_content_autonomous_node($prg::HTML_INPUT,
                                                                      array('id'    => 'progress-subreport-select-button-'.self::SHORT_NAME,
                                                                             'name'  => 'subreport-select-submit-'.self::SHORT_NAME,
                                                                             'value' => get_string(self::SUBMIT_BUTTON_STRING, 'gradereport_progress'),
                                                                             'type'  => 'submit'
                                                                            ));
        //table
        $item['children'][2] = $prg::build_content_container_node($prg::HTML_TABLE);
        //table header
        $item['children'][2]['children'][0] = $prg::build_content_container_node($prg::HTML_TABLE_ROW);
        //table header cell
        $item['children'][2]['children'][0]['children'][0] = $prg::build_content_container_node($prg::HTML_TABLE_HEADER);
        $text = get_string(self::TABLE_HEADER_ACTIVITY_STRING, 'gradereport_progress');
        $item['children'][2]['children'][0]['children'][0]['children'][0] = $prg::build_content_text_node($text);
        //table header cell
        $item['children'][2]['children'][0]['children'][1] = $prg::build_content_container_node($prg::HTML_TABLE_HEADER);
        $text = get_string(self::TABLE_HEADER_PERCENTAGE_STRING, 'gradereport_progress');
        $item['children'][2]['children'][0]['children'][1]['children'][0] = $prg::build_content_text_node($text);
        //1 table row per activity
        $i = 1;
        //output sorted activity data
        foreach ($this->activitysortedindex as $index) {
            //table row
            if ($this->activities[$index]['incomplete']) {
                $attributes = array('class' => 'incomplete');
            } else {
                $attributes = array();
            }
            $item['children'][2]['children'][$i] = $prg::build_content_container_node($prg::HTML_TABLE_ROW, $attributes);
            //table cell
            
            $item['children'][2]['children'][$i]['children'][0] = $prg::build_content_container_node($prg::HTML_TABLE_CELL);
            
            if (empty($this->activities[$index]['icon'])) {
                //spacer span equal to icon size
                $item['children'][2]['children'][$i]['children'][0]['children'][0] = $prg::build_content_container_node($prg::HTML_SPAN, array('class' => 'spacer'));
                //hyperlink
                $item['children'][2]['children'][$i]['children'][0]['children'][1] = $this->activities[$index]['name'];
            } else {
                //icon
                $item['children'][2]['children'][$i]['children'][0]['children'][0] = $this->activities[$index]['icon'];
                //hyperlink
                $item['children'][2]['children'][$i]['children'][0]['children'][1] = $this->activities[$index]['name'];
            }
            
            //table cell
            $item['children'][2]['children'][$i]['children'][1] = $prg::build_content_container_node($prg::HTML_TABLE_CELL);
            $text = $this->activities[$index]['percentage'];
            $item['children'][2]['children'][$i]['children'][1]['children'][0] = $prg::build_content_text_node($text);
            
            $i++;
        }
        
        return $item;
    }

    /**
     * Returns array representation of element icon, or empty array if no icon is available.
     *
     * @param array &$element An array representing an element in the grade_tree
     *
     * @return array
     */
    protected function get_element_icon_content(&$element) {
        global $CFG, $OUTPUT;

        switch ($element['type']) {
            case 'item':
            case 'courseitem':
            case 'categoryitem':
                $is_course   = $element['object']->is_course_item();
                $is_category = $element['object']->is_category_item();
                $is_scale    = $element['object']->gradetype == GRADE_TYPE_SCALE;
                $is_value    = $element['object']->gradetype == GRADE_TYPE_VALUE;
                $is_outcome  = !empty($element['object']->outcomeid);

                if ($element['object']->is_calculated()) {
                    $strcalc = get_string('calculatedgrade', 'grades');
                    //return representation of icon
                    $attributes = array('src'   => $OUTPUT->pix_url('i/calc'), 'class' => 'icon itemicon',
                                         'title' => s($strcalc), 'alt'   => s($strcalc));
                    return progress_report_generator::build_content_autonomous_node(progress_report_generator::HTML_IMAGE, $attributes);
                } else if (($is_course or $is_category) and ($is_scale or $is_value)) {
                    if ($category = $element['object']->get_item_category()) {
                        switch ($category->aggregation) {
                            case GRADE_AGGREGATE_MEAN:
                            case GRADE_AGGREGATE_MEDIAN:
                            case GRADE_AGGREGATE_WEIGHTED_MEAN:
                            case GRADE_AGGREGATE_WEIGHTED_MEAN2:
                            case GRADE_AGGREGATE_EXTRACREDIT_MEAN:
                                $stragg = get_string('aggregation', 'grades');
                                $attributes = array('src'   => $OUTPUT->pix_url('i/agg_mean'), 'class' => 'icon itemicon',
                                         'title' => s($stragg), 'alt'   => s($stragg));
                                return progress_report_generator::build_content_autonomous_node(progress_report_generator::HTML_IMAGE, $attributes);
                            case GRADE_AGGREGATE_SUM:
                                $stragg = get_string('aggregation', 'grades');
                                $attributes = array('src'   => $OUTPUT->pix_url('i/agg_sum'), 'class' => 'icon itemicon',
                                                    'title' => s($stragg), 'alt'   => s($stragg));
                                return progress_report_generator::build_content_autonomous_node(progress_report_generator::HTML_IMAGE, $attributes);
                        }
                    }

                } else if ($element['object']->itemtype == 'mod') {
                    //prevent outcomes being displaying the same icon as the activity they are attached to
                    if ($is_outcome) {
                        $stroutcome = s(get_string('outcome', 'grades'));
                        $attributes = array('src'   => $OUTPUT->pix_url('i/outcomes'), 'class' => 'icon itemicon',
                                            'title' => s($stroutcome), 'alt'   => s($stroutcome));
                        return progress_report_generator::build_content_autonomous_node(progress_report_generator::HTML_IMAGE, $attributes);
                    } else {
                        $strmodname = get_string('modulename', $element['object']->itemmodule);
                        $attributes = array('src'   => $OUTPUT->pix_url('icon',$element['object']->itemmodule), 'class' => 'icon itemicon',
                                            'title' => s($strmodname), 'alt'   => s($strmodname));
                        return progress_report_generator::build_content_autonomous_node(progress_report_generator::HTML_IMAGE, $attributes);
                    }
                } else if ($element['object']->itemtype == 'manual') {
                    if ($element['object']->is_outcome_item()) {
                        $stroutcome = get_string('outcome', 'grades');
                        $attributes = array('src'   => $OUTPUT->pix_url('i/outcomes'), 'class' => 'icon itemicon',
                                            'title' => s($stroutcome), 'alt'   => s($stroutcome));
                        return progress_report_generator::build_content_autonomous_node(progress_report_generator::HTML_IMAGE, $attributes);
                    } else {
                        $strmanual = get_string('manualitem', 'grades');
                        $attributes = array('src'   => $OUTPUT->pix_url('t/manual_item'), 'class' => 'icon itemicon',
                                            'title' => s($strmanual), 'alt'   => s($strmanual));
                        return progress_report_generator::build_content_autonomous_node(progress_report_generator::HTML_IMAGE, $attributes);
                    }
                }
                break;

            case 'category':
                $strcat = get_string('category', 'grades');
                $attributes = array('src'   => $OUTPUT->pix_url('f/folder'), 'class' => 'icon itemicon',
                                    'title' => s($strcat), 'alt'   => s($strcat));
                return progress_report_generator::build_content_autonomous_node(progress_report_generator::HTML_IMAGE, $attributes);
        }
        //if there is nothing, return the empty image.
        return array();
    }

    /**
     * Returns array representation of element name
     *
     * @param array &$element An array representing an element in the grade_tree
     *
     * @return string header
     */
    protected function get_element_header_content(&$element) {
        global $CFG;

        $content = array();
        $attributes = array();
        $text = $element['object']->get_name();

        if ($element['type'] != 'item' and $element['type'] != 'categoryitem' and
            $element['type'] != 'courseitem') {
            $content[0] = progress_report_generator::build_content_text_node($text);
            return $content;
        }

        $itemtype     = $element['object']->itemtype;
        $itemmodule   = $element['object']->itemmodule;
        $iteminstance = $element['object']->iteminstance;

        if ($itemtype=='mod' and $iteminstance and $itemmodule) {
            if ($cm = get_coursemodule_from_instance($itemmodule, $iteminstance, $this->courseid)) {

                $a = new stdClass();
                $a->name = get_string('modulename', $element['object']->itemmodule);
                $attributes['title'] = get_string('linktoactivity', 'grades', $a);
                $dir = $CFG->dirroot.'/mod/'.$itemmodule;

                if (file_exists($dir.'/grade.php')) {
                    $attributes['href'] = $CFG->wwwroot.'/mod/'.$itemmodule.'/grade.php?id='.$cm->id;
                } else {
                    $attributes['href'] = $CFG->wwwroot.'/mod/'.$itemmodule.'/view.php?id='.$cm->id;
                }
            }
        }
        $content[0] = progress_report_generator::build_content_container_node(progress_report_generator::HTML_HYPERLINK, $attributes);
        $content[0]['children'][0] = progress_report_generator::build_content_text_node($text);

        return $content[0];
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
        //$default = grade_get_setting($courseid, self::SETTING_WEIGHT, progress_report_generator::DEFAULT_SUBREPORT_WEIGHT, false);
        global $CFG;
        $weightless = progress_report_generator::WEIGHTLESS;
        $classweight = self::SETTING_WEIGHT;
        $default = (isset($CFG->classweight) ? $CFG->classweight : $weightless);
        return optional_param(self::SHORT_NAME.'weight', $default, PARAM_INT);
    }

    /**
     * Returns an array of parameters that this subreport consumes
     * @param $weight the weight of the report is used to test if it is not at the default value
     * @return array parameters consumed by subreport
     */
    static public function get_params($weight) {
        $params = array();
        $params[self::SHORT_NAME.'weight'] = $weight;
        return $params;
    }

    /**
     * returns the availability of this subreport
     * @param int $courseid
     * @return bool
     */
    static public function available($courseid) {
        //setting default value. default to off.
        //return grade_get_setting($courseid, self::SETTING_ENABLED, 0);
        global $CFG;
        $classenabled = self::SETTING_ENABLED;
        return (isset($CFG->$classenabled)? $CFG->$classenabled : false);
    }

}