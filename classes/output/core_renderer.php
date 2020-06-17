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
 * @copyright 2016 onwards Onlinecampus Virtuelle PH
 * www.virtuelle-ph.at, David Bogner www.edulabs.org
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace theme_squared\output;

defined('MOODLE_INTERNAL') || die;

use block_contents;
use coursecat;
use html_writer;
use moodle_url;
use stdClass;

require_once($CFG->dirroot . '/course/format/lib.php');

class core_renderer extends \theme_boost\output\core_renderer {

    /**
     * Wrapper for header elements.
     *
     * @return string HTML to display the main header.
     */
    public function full_header() {
        $html = html_writer::start_tag('header', array('id' => 'main-header', 'class' => 'row p-a-1'));
        $usecourseimage = ((!empty($this->page->layout_options['courseimage'])) &&
            (!empty($this->page->theme->settings->courseheaderimage)));
        $courseheader = $this->course_header();
        if ($usecourseimage) {
            $contextheader = null;
        } else {
            $contextheader = $this->context_header();
        }
        if ((!empty($courseheader)) || (!empty($contextheader))) {
            if (($this->page->pagelayout == 'mydashboard') && (\theme_squared\toolbox::course_content_search())) {
                $html .= html_writer::start_div('col-8');
                $courseitemsearch = '<div class="courseitemsearch mdl-right">';
                $courseitemsearch .= '<div><p>'.get_string('findcoursecontent', 'theme_squared').'</p></div>';
                $courseitemsearch .= '<div id="courseitemsearchresults">';
                $courseitemsearch .= '<input type="text" name="courseitemsearch" id="courseitemsearch" disabled="disabled">';
                $courseitemsearch .= '</div></div>';
            } else {
                $html .= html_writer::start_div('col-12');
            }
            if ($contextheader) {
                $html .= html_writer::start_div('pull-left');
                $html .= $contextheader;
                $html .= html_writer::end_div();
            }
            if (!empty($courseitemsearch)) {
                $html .= html_writer::end_div();
                $html .= html_writer::start_div('col-4');
                $html .= $courseitemsearch;
            }
            $html .= html_writer::end_div();
            $html .= html_writer::start_div('col-12');
            $html .= html_writer::tag('div', $courseheader, array('id' => 'course-header'));
            $html .= html_writer::end_div();
        }
        $html .= html_writer::end_tag('header');
        if ($usecourseimage) {
            $course = new \core_course_list_element($this->page->course);
            $courseimage = \theme_squared\coursecat_toolbox::course_image_url($course, \theme_squared\coursecat_toolbox::forcourse);
            if (empty($courseimage)) {
                if ((empty($this->page->theme->settings->courseheaderimagefallback)) ||
                    ($this->page->theme->settings->courseheaderimagefallback == 'courseheaderimagefallbackthemeimage')) {
                    // Use the 'courseheaderimagefallbackimage' image or theme image if empty.
                    $courseheaderimagefallbackimage = $this->page->theme->setting_file_url('courseheaderimagefallbackimage', 'courseheaderimagefallbackimage');
                    if (empty($courseheaderimagefallbackimage)) {
                        $courseheaderimagefallbackimage = $this->image_url('pexels-photo-220320_crop', 'theme')->out();
                    }
                    $courseimage = array('url' => $courseheaderimagefallbackimage, 'image' => true);
                } else {
                    // Use the generated image.
                    $courseimage = array('url' => \theme_squared\coursecat_toolbox::course_pattern_url($course), 'image' => false);
                }
            } else {
                $courseimage = array('url' => $courseimage, 'image' => true);
            }

            $attr = array(
                'id' => 'course-image',
                'style' => 'background-image: url("'.$courseimage['url'].'");'
            );
            if (!$courseimage['image']) {
                $attr['class'] = 'sq-course-image';
            }
            $html .= html_writer::tag('div', '', $attr);
            if ($this->page->user_is_editing()) {
                $html .= html_writer::start_tag('div', array('class' => 'row'));
                $html .= html_writer::start_tag('div', array('class' => 'col-12'));
                $html .= html_writer::tag('h2', get_string('courseheaderimagehelpheading', 'theme_squared'));
                $html .= html_writer::tag('p', get_string('courseheaderimagehelpcontent', 'theme_squared'));
                $html .= html_writer::end_tag('div');
                $html .= html_writer::end_tag('div');
            }
            $html .= html_writer::start_tag('div', array('class' => 'row'));
            $html .= html_writer::start_tag('div', array('class' => 'col-10'));
            $html .= html_writer::tag('h1', format_string($course->fullname), array('class' => 'course-title'));
            $html .= html_writer::end_tag('div');
            $html .= html_writer::start_tag('div', array('class' => 'col-2'));
            $html .= $this->courseprogress($this->page->course);
            $html .= html_writer::end_tag('div');
            $html .= html_writer::end_tag('div');
        }
        return $html;
    }

