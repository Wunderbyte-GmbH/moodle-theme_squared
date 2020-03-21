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
 * @copyright 2018 onwards Onlinecampus Virtuelle PH
 * www.virtuelle-ph.at, David Bogner www.edulabs.org
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace theme_squared;

defined('MOODLE_INTERNAL') || die;

class toolbox {

    protected static $instance;
    protected $squaredconfig;
    protected $boostparent;

    private function __construct() {
    }

    public static function get_instance() {
        if (!is_object(self::$instance)) {
            self::$instance = new self();
            self::$instance->boostparent = \theme_config::load('boost');
            self::$instance->squaredconfig = \theme_config::load('squared');
        }
        return self::$instance;
    }

    public function get_extra_scss($theme) {
        global $CFG;

        $scss = theme_boost_get_extra_scss($this->boostparent);

        $categorytree = \core_course_category::get(0)->get_children();

        // Navbar Colours.
        foreach ($categorytree as $cid => $value) {
            $setting = 'bgcolor'.$cid;
            if (isset($theme->settings->$setting)) {
                $scss .= '
                    @media (min-width: @screen-sm) {
                        .navbar-default .navbar-nav .catcolour'.$cid.' {
                             @include menu_item('.$theme->settings->$setting.');
                        }
                    }
                ';
                $scss .= '
                    .category-'.$cid.' {
                        #block-region-side-pre {
                            @media (min-width: $screen-breakpoint) {
                                .single-row,
                                .two-rows {
                                    background-color: '.$theme->settings->$setting.';
                                }
                            }
                            .blockheader,
                            .block .card-heading {
                                background-color: '.$theme->settings->$setting.';
                                @include gradient-directional(lighten('.$theme->settings->$setting.', 15%), darken('.$theme->settings->$setting.', 5%));
                            }
                            .block .card-heading .icon-container {
                                background-color: darken('.$theme->settings->$setting.', 20%);
                            }
                            .card-group {
                                background-color: '.$theme->settings->$setting.';
                            }
                        }
                        .course-content .sectionname .sqheadingicon,
                        #page-header .context-header-settings-menu {
                            background-color: '.$theme->settings->$setting.';
                        }
                        &.path-mod-forum {
                            .forumpost {
                                .row .left.picture {
                                    background-color: '.$theme->settings->$setting.';
                                }
                            }
                        }
                        .bg-messageheader {
                            background-color: '.$theme->settings->$setting.';
                        }

                    }
                    @media (max-width: $screen-breakpoint) {
                        .category-'.$cid.' {
                            #block-region-side-pre .card-group {
                                background-color: transparent;
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
            $scss .= '
                .headerimages .logoimg {
                    background-color: '.$theme->settings->logobgcolor.';
                }';
        }

        $customscss = $this->get_setting('customscss');
        if (!empty($customscss)) {
            $scss .= $customscss;
        }

        return $scss;
    }

    public function get_main_scss_content($theme) {
        global $CFG;
        require_once($CFG->dirroot.'/theme/boost/lib.php');

        $scss = $this->import_scss('squared_preset');

        $scss .= theme_boost_get_main_scss_content($this->boostparent);

        $scss .= $this->import_scss('squared');

        return $scss;
    }

    public function get_pre_scss($theme) {
        $scss = theme_boost_get_pre_scss($this->boostparent);

        if (!empty($theme->settings->bgcolordefault)) {
            $scss .= '$sq-bgc-default: '.$theme->settings->bgcolordefault.';'.PHP_EOL;
        }

        $prescss = $this->get_setting('prescss');
        if (!empty($prescss)) {
            $scss .= $prescss;
        }

        return $scss;
    }

    /**
     * Finds the given scss file in the theme.
     * @param string $filename Filename without extension to get.
     * @return string SCSS import statement for the file if it exists otherwise an empty string.
     */
    protected function import_scss($filename) {
        $content = '';
        $thefile = $this->get_scss_file($filename);
        if (!empty($thefile)) {
            $content .= file_get_contents($thefile);
        }
        return $content;
    }

    protected function get_scss_file($filename) {
        global $CFG;
        $filename .= '.scss';

        if (file_exists("$CFG->dirroot/theme/squared/scss/$filename")) {
            return "$CFG->dirroot/theme/squared/scss/$filename";
        } else if (!empty($CFG->themedir) and file_exists("$CFG->themedir/squared/scss/$filename")) {
            return "$CFG->themedir/squared/scss/$filename";
        } else {
            return dirname(__FILE__) . "/$filename";
        }
    }

    /**
     * Gets the theme config.
     *
     * @return theme_config object.
     */
    public function get_theme_config() {
        return self::$instance->squaredconfig;
    }

    /**
     * Gets the specified setting.
     *
     * @param string $settingname The name of the setting.
     * @return boolean|mixed false if not found or setting value.
     */
    public function get_setting($settingname) {
        $settingvalue = false;

        $theconfig = self::$instance->squaredconfig;
        if ($theconfig != null) {
            $settingvalue = $theconfig->settings->$settingname;
        }

        return $settingvalue;
    }

    /**
     * Finds the given setting in the theme using the get_config core function for when the
     * theme_config object has not been created.
     * @param string $setting Setting name.
     * @return any false|value of setting.
     */
    static public function get_config_setting($setting) {
        return \get_config('theme_squared', $setting);
    }

    /**
     * Gets the setting moodle_url for the given setting if it exists and set.
     *
     * See: https://moodle.org/mod/forum/discuss.php?d=371252#p1516474 and change if theme_config::setting_file_url
     * changes.
     */
    public function get_setting_moodle_url($setting) {
        $settingurl = null;

        $thesetting = self::get_config_setting($setting);
        if (!empty($thesetting)) {
            global $CFG;
            $itemid = \theme_get_revision();
            $syscontext = \context_system::instance();

            $settingurl = \moodle_url::make_file_url("$CFG->wwwroot/pluginfile.php", "/$syscontext->id/theme_squared/$setting/$itemid".$thesetting);
            $settingurl = preg_replace('|^https?://|i', '//', $settingurl->out(false));
        }
        return $settingurl;
    }

    /**
     * States if course content search can be used.  Will now work if theme is in $CFG->themedir.
     * @return boolean false|true if course content search can be used.
     */
    static public function course_content_search() {
        global $PAGE;

        $squaredsearch = new \moodle_url('/course/index.php');
        $squaredsearch->param('sesskey', sesskey());
        $inspectorscourerdata = array('data' => array('theme' => $squaredsearch->out(false)));
        $PAGE->requires->js_call_amd('theme_squared/inspector_scourer', 'init', $inspectorscourerdata);

        return true;
    }

    /**
     * Helper method for the default layout file.
     */
    public function default_ajax() {
        global $CFG, $PAGE;

        $courseautocompletesearchterm = optional_param('term', '', PARAM_TEXT);
        $categorycoursesearch = optional_param('ccs', 0, PARAM_INT);
        $frontpageavailablecourses = optional_param('sqfac', 0, PARAM_INT);
        $frontpagemycourses = optional_param('sqfmc', 0, PARAM_INT);
        if (($courseautocompletesearchterm) ||
            ($categorycoursesearch) ||
            ($frontpageavailablecourses) ||
            ($frontpagemycourses)) {
            // AJAX calls to have a sesskey and use the course renderer.

            // Might be overkill but would probably stop DOS attack from lots of DB reads.
            \require_sesskey();

            if ($CFG->forcelogin) {
                \require_login();
            }
            $courserenderer = $PAGE->get_renderer('core', 'course');

            if ($courseautocompletesearchterm) {
                echo json_encode($courserenderer->inspector_ajax($courseautocompletesearchterm));
            } else if ($frontpageavailablecourses) {
                echo $courserenderer->frontpage_available_courses();
            } else if ($frontpagemycourses) {
                echo $courserenderer->frontpage_my_courses();
            } else {
                // Must be $categorycoursesearch.
                $catid = optional_param('categoryid', -1, PARAM_INT);  // Zero is for all courses.  Also look at /course/index.php
                if ($catid != -1) {
                    echo $courserenderer->category_courses_from_search($catid);
                } else {
                    header('HTTP/1.0 400 Bad Request');
                    die('Category id not sent.');
                }
            }
            die();
        }
    }

    /**
     * Gets the default H5P fonts as supplied by the theme.
     *
     * @return string The font CSS.
     */
    public function gethvpdefaultfonts() {
        return "
            @font-face {
                font-family: 'Source Sans Pro';
                font-style: normal;
                font-weight: 200;
                src: url('[[font:theme|SourceSansPro-ExtraLight.otf]]');
            }

            @font-face {
                font-family: 'Source Sans Pro';
                font-style: italic;
                font-weight: 200;
                src: url('[[font:theme|SourceSansPro-ExtraLightIt.otf]]');
            }

            @font-face {
                font-family: 'Source Sans Pro';
                font-style: normal;
                font-weight: 300;
                src: url('[[font:theme|SourceSansPro-Light.otf]]');
            }

            @font-face {
                font-family: 'Source Sans Pro';
                font-style: italic;
                font-weight: 300;
                src: url('[[font:theme|SourceSansPro-LightIt.otf]]');
            }

            @font-face {
                font-family: 'Source Sans Pro';
                font-style: normal;
                font-weight: 400;
                src: url('[[font:theme|SourceSansPro-Regular.otf]]');
            }

            @font-face {
                font-family: 'Source Sans Pro';
                font-style: italic;
                font-weight: 400;
                src: url('[[font:theme|SourceSansPro-It.otf]]');
            }

            @font-face {
                font-family: 'Source Sans Pro';
                font-style: normal;
                font-weight: 600;
                src: url('[[font:theme|SourceSansPro-Semibold.otf]]');
            }

            @font-face {
                font-family: 'Source Sans Pro';
                font-style: italic;
                font-weight: 600;
                src: url('[[font:theme|SourceSansPro-SemiboldIt.otf]]');
            }

            @font-face {
                font-family: 'Source Sans Pro';
                font-style: normal;
                font-weight: 700;
                src: url('[[font:theme|SourceSansPro-Bold.otf]]');
            }

            @font-face {
                font-family: 'Source Sans Pro';
                font-style: italic;
                font-weight: 700;
                src: url('[[font:theme|SourceSansPro-BoldIt.otf]]');
            }

            @font-face {
                font-family: 'Source Sans Pro';
                font-style: normal;
                font-weight: 900;
                src: url('[[font:theme|SourceSansPro-Black.otf]]');
            }

            @font-face {
                font-family: 'Source Sans Pro';
                font-style: italic;
                font-weight: 900;
                src: url('[[font:theme|SourceSansPro-BlackIt.otf]]');
            }
        ";
    }

    public function get_fa5_from_fa4($icon, $hasprefix = false) {
        $icontofind = ($hasprefix) ? $icon : 'fa-'.$icon;

        // Ref: fa-v4-shims.js.
        static $icons = array(
            'fa-glass' => 'fas fa-glass-martini',
            'fa-meetup' => 'fab fa-meetup',
            'fa-star-o' => 'far fa-star',
            'fa-remove' => 'fas fa-times',
            'fa-close' => 'fas fa-times',
            'fa-gear' => 'fas fa-cog',
            'fa-trash-o' => 'far fa-trash-alt',
            'fa-file-o' => 'far fa-file',
            'fa-clock-o' => 'far fa-clock',
            'fa-arrow-circle-o-down' => 'far fa-arrow-alt-circle-down',
            'fa-arrow-circle-o-up' => 'far fa-arrow-alt-circle-up',
            'fa-play-circle-o' => 'far fa-play-circle',
            'fa-repeat' => 'fas fa-redo',
            'fa-rotate-right' => 'fas fa-redo',
            'fa-refresh' => 'fas fa-sync',
            'fa-list-alt' => 'far fa-list-alt',
            'fa-dedent' => 'fas fa-outdent',
            'fa-video-camera' => 'fas fa-video',
            'fa-picture' => 'fas fa-image',
            'fa-picture-o' => 'far fa-image',
            'fa-photo' => 'far fa-image',
            'fa-image' => 'far fa-image',
            'fa-pencil' => 'fas fa-pencil-alt',
            'fa-map-marker' => 'fas fa-map-marker-alt',
            'fa-pencil-square-o' => 'far fa-edit',
            'fa-share-square-o' => 'far fa-share-square',
            'fa-check-square-o' => 'far fa-check-square',
            'fa-arrows' => 'fas fa-arrows-alt',
            'fa-times-circle-o' => 'far fa-times-circle',
            'fa-check-circle-o' => 'far fa-check-circle',
            'fa-mail-forward' => 'fas fa-share',
            'fa-eye-slash' => 'far fa-eye-slash',
            'fa-warning' => 'fas fa-exclamation-triangle',
            'fa-calendar' => 'fas fa-calendar-alt',
            'fa-arrows-v' => 'fas fa-arrows-alt-v',
            'fa-arrows-h' => 'fas fa-arrows-alt-h',
            'fa-bar-chart' => 'far fa-chart-bar',
            'fa-bar-chart-o' => 'far fa-chart-bar',
            'fa-twitter-square' => 'fab fa-twitter-square',
            'fa-facebook-square' => 'fab fa-facebook-square',
            'fa-gears' => 'fas fa-cogs',
            'fa-thumbs-o-up' => 'far fa-thumbs-up',
            'fa-thumbs-o-down' => 'far fa-thumbs-down',
            'fa-heart-o' => 'far fa-heart',
            'fa-sign-out' => 'fas fa-sign-out-alt',
            'fa-linkedin-square' => 'fab fa-linkedin',
            'fa-thumb-tack' => 'fas fa-thumbtack',
            'fa-external-link' => 'fas fa-external-link-alt',
            'fa-sign-in' => 'fas fa-sign-in-alt',
            'fa-github-square' => 'fab fa-github-square',
            'fa-lemon-o' => 'far fa-lemon',
            'fa-square-o' => 'far fa-square',
            'fa-bookmark-o' => 'far fa-bookmark',
            'fa-twitter' => 'fab fa-twitter',
            'fa-facebook' => 'fab fa-facebook-f',
            'fa-facebook-f' => 'fab fa-facebook-f',
            'fa-github' => 'fab fa-github',
            'fa-credit-card' => 'far fa-credit-card',
            'fa-feed' => 'fas fa-rss',
            'fa-hdd-o' => 'far fa-hdd',
            'fa-hand-o-right' => 'far fa-hand-point-right',
            'fa-hand-o-left' => 'far fa-hand-point-left',
            'fa-hand-o-up' => 'far fa-hand-point-up',
            'fa-hand-o-down' => 'far fa-hand-point-down',
            'fa-arrows-alt' => 'fas fa-expand-arrows-alt',
            'fa-group' => 'fas fa-users',
            'fa-chain' => 'fas fa-link',
            'fa-scissors' => 'fas fa-cut',
            'fa-files-o' => 'far fa-copy',
            'fa-floppy-o' => 'far fa-save',
            'fa-navicon' => 'fas fa-bars',
            'fa-reorder' => 'fas fa-bars',
            'fa-pinterest' => 'fab fa-pinterest',
            'fa-pinterest-square' => 'fab fa-pinterest-square',
            'fa-google-plus-square' => 'fab fa-google-plus-square',
            'fa-google-plus' => 'fab fa-google-plus-g',
            'fa-money' => 'far fa-money-bill-alt',
            'fa-unsorted' => 'fas fa-sort',
            'fa-sort-desc' => 'fas fa-sort-down',
            'fa-sort-asc' => 'fas fa-sort-up',
            'fa-linkedin' => 'fab fa-linkedin-in',
            'fa-rotate-left' => 'fas fa-undo',
            'fa-legal' => 'fas fa-gavel',
            'fa-tachometer' => 'fas fa-tachometer-alt',
            'fa-dashboard' => 'fas fa-tachometer-alt',
            'fa-comment-o' => 'far fa-comment',
            'fa-comments-o' => 'far fa-comments',
            'fa-flash' => 'fas fa-bolt',
            'fa-clipboard' => 'far fa-clipboard',
            'fa-paste' => 'far fa-clipboard',
            'fa-lightbulb-o' => 'far fa-lightbulb',
            'fa-exchange' => 'fas fa-exchange-alt',
            'fa-cloud-download' => 'fas fa-cloud-download-alt',
            'fa-cloud-upload' => 'fas fa-cloud-upload-alt',
            'fa-bell-o' => 'far fa-bell',
            'fa-cutlery' => 'fas fa-utensils',
            'fa-file-text-o' => 'far fa-file-alt',
            'fa-building-o' => 'far fa-building',
            'fa-hospital-o' => 'far fa-hospital',
            'fa-tablet' => 'fas fa-tablet-alt',
            'fa-mobile' => 'fas fa-mobile-alt',
            'fa-mobile-phone' => 'fas fa-mobile-alt',
            'fa-circle-o' => 'far fa-circle',
            'fa-mail-reply' => 'fas fa-reply',
            'fa-github-alt' => 'fab fa-github-alt',
            'fa-folder-o' => 'far fa-folder',
            'fa-folder-open-o' => 'far fa-folder-open',
            'fa-smile-o' => 'far fa-smile',
            'fa-frown-o' => 'far fa-frown',
            'fa-meh-o' => 'far fa-meh',
            'fa-keyboard-o' => 'far fa-keyboard',
            'fa-flag-o' => 'far fa-flag',
            'fa-mail-reply-all' => 'fas fa-reply-all',
            'fa-star-half-o' => 'far fa-star-half',
            'fa-star-half-empty' => 'far fa-star-half',
            'fa-star-half-full' => 'far fa-star-half',
            'fa-code-fork' => 'fas fa-code-branch',
            'fa-chain-broken' => 'fas fa-unlink',
            'fa-shield' => 'fas fa-shield-alt',
            'fa-calendar-o' => 'far fa-calendar',
            'fa-maxcdn' => 'fab fa-maxcdn',
            'fa-html5' => 'fab fa-html5',
            'fa-css3' => 'fab fa-css3',
            'fa-ticket' => 'fas fa-ticket-alt',
            'fa-minus-square-o' => 'far fa-minus-square',
            'fa-level-up' => 'fas fa-level-up-alt',
            'fa-level-down' => 'fas fa-level-down-alt',
            'fa-pencil-square' => 'fas fa-pen-square',
            'fa-external-link-square' => 'fas fa-external-link-square-alt',
            'fa-compass' => 'far fa-compass',
            'fa-caret-square-o-down' => 'far fa-caret-square-down',
            'fa-toggle-down' => 'far fa-caret-square-down',
            'fa-caret-square-o-up' => 'far fa-caret-square-up',
            'fa-toggle-up' => 'far fa-caret-square-up',
            'fa-caret-square-o-right' => 'far fa-caret-square-right',
            'fa-toggle-right' => 'far fa-caret-square-right',
            'fa-eur' => 'fas fa-euro-sign',
            'fa-euro' => 'fas fa-euro-sign',
            'fa-gbp' => 'fas fa-pound-sign',
            'fa-usd' => 'fas fa-dollar-sign',
            'fa-dollar' => 'fas fa-dollar-sign',
            'fa-inr' => 'fas fa-rupee-sign',
            'fa-rupee' => 'fas fa-rupee-sign',
            'fa-jpy' => 'fas fa-yen-sign',
            'fa-cny' => 'fas fa-yen-sign',
            'fa-rmb' => 'fas fa-yen-sign',
            'fa-yen' => 'fas fa-yen-sign',
            'fa-rub' => 'fas fa-ruble-sign',
            'fa-ruble' => 'fas fa-ruble-sign',
            'fa-rouble' => 'fas fa-ruble-sign',
            'fa-krw' => 'fas fa-won-sign',
            'fa-won' => 'fas fa-won-sign',
            'fa-btc' => 'fab fa-btc',
            'fa-bitcoin' => 'fab fa-btc',
            'fa-file-text' => 'fas fa-file-alt',
            'fa-sort-alpha-asc' => 'fas fa-sort-alpha-down',
            'fa-sort-alpha-desc' => 'fas fa-sort-alpha-up',
            'fa-sort-amount-asc' => 'fas fa-sort-amount-down',
            'fa-sort-amount-desc' => 'fas fa-sort-amount-up',
            'fa-sort-numeric-asc' => 'fas fa-sort-numeric-down',
            'fa-sort-numeric-desc' => 'fas fa-sort-numeric-up',
            'fa-youtube-square' => 'fab fa-youtube-square',
            'fa-youtube' => 'fab fa-youtube',
            'fa-xing' => 'fab fa-xing',
            'fa-xing-square' => 'fab fa-xing-square',
            'fa-youtube-play' => 'fab fa-youtube',
            'fa-dropbox' => 'fab fa-dropbox',
            'fa-stack-overflow' => 'fab fa-stack-overflow',
            'fa-instagram' => 'fab fa-instagram',
            'fa-flickr' => 'fab fa-flickr',
            'fa-adn' => 'fab fa-adn',
            'fa-bitbucket' => 'fab fa-bitbucket',
            'fa-bitbucket-square' => 'fab fa-bitbucket',
            'fa-tumblr' => 'fab fa-tumblr',
            'fa-tumblr-square' => 'fab fa-tumblr-square',
            'fa-long-arrow-down' => 'fas fa-long-arrow-alt-down',
            'fa-long-arrow-up' => 'fas fa-long-arrow-alt-up',
            'fa-long-arrow-left' => 'fas fa-long-arrow-alt-left',
            'fa-long-arrow-right' => 'fas fa-long-arrow-alt-right',
            'fa-apple' => 'fab fa-apple',
            'fa-windows' => 'fab fa-windows',
            'fa-android' => 'fab fa-android',
            'fa-linux' => 'fab fa-linux',
            'fa-dribbble' => 'fab fa-dribble',
            'fa-skype' => 'fab fa-skype',
            'fa-foursquare' => 'fab fa-foursquare',
            'fa-trello' => 'fab fa-trello',
            'fa-gratipay' => 'fab fa-gratipay',
            'fa-gittip' => 'fab fa-gratipay',
            'fa-sun-o' => 'far fa-sun',
            'fa-moon-o' => 'far fa-moon',
            'fa-vk' => 'fab fa-vk',
            'fa-weibo' => 'fab fa-weibo',
            'fa-renren' => 'fab fa-renren',
            'fa-pagelines' => 'fab fa-pagelines',
            'fa-stack-exchange' => 'fab fa-stack-exchange',
            'fa-arrow-circle-o-right' => 'far fa-arrow-alt-circle-right',
            'fa-arrow-circle-o-left' => 'far fa-arrow-alt-circle-left',
            'fa-caret-square-o-left' => 'far fa-caret-square-left',
            'fa-toggle-left' => 'far fa-caret-square-left',
            'fa-dot-circle-o' => 'far fa-dot-circle',
            'fa-vimeo-square' => 'fab fa-vimeo-square',
            'fa-try' => 'fas fa-lira-sign',
            'fa-turkish-lira' => 'fas fa-lira-sign',
            'fa-plus-square-o' => 'far fa-plus-square',
            'fa-slack' => 'fab fa-slack',
            'fa-wordpress' => 'fab fa-wordpress',
            'fa-openid' => 'fab fa-openid',
            'fa-institution' => 'fas fa-university',
            'fa-bank' => 'fas fa-university',
            'fa-mortar-board' => 'fas fa-graduation-cap',
            'fa-yahoo' => 'fab fa-yahoo',
            'fa-google' => 'fab fa-google',
            'fa-reddit' => 'fab fa-reddit',
            'fa-reddit-square' => 'fab fa-reddit-square',
            'fa-stumbleupon-circle' => 'fab fa-stumbleupon-circle',
            'fa-stumbleupon' => 'fab fa-stumbleupon',
            'fa-delicious' => 'fab fa-delicious',
            'fa-digg' => 'fab fa-digg',
            'fa-pied-piper-pp' => 'fab fa-pied-piper-pp',
            'fa-pied-piper-alt' => 'fab fa-pied-piper-alt',
            'fa-drupal' => 'fab fa-drupal',
            'fa-joomla' => 'fab fa-joomla',
            'fa-spoon' => 'fas fa-utensil-spoon',
            'fa-behance' => 'fab fa-behance',
            'fa-behance-square' => 'fab fa-behance-square',
            'fa-steam' => 'fab fa-steam',
            'fa-steam-square' => 'fab fa-steam-square',
            'fa-automobile' => 'fas fa-car',
            'fa-cab' => 'fas fa-taxi',
            'fa-spotify' => 'fab fa-spotify',
            'fa-envelope-o' => 'far fa-envelope',
            'fa-soundcloud' => 'fab fa-soundcloud',
            'fa-file-pdf-o' => 'far fa-file-pdf',
            'fa-file-word-o' => 'far fa-file-word',
            'fa-file-excel-o' => 'far fa-file-excel',
            'fa-file-powerpoint-o' => 'far fa-file-powerpoint',
            'fa-file-image-o' => 'far fa-file-image',
            'fa-file-photo-o' => 'far fa-file-image',
            'fa-file-picture-o' => 'far fa-file-image',
            'fa-file-archive-o' => 'far fa-file-archive',
            'fa-file-zip-o' => 'far fa-file-archive',
            'fa-file-audio-o' => 'far fa-file-audio',
            'fa-file-sound-o' => 'far fa-file-audio',
            'fa-file-video-o' => 'far fa-file-video',
            'fa-file-movie-o' => 'far fa-file-video',
            'fa-file-code-o' => 'far fa-file-code',
            'fa-vine' => 'fab fa-vine',
            'fa-codepen' => 'fab fa-codepen',
            'fa-jsfiddle' => 'fab fa-jsfiddle',
            'fa-life-ring' => 'far fa-life-ring',
            'fa-life-bouy' => 'far fa-life-ring',
            'fa-life-buoy' => 'far fa-life-ring',
            'fa-life-saver' => 'far fa-life-ring',
            'fa-support' => 'far fa-life-ring',
            'fa-circle-o-notch' => 'fas fa-circle-notch',
            'fa-rebel' => 'fab fa-rebel',
            'fa-ra' => 'fab fa-rebel',
            'fa-resistance' => 'fab fa-rebel',
            'fa-empire' => 'fab fa-empire',
            'fa-ge' => 'fab fa-empire',
            'fa-git-square' => 'fab fa-git-square',
            'fa-git' => 'fab fa-git',
            'fa-hacker-news' => 'fab fa-hacker-news',
            'fa-y-combinator-square' => 'fab fa-hacker-news',
            'fa-yc-square' => 'fab fa-hacker-news',
            'fa-tencent-weibo' => 'fab fa-tencent-weibo',
            'fa-qq' => 'fab fa-gg',
            'fa-weixin' => 'fab fa-weixin',
            'fa-wechat' => 'fab fa-weixin',
            'fa-send' => 'fas fa-paper-plane',
            'fa-paper-plane-o' => 'far fa-paper-plane',
            'fa-send-o' => 'far fa-paper-plane',
            'fa-circle-thin' => 'far fa-circle',
            'fa-header' => 'fas fa-heading',
            'fa-sliders' => 'fas fa-sliders-h',
            'fa-futbol-o' => 'far fa-futbol',
            'fa-soccer-ball-o' => 'far fa-futbol',
            'fa-slideshare' => 'fab fa-slideshare',
            'fa-twitch' => 'fab fa-twitch',
            'fa-yelp' => 'fab fa-yelp',
            'fa-newspaper-o' => 'far fa-newspaper',
            'fa-paypal' => 'fab fa-paypal',
            'fa-google-wallet' => 'fab fa-google-wallet',
            'fa-cc-visa' => 'fab fa-cc-visa',
            'fa-cc-mastercard' => 'fab fa-cc-mastercard',
            'fa-cc-discover' => 'fab fa-cc-discover',
            'fa-cc-amex' => 'fab fa-cc-amex',
            'fa-cc-paypal' => 'fab fa-cc-paypal',
            'fa-cc-stripe' => 'fab fa-cc-stripe',
            'fa-bell-slash-o' => 'far fa-bell-slash',
            'fa-trash' => 'fas fa-trash-alt',
            'fa-copyright' => 'far fa-copyright',
            'fa-eyedropper' => 'fas fa-eye-dropper',
            'fa-area-chart' => 'fas fa-chart-area',
            'fa-pie-chart' => 'fas fa-chart-pie',
            'fa-line-chart' => 'fas fa-chart-line',
            'fa-lastfm' => 'fab fa-lastfm',
            'fa-lastfm-square' => 'fab fa-lastfm-square',
            'fa-ioxhost' => 'fab fa-ioxhost',
            'fa-angellist' => 'fab fa-angellist',
            'fa-cc' => 'far fa-closed-captioning',
            'fa-ils' => 'fas fa-shekel-sign',
            'fa-shekel' => 'fas fa-shekel-sign',
            'fa-sheqel' => 'fas fa-shekel-sign',
            'fa-meanpath' => 'fab fa-font-awesome',
            'fa-buysellads' => 'fab fa-buysellads',
            'fa-connectdevelop' => 'fab fa-connectdevelop',
            'fa-dashcube' => 'fab fa-dashcube',
            'fa-forumbee' => 'fab fa-forumbee',
            'fa-leanpub' => 'fab fa-leanpub',
            'fa-sellsy' => 'fab fa-sellsy',
            'fa-shirtsinbulk' => 'fab fa-shirtsinbulk',
            'fa-simplybuilt' => 'fab fa-simplybuilt',
            'fa-skyatlas' => 'fab fa-skyatlas',
            'fa-diamond' => 'far fa-gem',
            'fa-intersex' => 'fas fa-transgender',
            'fa-facebook-official' => 'fab fa-facebook',
            'fa-pinterest-p' => 'fab fa-pinterest-p',
            'fa-whatsapp' => 'fab fa-whatsapp',
            'fa-hotel' => 'fas fa-bed',
            'fa-viacoin' => 'fab fa-viacoin',
            'fa-medium' => 'fab fa-medium',
            'fa-y-combinator' => 'fab fa-y-combinator',
            'fa-yc' => 'fab fa-y-combinator',
            'fa-optin-monster' => 'fab fa-optin-monster',
            'fa-opencart' => 'fab fa-opencart',
            'fa-expeditedssl' => 'fab fa-expeditedssl',
            'fa-battery-4' => 'fas fa-battery-full',
            'fa-battery' => 'fas fa-battery-full',
            'fa-battery-3' => 'fas fa-battery-three-quarters',
            'fa-battery-2' => 'fas fa-battery-half',
            'fa-battery-1' => 'fas fa-battery-quarter',
            'fa-battery-0' => 'fas fa-battery-empty',
            'fa-object-group' => 'far fa-object-group',
            'fa-object-ungroup' => 'far fa-object-ungroup',
            'fa-sticky-note-o' => 'far fa-sticky-note',
            'fa-cc-jcb' => 'fab fa-cc-jcb',
            'fa-cc-diners-club' => 'fab fa-cc-diners-club',
            'fa-clone' => 'far fa-clone',
            'fa-hourglass-o' => 'far fa-hourglass',
            'fa-hourglass-1' => 'fas fa-hourglass-start',
            'fa-hourglass-2' => 'fas fa-hourglass-half',
            'fa-hourglass-3' => 'fas fa-hourglass-end',
            'fa-hand-rock-o' => 'far fa-hand-rock',
            'fa-hand-grab-o' => 'far fa-hand-rock',
            'fa-hand-paper-o' => 'far fa-hand-paper',
            'fa-hand-stop-o' => 'far fa-hand-paper',
            'fa-hand-scissors-o' => 'far fa-hand-scissors',
            'fa-hand-lizard-o' => 'far fa-hand-lizard',
            'fa-hand-spock-o' => 'far fa-hand-spock',
            'fa-hand-pointer-o' => 'far fa-hand-pointer',
            'fa-hand-peace-o' => 'far fa-hand-peace',
            'fa-registered' => 'far fa-registered',
            'fa-creative-commons' => 'fab fa-creative-commons',
            'fa-gg' => 'fab fa-gg',
            'fa-gg-circle' => 'fab fa-gg-circle',
            'fa-tripadvisor' => 'fab fa-tripadvisor',
            'fa-odnoklassniki' => 'fab fa-odnoklassniki',
            'fa-odnoklassniki-square' => 'fab fa-odnoklassniki-square',
            'fa-get-pocket' => 'fab fa-get-pocket',
            'fa-wikipedia-w' => 'fab fa-wikipedia-w',
            'fa-safari' => 'fab fa-safari',
            'fa-chrome' => 'fab fa-chrome',
            'fa-firefox' => 'fab fa-firefox',
            'fa-opera' => 'fab fa-opera',
            'fa-internet-explorer' => 'fab fa-internet-explorer',
            'fa-television' => 'fas fa-tv',
            'fa-contao' => 'fab fa-contao',
            'fa-500px' => 'fab fa-500px',
            'fa-amazon' => 'fab fa-amazon',
            'fa-calendar-plus-o' => 'far fa-calendar-plus',
            'fa-calendar-minus-o' => 'far fa-calendar-minus',
            'fa-calendar-times-o' => 'far fa-calendar-times',
            'fa-calendar-check-o' => 'far fa-calendar-check',
            'fa-map-o' => 'far fa-map',
            'fa-commenting' => 'fas fa-comment-alt',
            'fa-commenting-o' => 'far fa-comment-alt',
            'fa-houzz' => 'fab fa-houzz',
            'fa-vimeo' => 'fab fa-vimeo-v',
            'fa-black-tie' => 'fab fa-black-tie',
            'fa-fonticons' => 'fab fa-fonticons',
            'fa-reddit-alien' => 'fab fa-reddit-alien',
            'fa-edge' => 'fab fa-edge',
            'fa-credit-card-alt' => 'fas fa-credit-card',
            'fa-codiepie' => 'fab fa-codiepie',
            'fa-modx' => 'fab fa-modx',
            'fa-fort-awesome' => 'fab fa-fort-awesome',
            'fa-usb' => 'fab fa-usb',
            'fa-product-hunt' => 'fab fa-product-hunt',
            'fa-mixcloud' => 'fab fa-mixcloud',
            'fa-scribd' => 'fab fa-scribd',
            'fa-pause-circle-o' => 'far fa-pause-circle',
            'fa-stop-circle-o' => 'far fa-stop-circle',
            'fa-bluetooth' => 'fab fa-bluetooth',
            'fa-bluetooth-b' => 'fab fa-bluetooth-b',
            'fa-gitlab' => 'fab fa-gitlab',
            'fa-wpbeginner' => 'fab fa-wpbeginner',
            'fa-wpforms' => 'fab fa-wpforms',
            'fa-envira' => 'fab fa-envira',
            'fa-wheelchair-alt' => 'fab fa-accessible-icon',
            'fa-question-circle-o' => 'far fa-question-circle',
            'fa-volume-control-phone' => 'fas fa-phone-volume',
            'fa-asl-interpreting' => 'fas fa-american-sign-language-interpreting',
            'fa-deafness' => 'fas fa-deaf',
            'fa-hard-of-hearing' => 'fas fa-deaf',
            'fa-glide' => 'fab fa-glide',
            'fa-glide-g' => 'fab fa-glide-g',
            'fa-signing' => 'fas fa-sign-language',
            'fa-viadeo' => 'fab fa-viadeo',
            'fa-viadeo-square' => 'fab fa-viadeo-square',
            'fa-snapchat' => 'fab fa-snapchat',
            'fa-snapchat-ghost' => 'fab fa-snapchat-ghost',
            'fa-snapchat-square' => 'fab fa-snapchat-square',
            'fa-pied-piper' => 'fab fa-pied-piper',
            'fa-first-order' => 'fab fa-first-order',
            'fa-yoast' => 'fab fa-yoast',
            'fa-themeisle' => 'fab fa-themeisle',
            'fa-google-plus-official' => 'fab fa-google-plus',
            'fa-google-plus-circle' => 'fab fa-google-plus',
            'fa-font-awesome' => 'fab fa-font-awesome',
            'fa-fa' => 'fab fa-font-awesome',
            'fa-handshake-o' => 'far fa-handshake',
            'fa-envelope-open-o' => 'far fa-envelope-open',
            'fa-linode' => 'fab fa-linode',
            'fa-address-book-o' => 'far fa-address-book',
            'fa-vcard' => 'fas fa-address-card',
            'fa-address-card-o' => 'far fa-address-card',
            'fa-vcard-o' => 'far fa-address-card',
            'fa-user-circle-o' => 'far fa-user-circle',
            'fa-user-o' => 'far fa-user',
            'fa-id-badge' => 'far fa-id-badge',
            'fa-drivers-license' => 'fas fa-id-card',
            'fa-id-card-o' => 'far fa-id-card',
            'fa-drivers-license-o' => 'far fa-id-card',
            'fa-quora' => 'fab fa-quora',
            'fa-free-code-camp' => 'fab fa-free-code-camp',
            'fa-telegram' => 'fab fa-telegram',
            'fa-thermometer-4' => 'fas fa-thermometer-full',
            'fa-thermometer' => 'fas fa-thermometer-full',
            'fa-thermometer-3' => 'fas fa-thermometer-three-quarters',
            'fa-thermometer-2' => 'fas fa-thermometer-half',
            'fa-thermometer-1' => 'fas fa-thermometer-quarter',
            'fa-thermometer-0' => 'fas fa-thermometer-empty',
            'fa-bathtub' => 'fas fa-bath',
            'fa-s15' => 'fas fa-bath',
            'fa-window-maximize' => 'far fa-window-maximize',
            'fa-window-restore' => 'far fa-window-restore',
            'fa-times-rectangle' => 'fas fa-window-close',
            'fa-window-close-o' => 'far fa-window-close',
            'fa-times-rectangle-o' => 'far fa-window-close',
            'fa-bandcamp' => 'fab fa-bandcamp',
            'fa-grav' => 'fab fa-gray',
            'fa-etsy' => 'fab fa-etsy',
            'fa-imdb' => 'fab fa-imdb',
            'fa-ravelry' => 'fab fa-ravelry',
            'fa-eercast' => 'fab fa-sellcast',
            'fa-snowflake-o' => 'far fa-snowflake',
            'fa-superpowers' => 'fab fa-superpowers',
            'fa-wpexplorer' => 'fab fa-wpexplorer',
            'fa-deviantart' => 'fab fa-deviantart'
        );

        if (isset($icons[$icontofind])) {
            return $icons[$icontofind];
        } else {
            // Guess.
            return 'fas '.$icontofind;
        }
    }
}
