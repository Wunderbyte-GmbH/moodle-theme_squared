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
 * The squared theme makes uses a custom version of squared blocks
 *
 * @package theme_squared
 * @copyright 2018 onwards Onlinecampus Virtuelle PH
 * www.virtuelle-ph.at, David Bogner www.edulabs.org
 * @author G J Barnard - {@link http://moodle.org/user/profile.php?id=442195}
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die;

class theme_squared_core_course_renderer extends core_course_renderer {

    private $activitylayout = false;
    private static $contentcontrolinit = false;
    private $coursecat_toolbox = null;
    private $currentcategoryid = 0;
    private $categorysearchsort = 1;
    private $userisediting = false;

    private const moddivider = 1;
    private const hidemodsummary = 2;
    private const showmodsummary = 3;

    public function __construct(moodle_page $page, $target) {
        parent::__construct($page, $target);
        if (!$this->page->user_is_editing()) {
            if (!self::$contentcontrolinit) {
                $this->page->requires->js_call_amd('theme_squared/content_control', 'init');
                self::$contentcontrolinit = true;
            }
        }
        $this->coursecat_toolbox = \theme_squared\coursecat_toolbox::get_instance();
        $this->activitylayout = (!empty($this->page->theme->settings->activitylayout)) ? $this->page->theme->settings->activitylayout : false;
        $this->userisediting = $this->page->user_is_editing();
    }

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
        if (($this->userisediting == true) || ($this->activitylayout == false)) {
            return parent::course_section_cm_name_title($mod, $displayoptions);
        }

        $output = '';
        $url = $mod->url;
        if (!$mod->is_visible_on_course_page() || !$url) {
            // Nothing to be displayed to the user.
            return $output;
        }

        // Accessibility: for files get description via icon, this is very ugly hack!
        $instancename = $mod->get_formatted_name();
        $altname = $mod->modfullname;
        /* Avoid unnecessary duplication: if e.g. a forum name already
           includes the word forum (or Forum, etc) then it is unhelpful
           to include that in the accessible description that is added. */
        if (false !== strpos(core_text::strtolower($instancename), core_text::strtolower($altname))) {
            $altname = '';
        }
        // File type after name, for alphabetic lists (screen reader).
        if ($altname) {
            $altname = get_accesshide(' ' . $altname);
        }

        list($linkclasses, $textclasses) = $this->course_section_cm_classes($mod);

        /* Get on-click attribute value if specified and decode the onclick - it
           has already been encoded for display. */
        $onclick = htmlspecialchars_decode($mod->onclick, ENT_QUOTES);

        // Display link itself.
        $activitylink = html_writer::tag('span', $instancename . $altname, array('class' => 'instancename'));
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
     * Renders html to display the card activity header when used on a course page.
     *
     * @param cm_info $mod
     * @return array(boolean toexpand, string outout).
     */
    protected function squared_activity_header(cm_info $mod) {
        $output = html_writer::empty_tag('img', array('src' => $mod->get_icon_url(),
            'class' => 'sqactivityicon iconlarge activityicon', 'alt' => get_string('activityicon', 'theme_squared'), 'role' => 'presentation'));
        $instancename = $mod->get_formatted_name();
        $altname = $mod->modfullname;
        /* Avoid unnecessary duplication: if e.g. a forum name already
           includes the word forum (or Forum, etc) then it is unhelpful
           to include that in the accessible description that is added. */
        if (false !== strpos(core_text::strtolower($instancename), core_text::strtolower($altname))) {
            $altname = '';
        }
        // File type after name, for alphabetic lists (screen reader).
        if ($altname) {
            $altname = get_accesshide(' ' . $altname);
        }

        $output .= html_writer::tag('span', $instancename.$altname, array('class' => 'instancename modname'));

        return array('toexpand' => (core_text::strlen($instancename) > 48), 'output'=> $output);
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
        if ((empty($mod->url)) ||
            ($this->userisediting == true) ||
            ($this->activitylayout == false)) {
            return parent::course_section_cm($course, $completioninfo, $mod, $sectionreturn, $displayoptions);
        }

        $output = '';
        /* We return empty string (because course module will not be displayed at all)
           if:
           1) The activity is not visible to users
           and
           2) The 'availableinfo' is empty, i.e. the activity was
           hidden in a way that leaves no info, such as using the
           eye icon.
         */
        if (!$mod->is_visible_on_course_page()) {
            return $output;
        }

        $modicons = $this->course_section_cm_completion($course, $completioninfo, $mod, $displayoptions);
        if (!empty($modicons)) {
            $output .= html_writer::span($modicons, 'actions sqactions');
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
        if (($this->userisediting == true) || ($this->activitylayout == false)) {
            return parent::course_section_cm_list_item($course, $completioninfo, $mod, $sectionreturn, $displayoptions);
        }

        $output = '';
        $modclasses = 'activity ' . $mod->modname . ' modtype_' . $mod->modname . ' ' . $mod->extraclasses;

        if (empty($mod->url)) {
            $modulehtml = $this->course_section_cm($course, $completioninfo, $mod, $sectionreturn, $displayoptions);
            $output .= html_writer::tag('div', $modulehtml, array('class' => $modclasses, 'id' => 'module-' . $mod->id));
            $url = $mod->url;
            if (!empty($url)) {
                $output .= $this->course_section_cm_text($mod, $displayoptions);
            }
        } else {
            $url = $mod->url;
            if (!$mod->is_visible_on_course_page() || !$url) {
                // Nothing to be displayed to the user.
                return $output;
            }
            $modclasses = trim($modclasses).' card';

            list($linkclasses, $textclasses) = $this->course_section_cm_classes($mod);

            /* Get on-click attribute value if specified and decode the onclick - it
               has already been encoded for display. */
            $onclick = htmlspecialchars_decode($mod->onclick, ENT_QUOTES);

            $activitydata = $this->squared_activity_header($mod);
            $activity = $activitydata['output'];
            if ($mod->uservisible) {
                $cardcontent = html_writer::link($url, $activity, array('class' => $linkclasses.' card-header', 'onclick' => $onclick));
            } else {
                /* We may be displaying this just in order to show information
                   about visibility, without the actual link ($mod->is_visible_on_course_page()). */
                $cardcontent = html_writer::tag('div', $activity, array('class' => $textclasses.' card-header'));
            }

            $formattedcontent = $mod->get_formatted_content(array('overflowdiv' => false, 'noclean' => true));
            $contentlen = core_text::strlen(strip_tags($mod->content));

            if ((!empty($formattedcontent)) || ($displayoptions['sqshowdescription'] == self::showmodsummary)) {
                if ($contentlen > 45) {
                    $textclasses .= ' sqexpandedcontent';
                }
                if ($contentlen > 2748) {
                    $textclasses .= ' sqmorecontent';
                }

                $bodycontent = html_writer::tag('div', $formattedcontent, array('class' => trim('contentafterlink sqcontent '.$textclasses)));
                $cardcontent .= html_writer::start_tag('div', array('class' => 'card-body'));
                $cardcontent .= $bodycontent;
                $cardcontent .= html_writer::end_tag('div');
            }

            // Show availability info (if module is not available).
            $footercontent = $this->course_section_cm_availability($mod, $displayoptions);

            $modicons = $this->course_section_cm_completion($course, $completioninfo, $mod, $displayoptions);
            if (!empty($modicons)) {
                $footercontent .= html_writer::span($modicons, 'actions sqactions');
            }

            if ((($formattedcontent) && ($contentlen > 45)) || ($activitydata['toexpand'])) {
                if (\theme_squared\toolbox::get_config_setting('fav')) {
                    $icon = 'fas fa-chevron-circle-down';
                } else {
                    $icon = 'fa fa-chevron-circle-down';
                }
                $footercontent .= html_writer::tag('div',
                    html_writer::tag('i', null, array('class' => $icon, 'aria-hidden' => 'true', 'role' => 'button')).
                    html_writer::tag('span', get_string('expand'), array('class' => 'sqcc sqccopen', 'aria-hidden' => 'false')).
                    html_writer::tag('span', get_string('closebuttontitle'), array('class' => 'sqcc sqccclose hidden', 'aria-hidden' => 'true')),
                    array('class' => 'sqcontentcontrol'));
            }

            $cardcontent .= html_writer::start_tag('div', array('class' => 'card-footer'));
            $cardcontent .= $footercontent;
            $cardcontent .= html_writer::end_tag('div');


            $output .= html_writer::tag('div', $cardcontent, array('class' => $modclasses, 'id' => 'module-' . $mod->id));
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
     * @param array $displayoptions
     * @return void
     */
    public function course_section_cm_list($course, $section, $sectionreturn = null, $displayoptions = array()) {
        if (($this->userisediting == true) || ($this->activitylayout == false)) {
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

            $showdescriptions = $this->calculate_show_descriptions($modinfo->sections[$section->section], $modinfo);

            foreach ($modinfo->sections[$section->section] as $modnumber) {
                $mod = $modinfo->cms[$modnumber];

                if ($showdescriptions[$modnumber] != self::moddivider) {
                    $displayoptions['sqshowdescription'] = $showdescriptions[$modnumber];
                }

                if ($modulehtml = $this->course_section_cm_list_item($course, $completioninfo, $mod, $sectionreturn, $displayoptions)) {
                    if (!(empty($mod->url))) {
                        switch ($this->activitylayout) {
                            case 1: // 1,3,3.
                                $modclasses = 'col-sm-12 col-md-4 col-lg-4';
                                break;
                            case 2: // 1,2,4.
                                $modclasses = 'col-sm-12 col-md-6 col-lg-3';
                                break;
                            case 3: // 1,2,3.
                                $modclasses = 'col-sm-12 col-md-6 col-lg-4';
                                break;
                            default: // 1,3,3.
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
     * Calculates if a module should show its description.
     *
     * This function calls {@link core_course_renderer::course_section_cm()}
     *
     * @param array $sectionmodules int module numbers
     * @param course_modinfo $modinfo module info for the course.
     *
     * @return array boolean indicating if the description should be shown or self::moddivider if a label, indexed by module number.
     */
    private function calculate_show_descriptions($sectionmodules, $modinfo) {
        $showdescriptions = array();
        $current = false;

        foreach ($sectionmodules as $modnumber) {
            $mod = $modinfo->cms[$modnumber];

            if (!(empty($mod->url))) {
                if ($current == false) {
                    $formattedcontent = $mod->get_formatted_content(array('overflowdiv' => false, 'noclean' => true));
                    if (empty($formattedcontent)) {
                        $showdescriptions[$modnumber] = self::hidemodsummary;
                    } else {
                        $current = true;
                        $showdescriptions[$modnumber] = self::showmodsummary;
                        // Go back to the last label / start of section and show the rest.
                        $reversed = array_reverse($showdescriptions, true);
                        foreach($reversed as $modno => $modsd) {
                            if ($modsd == self::moddivider) {
                                // Label, so break.
                                break;
                            }
                            $showdescriptions[$modno] = self::showmodsummary;
                        }
                    }
                } else {
                    $showdescriptions[$modnumber] = self::showmodsummary;
                }
            } else {
                $showdescriptions[$modnumber] = self::moddivider; // A label.
                $current = false; // A label is a divider.
            }
        }

        return $showdescriptions;
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
                    $sectionname = format_string($thissection->name, true, array('context' => context_course::instance($course->id)));
                } else {
                    $sectionname = $courseformat->get_section_name($thissection->section);
                }
                if ($thissection->section <= $coursenumsections) {
                    // Do not link 'orphaned' sections.
                    $label = $course->fullname . ' - ' . $sectionname;
                    if (stristr($label, $term)) {
                        $courseurl = new moodle_url('/course/view.php');
                        $courseurl->param('id', $course->id);
                        $courseurl->param('sesskey', $sesskey);
                        if ((!empty($courseformatsettings['coursedisplay'])) &&
                                ($courseformatsettings['coursedisplay'] == COURSE_DISPLAY_MULTIPAGE)) {
                            $courseurl->param('section', $thissection->section);
                            $coursehref = $courseurl->out(false);
                        } else {
                            $coursehref = $courseurl->out(false) . '#section-' . $thissection->section;
                        }
                        $data[] = array('id' => $coursehref, 'label' => $label, 'value' => $label);
                    }
                }
                if (!empty($modinfo->sections[$thissection->section])) {
                    foreach ($modinfo->sections[$thissection->section] as $modnumber) {
                        $mod = $modinfo->cms[$modnumber];
                        if (!empty($mod->url)) {
                            $instancename = $mod->get_formatted_name();
                            $label = $course->fullname . ' - ' . $sectionname . ' - ' . $instancename;
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

    // Course listing #1009.
    /**
     * Renders HTML to display particular course category - list of it's subcategories and courses
     *
     * Invoked from /course/index.php
     *
     * @param int|stdClass|coursecat $category
     */
    public function course_category($category) {
        global $CFG;

        $coursecat = core_course_category::get(is_object($category) ? $category->id : $category);
        $site = get_site();
        $output = '';

        if (can_edit_in_category($coursecat->id)) {
            // Add 'Manage' button if user has permissions to edit this category.
            $managebutton = $this->single_button(new moodle_url('/course/management.php',
                array('categoryid' => $coursecat->id)), get_string('managecourses'), 'get');
            $this->page->set_button($managebutton);
        }
        $catcount = core_course_category::is_simple_site();
        if (!$coursecat->id) {
            if ($catcount == 1) {
                // There exists only one category in the system, do not display link to it
                $coursecat = core_course_category::get_default();
                $strfulllistofcourses = get_string('fulllistofcourses');
                $this->page->set_title("$site->shortname: $strfulllistofcourses");
            } else {
                $strcategories = get_string('categories');
                $this->page->set_title("$site->shortname: $strcategories");
            }
        } else {
            $title = $site->shortname;
            if ($catcount > 1) {
                $title .= ": " . $coursecat->get_formatted_name();
            }
            $this->page->set_title($title);
        }

        // Print current category description
        $chelper = new coursecat_helper();
        if ($description = $chelper->get_category_formatted_description($coursecat)) {
            $output .= $this->box($description, array('class' => 'generalbox info'));
        }

        $coursedisplayoptions = array();

        // Used in 'coursecat_tree()' and needed in 'coursecat_courses()'.
        $coursedisplayoptions['sqcategorysearch'] = optional_param('sqcategorysearch', '', PARAM_TEXT);
        $coursedisplayoptions['sqcardlayout'] = true;

        $perpage = optional_param('perpage', $CFG->coursesperpage, PARAM_INT);
        $page = optional_param('page', 0, PARAM_INT);
        $baseurl = new moodle_url('/course/index.php');
        if ($coursecat->id) {
            $baseurl->param('categoryid', $coursecat->id);
        } else {
            $chelper->set_show_courses(self::COURSECAT_SHOW_COURSES_EXPANDED_WITH_CAT); // State the category when all categories.
        }
        if ($perpage != $CFG->coursesperpage) {
            $baseurl->param('perpage', $perpage);
        }
        if ($perpage == 'all') { // Does the search on all and established in coursecat_courses_content().
            $coursedisplayoptions['limit'] = null;
            $coursedisplayoptions['offset'] = 0;
        } else {
            $coursedisplayoptions['limit'] = $perpage;
            $coursedisplayoptions['offset'] = $page * $perpage;
        }
        $coursedisplayoptions['paginationurl'] = new moodle_url($baseurl);
        $this->set_squared_search($coursedisplayoptions['paginationurl']);
        $chelper->set_courses_display_options($coursedisplayoptions);

        // Display course category tree.
        $output .= $this->coursecat_tree($chelper, $coursecat);

        // Add action buttons
        $output .= $this->container_start('buttons');
        $context = get_category_or_system_context($coursecat->id);
        if (has_capability('moodle/course:create', $context)) {
            // Print link to create a new course, for the 1st available category.
            if ($coursecat->id) {
                $url = new moodle_url('/course/edit.php', array('category' => $coursecat->id, 'returnto' => 'category'));
            } else {
                $url = new moodle_url('/course/edit.php', array('category' => $CFG->defaultrequestcategory, 'returnto' => 'topcat'));
            }
            $output .= $this->single_button($url, get_string('addnewcourse'), 'get');
        }
        ob_start();
        if ($catcount == 1) {
            print_course_request_buttons(context_system::instance());
        } else {
            print_course_request_buttons($context);
        }
        $output .= ob_get_contents();
        ob_end_clean();
        $output .= $this->container_end();

        return $output;
    }

    /**
     * Returns HTML to display a tree of subcategories and courses in the given category
     *
     * @param coursecat_helper $chelper various display options
     * @param coursecat $coursecat top category (this category's name and description will NOT be added to the tree)
     * @return string
     */
    protected function coursecat_tree(coursecat_helper $chelper, $coursecat) {
        if (!$this->is_card_layout($chelper)) {
            return parent::coursecat_tree($chelper, $coursecat);
        }
        $this->currentcategoryid = $coursecat->id;
        $this->categorysearchsort = optional_param('searchsort', 1, PARAM_INT);

        $courses = $this->coursecat_toolbox->search_courses(
                $chelper->get_courses_display_option('sqcategorysearch'),
                array(
                    'categoryid' => $coursecat->id,
                    'limit' => $chelper->get_courses_display_option('limit'),
                    'offset' => $chelper->get_courses_display_option('offset'),
                    'sort' => array('sortorder' => $this->categorysearchsort)
                )
        );

        $categorycontent = $this->coursecat_courses($chelper, $courses['courses'], $courses['totalcount']);
        if (empty($categorycontent)) {
            return '';
        }

        // Start content generation
        $content = '';
        $attributes = $chelper->get_and_erase_attributes('course_category_tree clearfix');
        $content .= html_writer::start_tag('div', $attributes);
        $content .= html_writer::tag('div', $categorycontent, array('class' => 'content'));
        $content .= html_writer::end_tag('div'); // .course_category_tree

        return $content;
    }

    /**
     * Renders the list of courses
     *
     * This is internal function, please use {@link core_course_renderer::courses_list()} or another public
     * method from outside of the class
     *
     * If list of courses is specified in $courses; the argument $chelper is only used
     * to retrieve display options and attributes, only methods get_show_courses(),
     * get_courses_display_option() and get_and_erase_attributes() are called.
     *
     * @param coursecat_helper $chelper various display options
     * @param array $courses the list of courses to display
     * @param init $totalcount total count of the courses
     * @return string
     */
    protected function coursecat_courses_content(coursecat_helper $chelper, $courses, $totalcount) {
        global $CFG;

        // Prepare content of paging bar if it is needed.
        $pagingbar = null;
        $paginationurl = $chelper->get_courses_display_option('paginationurl');

        if ($paginationurl) {
            if ($totalcount > count($courses)) {
                // There are more results that can fit on one page.
                $perpage = $chelper->get_courses_display_option('limit', $CFG->coursesperpage);
                $page = $chelper->get_courses_display_option('offset') / $perpage;
                $pagingbar = $this->paging_bar($totalcount, $page, $perpage, $paginationurl->out(false, array('perpage' => $perpage)));
                $pagingallbar = html_writer::tag('div', html_writer::link($paginationurl->out(false, array('perpage' => 'all')),
                    get_string('showall', '', $totalcount)), array('class' => 'paging paging-showall mdl-align'));
            } else if ($totalcount > $CFG->coursesperpage) {
                // There is more than one page of results and we are in 'show all' mode, suggest to go back to paginated view mode.
                $pagingallbar = html_writer::tag('div', html_writer::link($paginationurl->out(false,
                    array('perpage' => $CFG->coursesperpage)), get_string('showperpage', '', $CFG->coursesperpage)),
                    array('class' => 'paging paging-showperpage mdl-align'));
            }
        }

        // Display list of courses.
        $content = '';
        if (!empty($pagingbar)) {
            $content .= $pagingbar;
        }

        $content .= html_writer::start_tag('div', array('class' => 'sqcoursecards card-deck'));
        $coursecount = 0;
        foreach ($courses as $course) {
            $coursecount++;
            $classes = ($coursecount % 2) ? 'odd' : 'even';
            if ($coursecount == 1) {
                $classes .= ' first';
            }
            if ($coursecount >= count($courses)) {
                $classes .= ' last';
            }
            $content .= $this->coursecat_coursebox($chelper, $course, $classes);
        }
        $content .= html_writer::end_tag('div'); // .card-deck

        if (!empty($pagingbar)) {
            $content .= $pagingbar;
        }
        if (!empty($pagingallbar)) {
            $content .= $pagingallbar;
        }
        return $content;
    }

    /**
     * Renders the list of courses
     *
     * This is internal function, please use {@link core_course_renderer::courses_list()} or another public
     * method from outside of the class
     *
     * If list of courses is specified in $courses; the argument $chelper is only used
     * to retrieve display options and attributes, only methods get_show_courses(),
     * get_courses_display_option() and get_and_erase_attributes() are called.
     *
     * @param coursecat_helper $chelper various display options
     * @param array $courses the list of courses to display
     * @param int|null $totalcount total number of courses (affects display mode if it is AUTO or pagination if applicable),
     *     defaulted to count($courses)
     * @return string
     */
    protected function coursecat_courses(coursecat_helper $chelper, $courses, $totalcount = null) {
        if (!$this->is_card_layout($chelper)) {
            return parent::coursecat_courses($chelper, $courses, $totalcount);
        }
        if ($totalcount === null) {
            $totalcount = count($courses);
        }
        if (!$totalcount) {
            // Courses count is cached during courses retrieval.
            return '';
        }

        $content = html_writer::start_tag('div', array('id' => 'sqccf', 'class' => 'row justify-content-between')); // Start search row.
        $content .= html_writer::start_tag('div', array('class' => 'col-md-6 col-lg-4')); // Start search category.
        $content .= $this->squared_category_select_search();
        $content .= html_writer::end_tag('div'); // End search category.
        $content .= html_writer::start_tag('div', array('class' => 'col-md-6 col-lg-4')); // Start search form.
        $content .= $this->squared_category_course_search($chelper->get_courses_display_option('sqcategorysearch'));
        $content .= html_writer::end_tag('div'); // End search form.
        $content .= html_writer::start_tag('div', array('class' => 'col-md-12 col-lg-4')); // Start search sort.
        $content .= $this->squared_search_sort();
        $content .= html_writer::end_tag('div'); // End search sort.
        $content .= html_writer::end_tag('div'); // End search row.

        // Display list of courses.
        $attributes = $chelper->get_and_erase_attributes('courses');
        $attributes['id'] = 'sqccs';
        $content .= html_writer::start_tag('div', $attributes);
        $content .= $this->coursecat_courses_content($chelper, $courses, $totalcount);
        $content .= html_writer::end_tag('div'); // .courses

        return $content;
    }

    /**
     * Generate the category select markup.
     *
     * @return string Markup.
     */
    protected function squared_category_select_search() {
        $cats = $this->coursecat_toolbox::make_categories_list();

        $content = html_writer::start_tag('div', array('class' => 'sqscat'));
        $content .= html_writer::start_tag('form', array('class' => 'mdl-align'));

        $content .= html_writer::tag('label', get_string('coursecategory') . ': ', array('for' => 'sq-category-select', 'class' => 'd-inline'));

        $content .= html_writer::start_tag('select', array(
            'class' => 'sqselect sqformelement',
            'disabled' => 'disabled',
            'id' => 'sq-category-select',
            'name' => 'squaredcategoryselect')
        );

        $attrs = array('value' => '0');
        if ($this->currentcategoryid == 0) {
            $attrs['selected'] = 'selected';
        }
        $content .= html_writer::tag('option', get_string('all'), $attrs);
        foreach ($cats as $catkey => $catdata) {
            $coursecat = core_course_category::get($catkey);
            $attrs = array('value' => $catkey);
            if ($catkey == $this->currentcategoryid) {
                $attrs['selected'] = 'selected';
            }
            if ((!empty($coursecat->theme)) && ($coursecat->theme != 'squared')) {
                $attrs['data-differenttheme'] = 'true';
            }
            $catdatalen = mb_strlen($catdata);
            if ($catdatalen >= 50) {
                $catdata = '...'.mb_substr($catdata, ($catdatalen - 47));
            }
            $content .= html_writer::tag('option', $catdata, $attrs);
        }
        $content .= html_writer::end_tag('select');

        $content .= html_writer::end_tag('form');
        $content .= html_writer::end_tag('div');

        // AJAX initialised in squared_category_course_search().

        return $content;
    }

    /**
     * Returns the Squared category course search form and initialises the associated jQuery AMD module.
     *
     * @param string $sqcategorysearch Current search if any.
     * @return string Markup.
     */
    protected function squared_category_course_search($sqcategorysearch) {
        $content = html_writer::start_tag('form', array('class' => 'mdl-align'));
        $content .= html_writer::tag('label', get_string('searchcourses') . ': ', array('for' => 'sq-category-search', 'class' => 'd-inline'));
        $content .= html_writer::empty_tag('input', array(
                'class' => 'sqinput sqformelement',
                'disabled' => 'disabled',
                'id' => 'sq-category-search',
                'name' => 'sqcategorysearch',
                'size' => '28',
                'type' => 'text',
                'value' => $sqcategorysearch // Deal with populating the field if the user presses enter.
            )
        );
        $content .= html_writer::end_tag('form');

        $siteurl = new \moodle_url('/course/index.php');  // Needs to be this as can read category id.
        $ajaxurl = clone $siteurl;
        $this->set_squared_search($ajaxurl);
        $categorycoursesearchdata = array('data' => array(
            'siteurl' => $siteurl->out(false),
            'ajaxurl' => $ajaxurl->out(false),
            'categorystr' => get_string('category'),
            'catid' => $this->currentcategoryid,
            'sort' => $this->categorysearchsort));
        $this->page->requires->js_call_amd('theme_squared/category_course_search', 'init', $categorycoursesearchdata);

        return $content;
    }

    /**
     * Returns the Squared category course sort search form.
     *
     * @return string Markup.
     */
    protected function squared_search_sort() {
        $content = html_writer::start_tag('div', array('class' => 'sqssort'));
        $content .= html_writer::start_tag('form', array('class' => 'mdl-align'));
        $content .= html_writer::tag('label', get_string('sort') . ': ', array('for' => 'sq-category-sort', 'class' => 'd-inline'));
        $content .= html_writer::start_tag('select', array(
            'class' => 'sqselect sqformelement',
            'disabled' => 'disabled',
            'id' => 'sq-category-sort',
            'name' => 'squaredcategorysort')
        );

        $attrs = array('value' => '1');
        if ($this->categorysearchsort == 1) {
            $attrs['selected'] = 'selected';
        }
        $content .= html_writer::tag('option', get_string('asc'), $attrs);
        $attrs = array('value' => '-1');
        if ($this->categorysearchsort == -1) {
            $attrs['selected'] = 'selected';
        }
        $content .= html_writer::tag('option', get_string('desc'), $attrs);

        $content .= html_writer::end_tag('select');
        $content .= html_writer::end_tag('form');
        $content .= html_writer::end_tag('div');

        // AJAX initialised in squared_category_course_search().

        return $content;
    }

    /**
     * Displays one course in the list of courses.
     *
     * This is an internal function, to display an information about just one course
     * please use {@link core_course_renderer::course_info_box()}
     *
     * @param coursecat_helper $chelper various display options
     * @param course_in_list|stdClass $course
     * @param string $additionalclasses additional classes to add to the main <div> tag (usually
     *    depend on the course position in list - first/last/even/odd)
     * @return string
     */
    protected function coursecat_coursebox(coursecat_helper $chelper, $course, $additionalclasses = '') {
        if (!$this->is_card_layout($chelper)) {
            return parent::coursecat_coursebox($chelper, $course, $additionalclasses);
        }

        global $CFG;
        if (!isset($this->strings->summary)) {
            $this->strings->summary = get_string('summary');
        }
        if ($course instanceof stdClass) {
            $course = new core_course_list_element($course);
        }
        $content = '';
        $classes = trim('coursebox clearfix card ' . $additionalclasses);

        // Start .coursebox / .card.
        $content .= html_writer::start_tag('div', array(
            'class' => $classes,
            'data-courseid' => $course->id,
            'data-type' => self::COURSECAT_TYPE_COURSE,
        ));

        if ($course instanceof stdClass) {
            $course = new core_course_list_element($course);
        }
        $courseimage = $this->coursecat_toolbox->course_image($course, \theme_squared\coursecat_toolbox::foroverview);
        if (empty($courseimage['url'])) {
            $courseimage['image'] = false;
            $courseimage['url'] = 'data:image/svg+xml;charset=UTF-8,%3Csvg%20width%3D%22260%22%20height%3D%22180%22%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20viewBox%3D%220%200%20260%20180%22%20preserveAspectRatio%3D%22none%22%3E%3Cdefs%3E%3Cstyle%20type%3D%22text%2Fcss%22%3E%23holder_165246601bc%20text%20%7B%20fill%3A%23AAAAAA%3Bfont-weight%3Abold%3Bfont-family%3AArial%2C%20Helvetica%2C%20Open%20Sans%2C%20sans-serif%2C%20monospace%3Bfont-size%3A13pt%20%7D%20%3C%2Fstyle%3E%3C%2Fdefs%3E%3Cg%20id%3D%22holder_165246601bc%22%3E%3Crect%20width%3D%22260%22%20height%3D%22180%22%20fill%3D%22%23EEEEEE%22%3E%3C%2Frect%3E%3Cg%3E%3Ctext%20x%3D%2296.2734375%22%20y%3D%2296%22%3E100%%20x%20180%3C%2Ftext%3E%3C%2Fg%3E%3C%2Fg%3E%3C%2Fsvg%3E';
        }
        $classes = 'card-img-top';
        if ($courseimage['image']) {
            $classes .= ' sq-card-image';
        }
        $content .= html_writer::tag('div', '', array(
            'class' => $classes,
            'style' => 'background-image: url('.$courseimage['url'].');'
            )
        );
        $content .= html_writer::start_tag('div', array('class' => 'card-body'));
        // Course name.
        $coursename = $chelper->get_course_formatted_name($course);
        $coursenamelink = html_writer::link(new moodle_url('/course/view.php', array('id' => $course->id)), $coursename, array('class' => $course->visible ? '' : 'dimmed'));
        $content .= html_writer::tag('h3', $coursenamelink, array('class' => 'coursename card-title'));
        $content .= html_writer::start_tag('div', array('class' => 'card-text'));

        // Display course summary.
        if ($course->has_summary()) {
            $content .= html_writer::start_tag('div', array('class' => 'summary'));
            $content .= $chelper->get_course_formatted_summary($course, array('overflowdiv' => true, 'noclean' => true, 'para' => false));
            $content .= html_writer::end_tag('div'); // .summary
        }

        // Display course contacts. See core_course_list_element::get_course_contacts().
        if ($course->has_course_contacts()) {
            $content .= html_writer::start_tag('ul', array('class' => 'teachers'));
            foreach ($course->get_course_contacts() as $userid => $coursecontact) {
                $name = $coursecontact['rolename'] . ': ' .
                        html_writer::link(new moodle_url('/user/view.php', array('id' => $userid, 'course' => SITEID)), $coursecontact['username']);
                $content .= html_writer::tag('li', $name);
            }
            $content .= html_writer::end_tag('ul'); // Start .teachers.
        }

        // Display course category if necessary (for example in search results).
        if ($chelper->get_show_courses() == self::COURSECAT_SHOW_COURSES_EXPANDED_WITH_CAT) {
            if ($cat = core_course_category::get($course->category, IGNORE_MISSING)) {
                $content .= html_writer::start_tag('div', array('class' => 'coursecat'));
                $content .= get_string('category') . ': ' .
                    html_writer::link(new moodle_url('/course/index.php', array('categoryid' => $cat->id)), $cat->get_formatted_name(), array('class' => $cat->visible ? '' : 'dimmed'));
                $content .= html_writer::end_tag('div'); // End .coursecat.
            }
        }

        $content .= html_writer::end_tag('div'); // Start .card-text.
        $content .= html_writer::end_tag('div'); // Start .card-body.
        $content .= html_writer::start_tag('div', array('class' => 'card-footer'));
        // Print enrolmenticons.
        if ($icons = enrol_get_course_info_icons($course)) {
            $content .= html_writer::start_tag('div', array('class' => 'enrolmenticons')); // Start .enrolmenticons.
            foreach ($icons as $pix_icon) {
                $content .= $this->render($pix_icon);
            }
            $content .= html_writer::end_tag('div'); // End .enrolmenticons.
        }
        $content .= html_writer::end_tag('div'); // End .card-footer.

        $content .= html_writer::end_tag('div'); // .coursebox / .card

        return $content;
    }

    /**
     * Serve the AJAX call to search for the courses in the given category.
     *
     * @param int $categoryid The category.
     * @return string Markup.
     */
    public function category_courses_from_search($categoryid) {
        global $CFG;

        $coursecat = core_course_category::get($categoryid);
        $this->currentcategoryid = $categoryid;
        $chelper = new coursecat_helper();

        $coursedisplayoptions = array();
        $perpage = optional_param('perpage', $CFG->coursesperpage, PARAM_INT);
        $page = optional_param('page', 0, PARAM_INT);
        $coursedisplayoptions['sqcategorysearch'] = optional_param('sqcategorysearch', '', PARAM_TEXT);  // Needed in 'coursecat_courses()'.
        $coursedisplayoptions['sqcardlayout'] = true;
        $categorysearchsort = optional_param('searchsort', 1, PARAM_INT);
        $baseurl = new moodle_url('/course/index.php');
        $baseurl->param('categoryid', $coursecat->id);

        if ($perpage != $CFG->coursesperpage) {
            $baseurl->param('perpage', $perpage);
        }
        if ($perpage == 'all') { // Does the search on all and established in coursecat_courses_content().
            $coursedisplayoptions['limit'] = null;
            $coursedisplayoptions['offset'] = 0;
        } else {
            $coursedisplayoptions['limit'] = $perpage;
            $coursedisplayoptions['offset'] = $page * $perpage;
        }
        $coursedisplayoptions['paginationurl'] = new moodle_url($baseurl);
        $this->set_squared_search($coursedisplayoptions['paginationurl']);
        $chelper->set_courses_display_options($coursedisplayoptions);
        if (!$categoryid) {
            $chelper->set_show_courses(self::COURSECAT_SHOW_COURSES_EXPANDED_WITH_CAT); // State the category when all categories.
        }

        $courses = $this->coursecat_toolbox->search_courses(
                $coursedisplayoptions['sqcategorysearch'],
                array(
                    'categoryid' => $coursecat->id,
                    'limit' => $chelper->get_courses_display_option('limit'),
                    'offset' => $chelper->get_courses_display_option('offset'),
                    'sort' => array('sortorder' => $categorysearchsort)
                )
        );

        return $this->coursecat_courses_content($chelper, $courses['courses'], $courses['totalcount']);
    }

    // #1081 - Frontpage card layout.
    /**
     * Returns HTML to print list of available courses for the frontpage
     *
     * @return string
     */
    public function frontpage_available_courses() {
        global $CFG;

        $chelper = new coursecat_helper();
        $coursedisplayoptions = array();
        $coursedisplayoptions['sqcardlayout'] = true;
        $coursedisplayoptions['recursive'] = true;

        if (optional_param('sqfac', '0', PARAM_INT) == 1) { // Note: The AJAX for this allows another pagingation to remember.
            // If the optional parameters exist then their values are for us.
            $perpage = optional_param('perpage', $CFG->coursesperpage, PARAM_INT);
            $page = optional_param('page', 0, PARAM_INT);
        } else {
            $perpage = $CFG->coursesperpage;
            $page = 0;

            // Almost certainly not called by AJAX, so initialise.
            $this->page->requires->js_call_amd('theme_squared/frontpage_courses', 'init', array());
        }

        $baseurl = $this->get_squared_frontpage_courses();
        $baseurl->param('sqfac', 1);

        if ($perpage != $CFG->frontpagecourselimit) {
            $baseurl->param('perpage', $perpage);
        }
        if ($perpage == 'all') { // All available courses.
            $coursedisplayoptions['limit'] = $CFG->frontpagecourselimit;
            $coursedisplayoptions['offset'] = 0;
        } else {
            $coursedisplayoptions['limit'] = $perpage;
            $coursedisplayoptions['offset'] = $page * $perpage;
        }
        $coursedisplayoptions['paginationurl'] = new moodle_url($baseurl);

        $chelper->set_show_courses(self::COURSECAT_SHOW_COURSES_EXPANDED_WITH_CAT);
        $chelper->set_courses_display_options($coursedisplayoptions);

        $chelper->set_attributes(array('class' => 'frontpage-course-list-all'));
        $courses = core_course_category::get(0)->get_courses($chelper->get_courses_display_options());
        $totalcount = core_course_category::get(0)->get_courses_count($chelper->get_courses_display_options());
        if (!$totalcount && !$this->page->user_is_editing() && has_capability('moodle/course:create', context_system::instance())) {
            // Print link to create a new course, for the 1st available category.
            return $this->add_new_course_button();
        }

        // Display list of courses.
        $attributes = $chelper->get_and_erase_attributes('courses');
        $attributes['id'] = 'sqfac';
        $output = html_writer::start_tag('div', $attributes);
        $output .= $this->coursecat_courses_content($chelper, $courses, $totalcount);
        $output .= html_writer::end_tag('div'); // .courses

        return $output;
    }

    /**
     * Returns HTML to print list of courses user is enrolled to for the frontpage
     *
     * Also lists remote courses or remote hosts if MNET authorisation is used
     *
     * @return string
     */
    public function frontpage_my_courses() {
        global $USER, $CFG, $DB;

        if (!isloggedin() or isguestuser()) {
            return '';
        }

        $output = '';
        // The field 'summaryformat' can be used in get_course_formatted_summary().
        $courses  = enrol_get_my_courses('summary, summaryformat');
        $rhosts   = array();
        $rcourses = array();
        if (!empty($CFG->mnet_dispatcher_mode) && $CFG->mnet_dispatcher_mode==='strict') {
            $rcourses = get_my_remotecourses($USER->id);
            $rhosts   = get_my_remotehosts();
        }

        if (!empty($courses) || !empty($rcourses) || !empty($rhosts)) {
            $chelper = new coursecat_helper();
            $coursedisplayoptions = array();
            $coursedisplayoptions['sqcardlayout'] = true;

            if (optional_param('sqfmc', '0', PARAM_INT) == 1) { // Note: The AJAX for this allows another pagingation to remember.
                // If the optional parameters exist then their values are for us.
                $perpage = optional_param('perpage', $CFG->coursesperpage, PARAM_INT);
                $page = optional_param('page', 0, PARAM_INT);
            } else {
                $perpage = $CFG->coursesperpage;
                $page = 0;

                // Almost certainly not called by AJAX, so initialise.
                $this->page->requires->js_call_amd('theme_squared/frontpage_courses', 'init', array());
            }

            $baseurl = $this->get_squared_frontpage_courses();
            $baseurl->param('sqfmc', 1);

            if ($perpage != $CFG->frontpagecourselimit) {
                $baseurl->param('perpage', $perpage);
            }
            $totalcount = count($courses);
            if ($perpage == 'all') { // All enrolled courses.
                $coursedisplayoptions['limit'] = $CFG->frontpagecourselimit;
                $coursedisplayoptions['offset'] = 0;
            } else {
                $coursedisplayoptions['limit'] = $perpage;
                $coursedisplayoptions['offset'] = $page * $perpage;
                $courses = array_slice($courses, $coursedisplayoptions['offset'], $coursedisplayoptions['limit'], true);
            }
            $coursedisplayoptions['paginationurl'] = new moodle_url($baseurl);

            $morelink = null;
            if ($totalcount > $CFG->frontpagecourselimit) {
                // There are more enrolled courses than we can display, display link to 'My courses'.
                $courses = array_slice($courses, 0, $CFG->frontpagecourselimit, true);
                $totalcount = count($courses);
                $morelink = html_writer::tag('div', html_writer::link(new moodle_url('/my/'), new lang_string('mycourses')),
                    array('class' => 'paging paging-morelink'));
            } else if ($perpage == 'all') {
                // All enrolled courses are displayed, display link to 'All courses' if there are more courses in system.
                $morelink = html_writer::tag('div', html_writer::link(new moodle_url('/course/index.php'),
                    new lang_string('fulllistofcourses')),
                    array('class' => 'paging paging-morelink'));
            }

            $chelper->set_show_courses(self::COURSECAT_SHOW_COURSES_EXPANDED_WITH_CAT);
            $chelper->set_courses_display_options($coursedisplayoptions);
            $chelper->set_attributes(array('class' => 'frontpage-course-list-enrolled'));

            // Display list of courses.
            $attributes = $chelper->get_and_erase_attributes('courses');
            $attributes['id'] = 'sqfmc';
            $output .= html_writer::start_tag('div', $attributes);
            $output .= $this->coursecat_courses_content($chelper, $courses, $totalcount);
            if (!empty($morelink)) {
                $output .= $morelink;
            }
            $output .= html_writer::end_tag('div'); // .courses

            // MNET
            if (!empty($rcourses)) {
                // At the IDP, we know of all the remote courses.
                $output .= html_writer::start_tag('div', array('class' => 'courses'));
                foreach ($rcourses as $course) {
                    $output .= $this->frontpage_remote_course($course);
                }
                $output .= html_writer::end_tag('div'); // .courses
            } elseif (!empty($rhosts)) {
                // non-IDP, we know of all the remote servers, but not courses
                $output .= html_writer::start_tag('div', array('class' => 'courses'));
                foreach ($rhosts as $host) {
                    $output .= $this->frontpage_remote_host($host);
                }
                $output .= html_writer::end_tag('div'); // .courses
            }
        }
        return $output;
    }

    // Card layout and search helpers.
    /**
     * Sets the given url to be used for course category searches.
     * @param moodle_url $url The Moodle url to establish course category search on.
     */
    protected function set_squared_search(moodle_url $url) {
        $url->param('sesskey', sesskey());
        $url->param('ccs', 1); // Course category search.  Used to make code in 'toolbox.php::default_ajax()' simpler.
    }

    /**
     * Gets the given url to be used for front page course listings.
     *
     * @return moodle_url $url The Moodle url for front page course listings.
     */
    protected function get_squared_frontpage_courses() {
        $url = new moodle_url('/index.php');
        $url->param('sesskey', sesskey());
        $url->param('redirect', '0');

        return $url;
    }

    /**
     * States if we are using a squared theme card layout.  Thus any method that instigates the layout needs to set the
     * 'sqcardlayout' course display option to true.
     *
     * @param coursecat_helper $chelper The course helper.
     * @return boolean Yes we are!
     */
    protected function is_card_layout(coursecat_helper $chelper) {
        return ($chelper->get_courses_display_option('sqcardlayout') == true);
    }
}
