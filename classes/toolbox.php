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
        global $CFG;

        $scss = theme_boost_get_extra_scss($this->boostparent);

        $categorytree = \core_course_category::get(0)->get_children();

        // Navbar Colours.
        foreach ($categorytree as $cid => $value) {
            $setting = 'bgcolor'.$cid;
            if (isset($theme->settings->$setting)) {
                $scss .= '
                    @media (min-width: @screen-sm) {
                        .navbar-default .navbar-nav .catcolour'.$cid.' {
                             @include menu_item('.$theme->settings->$setting.');
                        }
                    }
                ';
                $scss .= '
                    .category-'.$cid.' {
                        #block-region-side-pre {
                            .blockheader, .block .card-heading {
                                background-color: '.$theme->settings->$setting.';
                                @include gradient-directional(lighten('.$theme->settings->$setting.', 15%), darken('.$theme->settings->$setting.', 5%));
                            }
                            .block .card-heading .icon-container {
                                background-color: darken('.$theme->settings->$setting.', 20%);
                            }
                            .card-group {
                                background-color: '.$theme->settings->$setting.';
                            }

                            .over-hover-to-bottom:before {
                                background-color: lighten('.$theme->settings->$setting.', 5%);
                            }
                        }
                        .course-content .sectionname .sqheadingicon,
                        #page-header .context-header-settings-menu {
                            background-color: '.$theme->settings->$setting.';
                        }
                        &.path-mod-forum {
                            .forumpost {
                                .row .left.picture {
                                    background-color: '.$theme->settings->$setting.';
                                }
                            }
                        }
                        .bg-messageheader {
                            background-color: '.$theme->settings->$setting.';
                        }

                    }
                    @media (max-width: $screen-breakpoint) {
                        .category-'.$cid.' {
                            #block-region-side-pre .card-group {
                                background-color: transparent;
                            }
                        }
                    }
                ';
            }
        }

        $showbgcolor = true;
        if (isset($theme->settings->nologobgcolor) && $theme->settings->nologobgcolor == 1) {
            $showbgcolor = false;
        }
        if ($showbgcolor && isset($theme->settings->logobgcolor)) {
            $scss .= '
                .headerimages .logoimg {
                    background-color: '.$theme->settings->logobgcolor.';
                }';
        }

        return $scss;
    }

    public function get_main_scss_content($theme) {
        global $CFG;
        require_once($CFG->dirroot.'/theme/boost/lib.php');

        $scss = $this->import_scss('squared_preset');

        $scss .= theme_boost_get_main_scss_content($this->boostparent);

        $scss .= $this->import_scss('squared');

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
     * States if course content search can be used.  Will now work if theme is in $CFG->themedir.
     * @return boolean false|true if course content search can be used.
     */
    static public function course_content_search() {
        global $PAGE;

        $squaredsearch = new \moodle_url('/course/index.php');
        $squaredsearch->param('sesskey', sesskey());
        $inspectorscourerdata = array('data' => array('theme' => $squaredsearch->out(false)));
        $PAGE->requires->js_call_amd('theme_squared/inspector_scourer', 'init', $inspectorscourerdata);

        return true;
    }

    /**
     * Helper method for the default layout file.
     */
    public function default_ajax() {
        global $CFG, $PAGE;
        
        $courseautocompletesearchterm = optional_param('term', '', PARAM_TEXT);
        $categorycoursesearch = optional_param('ccs', 0, PARAM_INT);
        $frontpageavailablecourses = optional_param('sqfac', 0, PARAM_INT);
        $frontpagemycourses = optional_param('sqfmc', 0, PARAM_INT);
        if (($courseautocompletesearchterm) || 
            ($categorycoursesearch) ||
            ($frontpageavailablecourses) ||
            ($frontpagemycourses)) {
            // AJAX calls to have a sesskey and use the course renderer.

            // Might be overkill but would probably stop DOS attack from lots of DB reads.
            \require_sesskey();

            if ($CFG->forcelogin) {
                \require_login();
            }
            $courserenderer = $PAGE->get_renderer('core', 'course');

            if ($courseautocompletesearchterm) {
                echo json_encode($courserenderer->inspector_ajax($courseautocompletesearchterm));
            } else if ($frontpageavailablecourses) {
                echo $courserenderer->frontpage_available_courses();
            } else if ($frontpagemycourses) {
                echo $courserenderer->frontpage_my_courses();
            } else {
                // Must be $categorycoursesearch.
                $catid = optional_param('categoryid', -1, PARAM_INT);  // Zero is for all courses.  Also look at /course/index.php
                if ($catid != -1) {
                    echo $courserenderer->category_courses_from_search($catid);
                } else {
                    header('HTTP/1.0 400 Bad Request');
                    die('Category id not sent.');
                }
            }
            die();
        }
    }
}
