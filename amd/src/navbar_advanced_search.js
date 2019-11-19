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

/*
Notes.

This is similar to inspector_scourer.js but is needed in case both are on the same page.
Based on code in core/search-input.js.
*/

/* jshint ignore:start */
define(['jquery', 'jqueryui', 'core/log'], function($, jqui, log) {

    "use strict"; // jshint ;_;

    log.debug('Squared Navbar Advanced Search AMD initialised');

    /*
     * This search box div node.
     *
     * @private
     */
    var wrapper = null;

    /*
     * Toggles the form visibility.
     *
     * @param {Event} ev
     * @method toggleForm
     * @private
     */
    var toggleSearch = function(ev) {
        if (wrapper.hasClass('expanded')) {
            hideSearch();
        } else {
            showSearch(ev);
        }
    };

    /*
     * Shows the search.
     *
     * @param {Event} ev
     * @method showForm
     * @private
     */
    var showSearch = function(ev) {

        // We are only interested in enter and space keys (accessibility).
        if (ev.type === 'keydown' && ev.keyCode !== 13 && ev.keyCode !== 32) {
            return;
        }

        if (ev.type === 'keydown') {
            ev.preventDefault();
        }

        wrapper.addClass('expanded');
        wrapper.find('input').focus();
    };

    /*
     * Hides the form.
     *
     * @method hideForm
     * @private
     */
    var hideSearch = function() {
        wrapper.removeClass('expanded');
    };

    return {
        init: function(data) {
            $(document).ready(function($, jqui) {

                log.debug('Squared Navbar Advanced Search AMD init');
                log.debug('Squared Navbar Advanced Search AJAX ID: ' + data.id);
                log.debug('Squared Navbar Advanced Search AJAX URL: ' + data.theme);

                $("#navbaradvsearch").autocomplete({
                    source: data.theme,
                    appendTo: "#navbaradvresults",
                    minLength: 2,
                    select: function(event, ui) {
                        var url = ui.item.id;
                        if (url != '#') {
                            location.href = url;
                        }
                    }
                }).prop("disabled", false);

                wrapper = $('#' + data.id);
                wrapper.on('click mouseover keydown', '#sqsearchbutton', toggleSearch);
            });
        }
    }
});
/* jshint ignore:end */