    /**
     * Outputs a custom heading with a wrapper
     *
     * @see core_renderer::heading()
     */
    public function heading($text, $level = 2, $classes = 'main', $id = null) {
        // For section headings.
        $icon = '';
        if ($level == 3) {
            $icon = html_writer::tag('div', '', array('class' => 'sqheadingicon'));
            $text = html_writer::tag('span', $text, array('class' => 'sqheadingtext'));
        }

        $content = parent::heading ( $icon . $text, $level, $classes, $id );

        return $content;
    }

    /**
     * Outputs the course progress donut if course completion is on.
     *
     * @return string Markup.
     */
    protected function courseprogress($course) {
        $output = '';
        $courseformat = course_get_format($course);
        if (get_class($courseformat) != 'format_tiles') {
            $completion = new \completion_info($course);

            if ($completion->is_enabled()) {
                $templatedata = new \stdClass;
                $templatedata->progress = \core_completion\progress::get_course_progress_percentage($course);
                if (!is_null($templatedata->progress)) {
                    $templatedata->progress = floor($templatedata->progress);
                } else {
                    $templatedata->progress = 0;
                }
                $progressbar = $this->render_from_template('theme_squared/progress-chart', $templatedata);
                if (has_capability('report/progress:view',  \context_course::instance($course->id))) {
                    $courseprogress = new \moodle_url('/report/progress/index.php');
                    $courseprogress->param('course', $course->id);
                    $courseprogress->param('sesskey', sesskey());
                    $output .= html_writer::link($courseprogress, $progressbar);
                } else {
                    $output .= $progressbar;
                }
            }
        }

        return $output;
    }

    /**
     * Returns HTML attributes to use within the body tag. This includes an ID and classes.
     *
     * @since Moodle 2.5.1 2.6
     * @param string|array $additionalclasses Any additional classes to give the body tag,
     * @return string
     */
    public function body_attributes($additionalclasses = array()) {
        if ($this->page->pagelayout == 'login') {
            $hidelocallogin = (!isset($this->page->theme->settings->hidelocallogin)) ? false : $this->page->theme->settings->hidelocallogin;
            if ($hidelocallogin && $this->page->theme->settings->alternateloginurl > 0) {
                if (is_array($additionalclasses)) {
                    $additionalclasses[] = 'hidelocallogin';
                } else {
                    $additionalclasses .= ' hidelocallogin';
                }
            }
        }
        return parent::body_attributes($additionalclasses);
    }

    /**
     * Returns the settings menu for the navbar if any.
     *
     * @return string
     */
    public function navbar_settings_menu() {
        $menu = $this->region_main_settings_menu();
        if (empty($menu)) {
            $menu = $this->context_header_settings_menu();
        }

        $themenu = '';
        if (!empty($menu)) {
            $themenu .= '<div class="popover-region collapsed popover-region-nsm nav-link">'.$menu.'</div>';
        }

        return $themenu;
    }

