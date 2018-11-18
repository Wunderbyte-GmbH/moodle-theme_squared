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

    log.debug('Squared Category Course Search AMD initialised');

    return {
        init: function(data) {
            $(document).ready(function($) {

                log.debug('Squared Category Course Search AMD init');
                log.debug('Squared Category Course Search AJAX URL: ' + data.theme);
                var timeoutId;
                var searched = false;

                $('#sq-category-search').prop("disabled", false);
                $('#sq-category-search').on('change textInput input', function(){
                    var inputLength = $(this).val().length;
                    if (inputLength > 2) {
                        window.clearTimeout(timeoutId);
                        timeoutId = window.setTimeout(
                            function(sqs){
                                $.ajax({
                                url: data.theme,
                                    data: {'search': sqs.val(), 'categoryid': data.catid},
                                    dataType: 'html'
                                }).done(function(html){
                                    $("#sqccs").html(html);
                                    log.debug('Squared Category Course Search done: ' + html);
                                }).fail(function(){
                                    $("#sq-category-search").val('Category course search call failed');
                                });
                                searched = true;
                            },
                            500,
                            $(this)
                        );
                    } else if (inputLength == 0) {
                        // Get them all.
                        if (searched == true) {
                                $.ajax({
                                url: data.theme,
                                    data: {'categoryid': data.catid},
                                    dataType: 'html'
                                }).done(function(html){
                                    $("#sqccs").html(html);
                                    log.debug('Squared Category Course Search done: ' + html);
                                }).fail(function(){
                                    $("#sq-category-search").val('Category course search call failed');
                                });
                                searched = false;
                        }
                    }
                });
            });
        }
    };
});
/* jshint ignore:end */
