(function($){$.InFieldLabels=function(b,c,d){var f=this;f.$label=$(b);f.label=b;f.$field=$(c);f.field=c;f.$label.data("InFieldLabels",f);f.showing=true;f.init=function(){f.options=$.extend({},$.InFieldLabels.defaultOptions,d);if(f.$field.val()!=""){f.$label.hide();f.showing=false};f.$field.focus(function(){f.fadeOnFocus()}).blur(function(){f.checkForEmpty(true)}).bind('keydown.infieldlabel',function(e){f.hideOnChange(e)}).change(function(e){f.checkForEmpty()}).bind('onPropertyChange',function(){f.checkForEmpty()})};f.fadeOnFocus=function(){if(f.showing){f.setOpacity(f.options.fadeOpacity)}};f.setOpacity=function(a){f.$label.stop().animate({opacity:a},f.options.fadeDuration);f.showing=(a>0.0)};f.checkForEmpty=function(a){if(f.$field.val()==""){f.prepForShow();f.setOpacity(a?1.0:f.options.fadeOpacity)}else{f.setOpacity(0.0)}};f.prepForShow=function(e){if(!f.showing){f.$label.css({opacity:0.0}).show();f.$field.bind('keydown.infieldlabel',function(e){f.hideOnChange(e)})}};f.hideOnChange=function(e){if((e.keyCode==16)||(e.keyCode==9))return;if(f.showing){f.$label.hide();f.showing=false};f.$field.unbind('keydown.infieldlabel')};f.init()};$.InFieldLabels.defaultOptions={fadeOpacity:0.5,fadeDuration:300};$.fn.inFieldLabels=function(c){return this.each(function(){var a=$(this).attr('for');if(!a)return;var b=$("input#"+a+"[type='text'],"+"input#"+a+"[type='password'],"+"textarea#"+a);if(b.length==0)return;(new $.InFieldLabels(this,b[0],c))})}})(jQuery);

(function($) {

    var callback = function() { };
    // [up, up, down, down, left, right, left, right, b, a];
    var code = [38, 38, 40, 40, 37, 39, 37, 39, 66, 65];
    var i = 0;

    $.fn.konami = function(f) {
        if (typeof f == 'function') callback = f;
        return this.keydown(function(e) {
            /// increment i if the key pressed matched the next key in the sequence.
            /// if not, i goes back to zero, so you can start the code again.
            i = (e.keyCode == code[i]) ? i + 1 : 0;
            
            /// if i reaches the length of the code array, the code must have been entered properly
            if (i == code.length) {
                typeof callback == 'function' && callback();
                i = 0;
            }
        });
    };

})(jQuery);

