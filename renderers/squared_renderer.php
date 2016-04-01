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

    public function image_header() {
        $template = new stdClass();
        $template->homeurl = new moodle_url('/');
        if ($this->page->theme->settings->pagelogo) {
            $template->logoimg = $this->page->theme->setting_file_url('pagelogo', 'pagelogo');
        } else {
            $template->logoimg = $this->pix_url('moodle-logo', 'theme_squared');
        }
        if (!empty($this->page->theme->settings->headerimagecourse)) {
            $template->headerimg = $this->page->theme->setting_file_url('headerimagecourse', 'headerimagecourse');
        } else {
            $template->headerimg = $this->pix_url('header-course', 'theme_squared');
        }
        return $this->render_from_template('theme_squared/imageheading', $template);
    }
}