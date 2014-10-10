<?php
$hasnavbar = (empty($PAGE->layout_options['nonavbar']) && $PAGE->has_navbar());
$hassidepre = $PAGE->blocks->region_has_content('side-pre', $OUTPUT);
$hassidepost = $PAGE->blocks->region_has_content('side-post', $OUTPUT);
$showsidepre = $hassidepre && !$PAGE->blocks->region_completely_docked('side-pre', $OUTPUT);
$showsidepost = $hassidepost && !$PAGE->blocks->region_completely_docked('side-post', $OUTPUT);
$hascolorguide = $OUTPUT->squared_colorguide();
$custommenu = $OUTPUT->custom_menu();
$hascustommenu = (empty($PAGE->layout_options['nocustommenu']) && !empty($custommenu));

$fpnews = (!empty($PAGE->theme->settings->fpnews));
if ($fpnews) {$fpnews = $PAGE->theme->settings->fpnews;} else { $fpnews = ""; }


//get userpref for col open or closed
squared_initialise_colpos($PAGE);
$usercol = squared_get_colpos();

$bodyclasses = array();
if ($showsidepre && !$showsidepost) {
    $bodyclasses[] = 'side-pre-only';
} else if ($showsidepost && !$showsidepre) {
    $bodyclasses[] = 'side-post-only';
} else if (!$showsidepost && !$showsidepre) {
    $bodyclasses[] = 'content-only';
}
if ($hascustommenu) {
    $bodyclasses[] = 'has_custom_menu';
}
if ($hascustommenu) {
    $bodyclasses[] = 'has_navbar';
}
if ($hascolorguide) {
	$bodyclasses[] = "bgcolor$hascolorguide";
}
if(isguestuser()){
	$bodyclasses[] = 'guestuser';
}
$bodyclasses[] = "$usercol";

echo $OUTPUT->doctype(); ?>

<html <?php echo $OUTPUT->htmlattributes() ?>>
<head>
    <title><?php echo $PAGE->title ?></title>
    <link rel="shortcut icon" href="<?php echo $OUTPUT->pix_url('favicon', 'theme')?>" />
    <meta name="viewport" content="width=1218">
    <?php echo $OUTPUT->standard_head_html() ?>
</head>
<body id="<?php p($PAGE->bodyid) ?>" class="<?php p($PAGE->bodyclasses.' '.join(' ', $bodyclasses)) ?>">
<?php echo $OUTPUT->standard_top_of_body_html() ?>

<div id="page" class="coursepage1 coursepage categorypage1">
<div id="fuzz"></div>
	<div id="wrapper">
	
<!-- start OF header -->
	<div id="above-header">
	<div id="newsearch">
	<div id="navbox">
	<a href="#"><img src="<?php echo $OUTPUT->pix_url('nav', 'theme')?>" id="navover" alt="navhover" /></a>
	
<!-- start of custom menu -->	
<?php if ($hascustommenu) { ?>
<div id="menuwrap"><div id="custommenu"><?php echo $custommenu; ?></div></div>
<?php } ?>
<!-- end of menu -->
	
	</div>
	<form id="newsearchform" accept-charset="UTF-8" action="http://www.virtuelle-ph.at/mod/page/view.php">
	<div>
    <label for="newsearchfield"><?php echo get_string('search'); ?></label>
    <input id="newsearchfield" type="text" value="" name="newsearchfield"/>
    <input type="hidden" value="10375" name="id"/>
    <input type="submit" value="" id="newsearchbutton"/>
    </div>
    </form>
	</div>

		<div class="headermenu">
        		<?php
	        	   // echo $OUTPUT->login_info();
    	        	echo $OUTPUT->lang_menu();
	        	    echo $PAGE->headingmenu;
		        ?>	    
		</div>
	</div>
		<div id="page-header" class="page-header-home"><div id="page-header2">
			<a href="<?php p($CFG->wwwroot) ?>"><img src="<?php
			if(!empty($PAGE->theme->settings->pagelogo)) {
				echo $PAGE->theme->setting_file_url('pagelogo', 'pagelogo');
			} else {
				echo $OUTPUT->pix_url('generallogo', 'theme');
			}
			?>" id="logo" alt="logo" /></a>
	
		</div></div>
<!-- end of header -->		

	

<div id="page-content-wrapper">
<!-- start OF moodle CONTENT -->
 <div id="page-content">
        <div id="region-main-box">
            <div id="region-post-box">
            
                <div id="region-main-wrap">
                
<!-- start of navbar -->
<?php if ($hasnavbar) { ?>
        <div class="navbar clearfix">
        <div id="dock-control">
          <div id="dock-control-inner"></div>
          </div>
          <div class="breadcrumb"><?php echo $OUTPUT->navbar(); ?></div>
          <div class="navbutton"> <?php echo $PAGE->button; ?></div>
        </div>
<?php } ?>
<!-- end of navbar -->
                
                    <div id="region-main">
                        <div class="region-content">                      	
                            <?php echo $OUTPUT->main_content() ?>
                        </div>
                    </div>
                </div>
                
                <?php if ($hassidepre) { ?>
                <div id="region-pre" class="block-region">
                    <div class="region-content">
                        <?php echo $OUTPUT->blocks_for_region('side-pre') ?>
                    </div>
                </div>
                <?php } ?>
                
                <?php if ($hassidepost) { ?>
                <div id="region-post" class="block-region">
                    <div class="region-content">
                        <?php echo $OUTPUT->blocks_for_region('side-post') ?>
                    </div>
                </div>
                <?php } ?>
                
            </div>
        </div>
    </div>
<!-- end OF moodle CONTENT -->
</div>
<!-- end OF moodle CONTENT wrapper -->

<!-- start of new far left column -->
<div id="leftcolumn">
<div id="left-user">
	<div id="newlogin" class="outerleftblocks generalpage">
     <?php echo $OUTPUT->login_info(); ?>
	</div>
</div>
<div id="course-link-holder">
<?php if (isloggedin() && !isguestuser()) { ?>
<div class="innertube"> 
<a href="<?php p($CFG->wwwroot) ?>/my/"><?php p(get_string('mycourses')); ?></a>

<a href="<?php p($CFG->wwwroot) ?>/message/"><?php p(get_string('messaging','message')); ?></a>

<a href="<?php p($CFG->wwwroot) ?>/user/profile.php"><?php p(get_string('myprofile')); ?></a>
</div>
<?php } else { ?>
<div class="innertube"> 
<h3>News</h3>
<p>
<?php echo $fpnews ?>
</p>
</div>
<?php } ?>


</div>
</div>
<!-- end OF of new left -->

<!-- start of footer -->	
<div id="page-footer">
<div id="footer-left">
<?php
echo $OUTPUT->squared_textlinks('footer');
//echo $OUTPUT->login_info();
//echo $OUTPUT->home_link();
echo $OUTPUT->standard_footer_html();
?>
</div>

<div id="footer-right">
<?php
echo $OUTPUT->squared_socialicons();
?>
</div>

</div>
<!-- end of footer -->	

<div class="clearer"></div>

	</div><!-- end #wrapper -->
</div><!-- end #page -->	

<?php echo $OUTPUT->standard_end_of_body_html() ?>
</body>
</html>