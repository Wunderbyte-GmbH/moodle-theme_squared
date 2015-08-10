<?php
$THEME->name = 'squared';

$THEME->parents = array (
        'canvas',
        'base' 
);
// $THEME->parents_exclude_sheets = array('base'=>array('pagelayout'),'canvas'=>array('pagelayout') );

// below are our theme's css sheets
$THEME->sheets = array (
        'font',
        'core',
        'menu',
        'blocks',
        'course',
        'dock',
        'pagelayout',

        // use for two column layouts
        'forum',
        'effects',
        'mod',

        // Grid format override.
        'grid',

        // Custom css.
        'custom'
);

$THEME->enable_dock = false;

$THEME->editor_sheets = array (
        'editor' 
);

$THEME->layouts = array (
        
        // to create side pre or side post layout remove one or the other from all below
        'base' => array (
                'file' => 'general.php',
                'regions' => array () 
        ),
        
        // Standard layout with blocks, this is recommended for most pages with general information
        'standard' => array (
                'file' => 'general.php',
                'regions' => array (
                        'side-pre' 
                ),
                'defaultregion' => 'side-pre' 
        ),
        
        // Main course page
        'course' => array (
                'file' => 'general.php',
                'regions' => array (
                        'side-pre' 
                ),
                'defaultregion' => 'side-pre',
                'options' => array (
                        'langmenu' => true 
                ) 
        ),
        'coursecategory' => array (
                'file' => 'category.php',
                'regions' => array (
                        'side-pre' 
                ),
                'defaultregion' => 'side-pre',
                'options' => array (
                        'nonavbar' => false 
                ) 
        ),
        
        // part of course, typical for modules - default page layout if $cm specified in require_login()
        'incourse' => array (
                'file' => 'general.php',
                'regions' => array (
                        'side-pre' 
                ),
                'defaultregion' => 'side-pre' 
        ),
        
        // The site home page.
        'frontpage' => array (
                'file' => 'frontpage.php',
                'regions' => array (
                        'side-pre' 
                ),
                'defaultregion' => 'side-pre' 
        ),
        
        // Server administration scripts.
        'admin' => array (
                'file' => 'general.php',
                'regions' => array (
                        'side-pre' 
                ),
                'defaultregion' => 'side-pre' 
        ),
        
        // My dashboard page
        'mydashboard' => array (
                'file' => 'general.php',
                'regions' => array (
                        'side-pre' 
                ),
                'defaultregion' => 'side-pre',
                'options' => array (
                        'langmenu' => true 
                ) 
        ),
        
        // My public page
        'mypublic' => array (
                'file' => 'general.php',
                'regions' => array (
                        'side-pre' 
                ),
                'defaultregion' => 'side-pre' 
        ),
        'login' => array (
                'file' => 'general.php',
                'regions' => array (),
                'options' => array (
                        'langmenu' => true 
                ) 
        ),
        
        // Pages that appear in pop-up windows - no navigation, no blocks, no header.
        'popup' => array (
                'file' => 'general.php',
                'regions' => array (),
                'options' => array (
                        'nofooter' => true,
                        'nonavbar' => true,
                        'nocustommenu' => true,
                        'nologininfo' => true 
                ) 
        ),
        
        // No blocks and minimal footer - used for legacy frame layouts only!
        'frametop' => array (
                'file' => 'general.php',
                'regions' => array (),
                'options' => array (
                        'nofooter' => true 
                ) 
        ),
        
        // Embeded pages, like iframe/object embeded in moodleform - it needs as much space as possible
        'embedded' => array (
                'theme' => 'canvas',
                'file' => 'embedded.php',
                'regions' => array (),
                'options' => array (
                        'nofooter' => true,
                        'nonavbar' => true,
                        'nocustommenu' => true 
                ) 
        ),
        
        // Used during upgrade and install, and for the 'This site is undergoing maintenance' message.
        // This must not have any blocks, and it is good idea if it does not have links to
        // other places - for example there should not be a home link in the footer...
        'maintenance' => array (
                'file' => 'maintenance.php',
                'regions' => array (),
                'options' => array (
                        'noblocks' => true,
                        'nofooter' => true,
                        'nonavbar' => true,
                        'nocustommenu' => true 
                ) 
        ),
        
        // Should display the content and basic headers only.
        'print' => array (
                'file' => 'general.php',
                'regions' => array (),
                'options' => array (
                        'noblocks' => true,
                        'nofooter' => true,
                        'nonavbar' => false,
                        'nocustommenu' => true 
                ) 
        ),
        
        // The pagelayout used when a redirection is occuring.
        'redirect' => array (
                'theme' => 'canvas',
                'file' => 'embedded.php',
                'regions' => array (),
                'options' => array (
                        'nofooter' => true,
                        'nonavbar' => true,
                        'nocustommenu' => true 
                ) 
        ),
        
        // The pagelayout used for reports
        'report' => array (
                'file' => 'general.php',
                'regions' => array (
                        'side-pre' 
                ),
                'defaultregion' => 'side-pre' 
        ) 
);

/**
 * List of javascript files that need to included on each page
 */
$THEME->javascripts_footer = array(
    'squared'
);

$THEME->rendererfactory = 'theme_overridden_renderer_factory';
$THEME->csspostprocess = 'theme_squared_process_css';