<?php
require_once("$CFG->libdir/coursecatlib.php");
defined('MOODLE_INTERNAL') || die;

if ($ADMIN->fulltree) {

	$name = 'theme_squared/logo';
	$title = get_string('logo','theme_squared');
	$description = get_string('logodesc', 'theme_squared');
	$setting = new admin_setting_configstoredfile($name, $title, $description, 'logo');
	$setting->set_updatedcallback('theme_reset_all_caches');
	$settings->add($setting);
	
	$name = 'theme_squared/pagelogo';
	$title = get_string('pagelogo','theme_squared');
	$description = get_string('pagelogodesc', 'theme_squared');
	$setting = new admin_setting_configstoredfile($name, $title, $description, 'pagelogo');
	$setting->set_updatedcallback('theme_reset_all_caches');
	$settings->add($setting);
	
	$name = 'theme_squared/footertext';
	$title = get_string('footer','theme_squared');
	$description = get_string('footerdesc', 'theme_squared');
	$setting = new admin_setting_configtext($name, $title, $description, '', PARAM_TEXT);
	$settings->add($setting);

	$name = 'theme_squared/youtubelink';
	$title = get_string('youtubelink','theme_squared');
	$description = get_string('youtubelinkdesc', 'theme_squared');
	$setting = new admin_setting_configtext($name, $title, $description, '', PARAM_TEXT);
	$settings->add($setting);

	$name = 'theme_squared/googlepluslink';
	$title = get_string('googlepluslink','theme_squared');
	$description = get_string('googlepluslinkdesc', 'theme_squared');
	$setting = new admin_setting_configtext($name, $title, $description, '', PARAM_TEXT);
	$settings->add($setting);

	$name = 'theme_squared/facebooklink';
	$title = get_string('facebooklink','theme_squared');
	$description = get_string('facebooklinkdesc', 'theme_squared');
	$setting = new admin_setting_configtext($name, $title, $description, '', PARAM_TEXT);
	$settings->add($setting);

	$name = 'theme_squared/twitterlink';
	$title = get_string('twitterlink','theme_squared');
	$description = get_string('twitterlinkdesc', 'theme_squared');
	$setting = new admin_setting_configtext($name, $title, $description, '', PARAM_TEXT);
	$settings->add($setting);
    
	// default block color setting
	$name = 'theme_squared/bgcolordefault';
	$title = get_string('bgcolordefault','theme_squared');
	$description = get_string('bgcolordefaultdesc', 'theme_squared');
	$default = '#11847D';
	$previewconfig = NULL;
	$setting = new admin_setting_configcolourpicker($name, $title, $description, $default, 	$previewconfig);
	$settings->add($setting);
	
	// category color settings 1

	$categorytree = coursecat::get(0)->get_children();
	$i = 0;
	foreach($categorytree as $key => $value){
	    
		$i++;
		$choices = array();
		
		$name = 'theme_squared/bgcolorheading'.$i;
		$heading = get_string('catcolorheading', 'theme_squared');
		$information = get_string('catcolorheadingdesc', 'theme_squared');
		$setting = new admin_setting_heading($name, $heading, $information);
		$settings->add($setting);
		
		$choices[$key] = $value->name;
		$name = 'theme_squared/bgcolor'.$i;
		$title = get_string('bgcolor','theme_squared',$choices[$key]);
		$description = get_string('bgcolordesc', 'theme_squared',$choices[$key]);
		$default = '#11847D';
		$previewconfig = NULL;
		$setting = new admin_setting_configcolourpicker($name, $title, $description, $default, $previewconfig);
		$settings->add($setting);

		$name = "theme_squared/bgcolorcat".$key;
		$title = get_string('bgcolorcat','theme_squared');
		$description =  get_string('bgcolorcatdesc', 'theme_squared');
		$default = $i;
		$choices = array($i => $value->name);
		$setting = new admin_setting_configselect($name, $title, $description, $default, $choices);
		$settings->add($setting);
	}

	// inside page header image setting
 
	$name = 'theme_squared/headerimagecourse';
	$title = get_string('headerimagecourse','theme_squared');
	$description = get_string('headerimagecoursedesc', 'theme_squared');
	$setting = new admin_setting_configstoredfile($name, $title, $description, 'headerimagecourse');
	$setting->set_updatedcallback('theme_reset_all_caches');
	$settings->add($setting);
	
	$name = 'theme_squared/numberofslides';
	$title = get_string('numberofslides','theme_squared');
	$description = get_string('numberofslidesdesc', 'theme_squared');
	$default = 3;
	$choices = array(
	        1 => 1,
	        2 => 2,
	        3 => 3,
	        4 => 4,
	        5 => 5
	);
	$setting = new admin_setting_configselect($name, $title, $description, $default, $choices);
	$settings->add($setting);
	
	
    // slideshow settings
	for($i = 1; $i < 6; $i++){
	    
	    $name = 'theme_squared/slideheading'.$i;
	    $heading = get_string('slideheading', 'theme_squared') ." $i";
	    $information = get_string('slideheadingdesc', 'theme_squared'). " $i";
	    $setting = new admin_setting_heading($name, $heading, $information);
	    $settings->add($setting);
	    
	    $name = 'theme_squared/slideimage'.$i;
	    $title = get_string('slideimage','theme_squared'). " $i";
	    $description = get_string('slideimagedesc', 'theme_squared');
	    $setting = new admin_setting_configstoredfile($name, $title, $description, 'slideimage'.$i);
	    $setting->set_updatedcallback('theme_reset_all_caches');
	    $settings->add($setting);
	    
	    $name = 'theme_squared/fptitle'.$i;
	    $title = get_string('fptitle','theme_squared'). " $i";
	    $description = get_string('fptitledesc', 'theme_squared');
	    $default = 'Photo from';
	    $setting = new admin_setting_configtext($name, $title, $description, $default);
	    $settings->add($setting);
	    
	    $name = 'theme_squared/fptext'.$i;
	    $title = get_string('fptext','theme_squared'). " $i";
	    $description = get_string('fptextdesc', 'theme_squared');
        switch ($i) {
            case 1:
                $default = 'Dave Clark flickr.com/photos/fotobydave CreativeCommmons';
                break;
            case 2:
                $default = 'eldeem flickr.com/photos/ltdemartinet CreativeCommmons';
                break;
            case 3:
                $default = ' Georgie Pauwels flickr.com/photos/frosch50 CreativeCommmons';
                break;
            default:
                $default = "Change me in the theme settings";
        }
	    $setting = new admin_setting_configtextarea($name, $title, $description, $default, PARAM_RAW);
	    $settings->add($setting);
	    
	    $name = 'theme_squared/fppos'.$i;
	    $title = get_string('fppos','theme_squared'). " $i";
	    $description = get_string('fpposdesc', 'theme_squared');
	    $default = 'Text on the left';
	    $choices = array(
	            '2' => get_string('fpposleft', 'theme_squared'),
	            '1' => get_string('fpposright', 'theme_squared')
	    );
	    $setting = new admin_setting_configselect($name, $title, $description, $default, $choices);
	    $settings->add($setting);
	    
	    $name = 'theme_squared/fplink'.$i;
	    $title = get_string('fplink','theme_squared'). " $i";
	    $description = get_string('fplinkdesc', 'theme_squared');
	    $setting = new admin_setting_configtext($name, $title, $description, '', PARAM_URL);
	    $settings->add($setting);
	     
	}

	$name = 'theme_squared/fpnews';
	$title = get_string('fpnews','theme_squared');
	$description = get_string('fpnewsdesc', 'theme_squared');
	$default = 'Here is some text';
	$setting = new admin_setting_configtextarea($name, $title, $description, $default, PARAM_CLEANHTML);
	$settings->add($setting);

	$name = 'theme_squared/alternateloginurl';
	$title = get_string('alternateloginurl','theme_squared');
	$description = get_string('alternateloginurldesc', 'theme_squared');
	$default = 0;
	$sql = "SELECT DISTINCT h.id, h.wwwroot, h.name, a.sso_jump_url, a.name as application
			FROM {mnet_host} h
			JOIN {mnet_host2service} m ON h.id = m.hostid
			JOIN {mnet_service} s ON s.id = m.serviceid
			JOIN {mnet_application} a ON h.applicationid = a.id
			WHERE s.name = ? AND h.deleted = ? AND m.publish = ?";
	$params = array('sso_sp', 0, 1);

	if (!empty($CFG->mnet_all_hosts_id)) {
		$sql .= " AND h.id <> ?";
		$params[] = $CFG->mnet_all_hosts_id;
	}

	if ($hosts = $DB->get_records_sql($sql, $params)) {
		$choices = array();
		$choices[0] = 'notset';
		foreach ($hosts as $id => $host){
			$choices[$id] = $host->name;
		}	
	} else {
		$choices = array();
		$choices[0] = 'notset';
	}
	$setting = new admin_setting_configselect($name, $title, $description, $default, $choices);
	$settings->add($setting);
	
	// Custom CSS
	$name = 'theme_squared/customcss';
	$title = get_string('customcss', 'theme_squared');
	$description = get_string('customcssdesc', 'theme_squared');
	$setting = new admin_setting_configtextarea($name, $title, $description, '');
	$settings->add($setting);

}