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
 * Squared theme.
 *
 * @package    theme
 * @subpackage squared
 * @copyright  &copy; 2015-onwards G J Barnard in respect to modifications of the Clean theme.
 * @copyright  &copy; 2015-onwards Work undertaken for David Bogner of Edulabs.org.
 * @author     G J Barnard - gjbarnard at gmail dot com and {@link http://moodle.org/user/profile.php?id=442195}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/* jshint ignore:start */
define(['jquery', 'theme_squared/jqueryeasing', 'theme_squared/jqueryflexslider', 'theme_squared/infieldlabels', 'theme_squared/konami', 'core/log'], function($, e, fs, ifl, k, log) {

    "use strict"; // jshint ;_;

    console.log('Squared Custom AMD');

    $(document).ready(function(){

        $("#login2 label, #newsearchform label").inFieldLabels();

        // Block selector for classes rather than css3.
        $("#page-course-index-category #movecourses th.header").first().wrap('<div class="catheadwrap" />');
        $("#custommenu ul > li").not("#custommenu ul li ul li").addClass('baritem');
        // Forum header list.
        $('#page-mod-forum-view .forumheaderlist thead').after('<div class="forumblank"></div>');
        // HTML block links.
        $('#page-site-index .block_html.block').click(function(e) {
            if ( $('body').hasClass("editing") ) {
            } else {
                var testlink = $(this).find(".content a:first").attr('href');
                if (testlink !== undefined) {
                    window.location = testlink;
                }
                return false;
            }
        });

        // Coursepage blocks to remove showing of content when another block is clicked.
        $('.coursepage .block.block-hider-show .header').click(function(e) {
            if ( $('body').hasClass("editing") ) {
            } else {
                $('.block.vclass').not($(this).parent()).addClass('hidden');
                return false;
            }
        });

        // custommenu hover for overlay.
        $("#navbox").not(".ios #navbox,.android #navbox").hover(
            function () {
                $("#fuzz").css("height", $(document).height());
                $("#fuzz").fadeIn();
            },
            function () {
                $("#fuzz").fadeOut();
            }
        );

        $('.ios #navover, .android #navover').toggle(function() {
            $(this).removeClass('ioshover');
            $("#fuzz").css("height", $(document).height());
            $("#fuzz").css("width", $(document).width());
            $("#fuzz, #menuwrap").fadeIn();
            $('.custom_menu_submenu').not(".cus2tom_menu_submenu .custom_menu_submenu").removeClass('yui3-menu-hidden').addClass('iosnothidden');
        }, function() {
            $("#fuzz, #menuwrap").fadeOut();
            $(this).addClass('ioshover');
        });

        $('.ios .block.hidden.vclass, .android .block.hidden.vclass').not('.ios .coursepage .block.hidden.vclass, .android .coursepage .block.hidden.vclass').toggle(function() {
            $(this).closest('div.content').show();
        }, function() {
            $(this).closest('div.content').hide();
        });

        $('#login_username2, #newsearchfield').konami(function() {
            $('#logo').removeClass('tada');
            $("html, body").animate({ scrollTop: 0 }, "slow");
            $('#logo').addClass('tada');
            setTimeout(function() {
                $('#logo').addClass('hinge');
            }, 2000);
        });

        $('#dock-control').click(function(e) {
            if ( $('body').hasClass("idock") ) {
                $('body').removeClass('idock');
                var slids = "noidock";
                M.util.set_user_preference('theme_squared_chosen_colpos', slids);
            } else {
                $('body').addClass('idock');
                var slids = "idock";
                M.util.set_user_preference('theme_squared_chosen_colpos', slids);
            }
        });

        $(".idock .block").not(".ios #navbox,.android #navbox").hover(
            function () {
                // $(this).addClass("hover");
                $('body').addClass('overflow');
            },
            function () {
                // $(this).removeClass("hover");
                $('body').removeClass('overflow');
            }
        );

        if ($('#page-site-index.notloggedin .block_rss_client').length){
            $("#page-site-index.notloggedin .block_rss_client .content").appendTo("#leftcolumn .innertube");
        }

        if ($('#page-login-index.notloggedin .block_rss_client').length){
            $("#page-login-index.notloggedin .block_rss_client .content").appendTo("#leftcolumn .innertube");
        }

        $('#main-slider').flexslider({
            namespace           : "flex-",           // {NEW} String: Prefix string attached to the class of every element generated by the plugin.
            selector            : ".scroll-header",  // {NEW} Selector: Must match a simple pattern. '{container} > {slide}' -- Ignore pattern at your own peril.
            animation           : "fade",            // String: Select your animation type, "fade" or "slide".
            easing              : "swing",           // {NEW} String: Determines the easing method used in jQuery transitions. jQuery easing plugin is supported!
            direction           : "horizontal",      // String: Select the sliding direction, "horizontal" or "vertical".
            reverse             : false,             // {NEW} Boolean: Reverse the animation direction.
            animationLoop       : true,              // Boolean: Should the animation loop? If false, directionNav will received "disable" classes at either end.
            smoothHeight        : false,             // {NEW} Boolean: Allow height of the slider to animate smoothly in horizontal mode.
            startAt             : 0,                 // Integer: The slide that the slider should start on. Array notation (0 = first slide).
            slideshow           : true,              // Boolean: Animate slider automatically.
            slideshowSpeed      : 7000,              // Integer: Set the speed of the slideshow cycling, in milliseconds.
            animationSpeed      : 600,               // Integer: Set the speed of animations, in milliseconds.
            initDelay           : 0,                 // {NEW} Integer: Set an initialization delay, in milliseconds.
            randomize           : false,             // Boolean: Randomize slide order.

            // Usability features
            pauseOnAction       : true,              // Boolean: Pause the slideshow when interacting with control elements, highly recommended.
            pauseOnHover        : false,             // Boolean: Pause the slideshow when hovering over slider, then resume when no longer hovering.
            useCSS              : true,              // {NEW} Boolean: Slider will use CSS3 transitions if available.
            touch               : true,              // {NEW} Boolean: Allow touch swipe navigation of the slider on touch-enabled devices.
            video               : false,             // {NEW} Boolean: If using video in the slider, will prevent CSS3 3D Transforms to avoid graphical glitches.

            // Primary Controls
            controlNav          : false,             // Boolean: Create navigation for paging control of each clide? Note: Leave true for manualControls usage.
            directionNav        : false,             // Boolean: Create navigation for previous/next navigation? (true/false).
            prevText            : "Previous",        // String: Set the text for the "previous" directionNav item.
            nextText            : "Next",            // String: Set the text for the "next" directionNav item.

            // Secondary Navigation
            keyboard            : true,              // Boolean: Allow slider navigating via keyboard left/right keys.
            multipleKeyboard    : false,             // {NEW} Boolean: Allow keyboard navigation to affect multiple sliders. Default behavior cuts out keyboard navigation with more than one slider present.
            mousewheel          : false,             // {UPDATED} Boolean: Requires jquery.mousewheel.js (https://github.com/brandonaaron/jquery-mousewheel) - Allows slider navigating via mousewheel.
            pausePlay           : false,             // Boolean: Create pause/play dynamic element.
            pauseText           : 'Pause',           // String: Set the text for the "pause" pausePlay item.
            playText            : 'Play',            // String: Set the text for the "play" pausePlay item.

            // Special properties
            controlsContainer   : "",                // {UPDATED} Selector: USE CLASS SELECTOR. Declare which container the navigation elements should be appended too. Default container is the FlexSlider element. Example use would be ".flexslider-container". Property is ignored if given element is not found.
            manualControls      : "",                // Selector: Declare custom control navigation. Examples would be ".flex-control-nav li" or "#tabs-nav li img", etc. The number of elements in your controlNav should match the number of slides/tabs.
            sync                : "",                // {NEW} Selector: Mirror the actions performed on this slider with another slider. Use with care.
            asNavFor            : "",                // {NEW} Selector: Internal property exposed for turning the slider into a thumbnail navigation for another slider.
        });
    });

});
/* jshint ignore:end */
