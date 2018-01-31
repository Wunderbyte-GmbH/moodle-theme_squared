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
        global $CFG;
        if (empty($this->theme)) {
            $this->theme = theme_config::load('squared');
        }
        $settings = $this->theme->settings;
        $template = new stdClass();

        $template->homeurl = new moodle_url('/');

        if (isset($settings->logoposition) && $settings->logoposition == 'right') {
            $template->logocontainerclass = 'col-sm-3 col-md-3 col-sm-push-9 col-md-push-9 logo right';
            $template->headerbgcontainerclass = 'col-sm-9 col-md-9 col-sm-pull-3 col-md-pull-3  right background';

            if (isset($settings->headerlayout) && ($settings->headerlayout == 1)) {
                $template->logocontainerclass = 'col-sm-3 col-md-3 col-sm-push-9 col-md-push-9 logo right logo fixed';
                $template->headerbgcontainerclass = 'col-sm-12 background';
            }
        } else {
            $template->logocontainerclass = 'col-sm-3 col-md-3 col-lg-2 logo left';
            $template->headerbgcontainerclass = 'col-sm-9 col-md-9 col-lg-10 grid background';

            if (isset($settings->headerlayout) && ($settings->headerlayout == 1)) {
                $template->logocontainerclass = 'col-sm-3 col-md-3 col-lg-2 logo left fixed';
                $template->headerbgcontainerclass = 'col-sm-12 background';
            }
        }

        $images = array('logo', 'logosmall', 'headerbg', 'headerbgsmall');

        foreach ($images as $image) {
            if (!empty($settings->$image)) {
                $template->$image = $this->theme->setting_file_url($image, $image);
            } else {
                if ($CFG->branch >= 33) {
                    $template->$image = $this->image_url($image, 'theme_squared');
                } else {
                    $template->$image = $this->pix_url($image, 'theme_squared');
                }
            }
        }

        return $this->render_from_template('theme_squared/imageheading', $template);
    }

    /**
     * Full top Navbar. Returns Mustache rendererd menu.
     */
    public function navigation_menu() {
        global $OUTPUT, $SITE, $CFG;
        $template = new stdClass();
        $template->siteurl = new moodle_url('/');
        $template->sitename = $SITE->shortname;
        $template->usermenu = $OUTPUT->user_menu();
        $template->custommenu = $OUTPUT->custom_menu();
        $template->pageheadingmenu = $OUTPUT->page_heading_menu();
        if (isset($this->page->layout_options['langmenu'])) {
            $template->languagemenu = $this->languagemenu();
        }
        $template->search = $this->searchbox();
        if ($CFG->branch >= 33) {
            $template->togglebtn = $OUTPUT->image_url('more-button', 'theme_squared');
        } else {
            $template->togglebtn = $OUTPUT->pix_url('more-button', 'theme_squared');
        }
        return $this->render_from_template('theme_squared/navigation', $template);
    }

    /**
     * Render the social icons shown in the page footer.
     */
    public function squared_socialicons() {
        global $OUTPUT, $CFG;
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
                if ($CFG->branch >= 33) {
                    $icon->image = $OUTPUT->image_url($icon->name, 'theme');
                } else {
                    $icon->image = $OUTPUT->pix_url($icon->name, 'theme');
                }
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
        global $CFG;
        if (empty($this->theme)) {
            $this->theme = theme_config::load('squared');
        }

        $domain = preg_replace ( "(^https?://)", "", $CFG->wwwroot );
        if (! empty ( $this->theme->settings->searchurl )) {
            $url = new moodle_url ( $this->theme->settings->searchurl );
            $hiddenfields = html_writer::input_hidden_params ( $url );
            $formaction = $url->out_omit_querystring ();
        } else {
            $hiddenfields = '';
            $formaction = 'http://www.google.com/search';
        }
        if (! empty ( $this->theme->settings->searchfield )) {
            $searchfield = $this->theme->settings->searchfield;
        } else {
            $searchfield = "q";
        }

        $configsearchurl = $this->theme->settings->searchurl;
        $searchurl = empty($configsearchurl) ? '/course/search.php' : $formaction;
        $templateinfo = new stdClass();
        $templateinfo->formaction = $formaction;
        $templateinfo->hiddenfields = $hiddenfields;
        $templateinfo->searchfield = $searchfield;
        return $this->render_from_template('theme_squared/navbarsearch', $templateinfo);
    }

    /**
     * Find the toplevel category for use in the bodyclasses
     */
    public function toplevel_category() {
        foreach ($this->page->categories as $cat) {
            if ($cat->depth == 1) {
                return 'category-' . $cat->id;
            }
        }

    }
}