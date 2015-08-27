<?php
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
    
    $bgcolorsettings = theme_squared_get_backgroundcolorsettings_array ( '/bgcolor_.+/', $theme->settings );
    $css = theme_squared_add_categorycolorguide_css ( $css, $bgcolorsettings );
    
    if (! empty ( $theme->settings->bgcolordefault )) {
        $bgcolordefault = $theme->settings->bgcolordefault;
    } else {
        $bgcolordefault = null;
    }
    $css = theme_squared_set_bgcolordefault ( $css, $bgcolordefault );
    $css = theme_squared_set_slideimage ( $css, $theme );
    
    // Set the inside header image
    if (! empty ( $theme->settings->headerimagecourse )) {
        $headerimagecourse = $theme->setting_file_url ( 'headerimagecourse', 'headerimagecourse' );
    } else {
        $headerimagecourse = null;
    }
    $css = theme_squared_set_headerimagecourse ( $css, $headerimagecourse );
    
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
 * Returns an array of the all settings used to define background color for a specified category
 *
 * @param string $pattern
 *            the setting prefix used for the setting in settings.php. Example: bgcolor_
 * @param array $input
 *            All available settings of the theme ($theme->settings)
 * @param number $flags            
 * @return array matching key/value pairs as array:
 */
function theme_squared_get_backgroundcolorsettings_array($pattern, $input, $flags = 0) {
    $varnames = get_object_vars ( $input );
    $settings = array_flip ( preg_grep ( $pattern, array_flip ( $varnames ), $flags ) );
    return $settings;
}

/**
 * Add CSS for the category color guide defined in the theme settings
 *
 * @param string $css            
 * @param array $bgcolorsettings            
 * @return string
 */
function theme_squared_add_categorycolorguide_css($css, $bgcolorsettings) {
    $replacement = "";
    foreach ( $bgcolorsettings as $settingname => $color ) {
        if (! isset ( $color ) || $color == '') {
            $color = "blue";
        }
        
        $categoryid = str_replace ( 'bgcolor_', '', $settingname );
        
        /**
         * blocks.css set additional small course block colors *
         */
        $replacement .= ".category-$categoryid .coursepage .block.vclass .header, .category-$categoryid .coursepage .block.vclass.block2 .header, .category-$categoryid .coursepage .block.vclass.block3 .header, .category-$categoryid .coursepage .block.vclass.block-hider-show.hidden .header {
    	background-color: $color !important; }
        ";
        
        /**
         * blocks.css set additional colors for small blocks, like the below.
         * you need rgb for opacity *
         */
        $replacement .= ".category-$categoryid .coursepage .block.hidden.vclass:hover .content, .category-$categoryid .coursepage .block.vclass.block-hider-show .content {
            background: $color; }
        ";
        
        /**
         * core.css custom breadcrumb color *
         */
        $replacement .= ".category-$categoryid .breadcrumb li:last-child a {
	           border-bottom-color: $color; }
        ";
        
        /**
         * core.css tabs color*
         */
        $replacement .= ".category-$categoryid .tabtree .tabrow0 .here a, .category-$categoryid .tabtree .tabrow0 li a:hover {
	           background-color: $color; }
        ";
        
        /**
         * core.css buttons *
         */
        $replacement .= ".category-$categoryid input[type=\"submit\"], .category-$categoryid input[type=\"button\"] {
                background: $color; }
        ";
        
        /**
         * course.css movecourses *
         */
        $replacement .= "#page-course-index-category.category-$categoryid #movecourses .catheadwrap {
	       background-color: $color; }
        ";
        
        /**
         * course.css heading *
         */
        $replacement .= ".path-course-view.category-$categoryid .headingwrap1 {
            background-color: $color; }
        ";

        /**
         * course.css table header *
         */
        $replacement .= "#page-course-index-category.category-$categoryid .category_subcategories th {
	           background-color: $color; }
        ";
        
        /**
         * course.css heading wrap *
         */
        $replacement .= "#page-course-index-category.category-$categoryid #movecourses .catheadwrap {
	           background-color: $color; }
        ";
        
        /**
         * course.css table header *
         */
        $replacement .= "#page-course-index-category.category-$categoryid .category_subcategories th {
	           background-color: $color; }
        ";
        
        /**
         * course.css buttons *
         */
        $replacement .= "#page-my-index.category-$categoryid .block_course_overview.block .header {
	           background-color: $color; }
        ";

        /**
         * dock.css dock *
         */
        $replacement .= ".category-$categoryid #dock-control, .category-$categoryid.idock.editing .block .header .commands {
	           background-color: $color; }
        ";
        
        /**
         * forum.css userpicture *
         */
        $replacement .= ".category-$categoryid .forumpost .row .left.picture {
	           background: $color; }
        ";
        
        /**
         * forum.css forumpost *
         */
        $replacement .= ".category-$categoryid .forumpost.unread .maincontent {
        border: 2px solid $color; }
        ";
        
        /**
         * menu.css background color for top level elements in custom menu (quicknavi) *
         */
        $replacement .= "#custommenu .yui3-menu-content li.category-$categoryid a, #custommenu .yui3-menu-content li.category-$categoryid .custom_menu_submenu {
	            background-color: $color !important; }
        ";
    }

    $squaredcategorytree = \theme_squared\toolbox::get_top_level_categories();
    foreach($squaredcategorytree as $key => $value) {
        $color = $bgcolorsettings['bgcolor_'.$key];
        /**
         * course.css category listing *
         */
        $replacement .= ".course_category_tree .category.topcategoryid-$key >.info > span.squared {
               background-color: $color; }
        ";
    }

    $css = str_replace ( '[[setting:categorycolorguidecss]]', $replacement, $css );

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

    if (! empty ( $authurl )) {
    	// check the id of the MNET host for the idp
    	if (!empty($PAGE->theme->settings->alternateloginurl)){
    		$host = $DB->get_field ( 'mnet_host', 'name', array (
    				'id' => $PAGE->theme->settings->alternateloginurl
    		) );
    	} else {
    		$host = '';
    	}
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
 * set the logo in the header
 *
 * @param string $css            
 * @param string $logo            
 * @return mixed
 */
function theme_squared_set_logo($css, $logo) {
    global $OUTPUT;
    $tag = '[[setting:logo]]';
    $replacement = $logo;
    if (is_null ( $replacement )) {
        $replacement = $OUTPUT->pix_url ( 'moodle-logo', 'theme_squared' );
    }
    $css = str_replace ( $tag, $replacement, $css );
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
 * user defined columns to show or not
 *
 * @param moodle_page $page            
 */
function theme_squared_initialise_colpos(moodle_page $page) {
    user_preference_allow_ajax_update ( 'theme_squared_chosen_colpos', PARAM_ALPHA );
}

/**
 * get user preference
 *
 * @param string $default            
 * @return string|mixed|null A string containing the value of a single preference. An array with all of the preferences or null
 */
function theme_squared_get_colpos($default = 'noidock') {
    return get_user_preferences ( 'theme_squared_chosen_colpos', $default );
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
 * include jquery and custom javascript
 *
 * @param moodle_page $page            
 */
/*
function theme_squared_page_init(moodle_page $page) {
    $page->requires->jquery ();
    $page->requires->jquery_plugin ( 'jqueryflexslider', 'theme_squared' );
    $page->requires->jquery_plugin ( 'jqueryeasing', 'theme_squared' );
    $page->requires->jquery_plugin ( 'custom', 'theme_squared' );
}
*/
