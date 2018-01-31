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

    protected static $instance;
    protected $boostparent;

    private function __construct() {
    }

    public static function get_instance() {
        if (!is_object(self::$instance)) {
            self::$instance = new self();
            self::$instance->boostparent = \theme_config::load('boost');
        }
        return self::$instance;
    }

    public function get_extra_scss($theme) {
        return theme_boost_get_extra_scss($this->boostparent);
    }

    public function get_main_scss_content($theme) {
        global $CFG;
        require_once($CFG->dirroot.'/theme/boost/lib.php');

        $scss = theme_boost_get_main_scss_content($this->boostparent);

        $scss .= $this->import_scss('squared');
		//TODO error_log($scss);

        return $scss;
    }

    public function get_pre_scss($theme) {
        return theme_boost_get_pre_scss($this->boostparent);
    }

    /**
     * Finds the given scss file in the theme.
     * @param string $filename Filename without extension to get.
     * @return string SCSS import statement for the file if it exists otherwise an empty string.
     */
    protected function import_scss($filename) {
        $content = '';
        $thefile = $this->get_scss_file($filename);
        if (!empty($thefile)) {
            $content .= file_get_contents($thefile);
        }
        return $content;
    }

    protected function get_scss_file($filename) {
        global $CFG;
        $filename .= '.scss';

        if (file_exists("$CFG->dirroot/theme/squared/scss/$filename")) {
            return "$CFG->dirroot/theme/squared/scss/$filename";
        } else if (!empty($CFG->themedir) and file_exists("$CFG->themedir/squared/scss/$filename")) {
            return "$CFG->themedir/squared/scss/$filename";
        } else {
            return dirname(__FILE__) . "/$filename";
        }
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
