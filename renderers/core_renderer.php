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

defined('MOODLE_INTERNAL') || die();

class theme_squared_core_renderer extends theme_bootstrap_core_renderer {

	public $squaredbodyclasses = array ();
    
    /**
     * Outputs a custom heading with a wrapper (non-PHPdoc)
     *
     * @see core_renderer::heading()
     */
    // public function heading($text, $level = 2, $classes = 'main', $id = null) {
    //     // for section headings//
    //     if ($level == 3) {
    //         $content = html_writer::start_tag ( 'div', array (
    //                 'class' => 'headingwrap1' 
    //         ) );
    //         $content .= html_writer::start_tag ( 'div', array (
    //                 'class' => 'headingwrap2' 
    //         ) );
    //     } else {
    //         $content = "";
    //     }
        
    //     $content .= parent::heading ( $text, $level, $classes, $id );
    //     if ($level == 3) {
    //         $content .= html_writer::end_tag ( 'div' );
    //         $content .= html_writer::end_tag ( 'div' );
    //     }
    //     return $content;
    // }
    
    /**
     * Output all the blocks in a particular region.
     *
     * @param string $region the name of a region on this page.
     * @return string the HTML to be output.
     */
    public function blocks_for_region($region) {
        $blockcontents = $this->page->blocks->get_content_for_region($region, $this);
        $blocks = $this->page->blocks->get_blocks_for_region($region);
        $lastblock = null;
        $zones = array();
        foreach ($blocks as $block) {
            $zones[] = $block->title;
        }
        $output = '';
        $template = new stdClass();

        //One block column
        foreach ($blockcontents as $bc) {
            if ($bc instanceof block_contents) {

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

        //Two block columns
        $template->pairs = array();
        $cols = 2;
        $count = 1;
        $pair = new stdClass();
        foreach ($template->blocks as $block) {
            if (!$block->movetarget && is_array($block->attributes) && isset($block->attributes['data-block'])) {
                $block->name = 'block_' . $block->attributes['data-block'];
            }
            $block->shape = 'squared';
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

        $theme = theme_config::load('squared');
        if ($numblocks >= $theme->settings->blockperrowlimit) {
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
        $bc = clone($bc); // Avoid messing up the object passed in.
        if (empty($bc->blockinstanceid) || !strip_tags($bc->title)) {
            $bc->collapsible = block_contents::NOT_HIDEABLE;
        }
        if (!empty($bc->blockinstanceid)) {
            $bc->attributes['data-instanceid'] = $bc->blockinstanceid;
        }
        $bc->skiptitle = strip_tags($bc->title);

        if (empty($bc->arialabel)) {
            $bc->arialabel = 'instance-'.$bc->blockinstanceid.'-header';
        }
        if ($bc->dockable) {
            $bc->attributes['data-dockable'] = 1;
        }
        if ($bc->collapsible == block_contents::HIDDEN) {
            $bc->add_class('hidden');
        }

        $bc->add_class('panel panel-default');

        $bc->content = $this->block_content($bc);
        $bc->annotation = $this->block_annotation($bc);
        
        foreach ($bc->attributes as $key => $val) {
            $attribute = new stdClass();
            $attribute->key = $key;
            $attribute->value = $val;
            $bc->atts[] = $attribute;
        }

        $this->init_block_hider_js($bc);
        return $bc;
    }
    
    
    /**
     * Add a complete course category to the custom menu
     * Added by Georg Maißer and David Bogner, based on work of Sam Hemelryk
     *
     * @param custom_menu_item $parent            
     * @param coursecat $category            
     */
    protected function add_category_to_custommenu(custom_menu_item $parent, coursecat $category) {
        
        // This value allows you to change the depth of the menu you want to show (reducing the depth may help with performance issues)
        // for courses and categories:
        $show_course_category_depth = 4;
        // for modules
        $show_modules_depth = 2;
        $categorychildren = $category->get_children ();
        $actual_depth = $category->depth;
        
        // This value allows you to decide if you want to show modules on the last depth which is still displayed
        $show_deep_modules = false;
        
        // We add the Categories and Subcategories to the menu
        if (! empty ( $categorychildren )) {
            $i = 1;
            foreach ( $categorychildren as $subcategory ) {
                $actual_depth = $subcategory->depth;
                
                // we want to check if the depth of the given category is below the limit specified above
                if ($actual_depth > $show_course_category_depth) {
                    continue;
                }
                // the value "1000" is chosen to add the items at the end. By choosing a lower or even negative value, you can add these items in front of the manually created custommenuitems
                $sub_parent = $parent->add ( $subcategory->name, new moodle_url ( '/course/index.php', array (
                        'categoryid' => $subcategory->id 
                ) ), null, 1000 + $i );
                $this->add_category_to_custommenu ( $sub_parent, $subcategory );
                $i ++;
            }
        }
        // all courses visible to user
        $catcourses = $category->get_courses ();
        // We add the courses and modules to the categories
        if (! empty ( $catcourses ) && ($actual_depth <= $show_course_category_depth)) {
            foreach ( $catcourses as $course ) {
                $course_branch = $parent->add ( $course->shortname, new moodle_url ( '/course/view.php', array (
                        'id' => $course->id 
                ) ), $course->fullname );
                
                // We add modules the the courses
                
                // first we check if we shall still show modules on this level
                
                $modules = get_course_mods ( $course->id );
                $courseobject = get_course ( $course->id );
                if (! empty ( $modules ) && ($actual_depth <= $show_modules_depth)) {
                    foreach ( $modules as $module ) {
                        // We don't include the module if it can't be accessed by the visiting user
                        if ($module->visible == 0 || ! can_access_course ( $courseobject )) {
                            continue;
                        }
                        if ($module->modname == "label") {
                            continue;
                        }
                        // the normal $module object does not include the name, so we have to make a little deviation
                        $module_object = get_coursemodule_from_id ( $module->modname, $module->id );
                        // now we have all the info we need and can just add the node to the menu
                        if ($module_object) {
                            $course_branch->add ( $module_object->name, new moodle_url ( '/mod/' . $module->modname . '/view.php', array (
                                    'id' => $module->id 
                            ) ), $module->modname );
                        }
                    }
                }
            }
        }
    }
    
    
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
        $content = '';
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
        
        return $content;
    }

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
            $userpicture = new user_picture($user);
            $userpicture->link = false;
            $userpicture->size = 34;
            $picture = html_writer::tag('span', $this->render($userpicture), array('class' => 'picspan'));
            $name = fullname($user);
            $name = html_writer::tag('span', $name, array('class' => 'username hidden-sm'));
            $usermenu = $menu->add($name . $picture, new moodle_url('#'), fullname($user), 10001);

            $usermenu->add(
                $this->glyphicon('dashboard')  . get_string('myhome'),
                new moodle_url('/my'),
                get_string('myhome')
            );

            $usermenu->add(
                '#######',
                new moodle_url('/'),
                '#######'
            );

            $usermenu->add(
                $this->glyphicon('user') . get_string('profile'),
                new moodle_url('/user/profile.php', array('id' => $user->id)),

                get_string('profile')
            );

            $usermenu->add(
                $this->glyphicon('list-alt') . get_string('grades'),
                new moodle_url('/grade/report/overview/index.php'),
                get_string('grades')
            );

            $usermenu->add(
                $this->glyphicon('inbox') . get_string('messages', 'message'),
                new moodle_url('/message/index.php'),

                get_string('messages', 'message')
            );

            $usermenu->add(
                $this->glyphicon('cog') . get_string('preferences'),
                new moodle_url('/user/preferences.php'),

                get_string('preferences')
            );

            $usermenu->add(
                '#######',
                new moodle_url('/'),
                '#######'
            );

            $usermenu->add(
                $this->glyphicon('sign-out') . get_string('logout'),
                new moodle_url('/login/logout.php', array('sesskey' => sesskey(), 'alt' => 'logout')),
                get_string('logout')
            );
        } else {
            $menu->add(
                $this->glyphicon('sign-in')  . get_string('login'),
                new moodle_url('/login/index.php', array('alt' => get_string('login'))),
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

    private function glyphicon($icon) {
        $icon = html_writer::tag('i', '', array('class' => 'glyphicon glyphicon-' . $icon));
        return html_writer::tag('span', $icon, array('class' => 'iconwrapper'));
    }
    
    /**
     * Return the navbar content so that it can be echoed out by the layout
     *
     * @return string XHTML navbar
     */
    // public function navbar() {
    //     $items = $this->page->navbar->get_items ();
    //     $htmlblocks = array ();
    //     // Iterate the navarray and display each node
    //     $itemcount = count ( $items );
    //     $separator = get_separator ();
    //     for($i = 0; $i < $itemcount; $i ++) {
    //         $item = $items [$i];
    //         $item->hideicon = true;
    //         if ($i === 0) {
    //             $content = html_writer::tag ( 'li', $this->render ( $item ), array (
    //                     'class' => 'navbar_' . $item->key . '_' . $item->type 
    //             ) );
    //         } else {
    //             $content = html_writer::tag ( 'li', $separator . $this->render ( $item ), array (
    //                     'class' => 'navbar_' . $item->key . '_' . $item->type . ' type' . $item->type 
    //             ) );
    //         }
    //         $htmlblocks [] = $content;
    //     }
        
    //     // accessibility: heading for navbar list (MDL-20446)
    //     $navbarcontent = html_writer::tag ( 'span', get_string ( 'pagepath' ), array (
    //             'class' => 'accesshide' 
    //     ) );
    //     $navbarcontent .= html_writer::tag ( 'ul', join ( '', $htmlblocks ) );
    //     // XHTML
    //     return $navbarcontent;
    // }
    
    /**
     * Return the standard string that says whether you are logged in (and switched
     * roles/logged in as another user).
     *
     * @param bool $withlinks
     *            if false, then don't include any links in the HTML produced.
     *            If not set, the default is the nologinlinks option from the theme config.php file,
     *            and if that is not set, then links are included.
     * @return string HTML fragment.
     */
    // public function login_info($withlinks = null) {
    //     global $USER, $CFG, $DB, $SESSION, $OUTPUT;
        
    //     if (during_initial_install ()) {
    //         return '';
    //     }
        
    //     if (is_null ( $withlinks )) {
    //         $withlinks = empty ( $this->page->layout_options ['nologinlinks'] );
    //     }
        
    //     $loginpage = (( string ) $this->page->url === theme_squared_get_login_url ());
    //     $course = $this->page->course;
    //     if (\core\session\manager::is_loggedinas ()) {
    //         $realuser = \core\session\manager::get_realuser ();
    //         $fullname = fullname ( $realuser, true );
    //         if ($withlinks) {
    //             $loginastitle = get_string ( 'loginas' );
    //             $realuserinfo = " [<a href=\"$CFG->wwwroot/course/loginas.php?id=$course->id&amp;sesskey=" . sesskey () . "\"";
    //             $realuserinfo .= "title =\"" . $loginastitle . "\">$fullname</a>] ";
    //         } else {
    //             $realuserinfo = " [$fullname] ";
    //         }
    //     } else {
    //         $realuserinfo = '';
    //     }
        
    //     $loginurl = theme_squared_get_login_url ();
    //     $subscribeurl = preg_replace ( '/login\/index\.php/i', 'login/signup.php', $loginurl );
        
    //     if (empty ( $course->id )) {
    //         // $course->id is not defined during installation
    //         return '';
    //     } else if (isloggedin ()) {
    //         $context = context_course::instance ( $course->id );
            
    //         $fullname = fullname ( $USER, true );
    //         // Since Moodle 2.0 this link always goes to the public profile page (not the course profile page)
    //         if ($withlinks) {
    //             $linktitle = get_string ( 'viewprofile' );
    //             $username = "<a href=\"$CFG->wwwroot/user/profile.php?id=$USER->id\" title=\"$linktitle\">$fullname</a>";
    //         } else {
    //             $username = $fullname;
    //         }
    //         if (is_mnet_remote_user ( $USER ) and $idprovider = $DB->get_record ( 'mnet_host', array (
    //                 'id' => $USER->mnethostid 
    //         ) )) {
    //             if ($withlinks) {
    //                 $username .= " from <a href=\"{$idprovider->wwwroot}\">{$idprovider->name}</a>";
    //             } else {
    //                 $username .= " from {$idprovider->name}";
    //             }
    //         }
    //         if (isguestuser ()) {
    //             $loggedinas = $realuserinfo . get_string ( 'loggedinasguest' );
    //             if (! $loginpage && $withlinks) {
    //                 $loggedinas .= " <a class=\"standardbutton plainlogin\" href=\"$loginurl\">" . get_string ( 'login' ) . '</a> 
    //                   <span class="loginlink"><a href="' . $subscribeurl . '">' . get_string ( 'createaccount' ) . '</a></span>';
    //             }
    //         } else if (is_role_switched ( $course->id )) { // Has switched roles
    //             $rolename = '';
    //             if ($role = $DB->get_record ( 'role', array (
    //                     'id' => $USER->access ['rsw'] [$context->path] 
    //             ) )) {
    //                 $rolename = ': ' . role_get_name ( $role, $context );
    //             }
    //             $loggedinas = '<span class="loggedintext">' . get_string ( 'loggedinas', 'moodle', $username ) . $rolename . '</span>';
    //             if ($withlinks) {
    //                 $url = new moodle_url ( '/course/switchrole.php', array (
    //                         'id' => $course->id,
    //                         'sesskey' => sesskey (),
    //                         'switchrole' => 0,
    //                         'returnurl' => $this->page->url->out_as_local_url ( false ) 
    //                 ) );
    //                 $loggedinas .= '(' . html_writer::tag ( 'a', get_string ( 'switchrolereturn' ), array (
    //                         'href' => $url 
    //                 ) ) . ')';
    //             }
    //         } else {
    //             $loggedinas = '<span class="loggedintext">' . $realuserinfo . get_string ( 'loggedinas', 'moodle', $username ) . '</span>';
    //             if ($withlinks) {
    //                 $loggedinas .= html_writer::tag ( 'div', $OUTPUT->user_picture ( $USER, array (
    //                         'size' => 174 
    //                 ) ), array (
    //                         'class' => 'userimg2' 
    //                 ) ) . " <span class=\"loggedinlogout\"> <a href=\"$CFG->wwwroot/login/logout.php?sesskey=" . sesskey () . "\">" . get_string ( 'logout' ) . '</a></span>';
    //             }
    //         }
    //     } else {
    //         $loggedinas = get_string ( 'loggedinnot', 'moodle' );
    //         if (! $loginpage && $withlinks) {
    //             $loggedinas .= " <a class=\"standardbutton plainlogin\" href=\"$loginurl\">" . get_string ( 'login' ) . '</a>
    //               <span class="loginlink"><a href="' . $subscribeurl . '">' . get_string ( 'createaccount' ) . '</a></span>';
    //         }
    //     }
        
    //     $loggedinas = '<div class="logininfo">' . $loggedinas . '</div>';
        
    //     if (isset ( $SESSION->justloggedin )) {
    //         unset ( $SESSION->justloggedin );
    //         if (! empty ( $CFG->displayloginfailures )) {
    //             if (! isguestuser ()) {
    //                 if ($count = count_login_failures ( $CFG->displayloginfailures, $USER->username, $USER->lastlogin )) {
    //                     $loggedinas .= '&nbsp;<div class="loginfailures">';
    //                     if (empty ( $count->accounts )) {
    //                         $loggedinas .= get_string ( 'failedloginattempts', '', $count );
    //                     } else {
    //                         $loggedinas .= get_string ( 'failedloginattemptsall', '', $count );
    //                     }
    //                     if (file_exists ( "$CFG->dirroot/report/log/index.php" ) and has_capability ( 'report/log:view', context_system::instance () )) {
    //                         $loggedinas .= ' (<a href="' . $CFG->wwwroot . '/report/log/index.php' . '?chooselog=1&amp;id=1&amp;modid=site_errors">' . get_string ( 'logs' ) . '</a>)';
    //                     }
    //                     $loggedinas .= '</div>';
    //                 }
    //             }
    //         }
    //     }
        
    //     return $loggedinas;
    // }
    
    /**
     * Check settings and return the slideshow as defined in the settings
     *
     * @return string the html for the slideshow
     */
    // public function squared_render_slideshow() {
    //     $o = "";
    //     $settings = $this->page->theme->settings;
        
    //     $slides = array ();
    //     for($i = 1; $i <= $settings->numberofslides; $i ++) {
    //         $link = "fplink$i";
    //         $text = "fptext$i";
    //         $title = "fptitle$i";
    //         $position = "fppos$i";
    //         if (! empty ( $settings->$title )) {
    //             // defaults if empty
    //             $slides [$i] = array (
    //                     'link' => '#',
    //                     'text' => '',
    //                     'title' => $settings->$title,
    //                     'position' => 2 
    //             );
    //             if (! empty ( $settings->$link )) {
    //                 $slides [$i] ['link'] = $settings->$link;
    //             }
    //             if (! empty ( $settings->$text )) {
    //                 $slides [$i] ['text'] = $settings->$text;
    //             }
    //             if (! empty ( $settings->$position )) {
    //                 $slides [$i] ['position'] = $settings->$position;
    //             }
    //         } else if ($i === 1 and empty ( $settings->$title )) {
    //             $slides [$i] = array (
    //                     'link' => "#",
    //                     'text' => "Change me in the theme settings",
    //                     'title' => "Default title",
    //                     'position' => 2 
    //             );
    //         }
    //     }
        
    //     // add body class if only one slide is present and prevent animation
    //     if (count ( $slides ) <= 1) {
    //         $this->squaredbodyclasses [] = 'oneblock';
    //     }
        
    //     foreach ( $slides as $key => $slide ) {
    //         $this->squaredbodyclasses [] = "blocklayout-$key-" . $slide ['position'];
    //         $o .= html_writer::start_div ( "scroll-header scroll$key" );
    //         $o .= html_writer::start_div ( "headerblock text headerblock1" );
    //         $o .= html_writer::link ( $slide ['link'], "<span>" . $slide ['title'] . "</span>", array (
    //                 'class' => 'inner' 
    //         ) );
    //         $o .= html_writer::end_div ();
    //         $o .= html_writer::start_div ( 'headerblock trans headerblock2' );
    //         $o .= html_writer::link ( $slide ['link'], '', array (
    //                 'class' => 'inner' 
    //         ) );
    //         $o .= html_writer::end_div ();
    //         $o .= html_writer::div ( '', 'clearer2' );
    //         $o .= html_writer::start_div ( 'headerblock para headerblock3' );
    //         $o .= html_writer::link ( $slide ['link'], "<span>" . $slide ['text'] . "</span>", array (
    //                 'class' => 'inner' 
    //         ) );
    //         $o .= html_writer::end_div ();
    //         $o .= html_writer::start_div ( 'headerblock trans headerblock4' );
    //         $o .= html_writer::link ( $slide ['link'], '', array (
    //                 'class' => 'inner' 
    //         ) );
    //         $o .= html_writer::end_div ();
    //         $o .= html_writer::end_div ();
    //     }
    //     return $o;
    // }
    
    /**
     * Output search form according to the theme settings
     *
     * @return string
     */
    // function squared_render_searchform() {
    //     global $CFG;
    //     $settings = $this->page->theme->settings;
    //     $domain = preg_replace ( "(^https?://)", "", $CFG->wwwroot );
    //     if (! empty ( $settings->searchurl )) {
    //         $url = new moodle_url ( $settings->searchurl );
    //         $hiddenfields = html_writer::input_hidden_params ( $url );
    //         $formaction = $url->out_omit_querystring ();
    //     } else {
    //         $hiddenfields = '';
    //         $formaction = 'http://www.google.com/search';
    //     }
    //     if (! empty ( $this->page->theme->settings->searchfield )) {
    //         $searchfield = $settings->searchfield;
    //     } else {
    //         $searchfield = "q";
    //     }
        
    //     $o = '';
    //     $o .= html_writer::start_tag ( 'form', array (
    //             'accept-charset' => 'UTF-8',
    //             'action' => $formaction,
    //             'id' => 'newsearchform' 
    //     ) );
    //     $o .= html_writer::start_div ( '' );
    //     $o .= html_writer::tag ( 'label', get_string ( 'search' ), array (
    //             'for' => 'newsearchfield' 
    //     ) );
    //     $o .= html_writer::start_tag ( 'input', array (
    //             'id' => 'newsearchfield',
    //             'type' => 'text',
    //             'name' => $searchfield 
    //     ) );
    //     $o .= $hiddenfields;
    //     $o .= html_writer::empty_tag ( 'input', array (
    //             'type' => 'submit',
    //             'value' => '',
    //             'id' => 'newsearchbutton' 
    //     ) );
    //     $o .= html_writer::end_div ();
    //     $o .= html_writer::end_tag ( 'form' );
    //     return $o;
    // }
    // end class
}