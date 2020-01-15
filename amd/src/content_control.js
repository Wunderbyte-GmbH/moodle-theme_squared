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

                $('.sqcol .sqcontentcontrol').on('click', function() {
                    log.debug('Squared Content Control AMD clicked');

                    var $content = $(this).parent().parent().find('.sqcontent > .no-overflow');
                    if ($content.length) {
                        var $sqcontent = $(this).parent().parent().find('.sqcontent');
                        if ($content.hasClass('-expanded')) {
                            $content.removeClass('-expanded');
                            $sqcontent.removeClass('sqexpanded');
                        } else {
                            $content.addClass('-expanded');
                            $sqcontent.addClass('sqexpanded');
                        }
                    }
                    var $instancename = $(this).parent().parent().find('.instancename');
                    if ($instancename.length) {
                        if ($instancename.hasClass('-expanded')) {
                            $instancename.removeClass('-expanded');
                        } else {
                            $instancename.addClass('-expanded');
                        }
                    }
                    if ($(this).hasClass('-expanded')) {
                        $(this).find('i').removeClass('fa-chevron-circle-up').addClass('fa-chevron-circle-down');
                        $(this).find('.sqccclose').addClass('hidden').attr('aria-hidden', 'true');
                        $(this).find('.sqccopen').removeClass('hidden').attr('aria-hidden', 'false');
                        $(this).removeClass('-expanded');
                    } else {
                        $(this).find('i').removeClass('fa-chevron-circle-down').addClass('fa-chevron-circle-up');
                        $(this).find('.sqccopen').addClass('hidden').attr('aria-hidden', 'true');
                        $(this).find('.sqccclose').removeClass('hidden').attr('aria-hidden', 'false');
                        $(this).addClass('-expanded');
                    }
                });

            });
        }
    };
});
/* jshint ignore:end */
