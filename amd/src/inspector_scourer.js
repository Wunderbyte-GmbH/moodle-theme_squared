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
define(['jquery', 'jqueryui', 'core/log'], function($, jqui, log) {

    "use strict"; // jshint ;_;

    log.debug('Squared Inspector Scourer AMD initialised');

    return {
        init: function(data) {
            $(document).ready(function($, jqui) {

                log.debug('Squared Inspector Scourer AMD init');
                log.debug('Squared Inspector Scourer AJAX URL: ' + data.theme);

                $("#courseitemsearch").autocomplete({
                    source: data.theme,
                    appendTo: "#courseitemsearchresults",
                    minLength: 2,
                    select: function(event, ui) {
                        var url = ui.item.id;
                        if (url != '#') {
                            location.href = url;
                        }
                    }
                }).prop("disabled", false);
            });
        }
    }
});
/* jshint ignore:end */