    /**
     * Returns a search box.
     *
     * @param  string $id     The search box wrapper div id, defaults to an autogenerated one.
     * @return string         HTML with the search form hidden by default.
     */
    public function search_box($id = false) {
        $navbarsearch = (empty($this->page->theme->settings->navbarsearch)) ? false : $this->page->theme->settings->navbarsearch;

        if (empty($navbarsearch)) {
            return '';
        }

        if ($navbarsearch == 3) { // Global search.   Extra check in case has been turned off but setting not changed.
            global $CFG;

            /* Accessing $CFG directly as using \core_search::is_global_search_enabled would
               result in an extra included file for each site, even the ones where global search
               is disabled. */
            if (empty($CFG->enableglobalsearch) || !has_capability('moodle/search:query', \context_system::instance())) {
                return '';
            }
        }

        if ($id == false) {
            $id = uniqid();
        } else {
            // Needs to be cleaned, we use it for the input id.
            $id = clean_param($id, PARAM_ALPHANUMEXT);
        }

        if (\theme_squared\toolbox::get_config_setting('fav')) {
            $icon = 'fas fa-search';
        } else {
            $icon = 'fa fa-search';
        }
        $searchicon = html_writer::tag('span', '', array('title' => get_string('search', 'search'), 'class' => $icon));
        $searchicon = html_writer::tag('div', $searchicon, array('id' => 'sqsearchbutton', 'role' => 'button', 'tabindex' => 0));

        if ($navbarsearch == 1) {
            // Based on 'course_search_form' in the core course renderer.
            // JS to animate the form.
            $this->page->requires->js_call_amd('core/search-input', 'init', array($id));
            $strsearchcourses = get_string("searchcourses");
            $searchurl = new moodle_url('/course/search.php');

            $searchinput = html_writer::start_tag('form', array('id' => 'fid_'.$id, 'action' => $searchurl, 'method' => 'get'));
            $inputid = 'inp_'.$id;
            $searchinput .= html_writer::tag('label', $strsearchcourses.': ', array('for' => $inputid, 'class' => 'accesshide'));
            $searchinput .= html_writer::empty_tag('input', array('type' => 'text', 'id' => $inputid,
                'size' => 19, 'name' => 'search'));
            $searchinput .= html_writer::end_tag('form');

        } else if ($navbarsearch == 2) {
            $squaredsearch = new \moodle_url('/course/index.php');
            $squaredsearch->param('sesskey', sesskey());
            $navbaradvsearchdata = array('data' => array('theme' => $squaredsearch->out(false), 'id' => $id));
            $this->page->requires->js_call_amd('theme_squared/navbar_advanced_search', 'init', $navbaradvsearchdata);

            $searchinput = '<span id="navbaradvresults">';
            $searchinput .= html_writer::tag('label', get_string('enteryoursearchquery', 'search'),
                array('for' => 'navbaradvsearch', 'class' => 'accesshide'));
            $searchinput .= '<input type="text" name="navbaradvsearch" id="navbaradvsearch" disabled="disabled">';
            $searchinput .= '</span>';

        } else if ($navbarsearch == 3) {
            global $CFG;
            // JS to animate the form.
            $this->page->requires->js_call_amd('core/search-input', 'init', array($id));

            $formattrs = array('class' => 'search-input-form', 'action' => $CFG->wwwroot . '/search/index.php');
            $inputattrs = array('type' => 'text', 'name' => 'q', 'placeholder' => get_string('search', 'search'),
                'size' => 19, 'tabindex' => -1, 'id' => 'id_q_'.$id);
            $contents = html_writer::tag('label', get_string('enteryoursearchquery', 'search'),
                array('for' => 'id_q_' . $id, 'class' => 'accesshide')) . html_writer::tag('input', '', $inputattrs);
            $searchinput = html_writer::tag('form', $contents, $formattrs);
        } else {
            return '';
        }

        return html_writer::tag('div', $searchinput.$searchicon, array('class' => 'search-input-wrapper nav-link', 'id' => $id));
    }

    /**
     * Return the standard string that says whether you are logged in (and switched
     * roles/logged in as another user).
     * @param bool $withlinks if false, then don't include any links in the HTML produced.
     * If not set, the default is the nologinlinks option from the theme config.php file,
     * and if that is not set, then links are included.
     * @return string HTML fragment.
     */
    public function login_info($withlinks = null) {
        global $USER, $CFG, $DB, $SESSION;

        if (during_initial_install()) {
            return '';
        }

        if (is_null($withlinks)) {
            $withlinks = empty($this->page->layout_options['nologinlinks']);
        }

        $loginurl = $this->squared_get_login_url();
        $loginpage = ((string) $this->page->url === $loginurl);
        $course = $this->page->course;
        if (\core\session\manager::is_loggedinas()) {
            $realuser = \core\session\manager::get_realuser();
            $fullname = fullname($realuser, true);
            if ($withlinks) {
                $loginastitle = get_string('loginas');
                $realuserinfo = " [<a href=\"$CFG->wwwroot/course/loginas.php?id=$course->id&amp;sesskey=" . sesskey() . "\"";
                $realuserinfo .= "title =\"" . $loginastitle . "\">$fullname</a>] ";
            } else {
                $realuserinfo = " [$fullname] ";
            }
        } else {
            $realuserinfo = '';
        }

        $subscribeurl = preg_replace('/login\/index\.php/i', 'login/signup.php', $loginurl);

        if (empty($course->id)) {
            // $course->id is not defined during installation
            return '';
        } else if (isloggedin()) {
            $context = \context_course::instance($course->id);

            $fullname = fullname($USER, true);
            // Since Moodle 2.0 this link always goes to the public profile page (not the course profile page)
            if ($withlinks) {
                $linktitle = get_string('viewprofile');
                $username = "<a href=\"$CFG->wwwroot/user/profile.php?id=$USER->id\" title=\"$linktitle\">$fullname</a>";
            } else {
                $username = $fullname;
            }
            if (is_mnet_remote_user($USER) and $idprovider = $DB->get_record('mnet_host',
                array('id' => $USER->mnethostid))) {
                if ($withlinks) {
                    $username .= " from <a href=\"{$idprovider->wwwroot}\">{$idprovider->name}</a>";
                } else {
                    $username .= " from {$idprovider->name}";
                }
            }
            if (isguestuser()) {
                if (!$loginpage && $withlinks) {
                    $loggedinas = " <a class=\"standardbutton plainlogin btn\" href=\"$loginurl\">" . get_string('login') . '</a>';
                }
            } else if (is_role_switched($course->id)) { // Has switched roles
                $rolename = '';
                if ($role = $DB->get_record('role', array('id' => $USER->access['rsw'][$context->path]))) {
                    $rolename = ': ' . role_get_name($role, $context);
                }
                $loggedinas = '<span class="loggedintext">' . get_string('loggedinas', 'moodle', $username) . $rolename . '</span>';
                if ($withlinks) {
                    $url = new moodle_url('/course/switchrole.php',
                        array('id' => $course->id, 'sesskey' => sesskey(), 'switchrole' => 0, 'returnurl' => $this->page->url->out_as_local_url(false)));
                    $loggedinas .= '(' . html_writer::tag('a', get_string('switchrolereturn'),
                        array('href' => $url, 'class' => 'btn')) . ')';
                }
            } else {
                $loggedinas = '<span class="loggedintext">' . $realuserinfo . get_string('loggedinas', 'moodle',
                    $username) . '</span>';
                if ($withlinks) {
                    if (\theme_squared\toolbox::get_config_setting('fav')) {
                        $icon = 'fas fa-sign-out-alt';
                    } else {
                        $icon = 'fa fa-sign-out';
                    }
                    $loggedinas .= html_writer::tag('div',
                        html_writer::link(new moodle_url('/login/logout.php?sesskey=' . sesskey()),
                        '<em><span class="'.$icon.'"></span>' . get_string('logout') . '</em>'));
                }
            }
        } else {
            if (!$loginpage && $withlinks) {
                $loggedinas = "<a class=\"standardbutton plainlogin btn\" href=\"$loginurl\">" . get_string('login') . '</a>';
            }
        }

        if (!empty($loggedinas)) {
            $loggedinas = '<div class="logininfo">' . $loggedinas . '</div>';
        } else {
            $loggedinas = '';
        }

        if (isset($SESSION->justloggedin)) {
            unset($SESSION->justloggedin);
            if (!empty($CFG->displayloginfailures)) {
                if (!isguestuser()) {
                    if ($count = user_count_login_failures($USER)) {
                        $loggedinas .= '<div class="loginfailures">';
                        $a = new stdClass();
                        $a->attempts = $count;
                        $loggedinas .= get_string('failedloginattempts', '', $a);
                        if (file_exists("$CFG->dirroot/report/log/index.php") and has_capability('report/log:view', \context_system::instance())) {
                            $loggedinas .= ' ('.html_writer::link(new moodle_url('/report/log/index.php', array('chooselog' => 1,
                                            'id' => 0 , 'modid' => 'site_errors')), get_string('logs')).')';
                        }
                        $loggedinas .= '</div>';
                    }
                }
            }
        }

        return $loggedinas;
    }

