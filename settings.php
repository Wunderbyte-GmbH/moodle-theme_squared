<?php
$settings = null;

defined ( 'MOODLE_INTERNAL' ) || die ();
if (is_siteadmin ()) {
    require_once ("$CFG->libdir/coursecatlib.php");
    
    $ADMIN->add ( 'themes', new admin_category ( 'theme_squared', 'squared' ) );
    
    $settingpage = new admin_settingpage ( 'theme_squared_basic', get_string ( 'basicsettings', 'theme_squared' ) );
    
    $name = 'theme_squared/logo';
    $title = get_string ( 'logo', 'theme_squared' );
    $description = get_string ( 'logodesc', 'theme_squared' );
    $setting = new admin_setting_configstoredfile ( $name, $title, $description, 'logo' );
    $setting->set_updatedcallback ( 'theme_reset_all_caches' );
    $settingpage->add ( $setting );
    
    $name = 'theme_squared/pagelogo';
    $title = get_string ( 'pagelogo', 'theme_squared' );
    $description = get_string ( 'pagelogodesc', 'theme_squared' );
    $setting = new admin_setting_configstoredfile ( $name, $title, $description, 'pagelogo' );
    $setting->set_updatedcallback ( 'theme_reset_all_caches' );
    $settingpage->add ( $setting );
    
    $name = 'theme_squared/footertext';
    $title = get_string ( 'footer', 'theme_squared' );
    $description = get_string ( 'footerdesc', 'theme_squared' );
    $setting = new admin_setting_configtext ( $name, $title, $description, '', PARAM_TEXT );
    $settingpage->add ( $setting );
    
    $name = 'theme_squared/youtubelink';
    $title = get_string ( 'youtubelink', 'theme_squared' );
    $description = get_string ( 'youtubelinkdesc', 'theme_squared' );
    $setting = new admin_setting_configtext ( $name, $title, $description, '', PARAM_TEXT );
    $settingpage->add ( $setting );
    
    $name = 'theme_squared/googlepluslink';
    $title = get_string ( 'googlepluslink', 'theme_squared' );
    $description = get_string ( 'googlepluslinkdesc', 'theme_squared' );
    $setting = new admin_setting_configtext ( $name, $title, $description, '', PARAM_TEXT );
    $settingpage->add ( $setting );
    
    $name = 'theme_squared/facebooklink';
    $title = get_string ( 'facebooklink', 'theme_squared' );
    $description = get_string ( 'facebooklinkdesc', 'theme_squared' );
    $setting = new admin_setting_configtext ( $name, $title, $description, '', PARAM_TEXT );
    $settingpage->add ( $setting );
    
    $name = 'theme_squared/twitterlink';
    $title = get_string ( 'twitterlink', 'theme_squared' );
    $description = get_string ( 'twitterlinkdesc', 'theme_squared' );
    $setting = new admin_setting_configtext ( $name, $title, $description, '', PARAM_TEXT );
    $settingpage->add ( $setting );
    
    $name = 'theme_squared/fpnews';
    $title = get_string ( 'fpnews', 'theme_squared' );
    $description = get_string ( 'fpnewsdesc', 'theme_squared' );
    $default = 'Change me in the theme settings';
    $setting = new admin_setting_configtextarea ( $name, $title, $description, $default, PARAM_CLEANHTML );
    $settingpage->add ( $setting );
    
    $name = 'theme_squared/searchurl';
    $title = get_string ( 'searchurl', 'theme_squared' );
    $description = get_string ( 'searchurldesc', 'theme_squared' );
    $domain = preg_replace ( "(^https?://)", "", $CFG->wwwroot );
    $default = 'https://www.google.com/search?as_sitesearch=' . $domain;
    $setting = new admin_setting_configtext ( $name, $title, $description, $default, PARAM_URL );
    $setting->set_updatedcallback ( 'theme_reset_all_caches' );
    $settingpage->add ( $setting );
    
    $name = 'theme_squared/searchfield';
    $title = get_string ( 'searchfield', 'theme_squared' );
    $description = get_string ( 'searchfielddesc', 'theme_squared' );
    $default = 'q';
    $setting = new admin_setting_configtext ( $name, $title, $description, $default, PARAM_ALPHANUM );
    $settingpage->add ( $setting );
    
    $name = 'theme_squared/alternateloginurl';
    $title = get_string ( 'alternateloginurl', 'theme_squared' );
    $description = get_string ( 'alternateloginurldesc', 'theme_squared' );
    $default = 0;
    $sql = "SELECT DISTINCT h.id, h.wwwroot, h.name, a.sso_jump_url, a.name as application
			FROM {mnet_host} h
			JOIN {mnet_host2service} m ON h.id = m.hostid
			JOIN {mnet_service} s ON s.id = m.serviceid
			JOIN {mnet_application} a ON h.applicationid = a.id
			WHERE s.name = ? AND h.deleted = ? AND m.publish = ?";
    $params = array (
            'sso_sp',
            0,
            1 
    );
    
    if (! empty ( $CFG->mnet_all_hosts_id )) {
        $sql .= " AND h.id <> ?";
        $params [] = $CFG->mnet_all_hosts_id;
    }
    
    if ($hosts = $DB->get_records_sql ( $sql, $params )) {
        $choices = array ();
        $choices [0] = 'notset';
        foreach ( $hosts as $id => $host ) {
            $choices [$id] = $host->name;
        }
    } else {
        $choices = array ();
        $choices [0] = 'notset';
    }
    $setting = new admin_setting_configselect ( $name, $title, $description, $default, $choices );
    $setting->set_updatedcallback ( 'theme_reset_all_caches' );
    $settingpage->add ( $setting );
    
    // Custom CSS
    $name = 'theme_squared/customcss';
    $title = get_string ( 'customcss', 'theme_squared' );
    $description = get_string ( 'customcssdesc', 'theme_squared' );
    $setting = new admin_setting_configtextarea ( $name, $title, $description, '' );
    $setting->set_updatedcallback ( 'theme_reset_all_caches' );
    $settingpage->add ( $setting );
    
    $ADMIN->add ( 'theme_squared', $settingpage );
    
    // Category color guide settings
    $settingpage = new admin_settingpage ( 'theme_squared_catcolor', get_string ( 'catcolorsettings', 'theme_squared' ) );
    
    // default block color setting
    $name = 'theme_squared/bgcolordefault';
    $title = get_string ( 'bgcolordefault', 'theme_squared' );
    $description = get_string ( 'bgcolordefaultdesc', 'theme_squared' );
    $default = '#0E00AF';
    $previewconfig = NULL;
    $setting = new admin_setting_configcolourpicker ( $name, $title, $description, $default, $previewconfig );
    $setting->set_updatedcallback ( 'theme_reset_all_caches' );
    $settingpage->add ( $setting );
    
    // category color settings
    $categorytree = coursecat::get ( 0 )->get_children ();
    $defaultvalues = array (
            0 => '#EF001C',
            1 => '#4B88FB',
            2 => '#A89E00',
            3 => '#013855' 
    );
    $i = 0;
    foreach ( $categorytree as $categoryid => $value ) {
        $choices = array ();
        
        $name = 'theme_squared/bgcolorheading';
        $heading = get_string ( 'catcolorheading', 'theme_squared' );
        $information = get_string ( 'catcolorheadingdesc', 'theme_squared' );
        $setting = new admin_setting_heading ( $name, $heading, $information );
        $settingpage->add ( $setting );
        
        $choices [$categoryid] = $value->name;
        $name = 'theme_squared/bgcolor_' . $categoryid;
        $title = get_string ( 'bgcolor', 'theme_squared' ) . ": $choices[$categoryid]";
        $description = get_string ( 'bgcolordesc', 'theme_squared' );
        if (! empty ( $defaults [$i] )) {
            $default = $defaultvalues [$i];
        } else {
            $default = "#015A3F";
        }
        
        $previewconfig = NULL;
        $setting = new admin_setting_configcolourpicker ( $name, $title, $description, $default, $previewconfig );
        $setting->set_updatedcallback ( 'theme_reset_all_caches' );
        $settingpage->add ( $setting );
        $i ++;
    }
    
    // inside page header image setting
    $name = 'theme_squared/headerimagecourse';
    $title = get_string ( 'headerimagecourse', 'theme_squared' );
    $description = get_string ( 'headerimagecoursedesc', 'theme_squared' );
    $setting = new admin_setting_configstoredfile ( $name, $title, $description, 'headerimagecourse' );
    $setting->set_updatedcallback ( 'theme_reset_all_caches' );
    $settingpage->add ( $setting );
    
    $ADMIN->add ( 'theme_squared', $settingpage );
    
    // frontpage slideshow settings
    $settingpage = new admin_settingpage ( 'theme_squared_slideshow', get_string ( 'slideshowsettings', 'theme_squared' ) );
    
    $name = 'theme_squared/numberofslides';
    $title = get_string ( 'numberofslides', 'theme_squared' );
    $description = get_string ( 'numberofslidesdesc', 'theme_squared' );
    $default = 3;
    $choices = array (
            1 => 1,
            2 => 2,
            3 => 3,
            4 => 4,
            5 => 5 
    );
    $setting = new admin_setting_configselect ( $name, $title, $description, $default, $choices );
    $setting->set_updatedcallback ( 'theme_reset_all_caches' );
    $settingpage->add ( $setting );
    
    // slideshow settings
    for($i = 1; $i < 6; $i ++) {
        
        $name = 'theme_squared/slideheading' . $i;
        $heading = get_string ( 'slideheading', 'theme_squared' ) . " $i";
        $information = get_string ( 'slideheadingdesc', 'theme_squared' ) . " $i";
        $setting = new admin_setting_heading ( $name, $heading, $information );
        $setting->set_updatedcallback ( 'theme_reset_all_caches' );
        $settingpage->add ( $setting );
        
        $name = 'theme_squared/slideimage' . $i;
        $title = get_string ( 'slideimage', 'theme_squared' ) . " $i";
        $description = get_string ( 'slideimagedesc', 'theme_squared' );
        $setting = new admin_setting_configstoredfile ( $name, $title, $description, 'slideimage' . $i );
        $setting->set_updatedcallback ( 'theme_reset_all_caches' );
        $settingpage->add ( $setting );
        
        $name = 'theme_squared/fptitle' . $i;
        $title = get_string ( 'fptitle', 'theme_squared' ) . " $i";
        $description = get_string ( 'fptitledesc', 'theme_squared' );
        $default = 'Photo credits';
        $setting = new admin_setting_configtext ( $name, $title, $description, $default );
        $setting->set_updatedcallback ( 'theme_reset_all_caches' );
        $settingpage->add ( $setting );
        
        $name = 'theme_squared/fptext' . $i;
        $title = get_string ( 'fptext', 'theme_squared' ) . " $i";
        $description = get_string ( 'fptextdesc', 'theme_squared' );
        switch ($i) {
            case 1 :
                $default = 'Dave Clark flickr.com/photos/fotobydave CreativeCommmons';
                break;
            case 2 :
                $default = 'eldeem flickr.com/photos/ltdemartinet CreativeCommmons';
                break;
            case 3 :
                $default = ' Georgie Pauwels flickr.com/photos/frosch50 CreativeCommmons';
                break;
            default :
                $default = "Change me in the theme settings";
        }
        $setting = new admin_setting_configtextarea ( $name, $title, $description, $default, PARAM_RAW );
        $setting->set_updatedcallback ( 'theme_reset_all_caches' );
        $settingpage->add ( $setting );
        
        $name = 'theme_squared/fppos' . $i;
        $title = get_string ( 'fppos', 'theme_squared' ) . " $i";
        $description = get_string ( 'fpposdesc', 'theme_squared' );
        $default = 'Text on the left';
        $choices = array (
                '2' => get_string ( 'fpposleft', 'theme_squared' ),
                '1' => get_string ( 'fpposright', 'theme_squared' ) 
        );
        $setting = new admin_setting_configselect ( $name, $title, $description, $default, $choices );
        $setting->set_updatedcallback ( 'theme_reset_all_caches' );
        $settingpage->add ( $setting );
        
        $name = 'theme_squared/fplink' . $i;
        $title = get_string ( 'fplink', 'theme_squared' ) . " $i";
        $description = get_string ( 'fplinkdesc', 'theme_squared' );
        $setting = new admin_setting_configtext ( $name, $title, $description, '', PARAM_URL );
        $setting->set_updatedcallback ( 'theme_reset_all_caches' );
        $settingpage->add ( $setting );
    }
    $ADMIN->add ( 'theme_squared', $settingpage );
}