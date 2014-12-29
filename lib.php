<?php
function squared_process_css($css, $theme) {
    global $CFG;
    $css = squared_include_fonts ( $css, $theme );
    
    $bgcolorsettings = squared_get_backgroundcolorsettings_array ( '/bgcolor_.+/', $theme->settings );
    $css .= squared_add_categorycolorguide_css ( $bgcolorsettings );
    
    if (! empty ( $theme->settings->bgcolordefault )) {
        $bgcolordefault = $theme->settings->bgcolordefault;
    } else {
        $bgcolordefault = null;
    }
    $css = squared_set_bgcolordefault ( $css, $bgcolordefault );
    
    // Set the frontpage header image
    for($i = 1; $i < 6; $i ++) {
        $setting = 'slideimage' . $i;
        if (! empty ( $theme->settings->$setting )) {
            $slideimage = $theme->setting_file_url ( '$setting', '$setting' );
        } else {
            $slideimage = null;
        }
        $css = squared_set_slideimage ( $css, $slideimage, $setting );
    }
    
    // Set the inside header image
    if (! empty ( $theme->settings->headerimagecourse )) {
        $headerimagecourse = $theme->setting_file_url ( 'headerimagecourse', 'headerimagecourse' );
    } else {
        $headerimagecourse = null;
    }
    $css = squared_set_headerimagecourse ( $css, $headerimagecourse );
    
    if (! empty ( $theme->settings->customcss )) {
        $customcss = $theme->settings->customcss;
    } else {
        $customcss = null;
    }
    
    $css = squared_set_customcss ( $css, $customcss );
    // Return the CSS
    return $css;
}
function squared_include_fonts($css) {
    global $CFG, $PAGE;
    if (empty ( $CFG->themewww )) {
        $themewww = $CFG->wwwroot . "/theme";
    } else {
        $themewww = $CFG->themewww;
    }
    $tag = '[[setting:fontface]]';
    $replacement = '
   @font-face {
   font-family: "SourceSansPro";
   src: url("' . $themewww . '/' . $PAGE->theme->name . '/fonts/SourceSansPro-Regular.eot");
     src: url("' . $themewww . '/' . $PAGE->theme->name . '/fonts/SourceSansPro-Regular.eot?#iefix") format("embedded-opentype"),
       url("' . $themewww . '/' . $PAGE->theme->name . '/fonts/SourceSansPro-Regular.otf.woff") format("woff");
             font-weight: normal;
             font-style: normal;
}
   @font-face {
   font-family: "SourceSansPro";
   src: url("' . $themewww . '/' . $PAGE->theme->name . '/fonts/SourceSansPro-Semibold.eot");
     src: url("' . $themewww . '/' . $PAGE->theme->name . '/fonts/SourceSansPro-Semibold.eot?#iefix") format("embedded-opentype"),
       url("' . $themewww . '/' . $PAGE->theme->name . '/fonts/SourceSansPro-Semibold.otf.woff") format("woff");
             font-weight: bold;
             font-style: normal;
}
   @font-face {
   font-family: "SourceSansPro";
   src: url("' . $themewww . '/' . $PAGE->theme->name . '/fonts/SourceSansPro-It.eot");
     src: url("' . $themewww . '/' . $PAGE->theme->name . '/fonts/SourceSansPro-It.eot?#iefix") format("embedded-opentype"),
       url("' . $themewww . '/' . $PAGE->theme->name . '/fonts/SourceSansPro-It.otf.woff") format("woff");
             font-weight: normal;
             font-style: italic;
}
   @font-face {
   font-family: "SourceSansPro";
   src: url("' . $themewww . '/' . $PAGE->theme->name . '/fonts/SourceSansPro-SemiboldIt.eot");
     src: url("' . $themewww . '/' . $PAGE->theme->name . '/fonts/SourceSansPro-SemiboldIt.eot?#iefix") format("embedded-opentype"),
       url("' . $themewww . '/' . $PAGE->theme->name . '/fonts/SourceSansPro-SemiboldIt.otf.woff") format("woff");
             font-weight: bold;
             font-style: italic;
}
   @font-face {
   font-family: "SourceSansPro-Light";
   src: url("' . $themewww . '/' . $PAGE->theme->name . '/fonts/SourceSansPro-Light.eot");
     src: url("' . $themewww . '/' . $PAGE->theme->name . '/fonts/SourceSansPro-Light.eot?#iefix") format("embedded-opentype"),
       url("' . $themewww . '/' . $PAGE->theme->name . '/fonts/SourceSansPro-Light.otf.woff") format("woff");
             font-weight: normal;
             font-style: normal;
}
   @font-face {
   font-family: "SourceSansPro-Light";
   src: url("' . $themewww . '/' . $PAGE->theme->name . '/fonts/SourceSansPro-LightIt.eot");
     src: url("' . $themewww . '/' . $PAGE->theme->name . '/fonts/SourceSansPro-LightIt.eot?#iefix") format("embedded-opentype"),
       url("' . $themewww . '/' . $PAGE->theme->name . '/fonts/SourceSansPro-LightIt.otf.woff") format("woff");
             font-weight: normal;
             font-style: italic;
}
                       ';
    $css = str_replace ( $tag, $replacement, $css );
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
function squared_get_backgroundcolorsettings_array($pattern, $input, $flags = 0) {
    $varnames = get_object_vars($input);
    $settings = array_flip (preg_grep ( $pattern, array_flip ( $varnames ), $flags ));
    return $settings;
}

/**
 * Add CSS for the category color guide defined in the theme settings
 * @param array $bgcolorsettings
 * @return string
 */
function squared_add_categorycolorguide_css($bgcolorsettings) {
    $css = "";
    foreach ( $bgcolorsettings as $settingname => $color ) {
        if (! isset ( $color ) || $color == '') {
            $color = "blue";
        }
        
        $categoryid = str_replace ( 'bgcolor_', '', $settingname );
        
        /**
         * blocks.css set additional small course block colors *
         */
        $css .= ".category-$categoryid .coursepage .block.vclass .header, .category-$categoryid .coursepage .block.vclass.block2 .header, .category-$categoryid .coursepage .block.vclass.block3 .header, .category-$categoryid .coursepage .block.vclass.block-hider-show.hidden .header {
    	background-color: $color !important; }
        ";
        
        /**
         * blocks.css set additional colors for small blocks, like the below.
         * you need rgb for opacity *
         */
        $css .= ".category-$categoryid .coursepage .block.hidden.vclass:hover .content, .category-$categoryid .coursepage .block.vclass.block-hider-show .content {
            background: $color; }
        ";
        
        /**
         * core.css custom breadcrumb color *
         */
        $css .= ".category-$categoryid .breadcrumb li:last-child a {
	           border-bottom-color: $color; }
        ";
        
        /**
         * core.css tabs color*
         */
        $css .= ".category-$categoryid .tabtree .tabrow0 .here a, .category-$categoryid .tabtree .tabrow0 li a:hover {
	           background-color: $color; }
        ";
        
        /**
         * core.css buttons *
         */
        $css .= ".category-$categoryid input[type=\"submit\"], .category-$categoryid input[type=\"button\"] {
                background: $color; }
        ";
        
        /**
         * course.css movecourses *
         */
        $css .= "#page-course-index-category.category-$categoryid #movecourses .catheadwrap {
	       background-color: $color; }
        ";
        
        /**
         * course.css heading *
         */
        $css .= ".path-course-view.category-$categoryid .headingwrap1 {
	           background-color: $color; }
        ";
        
        /**
         * course.css table header *
         */
        $css .= "#page-course-index-category.category-$categoryid .category_subcategories th {
	           background-color: $color; }
        ";
        
        /**
         * course.css heading wrap *
         */
        $css .= "#page-course-index-category.category-$categoryid #movecourses .catheadwrap {
	           background-color: $color; }
        ";
        
        /**
         * course.css table header *
         */
        $css .= "#page-course-index-category.category-$categoryid .category_subcategories th {
	           background-color: $color; }
        ";
        
        /**
         * course.css buttons *
         */
        $css .= "#page-my-index.category-$categoryid .block_course_overview.block .header {
	           background-color: $color; }
        ";
        
        /**
         * dock.css dock *
         */
        $css .= ".category-$categoryid #dock-control, .category-$categoryid.idock.editing .block .header .commands {
	           background-color: $color; }
        ";
        
        /**
         * forum.css userpicture *
         */
        $css .= ".category-$categoryid .forumpost .row .left.picture {
	           background: $color; }
        ";

        /**
         * forum.css forumpost *
         */
        $css .= ".category-$categoryid .forumpost.unread .maincontent {
        border: 2px solid $color; }
        ";
              
        /**
         * menu.css background color for top level elements in custom menu (quicknavi) *
         */
        $css .= "#custommenu .yui3-menu-content li.category-$categoryid a, #custommenu .yui3-menu-content li.category-$categoryid .custom_menu_submenu {
	            background-color: $color !important; }
        ";
    }
    
    return $css;
}