    /**
     * Returns MNET Login URL instead of standard login URL. Checks the wanted url
     * of user in order to provide correct redirect url for the identity provider
     *
     * @return string login url
     */
    private function squared_get_login_url() {
        global $DB, $SESSION, $CFG;
        if (empty($this->page->theme->settings->alternateloginurl)) {
            return get_login_url();
        }
        if ($this->page->url->out() === $CFG->wwwroot . "/login/index.php") {
            $urltogo = $SESSION->wantsurl;
        } else {
            $urltogo = $this->page->url->out();
        }
        $authplugin = get_auth_plugin('mnet');
        $authurl = $authplugin->loginpage_idp_list($urltogo);

        // Check the id of the MNET host for the idp
        $host = $DB->get_field('mnet_host', 'name', array('id' => $this->page->theme->settings->alternateloginurl));
        if (!empty($authurl)) {
            foreach ($authurl as $key => $urlarray) {
                if ($urlarray['name'] == $host) {
                    $loginurl = $authurl[$key]['url'];
                    return $loginurl;
                } else {
                    $loginurl = "$CFG->wwwroot/login/index.php";
                    if (!empty($CFG->loginhttps)) {
                        $loginurl = str_replace('http:', 'https:', $loginurl);
                    }
                }
            }
        } else {
            $loginurl = "$CFG->wwwroot/login/index.php";
            if (!empty($CFG->loginhttps)) {
                $loginurl = str_replace('http:', 'https:', $loginurl);
            }
        }
        return $loginurl;
    }

    /**
     * Produces a header for a block
     *
     * @param block_contents $bc
     * @return string
     */
    protected function block_header_collapse(block_contents $bc) {
        $title = '';
        $collapseattributes = array('role' => 'button',
            'class' => 'collapselink',
            'data-toggle' => 'collapse',
            'data-target' => '#subcollapse'.$bc->blockinstanceid,
            'aria-expanded' => 'false',
            'aria-controls' => 'inst'.$bc->blockinstanceid);
        if ($bc->title) {
            $attributes = array_merge($collapseattributes);
            $attributes['class'] = $attributes['class'].' sqtitle';
            if ($bc->blockinstanceid) {
                $attributes['id'] = 'instance-'.$bc->blockinstanceid.'-header';
            }
            $title = html_writer::tag('h2', $bc->title, $attributes);
        }

        $blockid = null;
        if (isset($bc->attributes['id'])) {
            $blockid = $bc->attributes['id'];
        }
        $controlshtml = $this->block_controls($bc->controls, $blockid);

        $collapseattributes['class'] = $collapseattributes['class'].' courseblock-icon';
        $icon = html_writer::tag('div', '', $collapseattributes);

        $iconarea = html_writer::tag('div', $icon.$controlshtml, array('class' => 'd-inline-block icon-container'));

        $output = '';
        if ($title || $controlshtml) {
            $output .= html_writer::tag('div', html_writer::tag('div', $iconarea.html_writer::tag('div', '',
                array('class'=>'block_action')).$title, array('class' => 'title')), array('class' => 'header'));
        }
        return $output;
    }