$(document).ready(function(){

$("#login2 label, #newsearchform label").inFieldLabels();

//block selector for classes rather than css3
//$("#region-pre .region-content .block.vclass:eq(1)").addClass('block2');	
//$("#region-pre .region-content .block.vclass:eq(2)").addClass('block3');
//$("#region-pre .region-content .block:odd").addClass('rightblock');
$("#page-course-index-category #movecourses th.header").first().wrap('<div class="catheadwrap" />');
$("#custommenu ul > li").not("#custommenu ul li ul li").addClass('baritem');
//forum header list
$('#page-mod-forum-view .forumheaderlist thead').after('<div class="forumblank"></div>');
//html block links
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

//Coursepage blocks to remove showing of content when another block is clicked
$('.coursepage .block.block-hider-show .header').click(function(e) {
	if ( $('body').hasClass("editing") ) {
} else {
	$('.block.vclass').not($(this).parent()).addClass('hidden');
	return false;
}
});

//*custommenu hover for overlay */
$("#navbox").not(".ios #navbox,.android #navbox").hover(
  function () {
    //$(this).addClass("hover");
    $("#fuzz").css("height", $(document).height());
    $("#fuzz").fadeIn();
  },
  function () {
    //$(this).removeClass("hover");
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
            //alert('good job');
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
    //$(this).addClass("hover");
     $('body').addClass('overflow');
  },
  function () {
    //$(this).removeClass("hover");
     $('body').removeClass('overflow');
  }
);

//remove idock if editing is turned on
if ( $('body').hasClass("editing") ) {
	//$('body').removeClass('idock');
}

if ($('#page-site-index.notloggedin .block_rss_client').length){
    $("#page-site-index.notloggedin .block_rss_client .content").appendTo("#leftcolumn .innertube");
}

if ($('#page-login-index.notloggedin .block_rss_client').length){
    $("#page-login-index.notloggedin .block_rss_client .content").appendTo("#leftcolumn .innertube");
}

 $('#main-slider').flexslider({
        namespace           : "flex-",           //{NEW} String: Prefix string attached to the class of every element generated by the plugin
        selector            : ".scroll-header",    //{NEW} Selector: Must match a simple pattern. '{container} > {slide}' -- Ignore pattern at your own peril
        animation           : "fade",            //String: Select your animation type, "fade" or "slide"
        easing              : "swing",           //{NEW} String: Determines the easing method used in jQuery transitions. jQuery easing plugin is supported!
        direction           : "horizontal",      //String: Select the sliding direction, "horizontal" or "vertical"
        reverse             : false,             //{NEW} Boolean: Reverse the animation direction
        animationLoop       : true,              //Boolean: Should the animation loop? If false, directionNav will received "disable" classes at either end
        smoothHeight        : false,             //{NEW} Boolean: Allow height of the slider to animate smoothly in horizontal mode
        startAt             : 0,                 //Integer: The slide that the slider should start on. Array notation (0 = first slide)
        slideshow           : true,              //Boolean: Animate slider automatically
        slideshowSpeed      : 7000,              //Integer: Set the speed of the slideshow cycling, in milliseconds
        animationSpeed      : 600,               //Integer: Set the speed of animations, in milliseconds
        initDelay           : 0,                 //{NEW} Integer: Set an initialization delay, in milliseconds
        randomize           : false,             //Boolean: Randomize slide order
         
        // Usability features
        pauseOnAction       : true,              //Boolean: Pause the slideshow when interacting with control elements, highly recommended.
        pauseOnHover        : false,             //Boolean: Pause the slideshow when hovering over slider, then resume when no longer hovering
        useCSS              : true,              //{NEW} Boolean: Slider will use CSS3 transitions if available
        touch               : true,              //{NEW} Boolean: Allow touch swipe navigation of the slider on touch-enabled devices
        video               : false,             //{NEW} Boolean: If using video in the slider, will prevent CSS3 3D Transforms to avoid graphical glitches
         
        // Primary Controls
        controlNav          : false,              //Boolean: Create navigation for paging control of each clide? Note: Leave true for manualControls usage
        directionNav        : false,              //Boolean: Create navigation for previous/next navigation? (true/false)
        prevText            : "Previous",        //String: Set the text for the "previous" directionNav item
        nextText            : "Next",            //String: Set the text for the "next" directionNav item
         
        // Secondary Navigation
        keyboard            : true,              //Boolean: Allow slider navigating via keyboard left/right keys
        multipleKeyboard    : false,             //{NEW} Boolean: Allow keyboard navigation to affect multiple sliders. Default behavior cuts out keyboard navigation with more than one slider present.
        mousewheel          : false,             //{UPDATED} Boolean: Requires jquery.mousewheel.js (https://github.com/brandonaaron/jquery-mousewheel) - Allows slider navigating via mousewheel
        pausePlay           : false,             //Boolean: Create pause/play dynamic element
        pauseText           : 'Pause',           //String: Set the text for the "pause" pausePlay item
        playText            : 'Play',            //String: Set the text for the "play" pausePlay item
         
        // Special properties
        controlsContainer   : "",                //{UPDATED} Selector: USE CLASS SELECTOR. Declare which container the navigation elements should be appended too. Default container is the FlexSlider element. Example use would be ".flexslider-container". Property is ignored if given element is not found.
        manualControls      : "",                //Selector: Declare custom control navigation. Examples would be ".flex-control-nav li" or "#tabs-nav li img", etc. The number of elements in your controlNav should match the number of slides/tabs.
        sync                : "",                //{NEW} Selector: Mirror the actions performed on this slider with another slider. Use with care.
        asNavFor            : "",                //{NEW} Selector: Internal property exposed for turning the slider into a thumbnail navigation for another slider
    });


});