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
use theme_config;

require_once($CFG->dirroot . '/course/format/lib.php');

class core_renderer extends \theme_boost\output\core_renderer {

    /**
     * Wrapper for header elements.
     *
     * @return string HTML to display the main header.
     */
    public function full_header() {
        $html = html_writer::start_tag('header', array('id' => 'page-header', 'class' => 'row'));
        $html .= html_writer::start_div('col-xs-12 p-a-1');
        $html .= html_writer::start_div('pull-xs-left');
        $html .= $this->context_header();
        $html .= html_writer::end_div();
        $html .= html_writer::tag('div', $this->course_header(), array('id' => 'course-header'));
        $html .= html_writer::end_div();
        $html .= html_writer::end_tag('header');

        return $html;
    }

    /**
     * Outputs a custom heading with a wrapper
     *
     * @see core_renderer::heading()
     */
    public function heading($text, $level = 2, $classes = 'main', $id = null) {
        // for section headings//
        $icon = '';
        if ($level == 3) {
            $icon = html_writer::tag('div', '', array('class' => 'sqheadingicon'));
            $text = html_writer::tag('span', $text, array('class' => 'sqheadingtext'));
        }

        $content = parent::heading ( $icon . $text, $level, $classes, $id );

        return $content;
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
     * Returns course-specific information to be output immediately above content on any course page
     * (for the current course)
     *
     * @param bool $onlyifnotcalledbefore output content only if it has not been output before
     * @return string
     */
    public function course_content_header($onlyifnotcalledbefore = false) {
        $content = parent::course_content_header($onlyifnotcalledbefore);

        if ($this->page->pagelayout == 'coursecategory') {
            if (\theme_squared\toolbox::course_content_search()) {
                $content .= '<div class="courseitemsearch">';
                $content .= '<div><p>'.get_string('findcoursecontent', 'theme_squared').'</p></div>';
                $content .= '<div id="courseitemsearchresults">';
                $content .= '<input type="text" name="courseitemsearch" id="courseitemsearch" disabled="disabled">';
                $content .= '</div></div>';
            }
        }

        return $content;
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

        return $menu;
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
                                    $loggedinas .= html_writer::tag('div',
                                            html_writer::link(new moodle_url('/login/logout.php?sesskey=' . sesskey()),
                                                    '<em><span class="fa fa-sign-out"></span>' . get_string('logout') . '</em>'));
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
        $output = '';
        $template = new stdClass();

        // Add flat navigation.
        global $PAGE;
        $flatnavname = get_string('flatnavigation', 'theme_squared');
        $templatecontext = array('flatnavigation' => $PAGE->flatnav);
        $thisblock = new stdClass();
        $thisblock->name = 'block_flat_navigation';
        $thisblock->title = '<span class="title">'.$flatnavname.'</span>';
        $thisblock->header = '<div class="title"><h2>'.$flatnavname.'</h2></div>';
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
                $thisblock->header = $this->block_header($bc);
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

        // Two block columns
        $template->pairs = array();
        $cols = 2;
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
                $pair->class = 'col-xs-6';
                $pair->blocka = $block;
                $count++;
            }
        }
        $numblocks = count($template->blocks);
        if ($pair) {
            if (($numblocks %2) != 0) {
                $pair->blocka->shape = 'rectangle';
                $pair->class = 'col-xs-12 lastblock';
            }
            $template->pairs[] = $pair;
        }