    /**
     * Output all the blocks in a particular region.
     *
     * @param string $region the name of a region on this page.
     * @return string the HTML to be output.
     */
    public function blocks_for_region($region) {
        if ($region == 'content') {
            return parent::blocks_for_region($region);
        }

        $blockcontents = $this->page->blocks->get_content_for_region($region, $this);
        $blocks = $this->page->blocks->get_blocks_for_region($region);
        $lastblock = null;
        $zones = array();
        foreach ($blocks as $block) {
            $zones[] = $block->title;
        }

        // Need to know the count of the blocks so we know what header to use.
        $numblocks = 1; // Take into account the fake flat navigation block.
        foreach ($blockcontents as $bc) {
            if ($bc instanceof block_contents) {
                if (($bc->attributes['data-block'] == 'navigation') || ($bc->attributes['data-block'] == 'settings')) {
                    continue;
                }

                $numblocks++;
            } else if ($bc instanceof block_move_target) {
                $numblocks++;
            } else {
                throw new coding_exception('Unexpected type of thing (' . get_class($bc) . ') found in list of block contents.');
            }
        }
        if (isset($this->page->theme->settings->blockperrowlimit) && $numblocks >= $this->page->theme->settings->blockperrowlimit) {
            $blocksrows = true;
        } else {
            $blocksrows = false;
        }

        $template = new stdClass();

        // Add flat navigation.
        global $PAGE;
        $flatnavname = get_string('flatnavigation', 'theme_squared');
        $templatecontext = array('flatnavigation' => $PAGE->flatnav);
        $thisblock = new stdClass();
        $thisblock->name = 'block_flat_navigation';
        $thisblock->title = '<span class="title">'.$flatnavname.'</span>';
        $thisblock->header = '<div role="button" class="collapselink" data-toggle="collapse" data-target="#subcollapsefake9999" aria-expanded="false" aria-controls="instfake9999">'.
            '<div class="header"><div class="title"><div class="d-inline-block icon-container"><div class="courseblock-icon"></div></div><h2 class="sqtitle">'.$flatnavname.'</h2></div></div>'.
            '</div>';
        $thisblock->content = $this->render_from_template('theme_squared/flat_navigation_content', $templatecontext);
        $thisblock->blockinstanceid = "fake9999"; // Not sure!  But we are a 'fake' block.
        $thisblock->instanceid = "fake9999";
        $thisblock->movetarget = false;
        $thisblock->attributes = array();
        $thisblock->attributes['aria-label'] = $flatnavname;
        $thisblock->attributes['class'] = 'block_flat_navigation block card';
        $thisblock->attributes['data-instanceid'] = $thisblock->blockinstanceid;
        $thisblock->atts = array();
        foreach ($thisblock->attributes as $key => $val) {
            $attribute = new stdClass();
            $attribute->key = $key;
            $attribute->value = $val;
            $thisblock->atts[] = $attribute;
        }
        $attribute = new stdClass();
        $attribute->key = "data-block";
        $attribute->value = "navigation";
        $specialattribute = new stdClass();
        $specialattribute->key = "id";
        $specialattribute->value = "inst-fake9999";
        $thisblock->atts[] = $specialattribute;
        $specialattribute = new stdClass();
        $specialattribute->key = "role";
        $specialattribute->value = "navigation";
        $thisblock->atts[] = $specialattribute;
        $specialattribute = new stdClass();
        $specialattribute->key = "data-instanceid";
        $specialattribute->value = "fake9999";
        $thisblock->atts[] = $specialattribute;
        $specialattribute = new stdClass();
        $specialattribute->key = "aria-labelledby";
        $specialattribute->value = "inst-fake9999-header";
        $thisblock->atts[] = $specialattribute;
        $template->blocks[] = $thisblock;

        // One block column.
        foreach ($blockcontents as $bc) {
            if ($bc instanceof block_contents) {
                if (($bc->attributes['data-block'] == 'navigation') || ($bc->attributes['data-block'] == 'settings')) {
                    continue;
                }

                $thisblock = $this->block($bc, $region);
                if ($bc->attributes['data-block'] == 'adminblock') {
                    $bc->blockinstanceid = -1;
                    $thisblock->blockinstanceid = $bc->blockinstanceid;
                }
                $thisblock->header = $this->block_header_collapse($thisblock); // Pass in the new potentially altered block_contents.
                $thisblock->movetarget = false;

                $template->blocks[] = $thisblock;
                $lastblock = $bc->title;
            } else if ($bc instanceof block_move_target) {
                $movetarget = new stdClass();
                $movetarget->movetarget = $this->block_move_target($bc, $zones, $lastblock, $region);

                $template->blocks[] = $movetarget;
            } else {
                throw new coding_exception('Unexpected type of thing (' . get_class($bc) . ') found in list of block contents.');
            }
        }

        if ($blocksrows) {
            // Two block columns
            $template->pairs = array();
            $count = 1;
            $pair = new stdClass();
            foreach ($template->blocks as $block) {
                if (!$block->movetarget && is_array($block->attributes) && isset($block->attributes['data-block'])) {
                    $block->name = 'block_' . $block->attributes['data-block'];
                }
                $block->shape = 'squared';
                if ($block->name == "block_adminblock") {
                    $block->blockinstanceid = -1;
                }
                if ($block->name == "block_settings") {
                    $block->subopen = true;
                }

                if ($count == 2) {
                    $pair->blockb = $block;
                    $template->pairs[] = $pair;
                    $pair = false;
                    $count = 1;
                } else {
                    $pair = new stdClass();
                    $pair->class = 'col-6';
                    $pair->blocka = $block;
                    $count++;
                }
            }
            if ($pair) {
                if (($numblocks % 2) != 0) {
                    $pair->blocka->shape = 'rectangle';
                    $pair->class = 'col-12 lastblock';
                }
                $template->pairs[] = $pair;
            }

            return $this->render_from_template('theme_squared/blocksrows', $template);
        } else {
            return $this->render_from_template('theme_squared/blocks', $template);
        }
    }

