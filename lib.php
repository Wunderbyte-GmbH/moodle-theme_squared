<?php
function squared_process_css($css, $theme) {
 $css = squared_include_fonts($css, $theme);
 if (!empty($theme->settings->bgcolor1)) {
  $bgcolor1 = $theme->settings->bgcolor1;
 } else {
  $bgcolor1 = null;
 }
 $css = squared_set_bgcolor1($css, $bgcolor1);

 if (!empty($theme->settings->bgcolor2)) {
  $bgcolor2 = $theme->settings->bgcolor2;
 } else {
  $bgcolor2 = null;
 }
 $css = squared_set_bgcolor2($css, $bgcolor2);

 if (!empty($theme->settings->bgcolor3)) {
  $bgcolor3 = $theme->settings->bgcolor3;
 } else {
  $bgcolor3 = null;
 }
 $css = squared_set_bgcolor3($css, $bgcolor3);

 if (!empty($theme->settings->bgcolor4)) {
  $bgcolor4 = $theme->settings->bgcolor4;
 } else {
  $bgcolor4 = null;
 }
 $css = squared_set_bgcolor4($css, $bgcolor4);

 if (!empty($theme->settings->bgcolordefault)) {
  $bgcolordefault = $theme->settings->bgcolordefault;
 } else {
  $bgcolordefault = null;
 }
 $css = squared_set_bgcolordefault($css, $bgcolordefault);

 // Set the frontpage header image
 if (!empty($theme->settings->headerback1)) {
  $headerback = $theme->setting_file_url('headerback1', 'headerback1');
 } else {
  $headerback = null;
 }
 $css = squared_set_headerback1($css, $headerback);

 // Set the frontpage header image
 if (!empty($theme->settings->headerback2)) {
  $headerback = $theme->setting_file_url('headerback2', 'headerback2');
 } else {
  $headerback = null;
 }
 $css = squared_set_headerback2($css, $headerback);

 // Set the frontpage header image
 if (!empty($theme->settings->headerback3)) {
  $headerback = $theme->setting_file_url('headerback3', 'headerback3');
 } else {
  $headerback = null;
 }
 $css = squared_set_headerback3($css, $headerback);

 // Set the frontpage header image
 if (!empty($theme->settings->headerback4)) {
  $headerback = $theme->setting_file_url('headerback4', 'headerback4');
 } else {
  $headerback = null;
 }
 $css = squared_set_headerback4($css, $headerback);

 // Set the frontpage header image
 if (!empty($theme->settings->headerback5)) {
  $headerback = $theme->setting_file_url('headerback5', 'headerback5');
 } else {
  $headerback = null;
 }
 $css = squared_set_headerback5($css, $headerback);

 // Set the inside header image
 if (!empty($theme->settings->headerbackcourse)) {
  $headerbackcourse = $theme->setting_file_url('headerbackcourse', 'headerbackcourse');
 } else {
  $headerbackcourse = null;
 }
 $css = squared_set_headerbackcourse($css, $headerbackcourse);
 
 if (!empty($theme->settings->customcss)) {
  $customcss = $theme->settings->customcss;
 } else {
  $customcss = null;
 }
 
 $css = squared_set_customcss($css, $customcss);
 // Return the CSS
 return $css;

}

