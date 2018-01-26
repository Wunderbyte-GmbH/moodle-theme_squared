<?php
$THEME->name = 'squared';

$THEME->parents = array ('bootstrap');
$THEME->parents_exclude_sheets = array('bootstrap' => array('moodle'));
$THEME->sheets = array('custom');
$THEME->lessfile = 'moodleallsquared';
$THEME->lessvariablescallback = 'theme_squared_less_variables';
$THEME->extralesscallback = 'theme_squared_extra_less';
$THEME->supportscssoptimisation = false;
$THEME->yuicssmodules = array();
$THEME->rendererfactory = 'theme_overridden_renderer_factory';
$THEME->csspostprocess = 'theme_squared_process_css';
$THEME->enable_dock = false;
$THEME->rarrow = ">";

$THEME->layouts = array(
    'base' => array(
        'file' => 'default.php',
        'regions' => array(),
    ),
    'standard' => array(
        'file' => 'default.php',
        'regions' => array('side-pre'),
        'defaultregion' => 'side-pre',
    ),
    'course' => array(
        'file' => 'default.php',
        'regions' => array('side-pre'),
        'defaultregion' => 'side-pre',
        'options' => array('langmenu' => true),
    ),
    'coursecategory' => array(
        'file' => 'default.php',
        'regions' => array('side-pre'),
        'defaultregion' => 'side-pre',
        'options' => array('categoryimage' => true),
    ),
    'incourse' => array(
        'file' => 'default.php',
        'regions' => array('side-pre'),
        'defaultregion' => 'side-pre',
        'options' => array('langmenu' => true),
    ),
    'frontpage' => array(
        'file' => 'default.php',
        'regions' => array('side-pre'),
        'defaultregion' => 'side-pre',
        'options' => array('categoryboxes' => true, 'notitle' => true, 'langmenu' => true),
    ),
    'admin' => array(
        'file' => 'default.php',
        'regions' => array('side-pre'),
        'defaultregion' => 'side-pre',
        'options' => array('fluid' => true),
    ),
    'mydashboard' => array(
        'file' => 'default.php',
        'regions' => array('side-pre'),
        'defaultregion' => 'side-pre',
        'options' => array('langmenu' => true),
    ),
    'mypublic' => array(
        'file' => 'default.php',
        'regions' => array('side-pre'),
        'defaultregion' => 'side-pre',
    ),
    'login' => array(
        'file' => 'default.php',
        'regions' => array(),
        'options' => array('notitle' => true, 'langmenu' => true),
    ),
    'popup' => array(
        'file' => 'popup.php',
        'regions' => array(),
        'options' => array('nofooter' => true, 'nonavbar' => true),
    ),
    'frametop' => array(
        'file' => 'default.php',
        'regions' => array(),
        'options' => array('nofooter' => true, 'nocoursefooter' => true),
    ),
    'embedded' => array(
        'file' => 'embedded.php',
        'regions' => array()
    ),
    'maintenance' => array(
        'file' => 'maintenance.php',
        'regions' => array(),
    ),
    'print' => array(
        'file' => 'default.php',
        'regions' => array(),
        'options' => array('nofooter' => true, 'nonavbar' => false),
    ),
    'redirect' => array(
        'file' => 'embedded.php',
        'regions' => array(),
    ),
    'report' => array(
        'file' => 'default.php',
        'regions' => array('side-pre'),
        'defaultregion' => 'side-pre',
    ),
    'secure' => array(
        'file' => 'default.php',
        'regions' => array('side-pre'),
        'defaultregion' => 'side-pre'
    ),
);

$THEME->javascripts_footer = array(
    'squared'
);
