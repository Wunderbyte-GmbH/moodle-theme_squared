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

function squared_grid($hassidepre) {

    if ($hassidepre) {
        $regions = array('content' => 'col-sm-9 col-sm-push-3 col-md-9 col-md-push-3 col-lg-10 col-lg-push-2');
        $regions['pre'] = 'col-sm-3 col-sm-pull-9 col-md-3 col-md-pull-9 col-lg-2 col-lg-pull-10';
    } else  {
        $regions = array('content' => 'col-md-12');
        $regions['pre'] = 'empty';
    }
    return $regions;
}

function theme_squared_less_variables($theme) {
    return '';
}

function theme_squared_extra_less($theme) {
    global $CFG;
    require_once("$CFG->libdir/coursecatlib.php");
    $categorytree = coursecat::get(0)->get_children ();

    // Navbar Colours.
    $content = '';
    foreach ($categorytree as $cid => $value) {
        $setting = 'bgcolor' . $cid;
        if (isset($theme->settings->$setting)) {
            $content .= '
            @media (min-width: @screen-sm) {
                .navbar-default .navbar-nav .catcolour'.$cid.' {
                    .menu_item('.$theme->settings->$setting.');
                }
            }
            ';
            $content .= '
            @media (min-width: 768px) {
                .category-'.$cid.' {
                    #block-region-side-pre {
                        .blockheader, .block .panel-heading {
                            background-color: lighten('.$theme->settings->$setting.', 15%);
                            #gradient > .directional(lighten('.$theme->settings->$setting.', 15%);lighten('.$theme->settings->$setting.', 5%));
                        }
                        .panel-group {
                            background-color: lighten('.$theme->settings->$setting.', 15%);
                        }

                        .over-hover-to-bottom:before {
                            background: lighten('.$theme->settings->$setting.', 5%);
                        }
                    }
                    .course-content .sectionname .sqheadingicon {
                        #gradient > .directional(lighten('.$theme->settings->$setting.', 25%);lighten('.$theme->settings->$setting.', 0%));
                    }
                    &.path-mod-forum {
                        .forumpost {
                            .row .left.picture {
                                background-color: '.$theme->settings->$setting.';
                            }
                        }
                    }

                }
            }

            ';
        }
    }

    $showbgcolor = true;
    if (isset($theme->settings->nologobgcolor) && $theme->settings->nologobgcolor == 1) {
        $showbgcolor = false;
    }
    if ($showbgcolor && isset($theme->settings->logobgcolor)) {
        $content .= '
            .headerimages .logoimg {
                background-color: ' . $theme->settings->logobgcolor . ';
            }';
    }
    return $content;
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
    global $CFG;
    
    if (! empty ( $theme->settings->bgcolordefault )) {
        $bgcolordefault = $theme->settings->bgcolordefault;
    } else {
        $bgcolordefault = null;
    }
    $css = theme_squared_set_bgcolordefault ( $css, $bgcolordefault );
    $css = theme_squared_set_slideimage ( $css, $theme );
    
    if (! empty ( $theme->settings->customcss )) {
        $customcss = $theme->settings->customcss;
    } else {
        $customcss = null;
    }
    
    $css = theme_squared_set_customcss ( $css, $customcss );
    // Return the CSS
    return $css;
}

/**
 * Sets the default background color for the blocks.
 * Used of no color is set
 * and on all admin pages and pages that do not belong to a course category
 *
 * @param string $css            
 * @param string $bgcolordefault            
 * @return string parsed CSS
 */
function theme_squared_set_bgcolordefault($css, $bgcolordefault) {
    $tag = '[[setting:bgcolordefault]]';
    $replacement = $bgcolordefault;
    if (is_null ( $replacement )) {
        $replacement = '#11847D';
    }
    $css = str_replace ( $tag, $replacement, $css );
    return $css;
}

