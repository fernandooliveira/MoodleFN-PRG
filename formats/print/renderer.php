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
 * Class definition of renderer for subplugin
 *
 * @package    gradebook
 * @subpackage progress
 * @copyright  2011 MoodleFN
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
 
/**
 * Custom renderer for the progress grade report print formatter
 *
 * To get an instance of this use the following code:
 * $renderer = $PAGE->get_renderer('gradereport_progress_print');
 */
class gradereport_progress_print_renderer extends gradereport_progress_base_renderer {
    
    /**
     * consumed by get_string
     * @const string PRINT_BUTTON
     */  
    const PRINT_BUTTON                           = 'printbutton';
    
    public function write_header($courseid, $heading) {
        global $CFG, $DB, $PAGE;
                
        $PAGE->requires->css('/grade/report/progress/formats/print/styles.css');

        if (!$course = $DB->get_record('course', array('id' => $courseid))) {
            print_error('nocourseid');
        }
        
        $output  = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">';
        $output .= html_writer::start_tag('html', array('xmlns' => 'http://www.w3.org/1999/xhtml', 'dir' => 'ltr', 'lang' => 'en', 'xml:lang' => 'en'));
        $output .= html_writer::start_tag('head');
        $output .= html_writer::empty_tag('meta', array('http-equiv' => 'Content-Type', 'content' => 'text/html; charset=utf-8'));
        $output .= html_writer::empty_tag('link', array('rel' => 'stylesheet', 'type' => 'text/css', 'href' => $CFG->wwwroot . '/grade/report/progress/styles.css'));
        $output .= html_writer::empty_tag('link', array('rel' => 'stylesheet', 'type' => 'text/css', 'href' => $CFG->wwwroot . '/grade/report/progress/formats/print/styles.css'));
        require_once($CFG->dirroot . '/grade/report/progress/lib.engine.php');
        $prg_subplugin_manager_instance = progress_report_subplugin_manager::get_instance();
        if ($files = $prg_subplugin_manager_instance->get_report_css_files_relative('print.css')) {
            foreach ($files as $file) {
                $PAGE->requires->css($file);
                $output .= html_writer::empty_tag('link', array('rel' => 'stylesheet', 'type' => 'text/css', 'href' => $CFG->wwwroot . $file));
            }
        }       
        
        $output .= html_writer::tag('title', $heading);
        $output .= html_writer::end_tag('head');
        $output .= html_writer::start_tag('body', array('id' => 'progress-print'));
        $output .= html_writer::start_tag('div');
        $output .= html_writer::start_tag('form');
        $output .= html_writer::empty_tag('input', array('id' => 'print-button', 'type' => 'submit', 'value' => get_string(self::PRINT_BUTTON, 'gradereport_progress'), 'onClick' => 'window.print()'));
        $output .= html_writer::end_tag('form');
        $output .= html_writer::end_tag('div');
        $output .= html_writer::start_tag('div', array('id' => 'print-generator'));
        $output .= html_writer::tag('h1', $heading);
        $output .= html_writer::tag('h2', $course->fullname);
        
        return $output;
    }

    public function write_footer() {
        $output  = html_writer::end_tag('div');
        $output .= html_writer::end_tag('body');
        $output .= html_writer::end_tag('html');    
  
        return $output;
    }
}