function squared_set_bgcolordefault($css, $bgcolordefault) {
    $tag = '[[setting:bgcolordefault]]';
    $replacement = $bgcolordefault;
    if (is_null ( $replacement )) {
        $replacement = '#11847D';
    }
    $css = str_replace ( $tag, $replacement, $css );
    return $css;
}
function squared_set_slideimage($css, $slideimage, $setting) {
    global $OUTPUT;
    $tag = "[[setting:$setting]]";
    $replacement = $slideimage;
    if (is_null ( $replacement )) {
        $replacement = $OUTPUT->pix_url ( $setting, 'theme_squared' );
    }
    $css = str_replace ( $tag, $replacement, $css );
    return $css;
}
function squared_set_headerimagecourse($css, $headerimagecourse) {
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
function squared_get_login_url() {
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
 * set the logo in the header
 * 
 * @param string $css            
 * @param string $logo            
 * @return mixed
 */
function squared_set_logo($css, $logo) {
    global $OUTPUT;
    $tag = '[[setting:logo]]';
    $replacement = $logo;
    if (is_null ( $replacement )) {
        $replacement = $OUTPUT->pix_url ( 'moodle-logo', 'theme_squared' );
    }
    $css = str_replace ( $tag, $replacement, $css );
    return $css;
}

function theme_squared_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, array $options = array()) {
    if ($context->contextlevel == CONTEXT_SYSTEM and $filearea) {
        $theme = theme_config::load ( 'squared' );
        return $theme->setting_file_serve ( $filearea, $args, $forcedownload, $options );
    } else {
        send_file_not_found ();
    }
}

// user defined columns to show or now
function squared_initialise_colpos(moodle_page $page) {
    user_preference_allow_ajax_update ( 'theme_squared_chosen_colpos', PARAM_ALPHA );
}
function squared_get_colpos($default = 'noidock') {
    return get_user_preferences ( 'theme_squared_chosen_colpos', $default );
}

/**
 * Sets the custom css variable in CSS
 *
 * @param string $css            
 * @param mixed $customcss            
 * @return string
 */
function squared_set_customcss($css, $customcss) {
    $tag = '[[setting:customcss]]';
    $replacement = $customcss;
    if (is_null ( $replacement )) {
        $replacement = '';
    }
    $css = str_replace ( $tag, $replacement, $css );
    return $css;
}

function theme_squared_page_init(moodle_page $page) {
    $page->requires->jquery ();
    $page->requires->jquery_plugin ( 'jqueryflexslider', 'theme_squared' );
    $page->requires->jquery_plugin ( 'jqueryeasing', 'theme_squared' );
    $page->requires->jquery_plugin ( 'custom', 'theme_squared' );
}
