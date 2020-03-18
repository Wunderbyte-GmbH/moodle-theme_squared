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
 *
 * The squared theme makes uses a custom version of squared blocks
 *
 * @package theme_squared
 * @copyright 2016 onwards Onlinecampus Virtuelle PH
 * www.virtuelle-ph.at, David Bogner www.edulabs.org
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

 defined('MOODLE_INTERNAL') || die;

function theme_squared_grid($hassidepre) {

    if ($hassidepre) {
        $regions = array('content' => 'col-md-9 col-lg-10 col-xl-10 order-last');
        $regions['pre'] = 'col-md-3 col-lg-2 col-xl-2 order-first';
    } else {
        $regions = array('content' => 'col-md-12');
        $regions['pre'] = 'empty';
    }
    return $regions;
}

/**
 * Inject additional SCSS.
 *
 * @param theme_config $theme The theme config object.
 * @return string
 */
function theme_squared_get_extra_scss($theme) {
    $toolbox = \theme_squared\toolbox::get_instance();

    return $toolbox->get_extra_scss($theme);
}

/**
 * Inject SCSS content.
 *
 * @param theme_config $theme The theme config object.
 * @return string
 */
function theme_squared_get_main_scss_content($theme) {
    $toolbox = \theme_squared\toolbox::get_instance();

    return $toolbox->get_main_scss_content($theme);
}

/**
 * Get SCSS to prepend.
 *
 * @param theme_config $theme The theme config object.
 * @return array
 */
function theme_squared_get_pre_scss($theme) {
    $toolbox = \theme_squared\toolbox::get_instance();

    return $toolbox->get_pre_scss($theme);
}

/**
 * Parses CSS before it is cached.
 *
 * This function can make alterations and replace patterns within the CSS.
 *
 * @param string $css The CSS
 * @param theme_config $theme The theme config object.
 * @return string The parsed CSS The parsed CSS.
 */
function theme_squared_process_css($css, $theme) {
    if (!empty($theme->settings->customcss)) {
        $customcss = $theme->settings->customcss;
        $css = theme_squared_set_customcss($css, $customcss);
    }

    // Return the CSS.
    return $css;
}

/**
 * Serves any files associated with the theme settings.
 *
 * @param stdClass $course
 * @param stdClass $cm
 * @param context $context
 * @param string $filearea
 * @param array $args
 * @param bool $forcedownload
 * @param array $options
 * @return boolean
 */
function theme_squared_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, array $options = array()) {
    $toolbox = \theme_squared\toolbox::get_instance();
    $theme = $toolbox->get_theme_config();

    // By default, theme files must be cache-able by both browsers and proxies.  From 'More' theme.
    if (!array_key_exists('cacheability', $options)) {
        $options['cacheability'] = 'public';
    }
    if ($context->contextlevel == CONTEXT_SYSTEM) {
        if ($filearea === 'favicon') {
            return $theme->setting_file_serve('favicon', $args, $forcedownload, $options);
        } else if ($filearea === 'hvp') {
            theme_squared_serve_hvp_css($args[1], $theme);
        } else if ($filearea) {
            return $theme->setting_file_serve ($filearea, $args, $forcedownload, $options);
        } else {
            send_file_not_found();
        }
    } else {
        send_file_not_found();
    }
}

/**
 * Serves the H5P Custom CSS.
 *
 * @param string $filename The filename.
 * @param theme_config $theme The theme config object.
 */
function theme_squared_serve_hvp_css($filename, $theme) {
    global $CFG, $PAGE;
    require_once($CFG->dirroot.'/lib/configonlylib.php'); // For min_enable_zlib_compression().

    $toolbox = \theme_squared\toolbox::get_instance();
    $PAGE->set_context(context_system::instance());

    $content = '';
    $hvpfontcss = $toolbox->get_setting('hvpfontcss');
    if (!empty($hvpfontcss)) {
        // Code adapted from post_process() in the theme_config object.
        if (preg_match_all('/\[\[font:([a-z0-9_]+\|)?([^\]]+)\]\]/', $hvpfontcss, $matches, PREG_SET_ORDER)) {
            $replaced = array();
            foreach ($matches as $match) {
                if (isset($replaced[$match[0]])) {
                    continue;
                }
                $replaced[$match[0]] = true;
                $fontname = $match[2];
                $component = rtrim($match[1], '|');
                $fonturl = $theme->font_url($fontname, $component)->out(false);
                // We do not need full url because the font.php is always in the same dir.
                $fonturl = preg_replace('|^http.?://[^/]+|', '', $fonturl);
                $hvpfontcss = str_replace($match[0], $fonturl, $hvpfontcss);
            }

            $content .= $hvpfontcss.PHP_EOL.PHP_EOL;
        }
    }

    $content .= $toolbox->get_setting('hvpcustomcss');
    $md5content = md5($content);
    $md5stored = get_config('theme_squared', 'hvpccssmd5');
    if ((empty($md5stored)) || ($md5stored != $md5content)) {
        // Content changed, so the last modified time needs to change.
        set_config('hvpccssmd5', $md5content, 'theme_squared');
        $lastmodified = time();
        set_config('hvpccsslm', $lastmodified, 'theme_squared');
    } else {
        $lastmodified = get_config('theme_squared', 'hvpccsslm');
        if (empty($lastmodified)) {
            $lastmodified = time();
        }
    }

    // Sixty days only - the revision may get incremented quite often.
    $lifetime = 60 * 60 * 24 * 60;

    header('HTTP/1.1 200 OK');

    header('Etag: "'.$md5content.'"');
    header('Content-Disposition: inline; filename="'.$filename.'"');
    header('Last-Modified: '.gmdate('D, d M Y H:i:s', $lastmodified).' GMT');
    header('Expires: '.gmdate('D, d M Y H:i:s', time() + $lifetime).' GMT');
    header('Pragma: ');
    header('Cache-Control: public, max-age='.$lifetime);
    header('Accept-Ranges: none');
    header('Content-Type: text/css; charset=utf-8');
    if (!min_enable_zlib_compression()) {
        header('Content-Length: '.strlen($content));
    }

    echo $content;

    die;
}

/**
 * Sets the custom css variable in CSS
 *
 * @param string $css
 * @param mixed $customcss
 * @return string
 */
function theme_squared_set_customcss($css, $customcss) {
    $tag = '[[setting:customcss]]';
    $replacement = $customcss;
    if (is_null ($replacement)) {
        $replacement = '';
    }
    $css = str_replace ($tag, $replacement, $css);
    return $css;
}

/**
 * Loads the JavaScript for the zoom function.
 *
 * @param moodle_page $page Pass in $PAGE.
 */
function theme_squared_initialise_zoom(moodle_page $page) {
    user_preference_allow_ajax_update('theme_squared_zoom', PARAM_TEXT);
}
/**
 * Get the user preference for the zoom function.
 */
function theme_squared_get_zoom() {
    return get_user_preferences('theme_squared_zoom', 'nozoom');
}