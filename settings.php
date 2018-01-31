<?php
$settings = null;

defined ( 'MOODLE_INTERNAL' ) || die ();
if (is_siteadmin()) {
    require_once("$CFG->libdir/coursecatlib.php");
    require_once(__DIR__."/simple_theme_settings.class.php");

    // Page General Settings
    $ADMIN->add('themes', new admin_category('theme_squared', 'squared'));
    $sp = new admin_settingpage('theme_squared_basic', get_string('basicsettings', 'theme_squared'));
    $simset = new squared_simple_theme_settings($sp, 'theme_squared');

    $simset->add_text('footertext');
    $simset->add_text('youtubelink');
    $simset->add_text('googlepluslink');
    $simset->add_text('facebooklink');
    $simset->add_text('twitterlink');
    $domain = preg_replace ( "(^https?://)", "", $CFG->wwwroot );
    $searchurl = 'https://www.google.com/search?as_sitesearch=' . $domain;
    $simset->add_text('searchurl', $searchurl);
    $simset->add_text('searchfield');
    $simset->add_select('alternateloginurl', 0, $simset->mnet_choices());
    $simset->add_checkbox('hidelocallogin',0,1,0);
    $simset->add_select('blockperrowlimit', 4, range(0, 30));
    $simset->add_textarea('customcss');
    $ADMIN->add('theme_squared', $sp);

    // Page Header Settings
    $sp = new admin_settingpage('theme_squared_header', get_string('pageheadersettings', 'theme_squared'));
    $simset = new squared_simple_theme_settings($sp, 'theme_squared');

    $simset->add_select('headerlayout', 0,
        array(get_string('hlogoside', 'theme_squared'), get_string('hlogotop', 'theme_squared')));
    $simset->add_file('logo');
    $simset->add_file('logosmall');
    $simset->add_file('headerbg');
    $simset->add_file('headerbgsmall');
    $simset->add_select('logoposition', 'left',
        array('left' => get_string('left', 'editor'), 'right'=> get_string('right', 'editor')));
    $simset->add_colourpicker('logobgcolor', '#4ba09b');
    $simset->add_checkbox('nologobgcolor', 0, 1, 0);
    $ADMIN->add('theme_squared', $sp);

    // Category color guide settings.
    $sp = new admin_settingpage('theme_squared_catcolor', get_string ('catcolorsettings', 'theme_squared'));
    $simset = new squared_simple_theme_settings($sp, 'theme_squared');
    $categorytree = coursecat::get(0)->get_children ();
    $cclr = array ('#EF001C', '#4B88FB', '#A89E00', '#013855');
    $i = 0;
    foreach ( $categorytree as $cid => $value ) {
        $simset->add_headings('bgcolorheading', $cid, $value->name);
        $simset->add_colourpickers('bgcolor', $cid, (!empty($cclr[$i]) ? $cclr[$i++] : "#666") );
    }
    $ADMIN->add('theme_squared', $sp);
}