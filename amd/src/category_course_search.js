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
        init: function (data) {
            $(document).ready(function ($) {

                log.debug('Squared Category Course Search AMD init');
                log.debug('Squared Category Course Search Site URL: ' + data.siteurl);
                log.debug('Squared Category Course Search AJAX URL: ' + data.ajaxurl);
                var currentCategoryId = data.catid;
                var currentSort = data.sort;
                var timeoutId;
                var searched = false;

                var searchAJAX = function(search, searchSort, updateSearchSort, categoryId, updateCurrentCategoryId, url) {
                    categoryId = typeof categoryId !== 'undefined' ? categoryId : currentCategoryId;
                    updateCurrentCategoryId = typeof updateCurrentCategoryId !== 'undefined' ? updateCurrentCategoryId : false;
                    updateSearchSort = typeof updateSearchSort !== 'undefined' ? updateSearchSort : false;
                    url = typeof url !== 'undefined' ? url : data.ajaxurl;
                    searchSort = typeof searchSort !== 'undefined' ? searchSort : currentSort;

                    $.ajax({
                        url: url,
                        data: {'sqcategorysearch': search, 'categoryid': categoryId, 'searchsort': searchSort},
                        dataType: 'html'
                    }).done(function (html) {
                        if (updateCurrentCategoryId === true) {
                            currentCategoryId = categoryId;
                        }
                        if (updateSearchSort === true) {
                            currentSort = searchSort;
                        }
                        $("#sqccs").html(html);
                        pagination();
                        log.debug('Squared Select Category Course Search done: ' + html);
                    }).fail(function () {
                        $("#sq-category-search").val('Select category course search call failed');
                    });
                };

                var paginationAJAX = function (event) {
                    event.preventDefault();
                    var pagelinkurl = event.target.getAttribute('href');
                    if (pagelinkurl === null) {
                        // Might be a 'span' within the 'a'.
                        pagelinkurl = event.target.parentElement.getAttribute('href');
                    }
                    log.debug('Squared Category Course Search Page Link AJAX URL: ' + pagelinkurl);
                    if (pagelinkurl !== null) { // Just in case!
                        searchAJAX($('#sq-category-search').val(), currentSort, false, currentCategoryId, false, pagelinkurl);
                    }
                };

                var pagination = function () {
                    $('#sqccs .pagination li.page-item:not(.active) .page-link').click(function (e) {
                        paginationAJAX(e);
                    });
                    $('#sqccs .paging a').click(function (e) {
                        paginationAJAX(e);
                    });
                };

                $('#sq-category-search').prop("disabled", false);
                $('#sq-category-search').on('change textInput input', function () {
                    var inputLength = $(this).val().length;
                    if (inputLength > 2) {
                        window.clearTimeout(timeoutId);
                        timeoutId = window.setTimeout(
                            function (sqs) {
                                searchAJAX(sqs.val());
                                searched = true;
                            },
                            500,
                            $(this)
                        );
                    } else if (inputLength === 0) {
                        // Get them all in the current category.
                        if (searched === true) {
                            searchAJAX('');
                            searched = false;
                        }
                    }
                });

                $('#sq-category-select').prop("disabled", false);
                $('#sq-category-select').on('change', function () {
                    var optionSelected = $("option:selected", this);
                    var optionSelectedCat = optionSelected.val();

                    if (optionSelected.data('differenttheme') == true) {
                        log.debug('Squared Select Category Course Redirect: ' + optionSelectedCat);
                        window.location.replace(data.siteurl + "?categoryid=" + optionSelectedCat);
                    } else {
                        var sqsValue = $('#sq-category-search').val();

                        log.debug('Squared Select Category Course Search: ' + optionSelectedCat + ' - ' + sqsValue);

                        var body = $('body');
                        if (body.hasClass('category-' + currentCategoryId)) {
                            body.removeClass('category-' + currentCategoryId);
                        }
                        if (optionSelectedCat !== '0') {
                            body.addClass('category-' + optionSelectedCat);
                        }

                        searchAJAX(sqsValue, currentSort, false, optionSelectedCat, true);
                    }
                    window.history.pushState(data.categorystr + " - " + optionSelectedCat, data.categorystr + " - " + optionSelectedCat, data.siteurl + "?categoryid=" + optionSelectedCat);
                });

                $('#sq-category-sort').prop("disabled", false);
                $('#sq-category-sort').on('change', function () {
                    var optionSelectedCat = $("option:selected", this).val();
                    var sqsValue = $('#sq-category-search').val();

                    log.debug('Squared Select Category Sort Course Search: ' + optionSelectedCat + ' - ' + sqsValue);

                    searchAJAX(sqsValue, optionSelectedCat, true);
                });

                pagination();
            });
        }
    };
});
/* jshint ignore:end */