function squared_include_fonts($css){
 global $CFG, $PAGE;
 if(empty($CFG->themewww)){
  $themewww = $CFG->wwwroot."/theme";
 } else {
  $themewww = $CFG->themewww;
 }
 $tag ='[[setting:fontface]]';
 $replacement = '
   @font-face {
   font-family: "SourceSansPro";
   src: url("'.$themewww.'/'.$PAGE->theme->name.'/fonts/SourceSansPro-Regular.eot");
     src: url("'.$themewww.'/'.$PAGE->theme->name.'/fonts/SourceSansPro-Regular.eot?#iefix") format("embedded-opentype"),
       url("'.$themewww.'/'.$PAGE->theme->name.'/fonts/SourceSansPro-Regular.otf.woff") format("woff");
             font-weight: normal;
             font-style: normal;
}
   @font-face {
   font-family: "SourceSansPro";
   src: url("'.$themewww.'/'.$PAGE->theme->name.'/fonts/SourceSansPro-Semibold.eot");
     src: url("'.$themewww.'/'.$PAGE->theme->name.'/fonts/SourceSansPro-Semibold.eot?#iefix") format("embedded-opentype"),
       url("'.$themewww.'/'.$PAGE->theme->name.'/fonts/SourceSansPro-Semibold.otf.woff") format("woff");
             font-weight: bold;
             font-style: normal;
}
   @font-face {
   font-family: "SourceSansPro";
   src: url("'.$themewww.'/'.$PAGE->theme->name.'/fonts/SourceSansPro-It.eot");
     src: url("'.$themewww.'/'.$PAGE->theme->name.'/fonts/SourceSansPro-It.eot?#iefix") format("embedded-opentype"),
       url("'.$themewww.'/'.$PAGE->theme->name.'/fonts/SourceSansPro-It.otf.woff") format("woff");
             font-weight: normal;
             font-style: italic;
}
   @font-face {
   font-family: "SourceSansPro";
   src: url("'.$themewww.'/'.$PAGE->theme->name.'/fonts/SourceSansPro-SemiboldIt.eot");
     src: url("'.$themewww.'/'.$PAGE->theme->name.'/fonts/SourceSansPro-SemiboldIt.eot?#iefix") format("embedded-opentype"),
       url("'.$themewww.'/'.$PAGE->theme->name.'/fonts/SourceSansPro-SemiboldIt.otf.woff") format("woff");
             font-weight: bold;
             font-style: italic;
}
   @font-face {
   font-family: "SourceSansPro-Light";
   src: url("'.$themewww.'/'.$PAGE->theme->name.'/fonts/SourceSansPro-Light.eot");
     src: url("'.$themewww.'/'.$PAGE->theme->name.'/fonts/SourceSansPro-Light.eot?#iefix") format("embedded-opentype"),
       url("'.$themewww.'/'.$PAGE->theme->name.'/fonts/SourceSansPro-Light.otf.woff") format("woff");
             font-weight: normal;
             font-style: normal;
}
   @font-face {
   font-family: "SourceSansPro-Light";
   src: url("'.$themewww.'/'.$PAGE->theme->name.'/fonts/SourceSansPro-LightIt.eot");
     src: url("'.$themewww.'/'.$PAGE->theme->name.'/fonts/SourceSansPro-LightIt.eot?#iefix") format("embedded-opentype"),
       url("'.$themewww.'/'.$PAGE->theme->name.'/fonts/SourceSansPro-LightIt.otf.woff") format("woff");
             font-weight: normal;
             font-style: italic;
}
                       ';
 $css = str_replace($tag, $replacement, $css);
 return $css;
}

function squared_set_bgcolor1($css, $bgcolor1) {
 $tag = '[[setting:bgcolor1]]';
 $replacement = $bgcolor1;
 if (is_null($replacement)) {
  $replacement = '#11847D';
 }
 $css = str_replace($tag, $replacement, $css);
 return $css;
}

function squared_set_bgcolor2($css, $bgcolor2) {
 $tag = '[[setting:bgcolor2]]';
 $replacement = $bgcolor2;
 if (is_null($replacement)) {
  $replacement = '#11847D';
 }
 $css = str_replace($tag, $replacement, $css);
 return $css;
}

function squared_set_bgcolor3($css, $bgcolor3) {
 $tag = '[[setting:bgcolor3]]';
 $replacement = $bgcolor3;
 if (is_null($replacement)) {
  $replacement = '#11847D';
 }
 $css = str_replace($tag, $replacement, $css);
 return $css;
}

function squared_set_bgcolor4($css, $bgcolor4) {
 $tag = '[[setting:bgcolor4]]';
 $replacement = $bgcolor4;
 if (is_null($replacement)) {
  $replacement = '#11847D';
 }
 $css = str_replace($tag, $replacement, $css);
 return $css;
}

function squared_set_bgcolordefault($css, $bgcolordefault) {
 $tag = '[[setting:bgcolordefault]]';
 $replacement = $bgcolordefault;
 if (is_null($replacement)) {
  $replacement = '#11847D';
 }
 $css = str_replace($tag, $replacement, $css);
 return $css;
}

function squared_set_headerback1($css, $headerback) {
 global $OUTPUT;
 $tag = '[[setting:headerback1]]';
 $replacement = $headerback;
 if (is_null($replacement)) {
  $replacement = $OUTPUT->pix_url('header-frontpage', 'theme');
 }
 $css = str_replace($tag, $replacement, $css);
 return $css;
}

function squared_set_headerback2($css, $headerback) {
 global $OUTPUT;
 $tag = '[[setting:headerback2]]';
 $replacement = $headerback;
 if (is_null($replacement)) {
  $replacement = $OUTPUT->pix_url('header-frontpage', 'theme');
 }
 $css = str_replace($tag, $replacement, $css);
 return $css;
}

