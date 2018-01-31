<?php
// This file is part of the Squared theme for Moodle
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle. If not, see <http://www.gnu.org/licenses/>.

/**
 * This is the squared theme.
 *
 *
 * The squared theme makes uses a custom version of squared blocks
 *
 * @package theme_squared
 * @copyright 2018 onwards Onlinecampus Virtuelle PH
 * www.virtuelle-ph.at, David Bogner www.edulabs.org
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace theme_squared;

defined('MOODLE_INTERNAL') || die;

class toolbox {

    private function __construct() {
    }

    public static function get_instance() {
        if (!is_object(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * States if course content search can be used.  Will not work if theme is in $CFG->themedir.
     * @return boolean false|true if course content search can be used.
     */
    static public function course_content_search() {
        global $PAGE;

        $squaredsearch = new \moodle_url('index.php');
        $squaredsearch->param('sesskey', sesskey());
        $inspectorscourerdata = array('data' => array('theme' => $squaredsearch->out(false)));
        $PAGE->requires->js_call_amd('theme_squared/inspector_scourer', 'init', $inspectorscourerdata);

        return true;
    }
}
