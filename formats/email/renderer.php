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
 * Custom renderer for html email strings
 *
 * To get an instance of this use the following code:
 * $renderer = $PAGE->get_renderer('gradereport_progress');
 */
class gradereport_progress_emailhtml_renderer extends plugin_renderer_base {
    
    public function write_node_self_closed($tag, $attributes){
        return html_writer::empty_tag($tag, $attributes);
    }

    public function write_open_node($tag, $attributes){
        return html_writer::start_tag($tag, $attributes);
    }

    public function write_close_node($tag){
        return html_writer::end_tag($tag);
    }
}

/**
 * Custom renderer for plaintext email strings
 *
 * To get an instance of this use the following code:
 * $renderer = $PAGE->get_renderer('gradereport_progress');
 */
class gradereport_progress_emailplain_renderer extends plugin_renderer_base {
    
    public function write_node_self_closed($tag, $attributes){
        //plain text does not allow for self closed elements
        return '';
    }
    
    public function write_open_node($tag, $attributes){
        switch($tag) {
            case 'legend' :
            case 'h3' :
            case 'h4' :
            case 'dt' :
            case 'dl' :
            case 'li' :
            case 'table' :
                return '\n';
                break;
            default :
                break;
        }
        return '';
    }
    
    public function write_close_node($tag){
        
        switch($tag) {
            case 'legend' :
            case 'h3' :
            case 'h4' :
            case 'dd' :
            case 'dl' :
            case 'li' :
            case 'table' :
            case 'p' :
            case 'tr' :
                return '\n';
                break;
            case 'td' :
            case 'th' :
                return ' | |';
                break;
            default :
                break;
        }
        return '';
    }
}