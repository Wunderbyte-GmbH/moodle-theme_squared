<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * This is the squared theme.
 *
 * The squared theme makes uses a custom version of squared blocks
 *
 * @package theme_squared
 * @copyright 2016 onwards Onlinecampus Virtuelle PH
 * www.virtuelle-ph.at, David Bogner www.edulabs.org
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined ( 'MOODLE_INTERNAL' ) || die ();

$plugin->version = 2023060700;
$plugin->requires = 2022112800.00; // 4.1 (Build: 20221128).
$plugin->supported = array(401, 401);
 // Release name: Famous squares around the world followed by Moodle version followed by stability number (> 1 = stable) followed by revision.
$plugin->release = "Freyung 401.1.0";
$plugin->maturity = MATURITY_STABLE;
$plugin->component = 'theme_squared'; // Full name of the plugin (used for diagnostics).
$plugin->dependencies = array (
    'theme_boost' => 2022112800
);
