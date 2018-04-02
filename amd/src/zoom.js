/* This file is part of Moodle - http://moodle.org/
 *
 * Moodle is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Moodle is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Moodle.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * Squared theme with the underlying Bootstrap theme.
 *
 * @package    theme
 * @subpackage squared
 * @copyright  &copy; Onlinecampus Virtuelle PH, edulabs.org
 * @author     Bas Brands, David Bogner
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/* jshint ignore:start */
define(['jquery', 'core/log'], function($, log) {

    "use strict"; // jshint ;_;

    log.debug('Squared zoom AMD initialised');

    $(document).ready(function() {

        var onZoom = function() {
            var zoomin = $('body').hasClass('zoomin');
            if (zoomin) {
                $('body').removeClass('zoomin');
                $('body').addClass('nozoom');
                M.util.set_user_preference('theme_squared_zoom', 'nozoom');
            } else {
                $('body').removeClass('nozoom');
                $('body').addClass('zoomin');
                M.util.set_user_preference('theme_squared_zoom', 'zoomin');
            }
        };
        // When the button with class .moodlezoom is clicked fire the onZoom function.
        $('body').delegate('.moodlezoom', 'click', onZoom);
    });
});
/* jshint ignore:end */