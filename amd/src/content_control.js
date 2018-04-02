// This file is part of the Squared theme for Moodle
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle. If not, see <http://www.gnu.org/licenses/>.

/**
 * This is the squared theme.
 *
 *
 * The squared theme makes uses a custom version of squared blocks
 *
 * @package theme_squared
 * @copyright 2018 onwards Onlinecampus Virtuelle PH
 * www.virtuelle-ph.at, David Bogner www.edulabs.org
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
 
/* jshint ignore:start */
define(['jquery', 'core/log'], function($, log) {

    "use strict"; // jshint ;_;

    log.debug('Squared Content Control AMD initialised');

    return {
        init: function() {
            $(document).ready(function($) {

                log.debug('Squared Content Control AMD init');

                $('.sqcol .contentafterlink .contentcontrol').on('click', function() {
                    log.debug('Squared Content Control AMD clicked');

                    var $content = $(this).parent().find('> .no-overflow');
                    if ($content.hasClass('-expanded')) {
                        $(this).find('i').removeClass('fa-minus-circle').addClass('fa-plus-circle');
                        $content.removeClass('-expanded');
                    } else {
                        $(this).find('i').removeClass('fa-plus-circle').addClass('fa-minus-circle');
                        $content.addClass('-expanded');
                    }
                });

            });
        }
    };
});
/* jshint ignore:end */
