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

defined('MOODLE_INTERNAL') || die;

class theme_squared_core_course_renderer extends core_course_renderer {

    /**
     * Serves requests to /theme/squared/inspector.ajax.php
     *
     * @param string $term search term.
     * @return array of results.
     * @throws coding_exception
     */
    public function inspector_ajax($term) {
        global $USER;

        $data = array();

        $courses = enrol_get_my_courses();
        $site = get_site();

        if (array_key_exists($site->id, $courses)) {
            unset($courses[$site->id]);
        }

        foreach ($courses as $c) {
            if (isset($USER->lastcourseaccess[$c->id])) {
                $courses[$c->id]->lastaccess = $USER->lastcourseaccess[$c->id];
            } else {
                $courses[$c->id]->lastaccess = 0;
            }
        }

        // Get remote courses.
        $remotecourses = array();
        if (is_enabled_auth('mnet')) {
            $remotecourses = get_my_remotecourses();
        }
        // Remote courses will have -ve remoteid as key, so it can be differentiated from normal courses.
        foreach ($remotecourses as $id => $val) {
            $remoteid = $val->remoteid * -1;
            $val->id = $remoteid;
            $courses[$remoteid] = $val;
        }

        foreach ($courses as $course) {
            $modinfo = get_fast_modinfo($course);
            $courseformat = course_get_format($course->id);
            $course = $courseformat->get_course();
            $courseformatsettings = $courseformat->get_format_options();
            $sesskey = sesskey();

            // Course name.
            $label = $course->fullname;
            if (stristr($label, $term)) {
                $courseurl = new moodle_url('/course/view.php');
                $courseurl->param('id', $course->id);
                $courseurl->param('sesskey', $sesskey);
                $coursehref = $courseurl->out(false);
                $data[] = array('id' => $coursehref, 'label' => $label, 'value' => $label);
            }

            foreach ($modinfo->get_section_info_all() as $section => $thissection) {
                if (!$thissection->uservisible) {
                    continue;
                }
                if (is_object($thissection)) {
                    $thissection = $modinfo->get_section_info($thissection->section);
                } else {
                    $thissection = $modinfo->get_section_info($thissection);
                }
                if ((string) $thissection->name !== '') {
                    $sectionname = format_string($thissection->name, true,
                        array('context' => context_course::instance($course->id)));
                } else {
                    $sectionname = $courseformat->get_section_name($thissection->section);
                }
                if ($thissection->section <= $course->numsections) {
                    // Do not link 'orphaned' sections.
                    $label = $course->fullname.' - '.$sectionname;
                    if (stristr($label, $term)) {
                        $courseurl = new moodle_url('/course/view.php');
                        $courseurl->param('id', $course->id);
                        $courseurl->param('sesskey', $sesskey);
                        if ((!empty($courseformatsettings['coursedisplay'])) &&
                            ($courseformatsettings['coursedisplay'] == COURSE_DISPLAY_MULTIPAGE)) {
                            $courseurl->param('section', $thissection->section);
                            $coursehref = $courseurl->out(false);
                        } else {
                            $coursehref = $courseurl->out(false).'#section-'.$thissection->section;
                        }
                        $data[] = array('id' => $coursehref, 'label' => $label, 'value' => $label);
                    }
                }
                if (!empty($modinfo->sections[$thissection->section])) {
                    foreach ($modinfo->sections[$thissection->section] as $modnumber) {
                        $mod = $modinfo->cms[$modnumber];
                        if (!empty($mod->url)) {
                            $instancename = $mod->get_formatted_name();
                            $label = $course->fullname.' - '.$sectionname.' - '.$instancename;
                            if (stristr($label, $term)) {
                                $data[] = array('id' => $mod->url->out(false), 'label' => $label, 'value' => $label);
                            }
                        }
                    }
                }
            }
        }

        return $data;
    }
}