/**
 * sets the slideimage in the frontpage slideshow
 *
 * @param string $css            
 * @param string $slideimage
 *            file_url
 * @param string $setting
 *            theme setting string to replace
 * @return string parsed CSS
 */
function theme_squared_set_slideimage($css, $theme) {
    global $OUTPUT, $CFG;
    for($i = 1; $i < 6; $i ++) {
        $setting = 'slideimage' . $i;
        $tag = "[[setting:$setting]]";
        if (! empty ( $theme->settings->$setting )) {
            $slideimage = $theme->setting_file_url ( $setting, $setting );
        } else {
            $slideimage = $OUTPUT->pix_url ( $setting, 'theme_squared' );
        }
        $css = str_replace ( $tag, $slideimage, $css );
    }
    return $css;
}

/**
 * sets the header image for all pages except the frontpage
 *
 * @param string $css            
 * @param string $headerimagecourse            
 * @return sting
 */
function theme_squared_set_headerimagecourse($css, $headerimagecourse) {
    global $OUTPUT;
    $tag = '[[setting:headerimagecourse]]';
    $replacement = $headerimagecourse;
    if (is_null ( $replacement )) {
        $replacement = $OUTPUT->pix_url ( 'header-course', 'theme_squared' );
    }
    $css = str_replace ( $tag, $replacement, $css );
    return $css;
}

/**
 * Returns MNET Login URL instead of standard login URL.
 * Checks the wanted url
 * of user in order to provide correct redirect url for the identity provider
 *
 * @return string login url
 */
function theme_squared_get_login_url() {
    global $PAGE, $DB, $SESSION, $CFG;
    if ($PAGE->url->out () === $CFG->wwwroot . "/login/index.php") {
        $urltogo = $SESSION->wantsurl;
    } else {
        $urltogo = $PAGE->url->out ();
    }
    $authplugin = get_auth_plugin ( 'mnet' );
    $authurl = $authplugin->loginpage_idp_list ( $urltogo );
    // check the id of the MNET host for the idp
    $host = $DB->get_field ( 'mnet_host', 'name', array (
            'id' => $PAGE->theme->settings->alternateloginurl 
    ) );
    if (! empty ( $authurl )) {
        foreach ( $authurl as $key => $urlarray ) {
            if ($urlarray ['name'] == $host) {
                $loginurl = $authurl [$key] ['url'];
                return $loginurl;
            } else {
                $loginurl = "$CFG->wwwroot/login/index.php";
                if (! empty ( $CFG->loginhttps )) {
                    $loginurl = str_replace ( 'http:', 'https:', $loginurl );
                }
            }
        }
    } else {
        $loginurl = "$CFG->wwwroot/login/index.php";
        if (! empty ( $CFG->loginhttps )) {
            $loginurl = str_replace ( 'http:', 'https:', $loginurl );
        }
    }
    return $loginurl;
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
    static $theme;
    if (empty ( $theme )) {
        $theme = theme_config::load ( 'squared' );
    }
    
    if ($context->contextlevel == CONTEXT_SYSTEM and $filearea) {
        return $theme->setting_file_serve ( $filearea, $args, $forcedownload, $options );
    } else {
        send_file_not_found ();
    }
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
    if (is_null ( $replacement )) {
        $replacement = '';
    }
    $css = str_replace ( $tag, $replacement, $css );
    return $css;
}

/**
 * Loads the JavaScript for the zoom function.
 *
 * @param moodle_page $page Pass in $PAGE.
 */
function theme_squared_initialise_zoom(moodle_page $page) {
    user_preference_allow_ajax_update('theme_squared_zoom', PARAM_TEXT);
    $page->requires->yui_module('moodle-theme_squared-zoom', 'M.theme_squared.zoom.init', array());
}
/**
 * Get the user preference for the zoom function.
 */
function theme_squared_get_zoom() {
    return get_user_preferences('theme_squared_zoom', 'nozoom');
}