    /**
     * Prints a nice side block with an optional header.
     *
     * Note: M3.7 version uses a template.  Ported M3.6 methods initially,
     *       but could consider using as such in the future.
     *
     * The content is described
     * by a {@link core_renderer::block_contents} object.
     *
     * <div id="inst{$instanceid}" class="block_{$blockname} block">
     *      <div class="header"></div>
     *      <div class="content">
     *          ...CONTENT...
     *          <div class="footer">
     *          </div>
     *      </div>
     *      <div class="annotation">
     *      </div>
     * </div>
     *
     * @param block_contents $bc HTML for the content
     * @param string $region the region the block is appearing in.
     * @return string the HTML to be output.
     */
    public function block(block_contents $bc, $region) {
        if ($region == 'content') {
            return parent::block($bc, $region);
        }
        $bc = clone($bc); // Avoid messing up the object passed in.
        if (empty($bc->blockinstanceid) || !strip_tags($bc->title)) {
            $bc->collapsible = block_contents::NOT_HIDEABLE;
        }
        if (!empty($bc->blockinstanceid)) {
            $bc->attributes['data-instanceid'] = $bc->blockinstanceid;
        }
        $skiptitle = strip_tags($bc->title);
        if ($bc->blockinstanceid && !empty($skiptitle)) {
            $bc->attributes['aria-labelledby'] = 'instance-'.$bc->blockinstanceid.'-header';
        } else if (!empty($bc->arialabel)) {
            $bc->attributes['aria-label'] = $bc->arialabel;
        }
        if ($bc->dockable) {
            $bc->attributes['data-dockable'] = 1;
        }
        // Collapsing does not work with default moodle hide/show blocks.
        /* if ($bc->collapsible == block_contents::HIDDEN) {
            $bc->add_class('hidden');
        } */
        if (!empty($bc->controls)) {
            $bc->add_class('block_with_controls');
        }

        $bc->add_class('card');

        $bc->content = $this->block_content($bc);
        $bc->annotation = $this->block_annotation($bc);

        foreach ($bc->attributes as $key => $val) {
            $attribute = new stdClass();
            $attribute->key = $key;
            $attribute->value = $val;
            // This is ridiculous. the book module should have a proper coding of the toc block ;-).
            if ($val == "_fake") {
                $attribute->key = "data-block";
                $attribute->value = "navigation";
                $specialattribute = new stdClass();
                $specialattribute->key = "id";
                $specialattribute->value = "inst-fake9";
                $bc->atts[] = $specialattribute;
                $specialattribute = new stdClass();
                $specialattribute->key = "role";
                $specialattribute->value = "navigation";
                $bc->atts[] = $specialattribute;
                $specialattribute = new stdClass();
                $specialattribute->key = "data-instanceid";
                $specialattribute->value = "fake9";
                $bc->atts[] = $specialattribute;
                $specialattribute = new stdClass();
                $specialattribute->key = "aria-labelledby";
                $specialattribute->value = "inst-fake9-header";
                $bc->atts[] = $specialattribute;
                $bc->collapsible = 2;
                $bc->instanceid = "fake9";
                $bc->blockinstanceid = "fake9";
            }
            $bc->atts[] = $attribute;
        }

        return $bc;
    }