function squared_set_headerback3($css, $headerback) {
 global $OUTPUT;
 $tag = '[[setting:headerback3]]';
 $replacement = $headerback;
 if (is_null($replacement)) {
  $replacement = $OUTPUT->pix_url('header-frontpage', 'theme');
 }
 $css = str_replace($tag, $replacement, $css);
 return $css;
}

function squared_set_headerback4($css, $headerback) {
 global $OUTPUT;
 $tag = '[[setting:headerback4]]';
 $replacement = $headerback;
 if (is_null($replacement)) {
  $replacement = $OUTPUT->pix_url('header-frontpage', 'theme');
 }
 $css = str_replace($tag, $replacement, $css);
 return $css;
}

function squared_set_headerback5($css, $headerback) {
 global $OUTPUT;
 $tag = '[[setting:headerback5]]';
 $replacement = $headerback;
 if (is_null($replacement)) {
  $replacement = $OUTPUT->pix_url('header-frontpage', 'theme');
 }
 $css = str_replace($tag, $replacement, $css);
 return $css;
}

function squared_set_headerbackcourse($css, $headerbackcourse) {
 global $OUTPUT;
 $tag = '[[setting:headerbackcourse]]';
 $replacement = $headerbackcourse;
 if (is_null($replacement)) {
  $replacement = $OUTPUT->pix_url('header-course', 'theme');
 }
 $css = str_replace($tag, $replacement, $css);
 return $css;
}
/**
 * Returns MNET Login URL instead of standard login URL. Checks the wanted url
 * of user in order to provide correct redirect url for the identity provider
 *
 * @return string login url
 */
function squared_get_login_url(){
 global $PAGE, $DB, $SESSION, $CFG;
 if ($PAGE->url->out() === $CFG->wwwroot."/login/index.php"){
  $urltogo = $SESSION->wantsurl;
 } else {
  $urltogo = $PAGE->url->out();
 }
 $authplugin = get_auth_plugin('mnet');
 $authurl = $authplugin->loginpage_idp_list($urltogo);
 // check the id of the MNET host for the idp
 $host = $DB->get_field('mnet_host', 'name', array('id' => $PAGE->theme->settings->alternateloginurl));
 if(!empty($authurl)){
  foreach($authurl as $key => $urlarray){
   if($urlarray['name'] == $host){
    $loginurl = $authurl[$key]['url'];
    return $loginurl;
   } else {
    $loginurl = "$CFG->wwwroot/login/index.php";
    if (!empty($CFG->loginhttps)) {
     $loginurl = str_replace('http:', 'https:', $loginurl);
    }
   }
  }
 } else {
  $loginurl = "$CFG->wwwroot/login/index.php";
  if (!empty($CFG->loginhttps)) {
   $loginurl = str_replace('http:', 'https:', $loginurl);
  }
 }
 return $loginurl;
}

/**
 * unused at the moment
 * @param unknown $css
 * @param unknown $logo
 * @return mixed
 */
function squared_set_logo($css, $logo) {
 global $OUTPUT;
 $tag = '[[setting:logo]]';
 $replacement = $logo;
 if (is_null($replacement)) {
  $replacement = $OUTPUT->pix_url('/header','theme');
 }
 $css = str_replace($tag, $replacement, $css);
 return $css;
}

function theme_squared_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, array $options = array()) {
 if ($context->contextlevel == CONTEXT_SYSTEM and $filearea) {
  $theme = theme_config::load('squared');
  return $theme->setting_file_serve($filearea, $args, $forcedownload, $options);
 } else {
  send_file_not_found();
 }
}

//user defined columns to show or now
function squared_initialise_colpos(moodle_page $page) {
 user_preference_allow_ajax_update('theme_squared_chosen_colpos', PARAM_ALPHA);
}

function squared_get_colpos($default='noidock') {
 return get_user_preferences('theme_squared_chosen_colpos', $default);
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
 if (is_null($replacement)) {
  $replacement = '';
 }
 $css = str_replace($tag, $replacement, $css);
 return $css;
}

function theme_squared_page_init(moodle_page $page) {
    $page->requires->jquery();
    $page->requires->jquery_plugin('jqueryflexslider', 'theme_squared');
    $page->requires->jquery_plugin('jqueryeasing', 'theme_squared');
    $page->requires->jquery_plugin('custom', 'theme_squared');
}
