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
 * Squared theme.
 *
 * @package    theme
 * @subpackage squared
 * @copyright  &copy; 2015-onwards G J Barnard in respect to modifications of the Clean theme.
 * @copyright  &copy; 2015-onwards Work undertaken for David Bogner of Edulabs.org.
 * @author     G J Barnard - gjbarnard at gmail dot com and {@link http://moodle.org/user/profile.php?id=442195}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace theme_squared;

class toolbox {
    static function get_top_level_categories() {
        global $CFG;
        include_once($CFG->libdir . '/coursecatlib.php');

        $categoryids = array();
        $categories = \coursecat::get(0)->get_children();  // Parent = 0 i.e. top-level categories only.

        foreach($categories as $category){
            $categoryids[$category->id] = $category->name;
        }

        return $categoryids;
    }
}