    /**
     * Produces the content area for a block
     *
     * @param block_contents $bc
     * @return string
     */
    protected function block_content(block_contents $bc) {
        $output = html_writer::start_tag('div', array('class' => 'content'));
        if (!$bc->title && !$this->block_controls($bc->controls)) {
            $output .= html_writer::tag('div', '', array('class'=>'block_action notitle'));
        }
        $output .= $bc->content;
        $output .= $this->block_footer($bc);
        $output .= html_writer::end_tag('div');

        return $output;
    }

    /**
     * Produces the footer for a block
     *
     * @param block_contents $bc
     * @return string
     */
    protected function block_footer(block_contents $bc) {
        $output = '';
        if ($bc->footer) {
            $output .= html_writer::tag('div', $bc->footer, array('class' => 'footer'));
        }
        return $output;
    }

    /**
     * Produces the annotation for a block
     *
     * @param block_contents $bc
     * @return string
     */
    protected function block_annotation(block_contents $bc) {
        $output = '';
        if ($bc->annotation) {
            $output .= html_writer::tag('div', $bc->annotation, array('class' => 'blockannotation'));
        }
        return $output;
    }

    protected function squared_prepare_textlinks($textlinks) {
        $textsnippets = explode ( ';', $textlinks );
        foreach ($textsnippets as $value) {
            $textandlinks [] = explode ( ',', $value, 2 );
        }
        $renderedtext = '';
        $lastelement = end ( $textandlinks );
        if (empty ( $lastelement [0] )) {
            $lastelement = prev ( $textandlinks );
        }
        $attributes = array ();
        foreach ($textandlinks as $value) {
            if (empty ( $value [0] )) {
                continue;
            }
            $renderedtext .= html_writer::start_tag ( 'span', $attributes );
            $renderedtext .= html_writer::tag ( 'a', trim ( $value [0] ), array (
                    'href' => trim ( $value [1] )
            ) );
            $renderedtext .= html_writer::end_tag ( 'span' );
        }
        $renderedtext .= html_writer::tag ('span', page_doc_link(get_string('moodledocslink')), array (
            'class' => 'helplink'
        ) );
        $renderedtext .= html_writer::tag ('span', 'Theme by <a href="http://www.edulabs.org" target="_blank">edulabs.org - e-learning solutions</a>', array (
            'class' => 'squared-themeby lastelement'
        ) );
        return $renderedtext;
    }

    /**
     * Produces the footer
     *
     * @return string
     */
    public function squared_textlinks($position) {
        $textlinks = '';
        if (empty ( $this->page->theme->settings->footertext )) {
            $setting = '';
        } else {
            $setting = $this->page->theme->settings->footertext;
        }
        if ($position == 'footer') {
            $textlinks = $this->squared_prepare_textlinks ( $setting );
        } else {
            $textlinks = $this->squared_prepare_textlinks ( $setting );
        }
        $content = html_writer::tag ( 'div', $textlinks, array (
                'class' => 'footercontent'
        ) );
        return $content;
    }