        if (isset($this->page->theme->settings->blockperrowlimit) && $numblocks >= $this->page->theme->settings->blockperrowlimit) {
            return $this->render_from_template('theme_squared/blocksrows', $template);
        } else {
            return $this->render_from_template('theme_squared/blocks', $template);
        }
    }

    /**
     * Prints a nice side block with an optional header.
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
        if ($bc->collapsible == block_contents::HIDDEN) {
            // collapsing does not work with default moodle hide/show blocks
            //$bc->add_class('hidden');
        }
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
            // this is ridiculous. the book module should have a proper coding of the toc block ;-)
            if($val == "_fake") {
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
     * Add a complete course category to the custom menu
     * Added by Georg Maißer and David Bogner, based on work of Sam Hemelryk
     *
     * @param custom_menu_item $parent
     * @param coursecat $category
     */
    /*protected function add_category_to_custommenu(custom_menu_item $parent, coursecat $category) {

        /* This value allows you to change the depth of the menu you want to show (reducing the depth may help with performance issues)
           for categories: */
        /*$show_category_depth = 4;
        $categorychildren = $category->get_children();
        $actual_depth = $category->depth;

        // This value allows you to decide if you want to show modules on the last depth which is still displayed.
        $show_deep_modules = false;

        // We add the Categories and Subcategories to the menu
        if (!empty($categorychildren)) {
            $i = 1;
            foreach ($categorychildren as $subcategory) {
                $actual_depth = $subcategory->depth;

                // We want to check if the depth of the given category is below the limit specified above.
                if ($actual_depth > $show_category_depth) {
                    continue;
                }
                // The value "1000" is chosen to add the items at the end. By choosing a lower or even negative value, you can add these items in front of the manually created custommenuitems.
                $sub_parent = $parent->add($subcategory->name, new moodle_url('/course/index.php', array (
                    'categoryid' => $subcategory->id
                )), null, 1000 + $i);
                $this->add_category_to_custommenu ( $sub_parent, $subcategory );
                $i++;
            }
        }
    }*/

    protected function squared_prepare_textlinks($textlinks) {
        $textsnippets = explode ( ';', $textlinks );
        foreach ( $textsnippets as $value ) {
            $textandlinks [] = explode ( ',', $value, 2 );
        }
        $renderedtext = '';
        $lastelement = end ( $textandlinks );
        if (empty ( $lastelement [0] )) {
            $lastelement = prev ( $textandlinks );
        }
        $attributes = array ();
        foreach ( $textandlinks as $value ) {
            if (empty ( $value [0] )) {
                continue;
            }
            $renderedtext .= html_writer::start_tag ( 'span', $attributes );
            $renderedtext .= html_writer::tag ( 'a', trim ( $value [0] ), array (
                    'href' => trim ( $value [1] )
            ) );
            $renderedtext .= html_writer::end_tag ( 'span' );
        }
        $renderedtext .= html_writer::tag ( 'span', page_doc_link(get_string('moodledocslink')), array (
                'class' => 'helplink'
        ) );
        $renderedtext .= html_writer::tag ( 'span', 'Theme by <a href="http://www.edulabs.org" target="_blank">edulabs.org - e-learning solutions</a>', array (
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
     * Output the social icons when present in theme settings
     *
     * @return string
     */
    public function squared_socialicons() {
        global $CFG;
        $content = '';
        if ($CFG->branch >= 33) {
            if (! empty ( $this->page->theme->settings->googlepluslink )) {
                $content .= html_writer::tag ( 'a', '<img src="' . $this->image_url ( 'gplus', 'theme' ) . '" class="sicons" alt="google plus" />', array (
                                'href' => $this->page->theme->settings->googlepluslink,
                                'class' => 'icons'
                ) );
            }
            if (! empty ( $this->page->theme->settings->twitterlink )) {

                $content .= html_writer::tag ( 'a', '<img src="' . $this->image_url ( 'twitter', 'theme' ) . '" class="sicons" alt="twitter" />', array (
                                'href' => $this->page->theme->settings->twitterlink,
                                'class' => 'icons'
                ) );
            }
            if (! empty ( $this->page->theme->settings->facebooklink )) {

                $content .= html_writer::tag ( 'a', '<img src="' . $this->image_url ( 'faceb', 'theme' ) . '" class="sicons" alt="facebook" />', array (
                                'href' => $this->page->theme->settings->facebooklink,
                                'class' => 'icons'
                ) );
            }
            if (! empty ( $this->page->theme->settings->youtubelink )) {

                $content .= html_writer::tag ( 'a', '<img src="' . $this->image_url ( 'youtube', 'theme' ) . '" class="sicons" alt="youtube" />', array (
                                'href' => $this->page->theme->settings->youtubelink,
                                'class' => 'icons'
                ) );
            }
        } else {
            if (! empty ( $this->page->theme->settings->googlepluslink )) {
                $content .= html_writer::tag ( 'a', '<img src="' . $this->pix_url ( 'gplus', 'theme' ) . '" class="sicons" alt="google plus" />', array (
                                'href' => $this->page->theme->settings->googlepluslink,
                                'class' => 'icons'
                ) );
            }
            if (! empty ( $this->page->theme->settings->twitterlink )) {

                $content .= html_writer::tag ( 'a', '<img src="' . $this->pix_url ( 'twitter', 'theme' ) . '" class="sicons" alt="twitter" />', array (
                                'href' => $this->page->theme->settings->twitterlink,
                                'class' => 'icons'
                ) );
            }
            if (! empty ( $this->page->theme->settings->facebooklink )) {

                $content .= html_writer::tag ( 'a', '<img src="' . $this->pix_url ( 'faceb', 'theme' ) . '" class="sicons" alt="facebook" />', array (
                                'href' => $this->page->theme->settings->facebooklink,
                                'class' => 'icons'
                ) );
            }
            if (! empty ( $this->page->theme->settings->youtubelink )) {

                $content .= html_writer::tag ( 'a', '<img src="' . $this->pix_url ( 'youtube', 'theme' ) . '" class="sicons" alt="youtube" />', array (
                                'href' => $this->page->theme->settings->youtubelink,
                                'class' => 'icons'
                ) );
            }
        }
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

    /*
    public function user_menu($user = null, $withlinks = null) {
        $usermenu = new custom_menu('', current_language());
        return $this->render_user_menu($usermenu, $user);
    }

    protected function render_user_menu(custom_menu $menu, $user) {
        global $USER;

        if (empty($user)) {
            $user = $USER;
        }

        $menuclass = 'navbar-nav guest';

        if (isloggedin() && !isguestuser()) {
            $menuclass = 'loggedin';
            $userpicture = new \user_picture($user);
            $userpicture->link = false;
            $userpicture->size = 34;
            $picture = html_writer::tag('span', $this->render($userpicture), array('class' => 'picspan'));
            $name = fullname($user);
            $name = html_writer::tag('span', $name, array('class' => 'username hidden-sm'));
            $usermenu = $menu->add($name . $picture, new moodle_url('#'), fullname($user), 10001);

            $usermenu->add(
                $this->fontawesome('tachometer').get_string('myhome'),
                new moodle_url('/my'),
                get_string('myhome')
            );

            $usermenu->add(
                '#######',
                new moodle_url('/'),
                '#######'
            );

            $usermenu->add(
                $this->fontawesome('user').get_string('profile'),
                new moodle_url('/user/profile.php', array('id' => $user->id)),

                get_string('profile')
            );

            $usermenu->add(
                $this->fontawesome('list-alt').get_string('grades'),
                new moodle_url('/grade/report/overview/index.php'),
                get_string('grades')
            );

            $usermenu->add(
                $this->fontawesome('inbox').get_string('messages', 'message'),
                new moodle_url('/message/index.php'),

                get_string('messages', 'message')
            );

            $usermenu->add(
                $this->fontawesome('cog').get_string('preferences'),
                new moodle_url('/user/preferences.php'),

                get_string('preferences')
            );

            $usermenu->add(
                '#######',
                new moodle_url('/'),
                '#######'
            );

            $usermenu->add(
                $this->fontawesome('sign-out').get_string('logout'),
                new moodle_url('/login/logout.php', array('sesskey' => sesskey(), 'alt' => 'logout')),
                get_string('logout')
            );
        } else {
            $menu->add(
                $this->fontawesome('sign-in').get_string('login'),
                new moodle_url($this->squared_get_login_url()),
                get_string('login')
            );
        }

        $content = html_writer::start_tag('ul', array('class' => 'nav pull-left usermenu ' . $menuclass, 'role' => 'menubar'));
        foreach ($menu->get_children() as $item) {
            $content .= $this->render_custom_menu_item($item, 1, 'pull-right');
        }
        $content .= html_writer::end_tag('ul');

        return $content;
    }
    */

    private function fontawesome($icon) {
        $icon = html_writer::tag('i', '', array('class' => 'fa fa-' . $icon));
        return html_writer::tag('span', $icon, array('class' => 'iconwrapper'));
    }

    /*public function custom_menu($custommenuitems = '') {
        global $CFG;
        if (empty($custommenuitems) && !empty($CFG->custommenuitems)) {
          $custommenuitems = $CFG->custommenuitems;
        }
        if (empty($custommenuitems)) {
          return '';
        }
        $custommenu = new custom_menu($custommenuitems, current_language());
        return $this->render($custommenu);
    }

    protected function render_custom_menu(custom_menu $menu) {
        global $CFG;
        require_once ($CFG->libdir.'/coursecatlib.php');

        // Get the custommenuitems.
        $custommenu = $menu->get_children();

        // Get all the categories and courses from the navigation node.
        $categorytree = coursecat::get(0)->get_children ();

        // Here we build the menu.
        foreach ($categorytree as $cid => $categorytreeitem ) {
            foreach ( $custommenu as $custommenuitem ) {
                if (($categorytreeitem->name == $custommenuitem->get_title())) {
                    $branch = $custommenuitem;
                    $this->add_category_to_custommenu($branch, $categorytreeitem);
                    $custommenuitem->set_title('catcolour'.$cid);
                    break;
                }
            }
        }

        $content = '<ul class="nav navbar-nav catnav">';
        foreach ($menu->get_children() as $item) {
            $content .= $this->render_custom_menu_item($item, 1, $item->get_title());
        }
        $content .= '</ul>';
        return $content;
    }

    protected function render_custom_menu_item(custom_menu_item $menunode, $level = 0, $direction = '') {
        static $submenucount = 0;

        if ($menunode->has_children()) {
            if ($level == 1) {
                $dropdowntype = 'dropdown';
            } else {
                $dropdowntype = 'dropdown-submenu';
            }

            $content = html_writer::start_tag('li', array('class' => $dropdowntype));
            // If the child has menus render it as a sub menu.
            $submenucount++;
            if ($menunode->get_url() !== null) {
                $url = $menunode->get_url();
                if (strstr($url->out(true), 'categoryid=')) {
                    $url->param('categorysort', 'default');
                }
                $url = $url->out(true);
            } else {
                $url = '#cm_submenu_'.$submenucount;
            }
            $linkattributes = array(
                'href' => '#',
                'class' => 'dropdown-toggle',
                'data-toggle' => 'dropdown',
                'title' => $menunode->get_title(),
            );
            $content .= html_writer::start_tag('a', $linkattributes);
            $content .= $menunode->get_text();
            if ($level == 1) {
                $content .= '<b class="caret"></b>';
            }
            $content .= '</a>';
            $content .= '<ul class="dropdown-menu '.$direction.'">';
            // Because the menu dropdown click expands it, it is unclickable, so add as a link at the top.
            $content .= html_writer::start_tag('li', array('class' => 'dropdown-item'));
            $linkattributes['href'] = $url;
            unset($linkattributes['class']);
            unset($linkattributes['data-toggle']);
            $content .= html_writer::start_tag('a', $linkattributes);
            $content .= $menunode->get_text();
            $content .= '</a>';
            $content .= html_writer::end_tag('li');
            $content .= html_writer::tag('li', '', array('class' => 'dropdown-item dropdown-divider'));
            foreach ($menunode->get_children() as $menunode) {
                $content .= $this->render_custom_menu_item($menunode, 0);
            }
            $content .= '</ul>';
        } else {
            $content = '<li>';
            // The node doesn't have children so produce a final menuitem.
            $class = '';
            if (preg_match("/^#+$/", $menunode->get_text())) {
                $content = '<li class="dropdown-item dropdown-divider" role="presentation">';
            } else {
                $content = '<li class="dropdown-item">';
                // The node doesn't have children so produce a final menuitem.
                if ($menunode->get_url() !== null) {
                    $url = $menunode->get_url();
                    if (strstr($url->out(true), 'categoryid=')) {
                         $url->param('categorysort', 'default');
                    }
                } else {
                    $url = '#';
                }
                $content .= html_writer::link($url, $menunode->get_text(), array('class' => $class,
                    'title' => $menunode->get_title()));
            }
        }
        return $content;
    }*/
}
