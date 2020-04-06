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

defined ( 'MOODLE_INTERNAL' ) || die ();

$settings = null;

if (is_siteadmin()) {
    require_once(__DIR__."/simple_theme_settings.class.php");
    require_once("$CFG->dirroot/theme/boost/classes/admin_settingspage_tabs.php");

    // Page general settings,
    $settings = new theme_boost_admin_settingspage_tabs('themesettingsquared', get_string('pluginname', 'theme_squared'));
    $sp = new admin_settingpage('theme_squared_basic', get_string('basicsettings', 'theme_squared'));
    $simset = new squared_simple_theme_settings($sp, 'theme_squared');

    // Custom favicon.
    $simset->add_file('favicon');

    // Navbar search.
    $options = array(
        1 => get_string('moodlecoursesearch', 'theme_squared'),
        2 => get_string('advancedcoursearch', 'theme_squared')
    );
    $default = 1;
    if ((!empty($CFG->enableglobalsearch)) && has_capability('moodle/search:query', context_system::instance())) {
        $options[3] = get_string('globalsearch', 'theme_squared');
        $default = 3;
    }
    $simset->add_select('navbarsearch', $default, $options);

    $simset->add_select('alternateloginurl', 0, $simset->mnet_choices());
    $simset->add_checkbox('hidelocallogin', 0, 1, 0);

    $simset->add_select('blockperrowlimit', 4, range(0, 30));
    $choices = array(
        0 => get_string('none'),
        1 => '1,3,3',
        2 => '1,2,4',
        3 => '1,2,3'
    );
    $simset->add_select('activitylayout', 1, $choices);

    $simset->add_colourpicker('bgcolordefault', '#a4daf0');  // This is $sq-bgc-default in squared_preset.scss.

    $simset->add_checkbox('fav', 0, 1, 0);

    $simset->add_textarea('prescss');
    $simset->add_textarea('customscss');
    $simset->add_textarea('customcss');
    $settings->add($sp);

    // Page header settings.
    $sp = new admin_settingpage('theme_squared_header', get_string('pageheadersettings', 'theme_squared'));
    $simset = new squared_simple_theme_settings($sp, 'theme_squared');

    $simset->add_select('navbarposition', 'static',
        array(
            'static' => get_string('navbarpositionstatic', 'theme_squared'),
            'fixed' => get_string('navbarpositionfixed', 'theme_squared')
        )
    );
    $simset->add_select('headerlayout', 0,
        array(get_string('hlogoside', 'theme_squared'), get_string('hlogotop', 'theme_squared')));
    $simset->add_file('logo');
    $simset->add_file('logosmall');
    $simset->add_file('headerbg');
    $simset->add_file('headerbgsmall');
    $simset->add_select('logoposition', 'left',
        array('left' => get_string('left', 'editor'), 'right' => get_string('right', 'editor')));
    $simset->add_colourpicker('logobgcolor', '#263683');
    $simset->add_checkbox('nologobgcolor', 0, 1, 0);
    $simset->add_checkbox('courseheaderimage', 1, 1, 0);
    $simset->add_select('courseheaderimagefallback', 'courseheaderimagefallbackthemeimage',
        array(
            'courseheaderimagefallbackthemeimage' => get_string('courseheaderimagefallbackthemeimage', 'theme_squared'),
            'courseheaderimagefallbackgenerated' => get_string('courseheaderimagefallbackgenerated', 'theme_squared')
        ));
    $simset->add_file('courseheaderimagefallbackimage');
    $settings->add($sp);

    // H5P settings.
    $sp = new admin_settingpage('theme_squared_hvp', get_string('hvp', 'theme_squared'));
    $simset = new squared_simple_theme_settings($sp, 'theme_squared');

    $simset->add_textarea('hvpcustomcss');
    $toolbox = \theme_squared\toolbox::get_instance();
    $simset->add_textarea('hvpfontcss', $toolbox->gethvpdefaultfonts());
    $settings->add($sp);

    // Footer settings.
    $sp = new admin_settingpage('theme_squared_footer', get_string('footersettings', 'theme_squared'));
    $simset = new squared_simple_theme_settings($sp, 'theme_squared');

    $simset->add_text('youtubelink');
    $simset->add_text('instagramlink');
    $simset->add_text('facebooklink');
    $simset->add_text('twitterlink');
    $simset->add_textarea('footertext');
    $simset->add_htmleditor('footnote');
    $settings->add($sp);

    // Category color guide settings.
    $sp = new admin_settingpage('theme_squared_catcolor', get_string ('catcolorsettings', 'theme_squared'));
    $simset = new squared_simple_theme_settings($sp, 'theme_squared');
    $categorytree = core_course_category::get(0)->get_children ();
    $cclr = array ('#EF001C', '#4B88FB', '#A89E00', '#013855');
    $i = 0;
    foreach ($categorytree as $cid => $value) {
        $simset->add_headings('bgcolorheading', $cid, $value->name);
        $simset->add_colourpickers('bgcolor', $cid, (!empty($cclr[$i]) ? $cclr[$i++] : "#666") );
    }
    $settings->add($sp);
}