    /**
     * Construct a user menu, returning HTML that can be echoed out by a
     * layout file.
     *
     * @param stdClass $user A user object, usually $USER.
     * @param bool $withlinks true if a dropdown should be built.
     * @return string HTML fragment.
     */
    public function user_menu($user = null, $withlinks = null) {
        global $USER, $CFG;
        require_once($CFG->dirroot . '/user/lib.php');

        if (is_null($user)) {
            $user = $USER;
        }

        // Note: this behaviour is intended to match that of core_renderer::login_info,
        // but should not be considered to be good practice; layout options are
        // intended to be theme-specific. Please don't copy this snippet anywhere else.
        if (is_null($withlinks)) {
            $withlinks = empty($this->page->layout_options['nologinlinks']);
        }

        // Add a class for when $withlinks is false.
        $usermenuclasses = 'usermenu';
        if (!$withlinks) {
            $usermenuclasses .= ' withoutlinks';
        }

        $returnstr = "";

        // If during initial install, return the empty return string.
        if (during_initial_install()) {
            return $returnstr;
        }

        $loginpage = $this->is_login_page();
        $loginurl = $this->squared_get_login_url();
        // If not logged in, show the typical not-logged-in string.
        if (!isloggedin()) {
            $returnstr = get_string('loggedinnot', 'moodle');
            if (!$loginpage) {
                $returnstr .= " (<a href=\"$loginurl\">" . get_string('login') . '</a>)';
            }
            return html_writer::div(
                html_writer::span(
                    $returnstr,
                    'login'
                ),
                $usermenuclasses
            );

        }

        // If logged in as a guest user, show a string to that effect.
        if (isguestuser()) {
            $returnstr = get_string('loggedinasguest');
            if (!$loginpage && $withlinks) {
                $returnstr .= " (<a href=\"$loginurl\">".get_string('login').'</a>)';
            }

            return html_writer::div(
                html_writer::span(
                    $returnstr,
                    'login'
                ),
                $usermenuclasses
            );
        }

        // Get some navigation opts.
        $opts = user_get_user_navigation_info($user, $this->page);

        $avatarclasses = "avatars";
        $avatarcontents = html_writer::span($opts->metadata['useravatar'], 'avatar current');
        $usertextcontents = $opts->metadata['userfullname'];

        // Other user.
        if (!empty($opts->metadata['asotheruser'])) {
            $avatarcontents .= html_writer::span(
                $opts->metadata['realuseravatar'],
                'avatar realuser'
            );
            $usertextcontents = $opts->metadata['realuserfullname'];
            $usertextcontents .= html_writer::tag(
                'span',
                get_string(
                    'loggedinas',
                    'moodle',
                    html_writer::span(
                        $opts->metadata['userfullname'],
                        'value'
                    )
                ),
                array('class' => 'meta viewingas')
            );
        }

        // Role.
        if (!empty($opts->metadata['asotherrole'])) {
            $role = \core_text::strtolower(preg_replace('#[ ]+#', '-', trim($opts->metadata['rolename'])));
            $usertextcontents .= html_writer::span(
                $opts->metadata['rolename'],
                'meta role role-' . $role
            );
        }

        // User login failures.
        if (!empty($opts->metadata['userloginfail'])) {
            $usertextcontents .= html_writer::span(
                $opts->metadata['userloginfail'],
                'meta loginfailures'
            );
        }

        // MNet.
        if (!empty($opts->metadata['asmnetuser'])) {
            $mnet = strtolower(preg_replace('#[ ]+#', '-', trim($opts->metadata['mnetidprovidername'])));
            $usertextcontents .= html_writer::span(
                $opts->metadata['mnetidprovidername'],
                'meta mnet mnet-' . $mnet
            );
        }

        $returnstr .= html_writer::span(
            html_writer::span($usertextcontents, 'usertext') .
            html_writer::span($avatarcontents, $avatarclasses),
            'userbutton'
        );

        // Create a divider (well, a filler).
        $divider = new \action_menu_filler();
        $divider->primary = false;

        $am = new \action_menu();
        $am->set_menu_trigger(
            $returnstr
        );
        $am->set_alignment(\action_menu::TR, \action_menu::BR);
        $am->set_nowrap_on_items();
        if ($withlinks) {
            $navitemcount = count($opts->navitems);
            $idx = 0;
            foreach ($opts->navitems as $key => $value) {

                switch ($value->itemtype) {
                    case 'divider':
                        // If the nav item is a divider, add one and skip link processing.
                        $am->add($divider);
                        break;

                    case 'invalid':
                        // Silently skip invalid entries (should we post a notification?).
                        break;

                    case 'link':
                        // Process this as a link item.
                        $pix = null;
                        if (isset($value->pix) && !empty($value->pix)) {
                            $pix = new \pix_icon($value->pix, $value->title, null, array('class' => 'iconsmall'));
                        } else if (isset($value->imgsrc) && !empty($value->imgsrc)) {
                            $value->title = html_writer::img(
                                $value->imgsrc,
                                $value->title,
                                array('class' => 'iconsmall')
                            ) . $value->title;
                        }

                        $al = new \action_menu_link_secondary(
                            $value->url,
                            $pix,
                            $value->title,
                            array('class' => 'icon')
                        );
                        if (!empty($value->titleidentifier)) {
                            $al->attributes['data-title'] = $value->titleidentifier;
                        }
                        $am->add($al);
                        break;
                }

                $idx++;

                // Add dividers after the first item and before the last item.
                if ($idx == 1 || $idx == $navitemcount - 1) {
                    $am->add($divider);
                }
            }
        }

        return html_writer::div(
            $this->render($am),
            $usermenuclasses
        );
    }

    /**
     * Returns the url of the custom favicon.
     */
    public function favicon() {
        $toolbox = \theme_squared\toolbox::get_instance();
        $favicon = $toolbox->get_setting_moodle_url('favicon');

        if (empty($favicon)) {
            return $this->page->theme->image_url('favicon', 'theme');
        } else {
            return $favicon;
        }
    }

    private function fontawesome($icon) {
        $toolbox = \theme_squared\toolbox::get_instance();
        if ($toolbox->get_setting('fav')) {
            $classes[] = $toolbox->get_fa5_from_fa4($theicon);
        } else {
            $classes[] = 'fa fa-'.$theicon;
        }
        $attributes['aria-hidden'] = 'true';
        $attributes['class'] = implode(' ', $classes);
        $icon = html_writer::tag('i', '', $attributes);

        return html_writer::tag('span', $icon, array('class' => 'iconwrapper'));
    }
}
