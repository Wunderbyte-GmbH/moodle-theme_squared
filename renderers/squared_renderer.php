<?php
// This file is part of the squared theme for Moodle
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
 * Theme squared renderer file.
 *
 * @package    theme_squared
 * @copyright  2016 onwards Onlinecampus Virtuelle PH
 * @author     Bas Brands
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class theme_squared_html_renderer extends plugin_renderer_base {

    private $theme;

    /**
     * Render the top image menu.
     */
    public function image_header() {
        if (empty($this->theme)) {
            $this->theme = theme_config::load('squared');
        }
        $settings = $this->theme->settings;
        $template = new stdClass();

        $template->homeurl = new moodle_url('/');

        $template->logocontainerclass = 'col-sm-3 col-md-3 col-lg-2 logo';
        $template->headerbgcontainerclass = 'col-sm-9 col-md-9 col-lg-10 grid background';

        if (isset($settings->headerlayout) && ($settings->headerlayout == 1)) {
            $template->logocontainerclass = 'col-sm-3 col-md-3 col-lg-2 logo fixed';
            $template->headerbgcontainerclass = 'col-sm-12 background';
        }

        $images = array('logo', 'logosmall', 'headerbg', 'headerbgsmall');

        foreach ($images as $image) {
            if (!empty($settings->$image)) {
                $template->$image = $this->theme->setting_file_url($image, $image);
            } else {
                $template->$image = $this->pix_url($image, 'theme_squared');
            }
        }
        
        return $this->render_from_template('theme_squared/imageheading', $template);
    }

    /**
     * Full top Navbar. Returns Mustache rendererd menu.
     */
    public function navigation_menu() {
        global $OUTPUT, $SITE;

        $this->category_menu();

        $template = new stdClass();
        $template->siteurl = new moodle_url('/');
        $template->sitename = $SITE->shortname;
        $template->usermenu = $OUTPUT->user_menu();
        $template->custommenu = $this->category_menu();
        $template->pageheadingmenu = $OUTPUT->page_heading_menu();
        if (isset($this->page->layout_options['langmenu'])) {
            $template->languagemenu = $this->languagemenu();
        }
        $template->search = $this->searchbox();
        $template->togglebtn = $OUTPUT->pix_url('more-button', 'theme_squared');
        return $this->render_from_template('theme_squared/navigation', $template);
    }

    /**
     * Menu with category Dropdowns.
     */
    private function category_menu() {
        $template = new stdClass();
        $template->categories = $this->categories(0);
        return $this->render_from_template('theme_squared/custommenu', $template);
    }

    /**
     * Render the category cards.
     */
    private function categories($catid = 0) {
        global $DB, $OUTPUT, $PAGE;
        
        $categories = $DB->get_records('course_categories', array('visible' => 1, 'parent' => 0));

        if ($catid > 0) {
            $categories[$catid] = $DB->get_record('course_categories', array('id' => $catid));
        }

        $returncategories = array();

        ksort($categories);

        $checkcat = $categories;

        $count = 1;
        foreach ($categories as $category) {
            if (($category->parent != $catid) && ($category->id != $catid)) {
                continue;
            }
            // We show 3 categories only.
            if ($count > 3) {
                continue;
            }
            $category->courses = $this->catcourses($category->id);
            // Not showing empty categories.
            if (count($category->courses) == 0) {
                continue;
            }
            $category->colour = 'catcolour' . $count++;
            $returncategories[] = $category;
        }
        return $returncategories;
    }

    /**
     * Get the category courses
     */
    private function catcourses($catid) {
        global $DB, $OUTPUT;

        $allcourses = $DB->get_records('course', array('visible' => 1, 'category' => $catid));

        $mycourses = array();

        if (isloggedin() && !isguestuser()) {
            $mycourses = enrol_get_my_courses();
        }

        $courses = array();
        $timenow = time();
        
        foreach ($allcourses as $acourse) {
            if ($acourse->id == 1) {
                continue;
            }

            $coursecontext = context_course::instance($acourse->id);

            $acourse->mycourse = false;

            if (array_key_exists($acourse->id, $mycourses)) {
                $acourse->mycourse = true;
            }

            $acourse->courselink = new moodle_url('/course/view.php', array('id' => $acourse->id));

            $courses[] = $acourse;
        }
        return $courses;
    }

    /**
     * Render the social icons shown in the page footer.
     */
    public function squared_socialicons() {
        global $OUTPUT;
        $content = '';

        if (empty($this->theme)) {
            $this->theme = theme_config::load('squared');
        }

        $template = new stdClass();
        $template->icons = array();

        $socialicons = array('googlepluslink', 'twitterlink', 'facebooklink', 'youtubelink');

        foreach ($socialicons as $si) {
            if (isset($this->theme->settings->$si)) {
                $icon = new stdClass();
                $icon->url = $this->theme->settings->$si;
                $icon->name = str_replace('link', '', $si);
                $icon->image = $OUTPUT->pix_url($icon->name, 'theme');
                $template->icons[] = $icon;
            }
        }
        return $this->render_from_template('theme_squared/socialicons', $template);
    }

    /**
     * Render the language menu.
     */
    public function languagemenu() {
        global $OUTPUT;
        if (empty($this->theme)) {
            $this->theme = theme_config::load('squared');
        }
        $haslangmenu = $OUTPUT->lang_menu() != '';
        $langmenu = new stdClass();
        
        if ($haslangmenu) {
            $langs = get_string_manager()->get_list_of_translations();
            $strlang = get_string('language');
            $currentlang = current_language();
            if (isset($langs[$currentlang])) {
                $langmenu->currentlang = $langs[$currentlang];
            } else {
                $langmenu->currentlang = $strlang;
            }
            $langmenu->languages = array();
            foreach ($langs as $type => $name) {
                $thislang = new stdClass();
                $thislang->langname = $name;
                $thislang->langurl = new moodle_url($this->page->url, array('lang' => $type));
                $langmenu->languages[] = $thislang;
            }
            return $this->render_from_template('theme_squared/language', $langmenu);
        }
    }

    /**
     * Render the text shown in the page footer.
     */
    public function footer() {
        if (empty($this->theme)) {
            $this->theme = theme_config::load('squared');
        }

        $template = new stdClass();
        $template->coursefooter = $this->course_footer();

        $template->socialicons = $this->squared_socialicons();

        $template->standardfooterhtml = $this->standard_footer_html();

        $template->list = array();

        if (isset($this->theme->settings->footertext)) {
            $footertext = $this->theme->settings->footertext;
            $menu = new custom_menu($footertext, current_language());
            foreach ($menu->get_children() as $item) {
                $listitem = new stdClass();
                $listitem->text = $item->get_text();
                $listitem->url = $item->get_url();
                $template->list[] = $listitem;
            }
        }
        return $this->render_from_template('theme_squared/footer', $template);
    }

    /**
     * Render the searchbox shown in the top navbar.
     */
    public function searchbox($value = '') {
        if (empty($this->theme)) {
            $this->theme = theme_config::load('squared');
        }
        $configsearchurl = $this->theme->settings->searchurl;
        $searchurl = empty($configsearchurl) ? '/course/search.php' : $configsearchurl;
        $templateinfo = new stdClass();
        $templateinfo->formaction = new moodle_url($searchurl);
        return $this->render_from_template('theme_squared/navbarsearch', $templateinfo);
    }
}