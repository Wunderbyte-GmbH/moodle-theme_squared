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
 * The squared theme makes uses a custom version of squared blocks.
 *
 * @package theme_squared
 * @copyright 2018 onwards Onlinecampus Virtuelle PH
 * www.virtuelle-ph.at, David Bogner www.edulabs.org
 * @author G J Barnard - {@link http://moodle.org/user/profile.php?id=442195}
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/* jshint ignore:start */
define(['jquery', 'core/log'], function ($, log) {

    "use strict"; // jshint ;_;

    log.debug('Squared Category Course Search AMD initialised');

    return {
        init: function () {
            $(document).ready(function ($) {

                log.debug('Squared Frontpage Courses AMD init');

                var paginationAJAX = function (event, id) {
                    event.preventDefault();
                    var pagelinkurl = event.target.getAttribute('href');
                    if (pagelinkurl === null) {
                        // Might be a 'span' within the 'a'.
                        pagelinkurl = event.target.parentElement.getAttribute('href');
                    }
                    log.debug('Squared Frontpage Courses Page Link Id: ' + id);
                    log.debug('Squared Frontpage Courses Page Link AJAX URL: ' + pagelinkurl);
                    if (pagelinkurl !== null) { // Just in case!
                        $.ajax({
                            url: pagelinkurl,
                            dataType: 'html'
                        }).done(function (html) {
                            $(id).html(html);
                            pagination();
                            log.debug('Squared Frontpage Courses AJAX done: ' + html);
                        }).fail(function () {
                            log.debug('Squared Frontpage Courses AJAX failed: ' + id);
                        });
                    }
                };

                var pagination = function () {
                    $('#sqfac .pagination li.page-item:not(.active) .page-link').click(function (e) {
                        paginationAJAX(e, '#sqfac');
                    });
                    $('#sqfac .paging-showall a').click(function (e) {
                        paginationAJAX(e, '#sqfac');
                    });
                    $('#sqfac .paging-showperpage a').click(function (e) {
                        paginationAJAX(e, '#sqfac');
                    });
                    $('#sqfmc .pagination li.page-item:not(.active) .page-link').click(function (e) {
                        paginationAJAX(e, '#sqfmc');
                    });
                    $('#sqfmc .paging-showall a').click(function (e) {
                        paginationAJAX(e, '#sqfmc');
                    });
                    $('#sqfmc .paging-showperpage a').click(function (e) {
                        paginationAJAX(e, '#sqfmc');
                    });
                };

                pagination();
            });
        }
    };
});
/* jshint ignore:end */
