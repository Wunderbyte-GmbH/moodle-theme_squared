<?php

/**
 * Theme version info
 *
 * @package    theme_squared
 * @copyright  2013 Onlinecampus Virtuelle PH www.virtuelle-ph.at
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

$plugin->version   = 2014042900; // The current module version (Date: YYYYMMDDXX)
$plugin->requires  = 2013050100; // Requires this Moodle version
$plugin->component = 'theme_squared'; // Full name of the plugin (used for diagnostics)
$plugin->dependencies = array(
    'theme_canvas'  => 2013050100,
    'theme_base'  => 2013050100,
);
