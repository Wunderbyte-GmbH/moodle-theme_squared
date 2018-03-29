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
     * Renders html to display a name with the link to the course module on a course page
     *
     * If module is unavailable for user but still needs to be displayed
     * in the list, just the name is returned without a link
     *
     * Note, that for course modules that never have separate pages (i.e. labels)
     * this function return an empty string
     *
     * @param cm_info $mod
     * @param array $displayoptions
     * @return string
     */
    public function course_section_cm_name_title(cm_info $mod, $displayoptions = array()) {
        if ($this->page->user_is_editing()) {
            return parent::course_section_cm_name_title($mod, $displayoptions);
        }

        $output = '';
        $url = $mod->url;
        if (!$mod->is_visible_on_course_page() || !$url) {
            // Nothing to be displayed to the user.
            return $output;
        }

        //Accessibility: for files get description via icon, this is very ugly hack!
        $instancename = $mod->get_formatted_name();
        $altname = $mod->modfullname;
        /* Avoid unnecessary duplication: if e.g. a forum name already
           includes the word forum (or Forum, etc) then it is unhelpful
           to include that in the accessible description that is added. */
        if (false !== strpos(core_text::strtolower($instancename),
                core_text::strtolower($altname))) {
            $altname = '';
        }
        // File type after name, for alphabetic lists (screen reader).
        if ($altname) {
            $altname = get_accesshide(' '.$altname);
        }

        list($linkclasses, $textclasses) = $this->course_section_cm_classes($mod);

        /* Get on-click attribute value if specified and decode the onclick - it
           has already been encoded for display. */
        $onclick = htmlspecialchars_decode($mod->onclick, ENT_QUOTES);

        // Display link itself.
        $activitylink = html_writer::tag('div', html_writer::empty_tag('img', array('src' => $mod->get_icon_url(),
                'class' => 'iconlarge activityicon', 'alt' => ' ', 'role' => 'presentation')), array('class' => 'sqactivityicon')) .
                html_writer::tag('span', $instancename . $altname, array('class' => 'instancename'));
        if ($mod->uservisible) {
            $output .= html_writer::link($url, $activitylink, array('class' => $linkclasses, 'onclick' => $onclick));
        } else {
            /* We may be displaying this just in order to show information
               about visibility, without the actual link ($mod->is_visible_on_course_page()). */
            $output .= html_writer::tag('div', $activitylink, array('class' => $textclasses));
        }
        return $output;
    }

    /**
     * Renders HTML to display one course module in a course section
     *
     * This includes link, content, availability, completion info and additional information
     * that module type wants to display (i.e. number of unread forum posts)
     *
     * This function calls:
     * {@link core_course_renderer::course_section_cm_name()}
     * {@link core_course_renderer::course_section_cm_text()}
     * {@link core_course_renderer::course_section_cm_availability()}
     * {@link core_course_renderer::course_section_cm_completion()}
     * {@link course_get_cm_edit_actions()}
     * {@link core_course_renderer::course_section_cm_edit_actions()}
     *
     * @param stdClass $course
     * @param completion_info $completioninfo
     * @param cm_info $mod
     * @param int|null $sectionreturn
     * @param array $displayoptions
     * @return string
     */
    public function course_section_cm($course, &$completioninfo, cm_info $mod, $sectionreturn, $displayoptions = array()) {
        if ($this->page->user_is_editing()) {
            return parent::course_section_cm($course, $completioninfo, $mod, $sectionreturn, $displayoptions);
        }

        $output = '';
        // We return empty string (because course module will not be displayed at all)
        // if:
        // 1) The activity is not visible to users
        // and
        // 2) The 'availableinfo' is empty, i.e. the activity was
        //     hidden in a way that leaves no info, such as using the
        //     eye icon.
        if (!$mod->is_visible_on_course_page()) {
            return $output;
        }

        // Display the link to the module (or do nothing if module has no url)
        $cmname = $this->course_section_cm_name($mod, $displayoptions);

        if (!empty($cmname)) {
            // Start the div for the activity title, excluding the edit icons.
            $output .= html_writer::start_tag('div', array('class' => 'activityinstance'));
            $output .= $cmname;


            // Module can put text after the link (e.g. forum unread)
            $output .= $mod->afterlink;

            // Closing the tag which contains everything but edit icons. Content part of the module should not be part of this.
            $output .= html_writer::end_tag('div'); // .activityinstance
        }

        /* If there is content but NO link (eg label), then display the
           content here (BEFORE any icons). In this case icons must be
           displayed after the content so that it makes more sense visually
           and for accessibility reasons, e.g. if you have a one-line label
           it should work similarly (at least in terms of ordering) to an
           activity. */
        $url = $mod->url;
        if (empty($url)) {
            $output .= $this->course_section_cm_text($mod, $displayoptions);
        }

        $modicons = $this->course_section_cm_completion($course, $completioninfo, $mod, $displayoptions);

        if (!empty($modicons)) {
            $output .= html_writer::span($modicons, 'actions');
        }

        // Show availability info (if module is not available).
        $output .= $this->course_section_cm_availability($mod, $displayoptions);

        return $output;
    }

    /**
     * Renders HTML to display one course module for display within a section.
     *
     * This function calls:
     * {@link core_course_renderer::course_section_cm()}
     *
     * @param stdClass $course
     * @param completion_info $completioninfo
     * @param cm_info $mod
     * @param int|null $sectionreturn
     * @param array $displayoptions
     * @return String
     */
    public function course_section_cm_list_item($course, &$completioninfo, cm_info $mod, $sectionreturn, $displayoptions = array()) {
        if ($this->page->user_is_editing()) {
            return parent::course_section_cm_list_item($course, $completioninfo, $mod, $sectionreturn, $displayoptions);
        }

        $output = '';
        if ($modulehtml = $this->course_section_cm($course, $completioninfo, $mod, $sectionreturn, $displayoptions)) {
            $modclasses = 'activity ' . $mod->modname . ' modtype_' . $mod->modname . ' ' . $mod->extraclasses;
            $output .= html_writer::tag('div', $modulehtml, array('class' => $modclasses, 'id' => 'module-' . $mod->id));
            $url = $mod->url;
            if (!empty($url)) {
                $output .= $this->course_section_cm_text($mod, $displayoptions);
            }
        }
        return $output;
    }

    /**
     * Renders HTML to display a list of course modules in a course section
     * Also displays "move here" controls in Javascript-disabled mode
     *
     * This function calls {@link core_course_renderer::course_section_cm()}
     *
     * @param stdClass $course course object
     * @param int|stdClass|section_info $section relative section number or section object
     * @param int $sectionreturn section number to return to
     * @param int $displayoptions
     * @return void
     */
    public function course_section_cm_list($course, $section, $sectionreturn = null, $displayoptions = array()) {
        if ($this->page->user_is_editing()) {
            return parent::course_section_cm_list($course, $section, $sectionreturn, $displayoptions);
        }

        $output = '';
        $modinfo = get_fast_modinfo($course);
        if (is_object($section)) {
            $section = $modinfo->get_section_info($section->section);
        } else {
            $section = $modinfo->get_section_info($section);
        }
        $completioninfo = new completion_info($course);

        // Get the list of modules visible to user.
        $sectionoutput = '';
        if (!empty($modinfo->sections[$section->section])) {
            foreach ($modinfo->sections[$section->section] as $modnumber) {
                $mod = $modinfo->cms[$modnumber];

                if ($modulehtml = $this->course_section_cm_list_item($course,
                        $completioninfo, $mod, $sectionreturn, $displayoptions)) {
                    if ($mod->modname != 'label') {
                        switch(get_config('theme_squared', 'activitylayout')) {
                            case 1: // 1,3,3
                               $modclasses = 'col-sm-12 col-md-4 col-lg-4';
                               break;
                            case 2: // 1,2,4
                               $modclasses = 'col-sm-12 col-md-6 col-lg-3';
                               break;
                            case 3: // 1,2,3
                               $modclasses = 'col-sm-12 col-md-6 col-lg-4';
                               break;
                            default:
                               $modclasses = 'col-sm-12 col-md-4 col-lg-4';
                        }
                        $modclasses .= ' sqcol';
                    } else {
                        $modclasses = 'col-sm-12';
                    }
                    $sectionoutput .= html_writer::tag('div', $modulehtml, array('class' => $modclasses));
                }
            }
        }

        // Always output the section module list.
        $output .= html_writer::tag('div', $sectionoutput, array('class' => 'section img-text row'));

        return $output;
    }

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
            $coursenumsections = $courseformat->get_last_section_number();
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
                if ($thissection->section <= $coursenumsections) {
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